<?php

namespace App\Controllers\Host;

use App\Controllers\BaseController;
use App\Services\CarteiraService;
use App\Services\EventoService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Painel do Organizador (cliente): home "Minhas listas".
 */
class Dashboard extends BaseController
{
    protected EventoService $eventos;

    protected CarteiraService $carteira;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->eventos  = new EventoService();
        $this->carteira = new CarteiraService();
    }

    public function index()
    {
        $usuarioId  = $this->usuarioId();
        $ativos     = $this->eventos->listarAtivos($usuarioId);
        $arquivados = $this->eventos->listarArquivados($usuarioId);
        $publicados = array_filter($ativos, static fn ($evento): bool => $evento->status === 'publicado');

        return $this->render('host/dashboard', [
            'titulo'     => 'Minhas listas',
            'ativos'     => $ativos,
            'arquivados' => $arquivados,
            'total'      => count($ativos) + count($arquivados),
            'publicados' => count($publicados),
            'arrecadado' => $this->carteira->totalArrecadado($usuarioId),
            'saldo'      => $this->carteira->saldo($usuarioId),
        ]);
    }
}
