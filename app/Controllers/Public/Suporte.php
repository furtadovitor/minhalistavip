<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Services\SuporteService;

/**
 * Chat de suporte ao vivo — lado cliente (organizador no painel ou visitante
 * do site). Usa polling e retorna JSON; o token CSRF é devolvido em cada
 * resposta porque o CI4 o regenera a cada POST.
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
            return $this->json([
                'ok'          => true,
                'csrf'        => csrf_hash(),
                'conversa'    => null,
                'autenticado' => $identidade['usuario_id'] !== null,
                'nome'        => $identidade['nome'],
            ]);
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

        if ($conv === null) {
            $nome = trim((string) $this->request->getPost('nome'));

            if ($identidade['usuario_id'] === null && $nome === '') {
                return $this->json([
                    'ok'          => false,
                    'precisa_nome' => true,
                    'erro'        => 'Informe seu nome para começar o atendimento.',
                    'csrf'        => csrf_hash(),
                ]);
            }

            $conv = $this->suporte->abrir($identidade, (string) $this->request->getPost('canal'), [
                'nome'      => $nome !== '' ? $nome : $identidade['nome'],
                'email'     => trim((string) $this->request->getPost('email')) ?: $identidade['email'],
                'evento_id' => $this->request->getPost('evento_id'),
            ]);
        }

        $this->suporte->enviar((int) $conv['id'], 'cliente', $identidade['usuario_id'], $texto);

        return $this->json($this->payload($this->suporte->conversa((int) $conv['id']), 0));
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

        return $this->json(['ok' => true, 'csrf' => csrf_hash()]);
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
                'id'         => (int) $conv['id'],
                'protocolo'  => $conv['protocolo'],
                'status'     => $conv['status'],
                'canal'      => $conv['canal'],
                'nao_lidas'  => (int) $conv['nao_lidas'],
                'tem_atendente' => ! empty($conv['atendente_id']),
            ],
            'mensagens' => $saida,
            'ultimo_id' => $ultimo,
        ];
    }

    /**
     * @return array{usuario_id: int|null, token: string, nome: string|null, email: string|null}
     */
    private function identidade(): array
    {
        $usuarioId = $this->auth->estaLogado() ? $this->usuarioId() : null;

        $token = (string) $this->session->get('suporte_token');

        if ($token === '') {
            $token = bin2hex(random_bytes(16));
            $this->session->set('suporte_token', $token);
        }

        return [
            'usuario_id' => $usuarioId > 0 ? $usuarioId : null,
            'token'      => $token,
            'nome'       => $this->usuario?->nome,
            'email'      => $this->usuario?->email,
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
