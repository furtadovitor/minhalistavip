<?php
/**
 * Layout do painel (organizador e SuperAdmin).
 *
 * O menu lateral é colapsável (ícone + nome / só ícone, estado salvo em
 * localStorage). Quando a view é de um evento ($evento definido), a navegação
 * da lista (Lista / Dinheiro / Personalização) aparece em uma barra própria,
 * ao lado do conteúdo — e NÃO no menu global.
 */
$atual      = uri_string();
$superadmin = isset($usuario) && $usuario !== null && $usuario->isSuperAdmin();
$evento     = $evento ?? null;

$estaAtivo = static function (array $item) use ($atual): bool {
    $rota  = $item['rota'];
    $match = ! empty($item['exato'])
        ? $atual === $rota
        : ($atual === $rota || str_starts_with($atual, $rota . '/'));

    if ($match) {
        return true;
    }

    foreach ((array) ($item['tambem'] ?? []) as $extra) {
        if ($atual === $extra || str_starts_with($atual, $extra . '/')) {
            return true;
        }
    }

    return false;
};

if ($superadmin) {
    $grupos = [
        ['rotulo' => 'Plataforma', 'itens' => [
            ['rota' => 'admin', 'icone' => 'bi-speedometer2', 'rotulo' => 'Visão geral', 'exato' => true],
            ['rota' => 'admin/financeiro', 'icone' => 'bi-graph-up-arrow', 'rotulo' => 'Financeiro'],
            ['rota' => 'admin/saques', 'icone' => 'bi-cash-coin', 'rotulo' => 'Saques'],
        ]],
        ['rotulo' => 'Gestão', 'itens' => [
            ['rota' => 'admin/listas', 'icone' => 'bi-list-ul', 'rotulo' => 'Listas'],
            ['rota' => 'admin/catalogo', 'icone' => 'bi-collection', 'rotulo' => 'Catálogo'],
            ['rota' => 'admin/categorias', 'icone' => 'bi-tags', 'rotulo' => 'Categorias'],
            ['rota' => 'admin/planos', 'icone' => 'bi-award', 'rotulo' => 'Planos'],
            ['rota' => 'admin/usuarios', 'icone' => 'bi-people', 'rotulo' => 'Usuários'],
            ['rota' => 'admin/configuracoes', 'icone' => 'bi-gear', 'rotulo' => 'Configurações'],
        ]],
    ];
} else {
    $grupos = [
        ['rotulo' => 'Geral', 'itens' => [
            ['rota' => 'painel', 'icone' => 'bi-grid', 'rotulo' => 'Minhas listas', 'exato' => true, 'tambem' => ['painel/eventos']],
            ['rota' => 'painel/carteira', 'icone' => 'bi-wallet2', 'rotulo' => 'Carteira'],
            ['rota' => 'painel/pedidos', 'icone' => 'bi-bag-check', 'rotulo' => 'Pedidos'],
        ]],
    ];
}

