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

    // Redefinição de senha.
    $routes->get('esqueci-senha', 'Auth::esqueciSenha');
    $routes->post('esqueci-senha', 'Auth::enviarRecuperacao');
    $routes->get('redefinir-senha/(:segment)', 'Auth::redefinirSenha/$1');
    $routes->post('redefinir-senha', 'Auth::salvarNovaSenha');

    // "Entrar com Google" (OAuth 2.0 / OpenID Connect).
    $routes->get('auth/google', 'Auth::google');
    $routes->get('auth/google/callback', 'Auth::googleCallback');
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

    // --- Workspace da lista (evento) ---
    $routes->get('eventos/(:num)', 'Host\Evento::index/$1');
    $routes->get('eventos/(:num)/informacoes', 'Host\Evento::informacoes/$1');
    $routes->post('eventos/(:num)/informacoes', 'Host\Evento::salvarInformacoes/$1');
    $routes->get('eventos/(:num)/aparencia', 'Host\Evento::aparencia/$1');
    $routes->post('eventos/(:num)/aparencia', 'Host\Evento::salvarAparencia/$1');
    $routes->get('eventos/(:num)/funcionalidades', 'Host\Evento::funcionalidades/$1');
    $routes->post('eventos/(:num)/funcionalidades', 'Host\Evento::salvarFuncionalidades/$1');
    $routes->get('eventos/(:num)/configuracoes', 'Host\Evento::configuracoes/$1');
    $routes->post('eventos/(:num)/configuracoes', 'Host\Evento::salvarConfiguracoes/$1');
    $routes->get('eventos/(:num)/forma-pagamento', 'Host\Evento::formaPagamento/$1');
    $routes->post('eventos/(:num)/forma-pagamento', 'Host\Evento::salvarFormaPagamento/$1');
    $routes->get('eventos/(:num)/pagamentos', 'Host\Evento::pagamentos/$1');
    $routes->get('eventos/(:num)/compartilhar', 'Host\Evento::compartilhar/$1');
    $routes->post('eventos/(:num)/arquivar', 'Host\Eventos::arquivar/$1');

    // --- Galeria de fotos ---
    $routes->get('eventos/(:num)/galeria', 'Host\Galeria::index/$1');
    $routes->post('eventos/(:num)/galeria', 'Host\Galeria::upload/$1');
    $routes->post('eventos/(:num)/galeria/(:num)/alternar', 'Host\Galeria::alternar/$1/$2');
    $routes->post('eventos/(:num)/galeria/(:num)/excluir', 'Host\Galeria::excluir/$1/$2');
    $routes->post('eventos/(:num)/galeria/(:num)', 'Host\Galeria::atualizar/$1/$2');

    // --- Recadinhos (mural) ---
    $routes->get('eventos/(:num)/recadinhos', 'Host\Recados::index/$1');
    $routes->post('eventos/(:num)/recadinhos/(:num)/publicar', 'Host\Recados::publicar/$1/$2');
    $routes->post('eventos/(:num)/recadinhos/(:num)/ocultar', 'Host\Recados::ocultar/$1/$2');
    $routes->post('eventos/(:num)/recadinhos/(:num)/excluir', 'Host\Recados::excluir/$1/$2');

    // --- Lista de convidados (RSVP) ---
    $routes->get('eventos/(:num)/convidados', 'Host\Convidados::index/$1');
    $routes->get('eventos/(:num)/convidados/exportar', 'Host\Convidados::exportar/$1');
    $routes->post('eventos/(:num)/convidados', 'Host\Convidados::adicionar/$1');
    $routes->get('eventos/(:num)/convidados/(:num)', 'Host\Convidados::ver/$1/$2');
    $routes->post('eventos/(:num)/convidados/(:num)/editar', 'Host\Convidados::editar/$1/$2');
    $routes->post('eventos/(:num)/convidados/(:num)/aprovar', 'Host\Convidados::aprovar/$1/$2');
    $routes->post('eventos/(:num)/convidados/(:num)/recusar', 'Host\Convidados::recusar/$1/$2');
    $routes->post('eventos/(:num)/convidados/(:num)/remover', 'Host\Convidados::remover/$1/$2');
    $routes->post('eventos/(:num)/convidados/(:num)/acompanhantes', 'Host\Convidados::adicionarAcompanhante/$1/$2');
    $routes->post('eventos/(:num)/convidados/(:num)/acompanhantes/(:num)/editar', 'Host\Convidados::editarAcompanhante/$1/$2/$3');
    $routes->post('eventos/(:num)/convidados/(:num)/acompanhantes/(:num)/remover', 'Host\Convidados::removerAcompanhante/$1/$2/$3');

    // --- Check-in presencial ---
    $routes->get('eventos/(:num)/checkin', 'Host\Checkin::index/$1');
    $routes->post('eventos/(:num)/checkin/(:num)/desfazer', 'Host\Checkin::desfazer/$1/$2');
    $routes->post('eventos/(:num)/checkin/(:num)', 'Host\Checkin::marcar/$1/$2');

    // --- Pedidos e carteira ---
    $routes->get('pedidos', 'Host\Pedidos::index');
    $routes->get('carteira', 'Host\Carteira::index');
    $routes->post('carteira/repasse', 'Host\Carteira::salvarRepasse');
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

    // --- Financeiro / conciliação ---
    $routes->get('financeiro', 'Admin\Financeiro::index');

    // --- Listas (eventos) de todos os organizadores ---
    $routes->get('listas', 'Admin\Eventos::index');
    $routes->get('listas/exportar', 'Admin\Eventos::exportar');
    $routes->post('listas/lote', 'Admin\Eventos::acaoEmLote');
    $routes->get('listas/(:num)', 'Admin\Eventos::ver/$1');
    $routes->post('listas/(:num)/publicar', 'Admin\Eventos::alternarPublicacao/$1');
    $routes->post('listas/(:num)/arquivar', 'Admin\Eventos::alternarArquivado/$1');

    // --- Configurações da plataforma ---
    $routes->get('configuracoes', 'Admin\Configuracoes::index');
    $routes->post('configuracoes', 'Admin\Configuracoes::salvar');

    // --- Suporte (chat ao vivo) ---
    $routes->get('suporte', 'Admin\Suporte::index');
    $routes->get('suporte/fila', 'Admin\Suporte::fila');
    $routes->get('suporte/(:num)/mensagens', 'Admin\Suporte::mensagens/$1');
    $routes->post('suporte/(:num)/assumir', 'Admin\Suporte::assumir/$1');
    $routes->post('suporte/(:num)/mensagens', 'Admin\Suporte::enviar/$1');
    $routes->post('suporte/(:num)/encerrar', 'Admin\Suporte::encerrar/$1');

    // --- Catálogo global ---
    $routes->get('catalogo', 'Admin\Catalogo::index');
    $routes->get('catalogo/novo', 'Admin\Catalogo::novo');
    $routes->post('catalogo', 'Admin\Catalogo::criar');
    $routes->get('catalogo/(:num)/editar', 'Admin\Catalogo::editar/$1');
    $routes->post('catalogo/(:num)/alternar', 'Admin\Catalogo::alternar/$1');
    $routes->post('catalogo/(:num)/excluir', 'Admin\Catalogo::excluir/$1');
    $routes->post('catalogo/(:num)', 'Admin\Catalogo::atualizar/$1');

    // --- Categorias ---
    $routes->get('categorias', 'Admin\Categorias::index');
    $routes->post('categorias', 'Admin\Categorias::criar');
    $routes->post('categorias/(:num)/alternar', 'Admin\Categorias::alternar/$1');
    $routes->post('categorias/(:num)/excluir', 'Admin\Categorias::excluir/$1');
    $routes->post('categorias/(:num)', 'Admin\Categorias::atualizar/$1');

    // --- Listas de exemplo (demos) ---
    $routes->get('demos', 'Admin\Demos::index');
    $routes->get('demos/novo', 'Admin\Demos::novo');
    $routes->post('demos', 'Admin\Demos::criar');
    $routes->post('demos/restaurar', 'Admin\Demos::restaurar');
    $routes->get('demos/(:num)/editar', 'Admin\Demos::editar/$1');
    $routes->post('demos/(:num)/alternar', 'Admin\Demos::alternar/$1');
    $routes->post('demos/(:num)/excluir', 'Admin\Demos::excluir/$1');
    $routes->post('demos/(:num)', 'Admin\Demos::atualizar/$1');

    // --- Planos ---
    $routes->get('planos', 'Admin\Planos::index');
    $routes->get('planos/novo', 'Admin\Planos::novo');
    $routes->post('planos', 'Admin\Planos::criar');
    $routes->get('planos/(:num)/editar', 'Admin\Planos::editar/$1');
    $routes->post('planos/(:num)/alternar', 'Admin\Planos::alternar/$1');
    $routes->post('planos/(:num)/excluir', 'Admin\Planos::excluir/$1');
    $routes->post('planos/(:num)', 'Admin\Planos::atualizar/$1');

    // --- Usuários ---
    $routes->get('usuarios', 'Admin\Usuarios::index');
    $routes->get('usuarios/(:num)', 'Admin\Usuarios::ver/$1');
    $routes->post('usuarios/(:num)/alternar', 'Admin\Usuarios::alternar/$1');
});

