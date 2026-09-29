<?php

namespace App\Services;

use App\Entities\Pedido;
use App\Models\MuralRecadoModel;
use App\Models\PagamentoModel;
use App\Models\PedidoModel;
use App\Models\WebhookLogModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Confirmação de pagamentos e efeitos colaterais do pedido pago (Regra 2.2):
 *  1. marca o pedido como 'pago';
 *  2. incrementa as cotas vendidas do presente;
 *  3. credita a carteira do organizador;
 *  4. publica a mensagem do convidado no mural.
 *
 * O processamento é idempotente: webhooks repetidos (mesma referência)
 * não geram crédito em duplicidade.
 */
class PagamentoService
{
    protected PagamentoModel $pagamentos;

    protected PedidoModel $pedidos;

    protected WebhookLogModel $logs;

    protected CarteiraService $carteira;

    protected BaseConnection $db;

    public function __construct(
        ?PagamentoModel $pagamentos = null,
        ?PedidoModel $pedidos = null,
        ?WebhookLogModel $logs = null,
        ?CarteiraService $carteira = null,
        ?BaseConnection $db = null
    ) {
        $this->pagamentos = $pagamentos ?? new PagamentoModel();
        $this->pedidos    = $pedidos ?? new PedidoModel();
        $this->logs       = $logs ?? new WebhookLogModel();
        $this->carteira   = $carteira ?? new CarteiraService();
        $this->db         = $db ?? db_connect();
    }

    /**
     * Registra a transação criada no gateway (PIX pendente).
     *
     * @param array<string, mixed> $cobranca
     */
    public function registrar(Pedido $pedido, array $cobranca): int
    {
        $this->pagamentos->insert([
            'pedido_id'            => (int) $pedido->id,
            'gateway'              => $cobranca['gateway'],
            'gateway_transacao_id' => $cobranca['gateway_transacao_id'],
            'tipo'                 => $cobranca['tipo'] ?? 'pix',
            'status'               => $cobranca['status'] ?? 'pendente',
            'valor'                => $cobranca['valor'] ?? $pedido->valor_total,
            'payload'              => json_encode($cobranca, JSON_UNESCAPED_UNICODE),
        ]);

        return (int) $this->pagamentos->getInsertID();
    }

    /**
     * Confirma um webhook pelo identificador da transação no gateway.
     *
     * @param array<string, mixed> $payload
     * @return array{ok: bool, mensagem: string}
     */
    public function confirmar(string $gateway, string $referenciaId, array $payload = [], string $eventoTipo = 'pix.pago'): array
    {
        $agora = date('Y-m-d H:i:s');

        $this->logs->insert([
            'gateway'       => $gateway,
            'evento_tipo'   => $eventoTipo,
            'referencia_id' => $referenciaId,
            'payload'       => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'processado'    => 0,
            'criado_em'     => $agora,
        ]);

        $logId = (int) $this->logs->getInsertID();

        $pedido = $this->pedidos->porTransacao($referenciaId);

        if ($pedido === null) {
            $this->logs->update($logId, ['mensagem' => 'Pedido não encontrado para a transação ' . $referenciaId]);

            return ['ok' => false, 'mensagem' => 'Pedido não encontrado para a transação informada.'];
        }

        return $this->confirmarPedido($pedido, $gateway, $payload, $logId);
    }

    /**
     * Efetiva o pagamento de um pedido já localizado.
     *
     * @param array<string, mixed> $payload
     * @return array{ok: bool, mensagem: string}
     */
    public function confirmarPedido(Pedido $pedido, string $gateway, array $payload = [], ?int $logId = null): array
    {
        $log = function (array $dados) use ($logId): void {
            if ($logId !== null) {
                $this->logs->update($logId, $dados);
            }
        };

        if ($pedido->estaPago()) {
            $log(['processado' => 1, 'mensagem' => 'Pedido já constava como pago (idempotência).']);

            return ['ok' => true, 'mensagem' => 'Pedido já estava pago.'];
        }

        if ($pedido->foiCancelado()) {
            $log(['processado' => 1, 'mensagem' => 'Pedido cancelado/expirado; pagamento ignorado.']);

            return ['ok' => false, 'mensagem' => 'Pedido não está disponível para pagamento.'];
        }

        $agora = date('Y-m-d H:i:s');

        $this->db->transStart();

        $this->pedidos->update((int) $pedido->id, [
            'status'  => 'pago',
            'pago_em' => $agora,
        ]);

        if (! empty($pedido->presente_evento_id)) {
            $this->db->table('presentes_evento')
                ->where('id', (int) $pedido->presente_evento_id)
                ->set('quantidade_vendida', 'quantidade_vendida + ' . max(1, (int) $pedido->quantidade), false)
                ->update();
        }

        $this->carteira->creditarPedido($pedido);
        $this->pagamentos->marcarPago((int) $pedido->id);
        $this->publicarRecado($pedido, $agora);

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            $log(['processado' => 0, 'mensagem' => 'Falha ao processar o pagamento no banco de dados.']);

            return ['ok' => false, 'mensagem' => 'Falha ao processar o pagamento.'];
        }

        $log(['processado' => 1, 'mensagem' => 'Pagamento confirmado com sucesso.']);

        return ['ok' => true, 'mensagem' => 'Pagamento confirmado.'];
    }

    /**
     * Publica a mensagem enviada no checkout (Regra 2.2).
     */
    private function publicarRecado(Pedido $pedido, string $agora): void
    {
        $mensagem = trim((string) $pedido->mensagem);

        if ($mensagem === '') {
            return;
        }

        (new MuralRecadoModel())->insert([
            'evento_id'    => (int) $pedido->evento_id,
            'pedido_id'    => (int) $pedido->id,
            'nome_autor'   => (string) $pedido->nome_convidado,
            'mensagem'     => $mensagem,
            'status'       => 'publicado',
            'publicado_em' => $agora,
        ]);
    }
}
