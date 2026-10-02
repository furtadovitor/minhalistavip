<?php

use App\Entities\Pedido;
use App\Models\PedidoModel;
use App\Services\ConciliacaoService;
use App\Services\PagamentoService;
use App\Services\Pix\GatewayPixInterface;
use App\Services\PixService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ConciliacaoServiceTest extends CIUnitTestCase
{
    private function pedido(int $id, string $transacaoId): Pedido
    {
        return new Pedido([
            'id'                   => $id,
            'protocolo'            => 'MLV' . $id,
            'status'               => 'pendente',
            'gateway'              => 'mercadopago',
            'gateway_transacao_id' => $transacaoId,
            'valor_total'          => 10.00,
        ]);
    }

    public function testConfirmarPendentesSoConfirmaOsPagos(): void
    {
        $pedidos = $this->createMock(PedidoModel::class);
        $pedidos->method('pendentesComTransacao')->willReturn([
            $this->pedido(1, '111'),
            $this->pedido(2, '222'),
        ]);

        $gateway = $this->createMock(GatewayPixInterface::class);
        $gateway->method('nome')->willReturn('mercadopago');
        $gateway->method('consultar')->willReturnCallback(
            static fn (string $id): array => $id === '111'
                ? ['status' => 'pago', 'valor' => 10.0, 'payload' => ['id' => 111]]
                : ['status' => 'pendente', 'valor' => 10.0, 'payload' => ['id' => 222]]
        );

        $pix = $this->createMock(PixService::class);
        $pix->method('gateway')->willReturn($gateway);

        $pagamentos = $this->createMock(PagamentoService::class);
        $pagamentos->expects($this->once())
            ->method('confirmar')
            ->with('mercadopago', '111', $this->anything(), 'pix.conciliacao')
            ->willReturn(['ok' => true, 'mensagem' => 'ok']);

        $resultado = (new ConciliacaoService($pedidos, $pix, $pagamentos))->confirmarPendentes();

        $this->assertSame(2, $resultado['consultados']);
        $this->assertSame(1, $resultado['confirmados']);
    }

    public function testExpirarVencidosDelegaParaOModelo(): void
    {
        $pedidos = $this->createMock(PedidoModel::class);
        $pedidos->expects($this->once())
            ->method('expirarVencidos')
            ->willReturn(3);

        $resultado = (new ConciliacaoService($pedidos))->expirarVencidos();

        $this->assertSame(3, $resultado);
    }

    public function testExecutarRetornaResumo(): void
    {
        $pedidos = $this->createMock(PedidoModel::class);
        $pedidos->method('pendentesComTransacao')->willReturn([]);
        $pedidos->method('expirarVencidos')->willReturn(2);

        $gateway = $this->createMock(GatewayPixInterface::class);
        $gateway->method('nome')->willReturn('mercadopago');

        $pix = $this->createMock(PixService::class);
        $pix->method('gateway')->willReturn($gateway);

        $resultado = (new ConciliacaoService($pedidos, $pix, $this->createMock(PagamentoService::class)))->executar();

        $this->assertSame(['consultados' => 0, 'confirmados' => 0, 'expirados' => 2], $resultado);
    }
}
