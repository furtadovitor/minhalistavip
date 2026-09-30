<?php
helper('url');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Requisição inválida · Minha Lista VIP</title>

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
        .codigo-erro {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: clamp(4rem, 14vw, 7rem);
            line-height: 1;
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .btn-brand { background-color: var(--brand); border-color: var(--brand); color: #fff; font-weight: 600; }
        .btn-brand:hover { background-color: var(--brand-dark); border-color: var(--brand-dark); color: #fff; }
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
                <p class="codigo-erro mb-0">400</p>
                <h1 class="h4 fw-bold mt-2 mb-2">Requisição inválida</h1>
                <p class="text-muted mb-4">
                    Não conseguimos processar o que foi enviado. Verifique os dados e tente novamente.
                </p>
                <a class="btn btn-brand" href="<?= site_url('/') ?>">
                    <i class="bi bi-house me-1"></i>Ir para o início
                </a>
            </div>
        </div>
    </div>
</body>
</html>
