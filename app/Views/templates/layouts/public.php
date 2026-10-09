<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?= view('templates/partials/design_system', [
        'titulo' => ($titulo ?? 'Minha Lista VIP') . ' · Minha Lista VIP',
        'seo'    => $seo ?? null,
    ]) ?>
</head>
<body>
<?= view('templates/partials/analytics_body') ?>
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand p-0" href="<?= site_url('/') ?>">
            <?= view('templates/partials/logo', ['altura' => 32]) ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPublica"
                aria-controls="navPublica" aria-expanded="false" aria-label="Alternar navegação">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navPublica">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= site_url('criar-lista-de-presente') ?>">Criar lista</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('/') ?>#como-funciona">Como funciona</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('/') ?>#exemplos">Exemplos</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('/') ?>#buscar">Buscar lista</a></li>
            </ul>
            <div class="d-flex flex-wrap gap-2">
                <?php if (isset($usuario) && $usuario !== null): ?>
                    <a class="btn btn-outline-brand btn-sm px-3" href="<?= site_url($usuario->isSuperAdmin() ? 'admin' : 'painel') ?>">Meu painel</a>
                    <a class="btn btn-brand btn-sm px-3" href="<?= site_url('logout') ?>">Sair</a>
                <?php else: ?>
                    <a class="btn btn-outline-brand btn-sm px-3" href="<?= site_url('login') ?>">Entrar</a>
                    <a class="btn btn-brand btn-sm px-3" href="<?= site_url('criar-lista-de-presente') ?>">Criar minha lista</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main>
    <?= $this->renderSection('conteudo') ?>
</main>

<footer class="site-footer py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <div class="mb-2"><?= view('templates/partials/logo', ['altura' => 30, 'claro' => true]) ?></div>
                <p class="fs-7 mb-0" style="max-width: 380px;">
                    Listas de presentes em dinheiro, RSVP e mural de recados para casamentos,
                    chás e aniversários. O convidado presenteia via PIX e o valor cai na sua conta.
                </p>
            </div>
            <div class="col-6 col-md-3">
                <p class="text-white fw-semibold mb-2 fs-7 text-uppercase">Plataforma</p>
                <ul class="list-unstyled fs-7">
                    <li class="mb-1"><a href="<?= site_url('criar-lista-de-presente') ?>">Criar lista</a></li>
                    <li class="mb-1"><a href="<?= site_url('lista-de-presentes') ?>">Listas por tipo</a></li>
                    <li class="mb-1"><a href="<?= site_url('login') ?>">Entrar</a></li>
                    <li class="mb-1"><a href="<?= site_url('/') ?>#exemplos">Exemplos</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-4">
                <p class="text-white fw-semibold mb-2 fs-7 text-uppercase">Contato</p>
                <ul class="list-unstyled fs-7">
                    <li class="mb-1"><i class="bi bi-envelope me-1"></i> suporte@minhalistavip.com.br</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary opacity-25">
        <p class="fs-8 mb-0">&copy; <?= date('Y') ?> Minha Lista VIP. Todos os direitos reservados.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= view('templates/partials/chat', ['canal' => 'site']) ?>
</body>
</html>
