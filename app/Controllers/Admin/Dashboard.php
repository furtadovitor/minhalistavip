<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventoModel;
use App\Models\PedidoModel;
use App\Models\UsuarioModel;
use App\Services\CarteiraService;

/**
 * Painel do SuperAdmin: visão global da plataforma.
 */
class Dashboard extends BaseController
{
    public function index()
    {
        return $this->render('admin/dashboard', [
            'titulo'            => 'Painel do SuperAdmin',
            'organizadores'     => (new UsuarioModel())->where('nivel', 'organizador')->countAllResults(),
            'eventosPublicados' => (new EventoModel())->where('status', 'publicado')->countAllResults(),
            'pedidosPagos'      => (new PedidoModel())->where('status', 'pago')->countAllResults(),
            'resumoSaques'      => (new CarteiraService())->resumoSaques(),
        ]);
    }
}
