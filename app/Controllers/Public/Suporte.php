<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Services\SuporteService;

/**
 * Chat de suporte ao vivo — lado cliente (organizador no painel ou visitante
 * do site). Usa polling e retorna JSON; o token CSRF é devolvido em cada
 * resposta porque o CI4 o regenera a cada POST.
 *
 * Visitante sem login informa nome, telefone e e-mail e recebe um CÓDIGO de
 * atendimento (ex.: SUP-8FK2ZP) para retomar a conversa depois. O código expira
 * 10 dias após o evento (ou, sem evento, 10 dias após a última mensagem).
 */
class Suporte extends BaseController
{
    protected SuporteService $suporte;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->suporte = new SuporteService();
    }

    /**
     * Estado atual (heartbeat + mensagens novas). Usado pelo polling.
     */
    public function estado()
    {
        $identidade = $this->identidade();
        $conv       = $this->suporte->conversaAberta($identidade);

        if ($conv === null) {
            return $this->json($this->vazio($identidade));
        }

        if ($this->suporte->expirou($conv)) {
            $this->session->remove('suporte_conversa_id');

            return $this->json($this->vazio($identidade) + ['expirou' => true]);
        }

        // Presença só é registrada com o widget aberto (heartbeat).
        if ($this->request->getGet('presenca')) {
            $this->suporte->visto((int) $conv['id'], 'cliente');
        }

        return $this->json($this->payload($conv, (int) $this->request->getGet('desde')));
    }

    /**
     * Envia uma mensagem do cliente (cria a conversa na primeira mensagem).
     */
    public function enviar()
    {
        $texto = trim((string) $this->request->getPost('texto'));

        if ($texto === '') {
            return $this->json(['ok' => false, 'erro' => 'Escreva uma mensagem.', 'csrf' => csrf_hash()]);
        }

        $identidade = $this->identidade();
        $conv       = $this->suporte->conversaAberta($identidade);

        if ($conv !== null && $this->suporte->expirou($conv)) {
            $this->session->remove('suporte_conversa_id');

            return $this->json([
                'ok'      => false,
                'expirado' => true,
                'erro'    => 'Este código expirou. Inicie um novo atendimento.',
                'csrf'    => csrf_hash(),
            ]);
        }

        if ($conv === null) {
            $dados = [];

            if ($identidade['usuario_id'] === null) {
                $dados = $this->identificacaoDoPost();

                if (isset($dados['erro'])) {
                    return $this->json([
                        'ok'            => false,
                        'precisa_dados' => true,
                        'faltando'      => $dados['faltando'],
                        'erro'          => $dados['erro'],
                        'csrf'          => csrf_hash(),
                    ]);
                }
            } else {
                $dados = ['nome' => $identidade['nome'], 'email' => $identidade['email']];
            }

            $dados['evento_id'] = $this->request->getPost('evento_id');

            $conv = $this->suporte->abrir($identidade, (string) $this->request->getPost('canal'), $dados);
        }

        $this->session->set('suporte_conversa_id', (int) $conv['id']);

        $this->suporte->enviar((int) $conv['id'], 'cliente', $identidade['usuario_id'], $texto);

        return $this->json($this->payload($this->suporte->conversa((int) $conv['id']), 0));
    }

    /**
     * Retoma uma conversa pelo código de atendimento informado pelo visitante.
     */
    public function retomar()
    {
        // Limita tentativas de adivinhar o código de atendimento por IP.
        if (! service('throttler')->check('suporte_retomar_' . md5($this->request->getIPAddress()), 10, 600)) {
            return $this->json([
                'ok'   => false,
                'erro' => 'Muitas tentativas. Aguarde alguns minutos e tente novamente.',
                'csrf' => csrf_hash(),
            ]);
        }

        $codigo = (string) $this->request->getPost('codigo');
        $conv   = $this->suporte->porCodigo($codigo);

        if ($conv === null) {
            return $this->json([
                'ok'   => false,
                'erro' => 'Código não encontrado. Confira e tente novamente.',
                'csrf' => csrf_hash(),
            ]);
        }

        if ($this->suporte->expirou($conv)) {
            return $this->json([
                'ok'      => false,
                'expirado' => true,
                'erro'    => 'Este código expirou e não pode mais ser usado para contato.',
                'csrf'    => csrf_hash(),
            ]);
        }

        $conv = $this->suporte->retomar((int) $conv['id'], $this->identidade());

        if ($conv === []) {
            return $this->json(['ok' => false, 'erro' => 'Não foi possível retomar a conversa.', 'csrf' => csrf_hash()]);
        }

        $this->session->set('suporte_conversa_id', (int) $conv['id']);

        return $this->json($this->payload($conv, 0));
    }

    /**
     * Encerra a conversa aberta do cliente.
     */
    public function encerrar()
    {
        $identidade = $this->identidade();
        $conv       = $this->suporte->conversaAberta($identidade);

        if ($conv !== null) {
            $this->suporte->encerrar((int) $conv['id'], 'cliente', $identidade['usuario_id']);
        }

        $this->session->remove('suporte_conversa_id');

        return $this->json(['ok' => true, 'csrf' => csrf_hash()]);
    }

    /**
     * @param array<string, mixed> $identidade
     * @return array<string, mixed>
     */
    private function vazio(array $identidade): array
    {
        return [
            'ok'          => true,
            'csrf'        => csrf_hash(),
            'conversa'    => null,
            'autenticado' => $identidade['usuario_id'] !== null,
            'nome'        => $identidade['nome'],
        ];
    }

    /**
     * Valida nome, telefone e e-mail do visitante. Retorna os dados limpos ou
     * ['erro' => ..., 'faltando' => [...]].
     *
     * @return array<string, mixed>
     */
    private function identificacaoDoPost(): array
    {
        $nome     = trim((string) $this->request->getPost('nome'));
        $telefone = trim((string) $this->request->getPost('telefone'));
        $email    = trim((string) $this->request->getPost('email'));

        $faltando = [];

        if (mb_strlen($nome) < 2) {
            $faltando[] = 'nome';
        }
        if (strlen((string) preg_replace('/\D+/', '', $telefone)) < 8) {
            $faltando[] = 'telefone';
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $faltando[] = 'e-mail';
        }

        if ($faltando !== []) {
            return ['erro' => 'Para começar, informe nome, telefone e e-mail.', 'faltando' => $faltando];
        }

        return ['nome' => $nome, 'telefone' => $telefone, 'email' => $email];
    }

    /**
     * @param array<string, mixed> $conv
     * @return array<string, mixed>
     */
    private function payload(array $conv, int $desde): array
    {
        $conv      = $this->suporte->comNaoLidas($conv, 'cliente');
        $mensagens = $this->suporte->mensagens((int) $conv['id'], $desde);

        $ultimo = $desde;
        $saida  = [];

        foreach ($mensagens as $m) {
            $ultimo  = max($ultimo, (int) $m['id']);
            $saida[] = [
                'id'    => (int) $m['id'],
                'autor' => $m['autor_tipo'],
                'texto' => $m['texto'],
                'hora'  => date('H:i', strtotime((string) $m['criado_em'])),
            ];
        }

        return [
            'ok'       => true,
            'csrf'     => csrf_hash(),
            'conversa' => [
                'id'            => (int) $conv['id'],
                'protocolo'     => $conv['protocolo'],
                'status'        => $conv['status'],
                'canal'         => $conv['canal'],
                'nao_lidas'     => (int) $conv['nao_lidas'],
                'tem_atendente' => ! empty($conv['atendente_id']),
                'anonimo'       => empty($conv['usuario_id']),
                'expira_em'     => $this->suporte->expiracao($conv),
            ],
            'mensagens' => $saida,
            'ultimo_id' => $ultimo,
        ];
    }

    /**
     * @return array{usuario_id: int|null, token: string, nome: string|null, email: string|null, conversa_id: int|null}
     */
    private function identidade(): array
    {
        $usuarioId = $this->auth->estaLogado() ? $this->usuarioId() : null;

        $token = (string) $this->session->get('suporte_token');

        if ($token === '') {
            $token = bin2hex(random_bytes(16));
            $this->session->set('suporte_token', $token);
        }

        $conversaId = (int) $this->session->get('suporte_conversa_id');

        return [
            'usuario_id'  => $usuarioId > 0 ? $usuarioId : null,
            'token'       => $token,
            'nome'        => $this->usuario?->nome,
            'email'       => $this->usuario?->email,
            'conversa_id' => $conversaId > 0 ? $conversaId : null,
        ];
    }

    /**
     * @param array<string, mixed> $dados
     */
    private function json(array $dados): \CodeIgniter\HTTP\Response
    {
        return $this->response->setJSON($dados);
    }
}
