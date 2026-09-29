<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ---------------------------------------------------------------------------
// ROTAS FIXAS (nunca podem virar slug de hotsite — ver EventoService).
// ---------------------------------------------------------------------------

// Página inicial da plataforma.
$routes->get('/', 'Home::index');

// Autenticação (apenas visitantes não autenticados).
$routes->group('', ['filter' => 'guest'], static function (RouteCollection $routes): void {
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::autenticar');
    $routes->get('registro', 'Auth::registro');
    $routes->post('registro', 'Auth::salvarRegistro');
});

$routes->get('logout', 'Auth::logout');

// ---------------------------------------------------------------------------
// Painel do Organizador (cliente) — exige autenticação.
// ---------------------------------------------------------------------------
$routes->group('painel', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Host\Dashboard::index');

    // --- Eventos (CRUD) ---
    $routes->get('eventos', 'Host\Eventos::index');
    $routes->get('eventos/novo', 'Host\Eventos::novo');
    $routes->post('eventos', 'Host\Eventos::criar');
    $routes->get('eventos/(:num)/editar', 'Host\Eventos::editar/$1');
    $routes->post('eventos/(:num)', 'Host\Eventos::atualizar/$1');
    $routes->post('eventos/(:num)/publicar', 'Host\Eventos::publicar/$1');
    $routes->post('eventos/(:num)/excluir', 'Host\Eventos::excluir/$1');

    // --- Presentes do evento ---
    $routes->get('eventos/(:num)/presentes', 'Host\Presentes::index/$1');
    $routes->get('eventos/(:num)/presentes/novo', 'Host\Presentes::novo/$1');
    $routes->post('eventos/(:num)/presentes', 'Host\Presentes::criar/$1');
    $routes->get('eventos/(:num)/presentes/catalogo', 'Host\Presentes::catalogo/$1');
    $routes->post('eventos/(:num)/presentes/clonar', 'Host\Presentes::clonar/$1');
    $routes->get('eventos/(:num)/presentes/(:num)/editar', 'Host\Presentes::editar/$1/$2');
    $routes->post('eventos/(:num)/presentes/(:num)/alternar', 'Host\Presentes::alternar/$1/$2');
    $routes->post('eventos/(:num)/presentes/(:num)/excluir', 'Host\Presentes::excluir/$1/$2');
    $routes->post('eventos/(:num)/presentes/(:num)', 'Host\Presentes::atualizar/$1/$2');

    // --- Pedidos e carteira ---
    $routes->get('pedidos', 'Host\Pedidos::index');
    $routes->get('carteira', 'Host\Carteira::index');
    $routes->get('carteira/saque', 'Host\Carteira::saque');
    $routes->post('carteira/saque', 'Host\Carteira::solicitarSaque');
});

// ---------------------------------------------------------------------------
// Painel do SuperAdmin — exige autenticação + nível superadmin (ACL).
// ---------------------------------------------------------------------------
$routes->group('admin', ['filter' => ['auth', 'role:superadmin']], static function (RouteCollection $routes): void {
    $routes->get('/', 'Admin\Dashboard::index');

    // --- Saques dos organizadores ---
    $routes->get('saques', 'Admin\Saques::index');
    $routes->post('saques/(:num)/processar', 'Admin\Saques::processar/$1');
    $routes->post('saques/(:num)/pagar', 'Admin\Saques::pagar/$1');
    $routes->post('saques/(:num)/recusar', 'Admin\Saques::recusar/$1');
});

// ---------------------------------------------------------------------------
// WEBHOOKS dos gateways de pagamento (isentos de CSRF — ver Config\Filters).
// ---------------------------------------------------------------------------
$routes->post('webhooks/pix', 'Webhook\Pix::receber');

// ---------------------------------------------------------------------------
// HOTSITES — página pública do evento na RAIZ: minhalistavip.com.br/{slug}
//
// Precisa ser o ÚLTIMO bloco: (:segment) captura qualquer segmento único.
// Rotas fixas acima têm prioridade por serem registradas antes.
// Slugs reservados são bloqueados em EventoService::SLUGS_RESERVADOS.
// ---------------------------------------------------------------------------
$routes->get('(:segment)', 'Public\Evento::show/$1');
$routes->post('(:segment)/rsvp', 'Public\Evento::rsvp/$1');
$routes->post('(:segment)/recado', 'Public\Evento::recado/$1');

// Checkout do convidado (mais específicas que o catch-all acima quando há
// segmentos fixos no meio do caminho).
$routes->get('(:segment)/presentear/(:num)', 'Public\Checkout::form/$1/$2');
$routes->post('(:segment)/presentear/(:num)', 'Public\Checkout::criar/$1/$2');
$routes->get('(:segment)/pedido/(:segment)', 'Public\Checkout::pedido/$1/$2');
$routes->post('(:segment)/pedido/(:segment)/simular', 'Public\Checkout::simular/$1/$2');
