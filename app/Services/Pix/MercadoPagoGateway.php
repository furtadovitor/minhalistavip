<?php

namespace App\Services\Pix;

use App\Entities\Pedido;
use App\Services\ConfiguracaoService;
use Config\Services;
use RuntimeException;
use Throwable;

/**
 * Gateway PIX do Mercado Pago.
 *
 * Cria a cobrança em /v1/payments (payment_method_id = pix), lê o QR Code /
 * Copia e Cola de `point_of_interaction` e confirma o pagamento consultando
 * /v1/payments/{id} quando chega a notificação de webhook.
 *
 * Configurações (painel do SuperAdmin):
 *  - mercadopago_access_token   (obrigatório)
 *  - mercadopago_webhook_secret (valida a assinatura x-signature; recomendado)
 */
class MercadoPagoGateway implements GatewayPixInterface
{
    public const NOME = 'mercadopago';

    private const BASE_URL = 'https://api.mercadopago.com';

    protected ConfiguracaoService $config;

    public function __construct(?ConfiguracaoService $config = null)
    {
        $this->config = $config ?? new ConfiguracaoService();
    }

    public function nome(): string
    {
        return self::NOME;
    }

    public function cobrarNaCriacao(): bool
    {
        return false;
    }

    /**
     * Public Key da aplicação, usada para inicializar o Checkout Bricks.
     */
    public function publicKey(): string
    {
        return trim($this->config->texto('mercadopago_public_key'));
    }

    /**
     * Cria o pagamento a partir dos dados enviados pelo Checkout Bricks
     * (PIX ou cartão de crédito/débito).
     *
     * O valor é sempre recalculado no servidor a partir do pedido — nunca
     * confiamos no `transaction_amount` devolvido pelo navegador.
     *
     * @param array<string, mixed> $dados formData devolvido pelo Brick
     * @return array<string, mixed>
     */
    public function pagar(Pedido $pedido, array $dados): array
    {
        $token = $this->accessToken();

        if ($token === '') {
            throw new RuntimeException('Gateway Mercado Pago sem "access token" configurado.');
        }

        $methodId = trim((string) ($dados['payment_method_id'] ?? ''));

        if ($methodId === '') {
            throw new RuntimeException('O Mercado Pago não informou o meio de pagamento.');
        }

        $pagamento = [
            'transaction_amount' => round((float) $pedido->valor_total, 2),
            'description'        => 'Presente - pedido ' . $pedido->protocolo,
            'external_reference' => (string) $pedido->protocolo,
            'payment_method_id'  => $methodId,
            'payer'              => $this->payerDoFormulario($dados, $pedido),
        ];

        foreach (['token', 'installments', 'issuer_id', 'payment_method_option_id', 'processing_mode'] as $campo) {
            $valor = $dados[$campo] ?? null;

            if ($valor !== null && $valor !== '') {
                $pagamento[$campo] = $valor;
            }
        }

        // Cartão entra no fluxo 3DS 2.0 (challenge só quando o emissor exigir).
        if ($methodId !== 'pix') {
            $pagamento['three_d_secure_mode'] = 'optional';
        }

        $notificacao = $this->urlWebhook();

        if ($notificacao !== null) {
            $pagamento['notification_url'] = $notificacao;
        }

        $idempotencia = $pedido->protocolo . '-' . bin2hex(random_bytes(6));

        $resposta = $this->enviar('POST', '/v1/payments', $token, $pagamento, $idempotencia);

        $id = (string) ($resposta['id'] ?? '');

        if ($id === '') {
            throw new RuntimeException('Mercado Pago recusou o pagamento: ' . $this->mensagemErro($resposta));
        }

        $transacao = $resposta['point_of_interaction']['transaction_data'] ?? [];

        return [
            'gateway'              => self::NOME,
            'gateway_transacao_id' => $id,
            'tipo'                 => (string) ($resposta['payment_type_id'] ?? $methodId),
            'status'               => $this->normalizarStatus((string) ($resposta['status'] ?? 'pending')),
            'status_detail'        => (string) ($resposta['status_detail'] ?? ''),
            'valor'                => (float) ($resposta['transaction_amount'] ?? $pedido->valor_total),
            'expira_em'            => $this->dataExpiracao($resposta),
            'copia_e_cola'         => (string) ($transacao['qr_code'] ?? ''),
            'chave'                => $methodId === 'pix' ? 'Mercado Pago' : null,
            'recebedor'            => $methodId === 'pix' ? 'Mercado Pago' : null,
            'qr_code_base64'       => (string) ($transacao['qr_code_base64'] ?? ''),
            'ticket_url'           => (string) ($transacao['ticket_url'] ?? ''),
            'three_ds_info'        => $resposta['three_ds_info'] ?? null,
            'payload_bruto'        => $resposta,
        ];
    }

