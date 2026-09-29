<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($titulo ?? 'Minha Lista VIP') ?> · Minha Lista VIP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-body-tertiary">
<div class="container" style="max-width: 440px; margin-top: 7vh;">
    <div class="text-center mb-4">
        <a href="<?= site_url('/') ?>" class="text-decoration-none">
            <h1 class="h4 text-primary mb-1">Minha Lista VIP</h1>
        </a>
        <p class="text-muted small mb-0">Presentes em dinheiro, RSVP e recados para o seu evento.</p>
    </div>

    <?= view('templates/partials/flash') ?>

    <?= $this->renderSection('conteudo') ?>

    <p class="text-center text-muted small mt-4">
        &copy; <?= date('Y') ?> Minha Lista VIP
    </p>
</div>
</body>
</html>
