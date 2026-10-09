<?php

/**
 * @var \App\Entities\Evento $evento
 * @var \App\Entities\Pedido $pedido
 * @var array<string, mixed>|null $presente
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?= view('templates/partials/design_evento', [
        'titulo'        => 'Obrigado! · ' . $evento->titulo,
        'corPrimaria'   => $evento->cor_primaria,
        'corSecundaria' => $evento->cor_secundaria,
        'tema'          => $evento->tema ?? 'classico',
        'seo'           => ['noindex' => true],
    ]) ?>
</head>
<body class="bg-body-tertiary">
<?= view('templates/partials/analytics_body') ?>
<header class="hero py-5 text-center">
    <div class="container" style="max-width: 620px;">
        <div class="display-5 mb-2"><i class="bi bi-check-circle-fill" style="color: var(--cor-secundaria);"></i></div>
        <h1 class="h3 fw-bold mb-1">Obrigado, <?= esc($pedido->nome_convidado) ?>!</h1>
        <p class="mb-0 opacity-75">Seu presente foi confirmado para <?= esc($evento->titulo) ?>.</p>
    </div>
</header>

<main class="container py-4" style="max-width: 620px;">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h6 text-uppercase text-muted fw-semibold mb-3">Resumo do presente</h2>
            <dl class="row mb-0">
                <?php if ($presente !== null): ?>
                    <dt class="col-7 fw-normal">Presente</dt>
                    <dd class="col-5 text-end"><?= esc($presente['nome']) ?></dd>
                <?php endif; ?>

                <dt class="col-7 fw-normal">Quantidade</dt>
                <dd class="col-5 text-end"><?= (int) $pedido->quantidade ?></dd>

                <dt class="col-7 fw-semibold border-top pt-3 mt-3">Total pago</dt>
                <dd class="col-5 text-end fw-bold border-top pt-3 mt-3"><?= esc(moeda_brl($pedido->valor_total)) ?></dd>

                <dt class="col-7 fw-normal">Pedido</dt>
                <dd class="col-5 text-end"><?= esc($pedido->protocolo) ?></dd>
            </dl>

            <?php if (! empty($pedido->mensagem)): ?>
                <div class="border-top mt-3 pt-3">
                    <p class="text-muted fs-8 text-uppercase fw-semibold mb-1">Sua mensagem</p>
                    <p class="mb-0 fst-italic">"<?= esc($pedido->mensagem) ?>"</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <a class="btn btn-evento w-100 py-2 mb-3" href="<?= site_url($evento->slug) ?>">
        <i class="bi bi-arrow-left me-1"></i>Voltar para o evento
    </a>

    <p class="text-center text-muted fs-8 mb-0">
        Guarde o número do pedido: <strong><?= esc($pedido->protocolo) ?></strong>
    </p>
</main>

<footer class="text-center text-muted small py-4">
    Página criada com <a href="<?= site_url('/') ?>" class="text-decoration-none">Minha Lista VIP</a>
</footer>
</body>
</html>
