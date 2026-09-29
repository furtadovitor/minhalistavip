<?php

namespace App\Controllers\Host;

use App\Controllers\BaseController;
use App\Services\CarteiraService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Carteira do organizador: saldo, extrato e solicitação de saque.
 */
class Carteira extends BaseController
{
    protected CarteiraService $carteira;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->carteira = new CarteiraService();
    }

    public function index()
    {
        $usuarioId = $this->usuarioId();

        return $this->render('host/carteira/index', [
            'titulo'         => 'Minha carteira',
            'saldo'          => $this->carteira->saldo($usuarioId),
            'arrecadado'     => $this->carteira->totalArrecadado($usuarioId),
            'taxas'          => $this->carteira->totalTaxas($usuarioId),
            'sacado'         => $this->carteira->totalSacado($usuarioId),
            'extrato'        => $this->carteira->extrato($usuarioId),
            'saques'         => $this->carteira->saques($usuarioId),
            'valorMinimo'    => $this->carteira->valorMinimoSaque(),
        ]);
    }

    public function saque()
    {
        return $this->render('host/carteira/saque', [
            'titulo'      => 'Solicitar saque',
            'saldo'       => $this->carteira->saldo($this->usuarioId()),
            'valorMinimo' => $this->carteira->valorMinimoSaque(),
        ]);
    }

    public function solicitarSaque()
    {
        $resultado = $this->carteira->solicitarSaque(
            $this->usuarioId(),
            (float) str_replace(',', '.', (string) $this->request->getPost('valor')),
            (string) $this->request->getPost('chave_pix'),
            (string) $this->request->getPost('observacao')
        );

        if (! $resultado['ok']) {
            return redirect()->back()->withInput()->with('erro', $resultado['mensagem']);
        }

        return redirect()->to(site_url('painel/carteira'))->with('sucesso', $resultado['mensagem']);
    }
}
