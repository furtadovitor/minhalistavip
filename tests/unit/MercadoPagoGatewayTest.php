<?php

use App\Entities\Pedido;
use App\Services\ConfiguracaoService;
use App\Services\Pix\MercadoPagoGateway;
use App\Services\Pix\SandboxGateway;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class MercadoPagoGatewayTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset(true);

        parent::tearDown();
    }

    private function pedido(): Pedido
    {
        return new Pedido([
            'id'              => 1,
            'protocolo'       => 'MLV123',
            'nome_convidado'  => 'Ana Souza',
            'email_convidado' => 'ana@example.com',
            'valor_total'     => 123.45,
        ]);
    }

    private function configuracao(): ConfiguracaoService
    {
        $config = $this->getMockBuilder(ConfiguracaoService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['texto', 'inteiro'])
            ->getMock();

        $config->method('texto')->willReturnCallback(
            static fn (string $chave, string $default = ''): string => match ($chave) {
                'mercadopago_access_token' => 'TEST-token',
                'mercadopago_public_key'   => 'TEST-public',
                'email_suporte'            => 'suporte@example.com',
                default                    => $default,
            }
        );

        $config->method('inteiro')->willReturn(30);

        return $config;
    }

    /**
     * @param array<string, mixed> $dados
     */
    private function respostaMock(array $dados, int $status = 200): ResponseInterface
    {
        $resposta = $this->createMock(ResponseInterface::class);
        $resposta->method('getBody')->willReturn((string) json_encode($dados));
        $resposta->method('getStatusCode')->willReturn($status);

        return $resposta;
    }

    public function testPagarCartaoEnviaDadosDoBrickComThreeDs(): void
    {
        $curl = $this->createMock(CURLRequest::class);
        $curl->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                $this->stringContains('/v1/payments'),
                $this->callback(static function (array $opcoes): bool {
                    $json = $opcoes['json'] ?? [];

                    return $json['transaction_amount'] === 123.45
                        && $json['token'] === 'tok_abc'
                        && $json['installments'] === 3
                        && $json['payment_method_id'] === 'visa'
                        && $json['issuer_id'] === 310
                        && $json['three_d_secure_mode'] === 'optional'
                        && $json['external_reference'] === 'MLV123'
                        && ($json['payer']['identification']['number'] ?? '') === '12345678909'
                        && ($opcoes['headers']['X-Idempotency-Key'] ?? '') !== '';
                })
            )
            ->willReturn($this->respostaMock([
                'id'                 => 987654,
                'status'             => 'approved',
                'status_detail'      => 'accredited',
                'payment_type_id'    => 'credit_card',
                'transaction_amount' => 123.45,
            ], 201));

        Services::injectMock('curlrequest', $curl);

        $resultado = (new MercadoPagoGateway($this->configuracao()))->pagar($this->pedido(), [
            'token'             => 'tok_abc',
            'installments'      => 3,
            'payment_method_id' => 'visa',
            'issuer_id'         => 310,
            'payer'             => [
                'email'          => 'ana@example.com',
                'identification' => ['type' => 'CPF', 'number' => '123.456.789-09'],
            ],
        ]);

        $this->assertSame('pago', $resultado['status']);
        $this->assertSame('987654', $resultado['gateway_transacao_id']);
    }

    public function testPagarPixNaoEnviaThreeDsECapturaQrCode(): void
    {
        $curl = $this->createMock(CURLRequest::class);
        $curl->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                $this->stringContains('/v1/payments'),
                $this->callback(static function (array $opcoes): bool {
                    $json = $opcoes['json'] ?? [];

                    return $json['payment_method_id'] === 'pix'
                        && ! array_key_exists('three_d_secure_mode', $json);
                })
            )
            ->willReturn($this->respostaMock([
                'id'                   => 111,
                'status'               => 'pending',
                'status_detail'        => 'pending_waiting_transfer',
                'payment_type_id'      => 'bank_transfer',
                'transaction_amount'   => 123.45,
                'point_of_interaction' => [
                    'transaction_data' => ['qr_code' => '000201BRCODE', 'qr_code_base64' => 'YWJj'],
                ],
            ]));

        Services::injectMock('curlrequest', $curl);

        $resultado = (new MercadoPagoGateway($this->configuracao()))->pagar($this->pedido(), [
            'payment_method_id' => 'pix',
            'payer'             => ['email' => 'ana@example.com'],
        ]);

        $this->assertSame('pendente', $resultado['status']);
        $this->assertSame('000201BRCODE', $resultado['copia_e_cola']);
        $this->assertSame('bank_transfer', $resultado['tipo']);
    }

    public function testPagarLancaExcecaoQuandoMercadoPagoRecusa(): void
    {
        $curl = $this->createMock(CURLRequest::class);
        $curl->method('request')->willReturn($this->respostaMock([
            'message' => 'Unauthorized use of live credentials',
            'cause'   => [['description' => 'Unauthorized use of live credentials']],
        ], 401));

        Services::injectMock('curlrequest', $curl);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unauthorized use of live credentials');

        (new MercadoPagoGateway($this->configuracao()))->pagar($this->pedido(), [
            'payment_method_id' => 'pix',
            'payer'             => ['email' => 'ana@example.com'],
        ]);
    }

    public function testCobrarNaCriacaoEPublicKey(): void
    {
        $config = $this->configuracao();
        $mp     = new MercadoPagoGateway($config);

        $this->assertFalse($mp->cobrarNaCriacao());
        $this->assertSame('TEST-public', $mp->publicKey());
        $this->assertTrue((new SandboxGateway($config))->cobrarNaCriacao());
    }
}
