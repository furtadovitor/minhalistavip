<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?= view('templates/partials/design_system', [
        'titulo' => ($titulo ?? 'Minha Lista VIP') . ' · Minha Lista VIP',
        // Login/registro/redefinição nunca entram no índice de busca.
        'seo'    => array_merge(['noindex' => true], (array) ($seo ?? [])),
    ]) ?>
</head>
<body class="hero-gradient">
<?= view('templates/partials/analytics_body') ?>
<div class="auth-shell">
    <div class="auth-card">
        <div class="text-center mb-4">
            <a href="<?= site_url('/') ?>" class="d-inline-flex mb-2 text-decoration-none">
                <?= view('templates/partials/logo', ['altura' => 42]) ?>
            </a>
            <p class="text-muted small mb-0">Presentes em dinheiro, RSVP e mural de recados para o seu evento.</p>
        </div>

        <?= view('templates/partials/flash') ?>

        <?= $this->renderSection('conteudo') ?>

        <p class="text-center text-muted small mt-4 mb-0">&copy; <?= date('Y') ?> Minha Lista VIP</p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
