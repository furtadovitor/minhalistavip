<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Entities\Evento as EventoEntity;
use App\Entities\Pedido;
use App\Models\PagamentoModel;
use App\Models\PedidoModel;
use App\Models\PresenteEventoModel;
use App\Services\CheckoutService;
use App\Services\EventoService;
use App\Services\PagamentoService;
use App\Services\PresenteEventoService;
use App\Services\PixService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Checkout do convidado e acompanhamento do pedido (interface pública).
 *
 * Fluxo: escolher cota → preencher dados → gerar pedido + PIX → pagar →
 * webhook confirma (ou botão de simulação em desenvolvimento).
 */
class Checkout extends BaseController
{
    protected EventoService $eventos;

    protected PresenteEventoService $presentes;

    protected CheckoutService $checkout;

    protected PedidoModel $pedidos;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->eventos   = new EventoService();
        $this->presentes = new PresenteEventoService();
        $this->checkout  = new CheckoutService();
        $this->pedidos   = new PedidoModel();
    }

    public function form($slug = null, $presenteId = null)
    {
        $evento   = $this->eventos->publicadoPorSlug((string) $slug);
        $presente = $this->presentes->um((int) $evento->id, (int) $presenteId);

        if (($presente['tipo'] ?? 'ficticio') !== 'ficticio') {
            return $this->irParaPresenteReal($evento, $presente);
        }

        $disponiveis = $this->checkout->cotasDisponiveis($presente);

        if ($disponiveis < 1) {
            return redirect()->to(site_url($evento->slug))
                ->with('erro', 'Todas as cotas deste presente já foram presenteadas.');
        }

        return view('public/checkout', [
            'evento'      => $evento,
            'presente'    => $presente,
            'disponiveis' => $disponiveis,
            'resumo'      => $this->checkout->resumo($evento, $presente, 1),
        ]);
    }

    public function criar($slug = null, $presenteId = null)
    {
        $evento   = $this->eventos->publicadoPorSlug((string) $slug);
        $presente = $this->presentes->um((int) $evento->id, (int) $presenteId);

        $convidado = [
            'nome'     => (string) $this->request->getPost('nome'),
            'email'    => (string) $this->request->getPost('email'),
            'telefone' => (string) $this->request->getPost('telefone'),
            'mensagem' => (string) $this->request->getPost('mensagem'),
        ];

        try {
            $resultado = $this->checkout->criar($evento, $presente, (int) $this->request->getPost('quantidade'), $convidado);
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('erro', $e->getMessage());
        }

        return redirect()->to(site_url($evento->slug . '/pedido/' . $resultado['pedido']->protocolo))
            ->with('sucesso', 'Pedido registrado! Conclua o pagamento via PIX.');
    }

    public function pedido($slug = null, $protocolo = null)
    {
        $evento = $this->eventos->publicadoPorSlug((string) $slug);
        $pedido = $this->buscarPedido($evento, (string) $protocolo);

        $pagamento = (new PagamentoModel())->ultimoDoPedido((int) $pedido->id);
        $cobranca  = $pagamento !== null ? json_decode((string) $pagamento['payload'], true) : null;

        return view('public/pedido', [
            'evento'       => $evento,
            'pedido'       => $pedido,
            'presente'     => $pedido->presente_evento_id !== null
                ? (new PresenteEventoModel())->find((int) $pedido->presente_evento_id)
                : null,
            'cobranca'     => is_array($cobranca) ? $cobranca : null,
            'simulacao'    => ENVIRONMENT !== 'production',
            'pix_pendente' => ! $pedido->estaPago() && ! $pedido->foiCancelado(),
        ]);
    }

    /**
     * Simula a confirmação do PIX (somente em desenvolvimento).
     * Exercita o MESMO caminho do webhook real (PagamentoService).
     */
    public function simular($slug = null, $protocolo = null)
    {
        if (ENVIRONMENT === 'production') {
            throw PageNotFoundException::forPageNotFound('Recurso indisponível.');
        }

        $evento = $this->eventos->publicadoPorSlug((string) $slug);
        $pedido = $this->buscarPedido($evento, (string) $protocolo);

        $gateway     = (string) ($pedido->gateway ?: (new PixService())->nomeGateway());
        $transacaoId = (string) ($pedido->gateway_transacao_id ?: (new PixService())->transacaoId($pedido));

        $resultado = (new PagamentoService())->confirmar(
            $gateway,
            $transacaoId,
            ['simulado' => true, 'protocolo' => $pedido->protocolo],
            'pix.simulado'
        );

        return redirect()->to(site_url($evento->slug . '/pedido/' . $pedido->protocolo))
            ->with($resultado['ok'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }

    private function buscarPedido(EventoEntity $evento, string $protocolo): Pedido
    {
        $pedido = $this->pedidos->porProtocolo($protocolo);

        if ($pedido === null || (int) $pedido->evento_id !== (int) $evento->id) {
            throw PageNotFoundException::forPageNotFound('Pedido não encontrado.');
        }

        return $pedido;
    }

    /**
     * Presentes reais (lojas) saem por link de afiliado, sem checkout.
     *
     * @param array<string, mixed> $presente
     */
    private function irParaPresenteReal(EventoEntity $evento, array $presente)
    {
        $link = trim((string) ($presente['link_afiliado'] ?? ''));

        if ($link !== '') {
            return redirect()->to($link);
        }

        return redirect()->to(site_url($evento->slug))
            ->with('erro', 'Este presente é comprado direto na loja e não passa pelo checkout.');
    }
}