    public function gerarCobranca(Pedido $pedido): array
    {
        $token = $this->accessToken();

        if ($token === '') {
            throw new RuntimeException('Gateway Mercado Pago sem "access token" configurado.');
        }

        $cobranca = [
            'transaction_amount' => round((float) $pedido->valor_total, 2),
            'description'        => 'Presente - pedido ' . $pedido->protocolo,
            'payment_method_id'  => 'pix',
            'external_reference' => (string) $pedido->protocolo,
            'payer'              => $this->payer($pedido),
        ];

        $notificacao = $this->urlWebhook();

        if ($notificacao !== null) {
            $cobranca['notification_url'] = $notificacao;
        }

        $resposta = $this->enviar('POST', '/v1/payments', $token, $cobranca, (string) $pedido->protocolo);

        $id = (string) ($resposta['id'] ?? '');

        if ($id === '') {
            throw new RuntimeException('Mercado Pago não retornou o identificador da cobrança: ' . $this->mensagemErro($resposta));
        }

        $transacao = $resposta['point_of_interaction']['transaction_data'] ?? [];
        $copia     = (string) ($transacao['qr_code'] ?? '');

        if ($copia === '') {
            throw new RuntimeException('Mercado Pago não retornou o QR Code PIX da cobrança.');
        }

        return [
            'gateway'              => self::NOME,
            'gateway_transacao_id' => $id,
            'tipo'                 => 'pix',
            'status'               => $this->normalizarStatus((string) ($resposta['status'] ?? 'pending')),
            'valor'                => (float) ($resposta['transaction_amount'] ?? $pedido->valor_total),
            'expira_em'            => $this->dataExpiracao($resposta),
            'copia_e_cola'         => $copia,
            'chave'                => 'Mercado Pago',
            'recebedor'            => 'Mercado Pago',
            'qr_code_base64'       => (string) ($transacao['qr_code_base64'] ?? ''),
            'ticket_url'           => (string) ($transacao['ticket_url'] ?? ''),
            'payload_bruto'        => $resposta,
        ];
    }

    public function transacaoIdDoWebhook(array $dados): ?string
    {
        $tipo = (string) ($dados['type'] ?? $dados['topic'] ?? '');

        if ($tipo !== '' && $tipo !== 'payment') {
            return null;
        }

        $id = $dados['data']['id'] ?? $dados['data.id'] ?? $dados['id'] ?? null;

        if ($id === null || $id === '') {
            return null;
        }

        return (string) $id;
    }

    public function consultar(string $transacaoId): ?array
    {
        $token = $this->accessToken();

        if ($token === '') {
            return null;
        }

        $resposta = $this->enviar('GET', '/v1/payments/' . rawurlencode($transacaoId), $token);

        if (empty($resposta['id'])) {
            return null;
        }

        return [
            'status'  => $this->normalizarStatus((string) ($resposta['status'] ?? 'pending')),
            'valor'   => (float) ($resposta['transaction_amount'] ?? 0),
            'payload' => $resposta,
        ];
    }

    public function validarAssinatura(array $headers, string $corpoBruto, array $dados): bool
    {
        $segredo = $this->config->texto('mercadopago_webhook_secret');

        if ($segredo === '') {
            return true;
        }

        $assinatura = (string) ($headers['x-signature'] ?? '');

        if ($assinatura === '') {
            return false;
        }

        $partes = [];
        foreach (explode(',', $assinatura) as $trecho) {
            [$chave, $valor] = array_pad(explode('=', trim($trecho), 2), 2, '');
            $partes[$chave] = $valor;
        }

        $ts  = (string) ($partes['ts'] ?? '');
        $v1  = (string) ($partes['v1'] ?? '');
        $rid = (string) ($headers['x-request-id'] ?? '');
        $id  = (string) ($dados['data']['id'] ?? $dados['data.id'] ?? '');

        if ($ts === '' || $v1 === '') {
            return false;
        }

        $manifesto = 'id:' . $id . ';request-id:' . $rid . ';ts:' . $ts . ';';
        $esperado  = hash_hmac('sha256', $manifesto, $segredo);

        return hash_equals($esperado, $v1);
    }

    public function transacaoIdLocal(Pedido $pedido): string
    {
        return '';
    }

    /**
     * @return array<string, mixed>
     */
    private function payer(Pedido $pedido): array
    {
        $email = trim((string) $pedido->email_convidado);

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $email = $this->config->texto('email_suporte', 'pagamentos@minhalistavip.com.br');
        }

        $partes = preg_split('/\s+/', trim((string) $pedido->nome_convidado), 2) ?: [];
        $nome   = $partes[0] ?? 'Convidado';
        $sobre  = $partes[1] ?? '';

