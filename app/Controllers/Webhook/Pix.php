<?php

namespace App\Controllers\Webhook;

use App\Controllers\BaseController;
use App\Services\PagamentoService;
use App\Services\PixService;

/**
 * Endpoint de webhook do gateway PIX.
 *
 * Rota: POST /webhooks/pix (isenta de CSRF em Config\Filters).
 * Autenticação simples por token compartilhado (X-Webhook-Token ou campo
 * `token`). A confirmação é idempotente (PagamentoService).
 *
 * Exemplo de corpo:
 *   { "transacao_id": "SBXMLV20260929ABC123", "status": "pago", "valor": 330.00 }
 */
class Pix extends BaseController
{
    public function receber()
    {
        $pix = new PixService();

        $token = $this->request->getHeaderLine('X-Webhook-Token');

        if ($token === '') {
            $token = (string) ($this->request->getPost('token') ?? $this->request->getGet('token'));
        }

        if (! $pix->tokenValido($token)) {
            return $this->response->setStatusCode(401)
                ->setJSON(['ok' => false, 'mensagem' => 'Token de webhook inválido.']);
        }

        $dados = $this->request->getJSON(true);

        if (! is_array($dados)) {
            $dados = $this->request->getPost();
        }

        $transacaoId = (string) ($dados['transacao_id'] ?? $dados['gateway_transacao_id'] ?? $dados['referencia_id'] ?? '');
        $status      = strtolower((string) ($dados['status'] ?? 'pago'));
        $gateway     = (string) ($dados['gateway'] ?? PixService::GATEWAY);

        if ($transacaoId === '') {
            return $this->response->setStatusCode(422)
                ->setJSON(['ok' => false, 'mensagem' => 'Informe o transacao_id.']);
        }

        if (! in_array($status, ['pago', 'paid', 'approved', 'aprovado', 'confirmed'], true)) {
            return $this->response->setJSON(['ok' => true, 'mensagem' => 'Status ignorado: ' . $status]);
        }

        $resultado = (new PagamentoService())->confirmar($gateway, $transacaoId, $dados, 'pix.pago');

        return $this->response->setStatusCode($resultado['ok'] ? 200 : 404)->setJSON($resultado);
    }
}
