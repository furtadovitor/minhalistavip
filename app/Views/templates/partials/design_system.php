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
        --raio-btn: .7rem;
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
</style>
