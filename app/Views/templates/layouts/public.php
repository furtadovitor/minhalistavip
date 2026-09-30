<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($titulo ?? 'Minha Lista VIP') ?> · Minha Lista VIP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --brand: #4F46E5;
            --brand-dark: #4338CA;
            --brand-soft: #EEF2FF;
            --success: #10B981;
            --bg: #F9FAFB;
            --ink: #111827;
            --muted: #6B7280;
        }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg);
            color: var(--ink);
        }
        h1, h2, h3, h4, h5, .font-display { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }

        /* Utilitários do design system */
        .fs-7 { font-size: .875rem; }
        .fs-8 { font-size: .75rem; }
        .bg-indigo-100 { background-color: var(--brand-soft); }
        .text-indigo-700 { color: var(--brand-dark); }
        .text-brand { color: var(--brand); }
        .bg-brand { background-color: var(--brand); }
        .btn-brand { background-color: var(--brand); border-color: var(--brand); color: #fff; font-weight: 600; }
        .btn-brand:hover { background-color: var(--brand-dark); border-color: var(--brand-dark); color: #fff; }
        .btn-outline-brand { color: var(--brand); border-color: var(--brand); font-weight: 600; }
        .btn-outline-brand:hover { background-color: var(--brand); color: #fff; }
        .transition-hover { transition: transform .18s ease, box-shadow .18s ease; }
        .transition-hover:hover { transform: translateY(-4px); box-shadow: 0 1rem 2rem rgba(17, 24, 39, .12); }
        .hero-gradient {
            background:
                radial-gradient(1200px 400px at 10% -10%, rgba(79, 70, 229, .18), transparent 60%),
                radial-gradient(900px 400px at 90% 0%, rgba(16, 185, 129, .15), transparent 55%),
                linear-gradient(180deg, #ffffff 0%, var(--bg) 100%);
        }
        .badge-soft {
            background-color: var(--brand-soft);
            color: var(--brand-dark);
            font-weight: 600;
            letter-spacing: .03em;
        }
        .navbar-brand { font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; }
        footer.site-footer { background: #111827; color: #9CA3AF; }
        footer.site-footer a { color: #D1D5DB; text-decoration: none; }
        footer.site-footer a:hover { color: #fff; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand text-brand" href="<?= site_url('/') ?>">
            <i class="bi bi-gift-fill me-1"></i>Minha Lista VIP
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPublica"
                aria-controls="navPublica" aria-expanded="false" aria-label="Alternar navegação">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navPublica">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
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
                    <a class="btn btn-brand btn-sm px-3" href="<?= site_url('registro') ?>">Criar conta grátis</a>
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
                <p class="navbar-brand text-white mb-2"><i class="bi bi-gift-fill me-1"></i>Minha Lista VIP</p>
                <p class="fs-7 mb-0" style="max-width: 380px;">
                    Listas de presentes em dinheiro, RSVP e mural de recados para casamentos,
                    chás e aniversários. O convidado presenteia via PIX e o valor cai na sua conta.
                </p>
            </div>
            <div class="col-6 col-md-3">
                <p class="text-white fw-semibold mb-2 fs-7 text-uppercase">Plataforma</p>
                <ul class="list-unstyled fs-7">
                    <li class="mb-1"><a href="<?= site_url('registro') ?>">Criar lista</a></li>
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
</body>
</html>