// ---------------------------------------------------------------------------
// PÁGINAS PÚBLICAS DE APOIO (exemplos, busca do convidado, criação rápida).
// ---------------------------------------------------------------------------
// Sitemap para os buscadores (precisa vir antes do catch-all de hotsite).
$routes->get('sitemap.xml', 'Public\Sitemap::index');

$routes->get('exemplos', 'Public\Demo::index');
$routes->get('demo', 'Public\Demo::index');
$routes->get('demo/(:segment)', 'Public\Demo::show/$1');
$routes->match(['get', 'post'], 'buscar', 'Public\Busca::buscar');

// Chat de suporte ao vivo (cliente: organizador ou visitante).
$routes->get('suporte/conversa', 'Public\Suporte::estado');
$routes->post('suporte/conversa/mensagens', 'Public\Suporte::enviar');
$routes->post('suporte/conversa/retomar', 'Public\Suporte::retomar');
$routes->post('suporte/conversa/encerrar', 'Public\Suporte::encerrar');

// Atalhos de criação de lista por tipo de evento (chá de bebê, pet, natal...).
$routes->get('criar-lista-de-presente', 'Public\CriarLista::index');
$routes->get('criar-lista-de-presente/continuar', 'Public\CriarLista::continuar');
$routes->post('criar-lista-de-presente', 'Public\CriarLista::criar');
$routes->get('criar-lista-de-presente/(:segment)', 'Public\CriarLista::form/$1');
$routes->post('criar-lista-de-presente/(:segment)', 'Public\CriarLista::criar/$1');

// Landing pages de SEO por tipo de evento (conteúdo + FAQ).
$routes->get('lista-de-presentes', 'Public\Landing::index');
$routes->get('lista-de-presentes/(:segment)', 'Public\Landing::show/$1');

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
$routes->get('(:segment)/pedido/(:segment)/status', 'Public\Checkout::status/$1/$2');
$routes->post('(:segment)/pedido/(:segment)/pagar', 'Public\Checkout::pagar/$1/$2');
$routes->post('(:segment)/pedido/(:segment)/simular', 'Public\Checkout::simular/$1/$2');
$routes->get('(:segment)/obrigado/(:segment)', 'Public\Checkout::obrigado/$1/$2');
