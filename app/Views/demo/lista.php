<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($demo['titulo']) ?> · Lista de exemplo · Minha Lista VIP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --cor-primaria: <?= esc($demo['cor_primaria'], 'raw') ?>;
            --cor-secundaria: <?= esc($demo['cor_secundaria'], 'raw') ?>;
        }
        body { font-family: 'Inter', system-ui, sans-serif; }
        h1, h2, h3 { font-family: 'Plus Jakarta Sans', sans-serif; }
        .hero { background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria)); color: #fff; }
        .btn-evento { background-color: var(--cor-primaria); border-color: var(--cor-primaria); color: #fff; font-weight: 600; }
        .btn-evento:hover { filter: brightness(0.92); color: #fff; }
        .titulo-evento { color: var(--cor-primaria); }
        .tema-escuro { background-color: #0B0B12; color: #E5E7EB; }
        .tema-escuro .card { background-color: #15151F; color: #E5E7EB; border: 1px solid rgba(255, 255, 255, .08) !important; }
        .tema-escuro .text-muted { color: #9CA3AF !important; }
        .faixa-demo { background: #111827; color: #fff; }
    </style>
</head>
<body class="bg-body-tertiary <?= ! empty($demo['escuro']) ? 'tema-escuro' : '' ?>">

<div class="faixa-demo py-2">
    <div class="container d-flex flex-wrap align-items-center justify-content-center gap-2 text-center">
        <span class="fs-7"><i class="bi bi-eye-fill me-1"></i>
            Você está vendo uma <strong>lista de exemplo</strong>.</span>
        <a class="btn btn-sm btn-light fw-semibold" href="<?= site_url('registro') ?>">Criar a minha lista grátis</a>
    </div>
</div>

<header class="hero py-5">
    <div class="container text-center" style="max-width: 820px;">
        <p class="text-uppercase small mb-1 opacity-75"><?= esc($demo['icone']) ?> <?= esc($demo['tipo']) ?></p>
        <h1 class="display-6 fw-bold mb-2"><?= esc($demo['titulo']) ?></h1>
        <?php if (! empty($demo['descricao'])): ?>
            <p class="lead mb-3"><?= esc($demo['descricao']) ?></p>
        <?php endif; ?>
        <p class="mb-0">
            <i class="bi bi-calendar-event me-1"></i><?= esc($demo['data']) ?>
            <span class="mx-2">·</span>
            <i class="bi bi-geo-alt me-1"></i><?= esc($demo['local']) ?>
        </p>
    </div>
</header>

<main class="container py-4" style="max-width: 900px;">
    <?php if (! empty($demo['mensagem_convite'])): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <p class="mb-0"><?= esc($demo['mensagem_convite']) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="h4 titulo-evento mb-3">Lista de presentes</h2>

    <div class="row g-3">
        <?php foreach ($demo['itens'] as $item): ?>
            <div class="col-sm-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h3 class="h6"><?= esc($item['nome']) ?></h3>
                        <?php if (! empty($item['descricao'])): ?>
                            <p class="text-muted small"><?= esc($item['descricao']) ?></p>
                        <?php endif; ?>

                        <div class="mt-auto">
                            <p class="fw-bold mb-1"><?= esc(moeda_brl($item['valor'])) ?></p>
                            <?php if ((int) $item['meta'] > 1): ?>
                                <p class="text-muted small mb-2"><?= (int) $item['vendida'] ?>/<?= (int) $item['meta'] ?> cotas presenteadas</p>
                            <?php endif; ?>
                            <button class="btn btn-evento btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalDemo">
                                Presentear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (! empty($demo['recados'])): ?>
        <h2 class="h4 titulo-evento mt-5 mb-3">Mural de recados</h2>
        <div class="row g-3">
            <?php foreach ($demo['recados'] as $recado): ?>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="mb-2"><?= esc($recado['mensagem']) ?></p>
                            <p class="text-muted small mb-0">— <?= esc($recado['autor']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="text-center mt-5">
        <p class="text-muted">Gostou? Crie uma lista assim para o seu evento em poucos minutos.</p>
        <a class="btn btn-evento btn-lg px-4" href="<?= site_url('registro') ?>">Criar minha lista grátis</a>
    </div>
</main>

<footer class="text-center text-muted small py-4">
    Página criada com <a href="<?= site_url('/') ?>" class="text-decoration-none">Minha Lista VIP</a>
</footer>

<!-- Modal de demonstração -->
<div class="modal fade" id="modalDemo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-body text-center p-4">
                <div class="mb-3" style="font-size: 2.5rem;">🎁</div>
                <h3 class="h5 fw-bold mb-2">Esta é uma lista de exemplo</h3>
                <p class="text-muted">
                    Aqui é só demonstração — nenhum pagamento é processado nesta página.
                    Crie a sua lista e comece a receber de verdade via PIX.
                </p>
                <div class="d-grid gap-2">
                    <a class="btn btn-evento" href="<?= site_url('registro') ?>">Criar minha lista grátis</a>
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Continuar navegando</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
