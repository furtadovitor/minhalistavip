<?php

namespace App\Services;

use App\Models\PedidoModel;

/**
 * Conciliação de pedidos pendentes.
 *
 * Rotina pensada para rodar por cron (comando `pedidos:conciliar`):
 *  1. consulta no gateway os pedidos pendentes que já têm transação — assim
 *     recupera pagamentos cujo webhook se perdeu;
 *  2. só depois expira os pedidos que passaram da validade, evitando marcar
 *     como 'expirado' um pedido que na verdade foi pago.
 */
class ConciliacaoService
{
    protected PedidoModel $pedidos;

    protected PixService $pix;

    protected PagamentoService $pagamentos;

    public function __construct(
        ?PedidoModel $pedidos = null,
        ?PixService $pix = null,
        ?PagamentoService $pagamentos = null
    ) {
        $this->pedidos    = $pedidos ?? new PedidoModel();
        $this->pix        = $pix ?? new PixService();
        $this->pagamentos = $pagamentos ?? new PagamentoService();
    }

    /**
     * Consulta o gateway para cada pedido pendente com transação e confirma os
     * que já foram pagos (fonte de verdade: o gateway).
     *
     * @return array{consultados: int, confirmados: int}
     */
    public function confirmarPendentes(): array
    {
        $pedidos    = $this->pedidos->pendentesComTransacao();
        $gateway    = $this->pix->gateway();
        $confirmados = 0;

        foreach ($pedidos as $pedido) {
            $transacaoId = (string) $pedido->gateway_transacao_id;
            $consulta    = $gateway->consultar($transacaoId);

            if ($consulta === null || ($consulta['status'] ?? '') !== 'pago') {
                continue;
            }

            $payload = is_array($consulta['payload'] ?? null) ? $consulta['payload'] : [];

            $resultado = $this->pagamentos->confirmar(
                $gateway->nome(),
                $transacaoId,
                $payload,
                'pix.conciliacao'
            );

            if ($resultado['ok']) {
                $confirmados++;
            }
        }

        return ['consultados' => count($pedidos), 'confirmados' => $confirmados];
    }

    /**
     * Expira pedidos pendentes cuja validade passou.
     */
    public function expirarVencidos(): int
    {
        return $this->pedidos->expirarVencidos(date('Y-m-d H:i:s'));
    }

    /**
     * Executa a conciliação completa: confirma pagamentos e depois expira.
     *
     * @return array{consultados: int, confirmados: int, expirados: int}
     */
    public function executar(): array
    {
        $conciliacao = $this->confirmarPendentes();
        $expirados   = $this->expirarVencidos();

        return [
            'consultados' => $conciliacao['consultados'],
            'confirmados' => $conciliacao['confirmados'],
            'expirados'   => $expirados,
        ];
    }
}
