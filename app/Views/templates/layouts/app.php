<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($titulo ?? 'Minha Lista VIP') ?> · Minha Lista VIP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-body-tertiary">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= site_url('/') ?>">Minha Lista VIP</a>
        <div class="ms-auto d-flex align-items-center gap-2">
            <?php if (isset($usuario) && $usuario !== null): ?>
                <a class="btn btn-outline-light btn-sm" href="<?= site_url('painel') ?>">Meus eventos</a>
                <a class="btn btn-outline-light btn-sm" href="<?= site_url('painel/pedidos') ?>">Pedidos</a>
                <a class="btn btn-outline-light btn-sm" href="<?= site_url('painel/carteira') ?>">Carteira</a>
                <?php if ($usuario->isSuperAdmin()): ?>
                    <a class="btn btn-outline-light btn-sm" href="<?= site_url('admin') ?>">SuperAdmin</a>
                    <a class="btn btn-outline-light btn-sm" href="<?= site_url('admin/saques') ?>">Saques</a>
                <?php endif; ?>
                <span class="text-white-50 small d-none d-md-inline">
                    <?= esc($usuario->nome) ?> · <?= esc(ucfirst($usuario->nivel)) ?>
                </span>
                <a class="btn btn-outline-light btn-sm" href="<?= site_url('logout') ?>">Sair</a>
            <?php else: ?>
                <a class="btn btn-outline-light btn-sm" href="<?= site_url('login') ?>">Entrar</a>
                <a class="btn btn-light btn-sm" href="<?= site_url('registro') ?>">Criar conta</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if (! empty($titulo)): ?>
        <h1 class="h3 mb-4"><?= esc($titulo) ?></h1>
    <?php endif; ?>

    <?= view('templates/partials/flash') ?>

    <?= $this->renderSection('conteudo') ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
