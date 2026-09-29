<?php

namespace App\Controllers\Host;

use App\Controllers\BaseController;
use App\Models\EventoModel;
use App\Models\PedidoModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Pedidos (vendas de cotas) de todos os eventos do organizador.
 * O filtro por usuario_id garante o isolamento de tenants (Regra 2.4).
 */
class Pedidos extends BaseController
{
    protected PedidoModel $pedidos;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->pedidos = new PedidoModel();
    }

    public function index()
    {
        $usuarioId = $this->usuarioId();
        $filtros   = [
            'status'    => $this->request->getGet('status') ?: null,
            'evento_id' => $this->request->getGet('evento') ?: null,
        ];

        return $this->render('host/pedidos/index', [
            'titulo'  => 'Pedidos',
            'pedidos' => $this->pedidos->doOrganizador($usuarioId, $filtros),
            'eventos' => (new EventoModel())->doOrganizador($usuarioId),
            'filtros' => $filtros,
        ]);
    }
}
