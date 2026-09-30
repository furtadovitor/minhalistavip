<?php

namespace App\Controllers\Host;

use App\Controllers\BaseController;
use App\Services\CarteiraService;
use App\Services\EventoService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Painel do Organizador (cliente).
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
        $eventos    = $this->eventos->listar($this->usuarioId());
        $publicados = array_filter($eventos, static fn ($evento): bool => $evento->status === 'publicado');

        return $this->render('host/dashboard', [
            'titulo'     => 'Dashboard',
            'eventos'    => $eventos,
            'total'      => count($eventos),
            'publicados' => count($publicados),
            'arrecadado' => $this->carteira->totalArrecadado($this->usuarioId()),
            'saldo'      => $this->carteira->saldo($this->usuarioId()),
        ]);
    }
}
