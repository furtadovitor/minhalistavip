<?php

namespace App\Services;

use App\Entities\Pedido;
use App\Services\Pix\GatewayPixInterface;
use App\Services\Pix\MercadoPagoGateway;
use App\Services\Pix\SandboxGateway;

/**
 * Fachada de cobrança PIX.
 *
 * Escolhe o gateway conforme a configuração `pix_gateway` (sandbox |
 * mercadopago) e delega as chamadas. O contrato de retorno é o mesmo para
 * todos os provedores — ver GatewayPixInterface.
 */
class PixService
{
    /**
     * @deprecated Use nomeGateway(); mantido por compatibilidade.
     */
    public const GATEWAY = SandboxGateway::NOME;

    public const TIPO = 'pix';

    protected ConfiguracaoService $config;

    public function __construct(?ConfiguracaoService $config = null)
    {
        $this->config = $config ?? new ConfiguracaoService();
    }

    /**
     * Gateway selecionado na configuração da plataforma.
     */
    public function gateway(): GatewayPixInterface
    {
        $escolhido = strtolower($this->config->texto('pix_gateway', SandboxGateway::NOME));

        return match ($escolhido) {
            MercadoPagoGateway::NOME => new MercadoPagoGateway($this->config),
            default                  => new SandboxGateway($this->config),
        };
    }

    public function nomeGateway(): string
    {
        return $this->gateway()->nome();
    }

    /**
     * @return array<string, mixed>
     */
    public function gerarCobranca(Pedido $pedido): array
    {
        return $this->gateway()->gerarCobranca($pedido);
    }

    public function transacaoId(Pedido $pedido): string
    {
        return $this->gateway()->transacaoIdLocal($pedido);
    }

    /**
     * Compatibilidade: helpers do BR Code (usados por testes/rotinas internas).
     */
    public function montarBrCode(float $valor, string $txid): string
    {
        return (new SandboxGateway($this->config))->montarBrCode($valor, $txid);
    }

    public function crc16(string $payload): string
    {
        return (new SandboxGateway($this->config))->crc16($payload);
    }

    public function tokenValido(?string $token): bool
    {
        return (new SandboxGateway($this->config))->tokenValido($token);
    }

    public function webhookToken(): string
    {
        return $this->config->texto('pix_webhook_token', 'sandbox-token');
    }
}
