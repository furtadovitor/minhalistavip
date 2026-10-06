<?php

namespace App\Controllers\Webhook;

use App\Controllers\BaseController;
use App\Services\PagamentoService;
use App\Services\PixService;

/**
 * Endpoint de webhook do gateway PIX ativo (sandbox ou Mercado Pago).
 *
 * Rota: POST /webhooks/pix (isenta de CSRF em Config\Filters).
 * A autenticidade é validada pelo gateway selecionado (token compartilhado no
 * sandbox; assinatura x-signature no Mercado Pago). A confirmação é idempotente
 * (PagamentoService), então notificações repetidas não duplicam crédito.
 */
class Pix extends BaseController
{
    public function receber()
    {
        $gateway = (new PixService())->gateway();

        $corpoBruto = (string) $this->request->getBody();

        $dados = $this->request->getJSON(true);

        if (! is_array($dados)) {
            $dados = $this->request->getPost();
        }

        if (! is_array($dados)) {
            $dados = [];
        }

        // Token via query string — apenas fora de produção (testes manuais/sandbox).
        if (ENVIRONMENT !== 'production') {
            $token = $this->request->getGet('token');

            if (is_string($token) && $token !== '' && ! isset($dados['token'])) {
                $dados['token'] = $token;
            }
        }

        $headers = [
            'x-signature'     => $this->request->getHeaderLine('x-signature'),
            'x-request-id'    => $this->request->getHeaderLine('x-request-id'),
            'x-webhook-token' => $this->request->getHeaderLine('X-Webhook-Token'),
        ];

        if (! $gateway->validarAssinatura($headers, $corpoBruto, $dados)) {
            return $this->response->setStatusCode(401)
                ->setJSON(['ok' => false, 'mensagem' => 'Assinatura de webhook inválida.']);
        }

        $transacaoId = $gateway->transacaoIdDoWebhook($dados);

        if ($transacaoId === null || $transacaoId === '') {
            return $this->response->setStatusCode(422)
                ->setJSON(['ok' => false, 'mensagem' => 'Notificação sem transação de pagamento.']);
        }

        $consulta = $gateway->consultar($transacaoId);

        if ($consulta !== null) {
            $dados['consulta'] = $consulta['payload'] ?? $consulta;
            $status            = strtolower((string) ($consulta['status'] ?? ''));
        } else {
            // Sem consulta ao gateway, exige status explícito no payload
            // (nunca assume "pago" — evita confirmação forjada).
            $status = strtolower((string) ($dados['status'] ?? ''));
        }

        if (! in_array($status, ['pago', 'paid', 'approved', 'aprovado', 'confirmed'], true)) {
            return $this->response->setJSON(['ok' => true, 'mensagem' => 'Status ignorado: ' . ($status !== '' ? $status : 'desconhecido')]);
        }

        $resultado = (new PagamentoService())->confirmar($gateway->nome(), $transacaoId, $dados, 'pix.pago');

        return $this->response->setStatusCode($resultado['ok'] ? 200 : 404)->setJSON($resultado);
    }
}
