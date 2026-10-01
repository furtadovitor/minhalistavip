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
            ->with('sucesso', 'Pedido registrado! Escolha a forma de pagamento.');
    }

    public function pedido($slug = null, $protocolo = null)
    {
        $evento = $this->eventos->publicadoPorSlug((string) $slug);
        $pedido = $this->buscarPedido($evento, (string) $protocolo);

        // "Já paguei, atualizar status": consulta o gateway antes de renderizar.
        if ($this->request->getGet('atualizar') !== null) {
            $pedido = $this->consultarGateway($pedido);
        }

        $pagamento = (new PagamentoModel())->ultimoDoPedido((int) $pedido->id);
        $cobranca  = $pagamento !== null ? json_decode((string) $pagamento['payload'], true) : null;

        $pix       = new PixService();
        $publicKey = $pix->publicKey();

        return view('public/pedido', [
            'evento'       => $evento,
            'pedido'       => $pedido,
            'presente'     => $pedido->presente_evento_id !== null
                ? (new PresenteEventoModel())->find((int) $pedido->presente_evento_id)
                : null,
            'cobranca'     => is_array($cobranca) ? $cobranca : null,
            'simulacao'    => ENVIRONMENT !== 'production',
            'pix_pendente' => ! $pedido->estaPago() && ! $pedido->foiCancelado(),
            'bricks'       => $publicKey !== '' && $pedido->estaPendente() && ! $pedido->expirado(),
            'public_key'   => $publicKey,
        ]);
    }

    /**
     * Cria o pagamento no Mercado Pago a partir dos dados do Checkout Bricks
     * (PIX ou cartão) e concilia o resultado com o pedido.
     */
    public function pagar($slug = null, $protocolo = null)
    {
        $evento = $this->eventos->publicadoPorSlug((string) $slug);
        $pedido = $this->buscarPedido($evento, (string) $protocolo);

        if (! $pedido->estaPendente() || $pedido->expirado()) {
            return $this->response->setStatusCode(409)->setJSON([
                'ok'       => false,
                'mensagem' => 'Este pedido não está mais disponível para pagamento.',
            ]);
        }

        $dados = $this->request->getJSON(true);

        if (! is_array($dados)) {
            $dados = [];
        }

        try {
            $resultado = (new PixService())->pagar($pedido, $dados);
        } catch (RuntimeException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok'       => false,
                'mensagem' => $e->getMessage(),
            ]);
        }

        $this->pedidos->update((int) $pedido->id, [
            'gateway'              => $resultado['gateway'],
            'gateway_transacao_id' => $resultado['gateway_transacao_id'],
            'metodo_pagamento'     => $resultado['tipo'],
            'expira_em'            => $resultado['expira_em'] ?? $pedido->expira_em,
        ]);

        $pagamentos = new PagamentoService();
        $pagamentos->registrar($pedido, $resultado);

        if ($resultado['status'] === 'pago') {
            $confirmacao = $pagamentos->confirmar(
                $resultado['gateway'],
                $resultado['gateway_transacao_id'],
                ['brick' => true],
                'checkout.brick'
            );

            return $this->response->setJSON([
                'ok'            => $confirmacao['ok'],
                'status'        => 'pago',
                'status_detail' => $resultado['status_detail'],
                'payment_id'    => $resultado['gateway_transacao_id'],
                'mensagem'      => $confirmacao['mensagem'],
            ]);
        }

        return $this->response->setJSON([
            'ok'            => $resultado['status'] !== 'recusado',
            'status'        => $resultado['status'],
            'status_detail' => $resultado['status_detail'],
            'payment_id'    => $resultado['gateway_transacao_id'],
            'three_ds_info' => $resultado['three_ds_info'],
            'mensagem'      => $resultado['status'] === 'recusado'
                ? $this->mensagemRecusado($resultado['status_detail'])
                : 'Pagamento em processamento.',
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

    /**
     * Traduz os principais motivos de recusa do Mercado Pago.
     */
    private function mensagemRecusado(string $detalhe): string
    {
        $mapa = [
            'cc_rejected_insufficient_amount'       => 'Saldo insuficiente no cartão.',
            'cc_rejected_bad_filled_security_code' => 'Código de segurança (CVV) incorreto.',
            'cc_rejected_bad_filled_date'           => 'Data de validade incorreta.',
            'cc_rejected_bad_filled_other'          => 'Confira os dados do cartão e tente novamente.',
            'cc_rejected_call_for_authorize'        => 'Pagamento não autorizado. Ligue para o seu banco.',
            'cc_rejected_card_disabled'             => 'Cartão desabilitado para compras online. Ligue para o banco.',
            'cc_rejected_duplicated_payment'        => 'Este pagamento já foi enviado.',
            'cc_rejected_high_risk'                 => 'Pagamento recusado por segurança. Tente outro cartão.',
            'cc_rejected_blacklist'                 => 'Pagamento recusado por segurança. Tente outro cartão.',
            'cc_rejected_max_attempts'              => 'Muitas tentativas. Tente novamente mais tarde.',
        ];

        return $mapa[$detalhe] ?? 'Pagamento recusado. Confira os dados ou tente outro cartão.';
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
     * Consulta o status atual no gateway (quando houver transação) e confirma
     * o pedido se o pagamento já tiver sido aprovado.
     */
    private function consultarGateway(Pedido $pedido): Pedido
    {
        if (! $pedido->estaPendente() || empty($pedido->gateway_transacao_id)) {
            return $pedido;
        }

        $gateway  = (new PixService())->gateway();
        $consulta = $gateway->consultar((string) $pedido->gateway_transacao_id);

        if ($consulta === null || ($consulta['status'] ?? '') !== 'pago') {
            return $pedido;
        }

        (new PagamentoService())->confirmar(
            $gateway->nome(),
            (string) $pedido->gateway_transacao_id,
            $consulta['payload'] ?? [],
            'checkout.consulta'
        );

        return $this->pedidos->porProtocolo((string) $pedido->protocolo) ?? $pedido;
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
