<?php
/** Layout do painel (organizador e SuperAdmin). */
$atual      = uri_string();
$superadmin = isset($usuario) && $usuario !== null && $usuario->isSuperAdmin();

$menu = $superadmin
    ? [
        ['rota' => 'admin', 'icone' => 'bi-shield-lock', 'rotulo' => 'Painel SuperAdmin', 'ativo' => $atual === 'admin'],
        ['rota' => 'admin/saques', 'icone' => 'bi-cash-coin', 'rotulo' => 'Saques', 'ativo' => str_starts_with($atual, 'admin/saques')],
    ]
    : [
        ['rota' => 'painel', 'icone' => 'bi-speedometer2', 'rotulo' => 'Dashboard', 'ativo' => $atual === 'painel'],
        ['rota' => 'painel/eventos', 'icone' => 'bi-calendar-event', 'rotulo' => 'Eventos', 'ativo' => str_starts_with($atual, 'painel/eventos')],
        ['rota' => 'painel/pedidos', 'icone' => 'bi-bag-check', 'rotulo' => 'Pedidos', 'ativo' => str_starts_with($atual, 'painel/pedidos')],
        ['rota' => 'painel/carteira', 'icone' => 'bi-wallet2', 'rotulo' => 'Carteira', 'ativo' => str_starts_with($atual, 'painel/carteira')],
    ];

$renderMenu = static function (array $itens): void {
    foreach ($itens as $item) {
        printf(
            '<a class="app-nav-link%s" href="%s"><i class="bi %s"></i><span>%s</span></a>',
            $item['ativo'] ? ' active' : '',
            esc(site_url($item['rota'])),
            esc($item['icone']),
            esc($item['rotulo'])
        );
    }
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?= view('templates/partials/design_system', ['titulo' => ($titulo ?? 'Painel') . ' · Minha Lista VIP']) ?>
    <style>
        .app-sidebar {
            width: 260px;
            background: #fff;
            border-right: 1px solid #E5E7EB;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .app-nav-link {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .6rem .85rem;
            border-radius: .65rem;
            color: #374151;
            text-decoration: none;
            font-weight: 500;
            font-size: .9rem;
        }
        .app-nav-link:hover { background: var(--brand-soft); color: var(--brand-dark); }
        .app-nav-link.active { background: var(--brand); color: #fff; }
        .app-topbar { background: #fff; border-bottom: 1px solid #E5E7EB; }
        .app-content { padding: 1.5rem; max-width: 1200px; }
        @media (max-width: 991.98px) { .app-content { padding: 1.25rem 1rem; } }
    </style>
</head>
<body>
<div class="d-flex" style="min-height: 100vh;">
    <!-- Sidebar (desktop) -->
    <aside class="app-sidebar d-none d-lg-flex flex-column p-3 flex-shrink-0">
        <a class="navbar-brand text-brand d-flex align-items-center gap-2 mb-4 px-2" href="<?= site_url('/') ?>">
            <i class="bi bi-gift-fill"></i>Minha Lista VIP
        </a>

        <nav class="d-flex flex-column gap-1">
            <?php $renderMenu($menu); ?>
        </nav>

        <div class="mt-auto border-top pt-3">
            <?php if ($usuario !== null): ?>
                <div class="px-2 mb-2">
                    <div class="fw-semibold fs-7 text-truncate"><?= esc($usuario->nome) ?></div>
                    <div class="text-muted fs-8 text-truncate"><?= esc($usuario->email) ?></div>
                    <span class="badge badge-soft mt-1"><?= esc(ucfirst($usuario->nivel)) ?></span>
                </div>
            <?php endif; ?>
            <a class="app-nav-link" href="<?= site_url('/') ?>" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i><span>Ver site</span>
            </a>
            <a class="app-nav-link text-danger" href="<?= site_url('logout') ?>">
                <i class="bi bi-box-arrow-right"></i><span>Sair</span>
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

            <?= $this->renderSection('conteudo') ?>
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
        <nav class="d-flex flex-column gap-1">
            <?php $renderMenu($menu); ?>
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
</body>
</html>
