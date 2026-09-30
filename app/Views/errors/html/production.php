<?php
helper('url');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Algo deu errado · Minha Lista VIP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root { --brand: #4F46E5; --brand-dark: #4338CA; --brand-soft: #EEF2FF; --bg: #F9FAFB; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background:
                radial-gradient(1200px 400px at 10% -10%, rgba(79, 70, 229, .18), transparent 60%),
                radial-gradient(900px 400px at 90% 0%, rgba(16, 185, 129, .15), transparent 55%),
                linear-gradient(180deg, #ffffff 0%, var(--bg) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        h1, h2 { font-family: 'Plus Jakarta Sans', sans-serif; }
        .navbar-brand { font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; }
        .text-brand { color: var(--brand); }
        .btn-brand { background-color: var(--brand); border-color: var(--brand); color: #fff; font-weight: 600; }
        .btn-brand:hover { background-color: var(--brand-dark); border-color: var(--brand-dark); color: #fff; }
        .btn-outline-brand { color: var(--brand); border-color: var(--brand); font-weight: 600; }
        .btn-outline-brand:hover { background-color: var(--brand); color: #fff; }
        .icone-erro {
            width: 84px; height: 84px; border-radius: 50%;
            background: var(--brand-soft); color: var(--brand-dark);
            display: inline-flex; align-items: center; justify-content: center;
        }
    </style>
</head>
<body>
    <div class="w-100" style="max-width: 620px;">
        <div class="text-center mb-4">
            <a href="<?= site_url('/') ?>" class="navbar-brand text-brand h4 text-decoration-none">
                <i class="bi bi-gift-fill me-1"></i>Minha Lista VIP
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4 text-center">
            <div class="card-body p-4 p-md-5">
                <div class="icone-erro mb-3"><i class="bi bi-exclamation-triangle fs-2"></i></div>
                <h1 class="h4 fw-bold mb-2">Ops, algo deu errado</h1>
                <p class="text-muted mb-4">
                    Tivemos um problema inesperado ao processar sua solicitação.
                    Já fomos notificados — tente novamente em alguns instantes.
                </p>

                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <a class="btn btn-brand" href="<?= site_url('/') ?>">
                        <i class="bi bi-house me-1"></i>Ir para o início
                    </a>
                    <a class="btn btn-outline-brand" href="<?= site_url('login') ?>">Entrar</a>
                </div>
            </div>
        </div>

        <p class="text-center text-muted small mt-4 mb-0">
            Se o problema persistir, fale com o suporte:
            <a href="mailto:suporte@minhalistavip.com.br" class="text-decoration-none">suporte@minhalistavip.com.br</a>
        </p>
    </div>
</body>
</html>