$renderGrupos = static function (array $grupos) use ($estaAtivo): void {
    foreach ($grupos as $grupo) {
        echo '<div class="app-nav-group">';

        if (($grupo['rotulo'] ?? '') !== '') {
            printf('<div class="app-nav-label">%s</div>', esc($grupo['rotulo']));
        }

        foreach ($grupo['itens'] as $item) {
            printf(
                '<a class="app-nav-link%s" href="%s" title="%s"><i class="bi %s"></i><span class="app-nav-text">%s</span></a>',
                $estaAtivo($item) ? ' active' : '',
                esc(site_url($item['rota'])),
                esc($item['rotulo']),
                esc($item['icone']),
                esc($item['rotulo'])
            );
        }

        echo '</div>';
    }
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?= view('templates/partials/design_system', ['titulo' => ($titulo ?? 'Painel') . ' · Minha Lista VIP']) ?>
    <style>
        .app-sidebar {
            width: 264px;
            background: #fff;
            border-right: 1px solid #E5E7EB;
            position: sticky;
            top: 0;
            height: 100vh;
            transition: width .18s ease;
            overflow-x: hidden;
        }
        .app-sidebar .app-nav-text { white-space: nowrap; }
        .app-nav-group { margin-bottom: 1rem; }
        .app-nav-label {
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #9CA3AF;
            font-weight: 700;
            padding: .25rem .85rem;
            margin-bottom: .25rem;
        }
        .app-nav-link {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: .58rem .85rem;
            border-radius: .65rem;
            color: #374151;
            text-decoration: none;
            font-weight: 500;
            font-size: .9rem;
            margin-bottom: .12rem;
        }
        .app-nav-link i { font-size: 1.05rem; flex-shrink: 0; width: 1.25rem; text-align: center; }
        .app-nav-link:hover { background: var(--brand-soft); color: var(--brand-dark); }
        .app-nav-link.active { background: var(--brand); color: #fff; }
        .app-topbar { background: #fff; border-bottom: 1px solid #E5E7EB; }
        .app-content { padding: 1.5rem; max-width: 1280px; }
        .app-toggle {
            border: 1px solid #E5E7EB;
            background: #fff;
            color: #6B7280;
            width: 32px; height: 32px;
            border-radius: .5rem;
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .app-toggle:hover { background: var(--brand-soft); color: var(--brand-dark); }

        /* Navegação do evento (fora do menu global) */
        .evento-subnav { width: 236px; }
        .evento-card {
            position: sticky;
            top: 76px;
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 1rem;
            padding: 1rem;
        }
        .evento-head { border-bottom: 1px solid #F3F4F6; padding-bottom: .85rem; margin-bottom: .5rem; }
        .evento-head .titulo { font-weight: 700; font-size: .95rem; line-height: 1.2; }
        .evento-head .slug { color: #9CA3AF; font-size: .75rem; }
        .evento-voltar {
            display: inline-flex; align-items: center; gap: .35rem;
            color: #6B7280; text-decoration: none; font-size: .8rem; margin-bottom: .6rem;
        }
        .evento-voltar:hover { color: var(--brand-dark); }
        .evento-nav { display: block; }
        .evento-nav-label {
            font-size: .66rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #9CA3AF;
            font-weight: 700;
            margin: .75rem 0 .3rem;
        }
        .evento-nav-link {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .5rem .65rem;
            border-radius: .6rem;
            color: #374151;
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
        }
        .evento-nav-link i { width: 1.1rem; text-align: center; }
        .evento-nav-link:hover { background: var(--brand-soft); color: var(--brand-dark); }
        .evento-nav-link.active { background: var(--brand); color: #fff; }

        /* Estado colapsado (somente desktop/sidebar) */
        body.menu-collapsed .app-sidebar { width: 80px; }
        body.menu-collapsed .app-sidebar .app-nav-text,
        body.menu-collapsed .app-sidebar .app-nav-label,
        body.menu-collapsed .app-sidebar .brand-text,
        body.menu-collapsed .app-sidebar .user-block { display: none; }
        body.menu-collapsed .app-sidebar .app-nav-link { justify-content: center; padding-left: .5rem; padding-right: .5rem; }
        body.menu-collapsed .app-sidebar .app-brand { justify-content: center; }
        body.menu-collapsed .app-sidebar .sidebar-foot { text-align: center; }

        @media (max-width: 991.98px) {
            .app-content { padding: 1.25rem 1rem; }
            .evento-subnav { width: 100%; }
            .evento-card { position: static; padding: .75rem; }
        }
    </style>
</head>
<body>
<div class="d-flex" style="min-height: 100vh;">
    <!-- Sidebar (desktop) -->
    <aside class="app-sidebar d-none d-lg-flex flex-column p-3 flex-shrink-0">
        <div class="d-flex align-items-center justify-content-between mb-3 gap-2">
            <a class="app-brand navbar-brand text-brand d-flex align-items-center gap-2 mb-0 px-1" href="<?= site_url('painel') ?>">
                <i class="bi bi-gift-fill"></i><span class="brand-text">Minha Lista VIP</span>
            </a>
            <button class="app-toggle" type="button" id="menuToggle" aria-label="Recolher menu" title="Recolher menu">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>

        <nav class="d-flex flex-column overflow-auto">
            <?php $renderGrupos($grupos); ?>
        </nav>

        <div class="mt-auto border-top pt-3 sidebar-foot">
            <?php if ($usuario !== null): ?>
                <div class="px-2 mb-2 user-block">
                    <div class="fw-semibold fs-7 text-truncate"><?= esc($usuario->nome) ?></div>
                    <div class="text-muted fs-8 text-truncate"><?= esc($usuario->email) ?></div>
                    <span class="badge badge-soft mt-1"><?= esc(ucfirst($usuario->nivel)) ?></span>
                </div>
            <?php endif; ?>
            <a class="app-nav-link" href="<?= site_url('/') ?>" target="_blank" title="Ver site">
                <i class="bi bi-box-arrow-up-right"></i><span class="app-nav-text">Ver site</span>
            </a>
            <a class="app-nav-link text-danger" href="<?= site_url('logout') ?>" title="Sair">
                <i class="bi bi-box-arrow-right"></i><span class="app-nav-text">Sair</span>
            </a>
        </div>
    </aside>

    <!-- Conteúdo -->
    <div class="flex-grow-1" style="min-width: 0;">
        <header class="app-topbar d-flex align-items-center gap-2 px-3 py-2 sticky-top">
            <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#menuLateral" aria-label="Abrir menu">
                <i class="bi bi-list"></i>
            </button>
            <span class="navbar-brand text-brand d-lg-none mb-0"><i class="bi bi-gift-fill"></i></span>
            <div class="ms-auto d-flex align-items-center gap-2">
                <?php if ($evento !== null && $evento->status === 'publicado'): ?>
                    <a class="btn btn-sm btn-outline-brand" href="<?= site_url($evento->slug) ?>" target="_blank">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Ver página
                    </a>
                <?php endif; ?>
                <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('/') ?>" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Ver site
                </a>
                <a class="btn btn-sm btn-outline-danger" href="<?= site_url('logout') ?>">Sair</a>
            </div>
        </header>

        <main class="app-content">
            <?php if (! empty($titulo)): ?>
                <h1 class="h3 fw-bold mb-3"><?= esc($titulo) ?></h1>
            <?php endif; ?>

            <?= view('templates/partials/flash') ?>

            <?php if ($evento !== null): ?>
                <div class="d-flex flex-column flex-lg-row gap-4">
                    <aside class="evento-subnav flex-shrink-0">
                        <div class="evento-card">
                            <?= view('templates/partials/evento_nav', ['evento' => $evento]) ?>
                        </div>
                    </aside>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <?= $this->renderSection('conteudo') ?>
                    </div>
                </div>
            <?php else: ?>
                <?= $this->renderSection('conteudo') ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Menu mobile -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="menuLateral" aria-labelledby="menuLateralLabel">
    <div class="offcanvas-header border-bottom">
        <span class="navbar-brand text-brand mb-0" id="menuLateralLabel"><i class="bi bi-gift-fill"></i>Minha Lista VIP</span>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <nav class="d-flex flex-column">
            <?php $renderGrupos($grupos); ?>
        </nav>
        <div class="mt-auto border-top pt-3">
            <?php if ($usuario !== null): ?>
                <div class="px-2 mb-2">
                    <div class="fw-semibold fs-7 text-truncate"><?= esc($usuario->nome) ?></div>
                    <div class="text-muted fs-8 text-truncate"><?= esc($usuario->email) ?></div>
                </div>
            <?php endif; ?>
            <a class="app-nav-link text-danger" href="<?= site_url('logout') ?>">
                <i class="bi bi-box-arrow-right"></i><span>Sair</span>
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    var chave = 'mlv_menu_colapsado';
    var botao = document.getElementById('menuToggle');
    if (!botao) { return; }

    function aplicar(colapsado) {
        document.body.classList.toggle('menu-collapsed', colapsado);
        botao.title = colapsado ? 'Expandir menu' : 'Recolher menu';
        botao.querySelector('i').className = colapsado ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
    }

    aplicar(localStorage.getItem(chave) === '1');

    botao.addEventListener('click', function () {
        var colapsado = ! document.body.classList.contains('menu-collapsed');
        localStorage.setItem(chave, colapsado ? '1' : '0');
        aplicar(colapsado);
    });
})();
</script>
</body>
</html>
