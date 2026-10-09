<?php
/**
 * Cabeçalho do design system da plataforma (Home, login, registro).
 *
 * Uso: <head><?= view('templates/partials/design_system', ['titulo' => '...']) ?></head>
 *
 * @var string|null $titulo
 */
$titulo = $titulo ?? 'Minha Lista VIP';
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($titulo) ?></title>

<link rel="icon" href="<?= base_url('favicon.ico') ?>" sizes="any">
<link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>">
<link rel="apple-touch-icon" href="<?= base_url('apple-touch-icon.png') ?>">
<meta name="theme-color" content="#722ED4">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    :root {
        /* Roxo da marca — o mesmo "VIP" da logo (#722ED4) */
        --brand: #722ED4;
        --brand-dark: #5B21B6;
        --brand-soft: #F3EDFE;
        --success: #10B981;
        --bg: #F9FAFB;
        --ink: #111827;
        --muted: #6B7280;
        --raio-btn: .7rem;

        /* Alinha o Bootstrap (links e "primary") à cor da marca */
        --bs-primary: var(--brand);
        --bs-primary-rgb: 114, 46, 212;
        --bs-primary-bg-subtle: var(--brand-soft);
        --bs-primary-text-emphasis: var(--brand-dark);
        --bs-link-color: var(--brand);
        --bs-link-color-rgb: 114, 46, 212;
        --bs-link-hover-color: var(--brand-dark);
        --bs-link-hover-color-rgb: 91, 33, 182;
    }
    body {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        background-color: var(--bg);
        color: var(--ink);
    }
    h1, h2, h3, h4, h5, h6, .font-display { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }

    /* Botões e campos padronizados (canto suave) no painel/Home/login */
    .btn,
    .form-control,
    .form-select {
        border-radius: var(--raio-btn);
    }
    .input-group > :first-child {
        border-top-left-radius: var(--raio-btn);
        border-bottom-left-radius: var(--raio-btn);
    }
    .input-group > :last-child {
        border-top-right-radius: var(--raio-btn);
        border-bottom-right-radius: var(--raio-btn);
    }

    /* Botões somente com ícone ficam circulares (mesmo padrão em todo o painel) */
    .btn-icon {
        width: 2.1rem;
        height: 2.1rem;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50% !important;
    }
    .btn-icon.btn-sm { width: 1.9rem; height: 1.9rem; }

    /* Abas em pílula com o mesmo raio e a cor da marca */
    .nav-pills .nav-link {
        border-radius: var(--raio-btn);
        font-weight: 600;
        color: var(--muted);
    }
    .nav-pills .nav-link:hover { color: var(--brand-dark); background-color: var(--brand-soft); }
    .nav-pills .nav-link.active { background-color: var(--brand); color: #fff; }

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

    /* Componentes do Bootstrap no tom da marca (site, painel e admin) */
    .form-control:focus,
    .form-select:focus {
        border-color: #B79BEF;
        box-shadow: 0 0 0 .25rem rgba(114, 46, 212, .18);
    }
    .form-check-input:checked {
        background-color: var(--brand);
        border-color: var(--brand);
    }
    .form-check-input:focus {
        border-color: #B79BEF;
        box-shadow: 0 0 0 .25rem rgba(114, 46, 212, .18);
    }
    .btn-primary {
        --bs-btn-bg: var(--brand);
        --bs-btn-border-color: var(--brand);
        --bs-btn-hover-bg: var(--brand-dark);
        --bs-btn-hover-border-color: var(--brand-dark);
        --bs-btn-active-bg: var(--brand-dark);
        --bs-btn-active-border-color: var(--brand-dark);
        --bs-btn-disabled-bg: var(--brand);
        --bs-btn-disabled-border-color: var(--brand);
        --bs-btn-focus-shadow-rgb: 114, 46, 212;
    }
    .btn-outline-primary {
        --bs-btn-color: var(--brand);
        --bs-btn-border-color: var(--brand);
        --bs-btn-hover-bg: var(--brand);
        --bs-btn-hover-border-color: var(--brand);
        --bs-btn-active-bg: var(--brand-dark);
        --bs-btn-active-border-color: var(--brand-dark);
        --bs-btn-focus-shadow-rgb: 114, 46, 212;
    }
    .pagination {
        --bs-pagination-color: var(--brand);
        --bs-pagination-hover-color: var(--brand-dark);
        --bs-pagination-focus-color: var(--brand-dark);
        --bs-pagination-focus-box-shadow: 0 0 0 .25rem rgba(114, 46, 212, .18);
        --bs-pagination-active-bg: var(--brand);
        --bs-pagination-active-border-color: var(--brand);
    }
    .dropdown-menu { --bs-dropdown-link-active-bg: var(--brand); }
    .list-group {
        --bs-list-group-active-bg: var(--brand);
        --bs-list-group-active-border-color: var(--brand);
    }
    .progress { --bs-progress-bar-bg: var(--brand); }
    .nav-tabs .nav-link.active { color: var(--brand-dark); }
    .transition-hover { transition: transform .18s ease, box-shadow .18s ease; }
    .transition-hover:hover { transform: translateY(-4px); box-shadow: 0 1rem 2rem rgba(17, 24, 39, .12); }
    .hero-gradient {
        background:
            radial-gradient(1200px 400px at 10% -10%, rgba(114, 46, 212, .18), transparent 60%),
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

    /* Páginas de autenticação */
    .auth-shell {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }
    .auth-card { width: 100%; max-width: 460px; }
    .auth-logo {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        color: var(--brand);
        text-decoration: none;
    }

    footer.site-footer { background: #111827; color: #9CA3AF; }
    footer.site-footer a { color: #D1D5DB; text-decoration: none; }
    footer.site-footer a:hover { color: #fff; }

    /* Logo da plataforma (lockup rasterizado "Minha Lista VIP") */
    .brand-logo { display: inline-flex; align-items: center; gap: .55rem; text-decoration: none; }
    .brand-logo .brand-img { display: block; flex: none; }
    .brand-logo .brand-img-mark { display: none; } /* exibida só no menu recolhido */
</style>
