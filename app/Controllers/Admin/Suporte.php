<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\SuporteService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Chat de suporte — lado atendente (SuperAdmin): fila de chamados, "assumir"
 * e conversa. O "assumir" é atômico para nunca duplicar entre atendentes.
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

    public function index()
    {
        $this->suporte->fecharInativos();

        return $this->render('admin/suporte/index', [
            'titulo'    => 'Suporte',
            'fila'      => $this->suporte->fila(),
            'usuarioId' => $this->usuarioId(),
        ]);
    }

    public function fila()
    {
        $this->suporte->fecharInativos();

        return $this->response->setJSON([
            'ok'   => true,
            'fila' => $this->suporte->fila(),
        ]);
    }

    public function mensagens($id = null)
    {
        $conv = $this->buscar((int) $id);

        $this->suporte->visto((int) $conv['id'], 'atendente');

        return $this->response->setJSON(
            $this->payload($this->suporte->conversa((int) $conv['id']), (int) $this->request->getGet('desde'))
        );
    }

    public function assumir($id = null)
    {
        $this->buscar((int) $id);

        $resultado = $this->suporte->assumir((int) $id, $this->usuarioId());

        if (! $resultado['ok']) {
            return $this->response->setJSON([
                'ok'   => false,
                'erro' => 'Esta conversa já foi assumida por outro atendente.',
                'csrf' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'ok'       => true,
            'csrf'     => csrf_hash(),
            'conversa' => $this->conversaPublica($this->suporte->comNaoLidas($resultado['conversa'], 'atendente')),
        ]);
    }

    public function enviar($id = null)
    {
        $conv  = $this->buscar((int) $id);
        $texto = trim((string) $this->request->getPost('texto'));

        if ($texto === '') {
            return $this->response->setJSON(['ok' => false, 'erro' => 'Escreva uma mensagem.', 'csrf' => csrf_hash()]);
        }

        $this->suporte->enviar((int) $conv['id'], 'atendente', $this->usuarioId(), $texto);

        return $this->response->setJSON(
            $this->payload($this->suporte->conversa((int) $conv['id']), 0)
        );
    }

    public function encerrar($id = null)
    {
        $this->buscar((int) $id);

        $this->suporte->encerrar((int) $id, 'atendente', $this->usuarioId());

        return $this->response->setJSON(['ok' => true, 'csrf' => csrf_hash()]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buscar(int $id): array
    {
        $conv = $this->suporte->conversa($id);

        if ($conv === null) {
            throw PageNotFoundException::forPageNotFound('Conversa não encontrada.');
        }

        return $conv;
    }

    /**
     * @param array<string, mixed> $conv
     * @return array<string, mixed>
     */
    private function payload(array $conv, int $desde): array
    {
        $conv      = $this->suporte->comNaoLidas($conv, 'atendente');
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
            'conversa' => $this->conversaPublica($conv),
            'mensagens' => $saida,
            'ultimo_id' => $ultimo,
        ];
    }

    /**
     * @param array<string, mixed> $conv
     * @return array<string, mixed>
     */
    private function conversaPublica(array $conv): array
    {
        return [
            'id'            => (int) $conv['id'],
            'protocolo'     => $conv['protocolo'],
            'status'        => $conv['status'],
            'canal'         => $conv['canal'],
            'nome'          => $conv['nome_exibicao'] ?? $this->suporte->nomeExibicao($conv),
            'email'         => $conv['email'] ?? null,
            'telefone'      => $conv['telefone'] ?? null,
            'assunto'       => $conv['assunto'] ?? null,
            'evento_id'     => ! empty($conv['evento_id']) ? (int) $conv['evento_id'] : null,
            'atendente_id'  => ! empty($conv['atendente_id']) ? (int) $conv['atendente_id'] : null,
            'nao_lidas'     => (int) ($conv['nao_lidas'] ?? 0),
        ];
    }
}