        return array_filter([
            'email'      => $email,
            'first_name' => $nome,
            'last_name'  => $sobre,
        ], static fn ($valor): bool => $valor !== '');
    }

    /**
     * Monta o `payer` a partir do formData do Brick, completando com os dados
     * do pedido. O documento (CPF/CNPJ) é obrigatório para cartão.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    private function payerDoFormulario(array $dados, Pedido $pedido): array
    {
        $enviado = is_array($dados['payer'] ?? null) ? $dados['payer'] : [];

        $email = trim((string) ($enviado['email'] ?? $pedido->email_convidado));

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $email = $this->config->texto('email_suporte', 'pagamentos@minhalistavip.com.br');
        }

        $payer = ['email' => $email];

        $numero = preg_replace('/\D/', '', (string) ($enviado['identification']['number'] ?? ''));

        if ($numero !== '') {
            $payer['identification'] = [
                'type'   => (string) ($enviado['identification']['type'] ?? 'CPF'),
                'number' => $numero,
            ];
        }

        $partes = preg_split('/\s+/', trim((string) $pedido->nome_convidado), 2) ?: [];
        $payer['first_name'] = $partes[0] ?? 'Convidado';

        if (! empty($partes[1])) {
            $payer['last_name'] = $partes[1];
        }

        return $payer;
    }

    private function accessToken(): string
    {
        return trim($this->config->texto('mercadopago_access_token'));
    }

    /**
     * URL pública de notificação, ou null quando não há uma válida.
     *
     * O Mercado Pago rejeita a cobrança se o `notification_url` não for uma URL
     * pública (ex.: `http://localhost/...`). Nesse caso omitimos o campo e o MP
     * usa o webhook cadastrado no painel da aplicação.
     */
    private function urlWebhook(): ?string
    {
        $url = trim($this->config->texto('mercadopago_notification_url'));

        if ($url === '') {
            $url = rtrim(base_url(), '/') . '/webhooks/pix';
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $host    = strtolower((string) parse_url($url, PHP_URL_HOST));
        $esquema = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($host === '' || in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return null;
        }

        if (! in_array($esquema, ['http', 'https'], true)) {
            return null;
        }

        return $url;
    }

    /**
     * @param array<string, mixed>|null $corpo
     * @return array<string, mixed>
     */
    private function enviar(string $metodo, string $caminho, string $token, ?array $corpo = null, ?string $idempotencia = null): array
    {
        $opcoes = [
            'headers' => array_filter([
                'Authorization'    => 'Bearer ' . $token,
                'Content-Type'     => 'application/json',
                'Accept'           => 'application/json',
                'X-Idempotency-Key' => $idempotencia,
            ]),
            'http_errors' => false,
            'timeout'     => 20,
        ];

        if ($corpo !== null) {
            $opcoes['json'] = $corpo;
        }

        try {
            $resposta = Services::curlrequest()->request($metodo, self::BASE_URL . $caminho, $opcoes);
        } catch (Throwable $e) {
            throw new RuntimeException('Falha ao comunicar com o Mercado Pago: ' . $e->getMessage());
        }

        $status = $resposta->getStatusCode();
        $corpo  = (string) $resposta->getBody();

        if ($status >= 400) {
            log_message('error', 'Mercado Pago HTTP {status} em {caminho}: {corpo}', [
                'status'  => $status,
                'caminho' => $caminho,
                'corpo'   => $corpo,
            ]);
        }

        $decodificado = json_decode($corpo, true);

        if (! is_array($decodificado)) {
            throw new RuntimeException('Resposta inválida do Mercado Pago (HTTP ' . $status . ').');
        }

        return $decodificado;
    }

    private function normalizarStatus(string $status): string
    {
        return match (strtolower($status)) {
            'approved', 'paid'                     => 'pago',
            'rejected', 'cancelled', 'refunded',
            'charged_back', 'chargedback'          => 'recusado',
            default                                => 'pendente',
        };
    }

    /**
     * @param array<string, mixed> $resposta
     */
    private function dataExpiracao(array $resposta): ?string
    {
        $expira = (string) ($resposta['date_of_expiration'] ?? '');

        if ($expira !== '') {
            $ts = strtotime($expira);

            if ($ts !== false) {
                return date('Y-m-d H:i:s', $ts);
            }
        }

        $minutos = $this->config->inteiro('pix_expiracao_minutos', 30);

        return date('Y-m-d H:i:s', time() + ($minutos * 60));
    }

    /**
     * @param array<string, mixed> $resposta
     */
    private function mensagemErro(array $resposta): string
    {
        $mensagem = (string) ($resposta['message'] ?? '');
        $causas   = $resposta['cause'] ?? [];

        if (is_array($causas)) {
            foreach ($causas as $causa) {
                if (is_array($causa) && ! empty($causa['description'])) {
                    $mensagem .= ' ' . (string) $causa['description'];
                }
            }
        }

        $normalizado = strtolower($mensagem);

        // Credenciais incoerentes entre Access Token e Public Key (ex.: token de
        // usuário de teste com Public Key de produção). Fica no log; o convidado
        // recebe uma mensagem neutra.
        foreach (['unauthorized use of live credentials', 'invalid access token', 'invalid_credentials', 'unauthorized'] as $assinatura) {
            if (str_contains($normalizado, $assinatura)) {
                return 'Pagamento indisponível no momento. Avise o organizador da lista.';
            }
        }

        return trim($mensagem) !== '' ? trim($mensagem) : 'erro desconhecido';
    }
}
