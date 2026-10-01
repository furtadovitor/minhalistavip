<?php

namespace App\Services\Pix;

use App\Entities\Pedido;

/**
 * Contrato comum dos gateways PIX.
 *
 * O restante do sistema (CheckoutService, PagamentoService, Webhook) fala
 * apenas com esta interface, então trocar de provedor é mudar a configuração
 * `pix_gateway` — sem alterar o fluxo do pedido nem a conciliação.
 */
interface GatewayPixInterface
{
    /**
     * Identificador do gateway gravado em `pedidos.gateway`.
     */
    public function nome(): string;

    /**
     * O gateway cria a cobrança junto com o pedido?
     *
     * Sandbox: sim (gera o BR Code na hora). Mercado Pago via Checkout Bricks:
     * não — o pagamento só é criado quando o convidado envia o Brick.
     */
    public function cobrarNaCriacao(): bool;

    /**
     * Cria a cobrança PIX do pedido e devolve os dados já normalizados.
     *
     * @return array{
     *     gateway: string,
     *     gateway_transacao_id: string,
     *     tipo: string,
     *     status: string,
     *     valor: float,
     *     expira_em: string|null,
     *     copia_e_cola: string,
     *     chave?: string,
     *     recebedor?: string,
     *     qr_code_base64?: string,
     *     ticket_url?: string,
     *     payload_bruto?: array<string, mixed>
     * }
     */
    public function gerarCobranca(Pedido $pedido): array;

    /**
     * Extrai o id da transação de um payload de webhook.
     * Devolve null quando a notificação não se refere a um pagamento.
     *
     * @param array<string, mixed> $dados
     */
    public function transacaoIdDoWebhook(array $dados): ?string;

    /**
     * Consulta o status atual no provedor. Devolve null quando o provedor não
     * exige consulta (o status vem no próprio webhook).
     *
     * @return array{status: string, valor?: float, payload?: array<string, mixed>}|null
     */
    public function consultar(string $transacaoId): ?array;

    /**
     * Valida a autenticidade do webhook.
     *
     * @param array<string, string> $headers cabeçalhos com nomes em minúsculas
     * @param array<string, mixed>  $dados
     */
    public function validarAssinatura(array $headers, string $corpoBruto, array $dados): bool;

    /**
     * Identificador local do pedido antes de existir a transação no provedor.
     */
    public function transacaoIdLocal(Pedido $pedido): string;
}
