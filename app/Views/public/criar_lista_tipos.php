<?= $this->extend('templates/layouts/public') ?>

<?= $this->section('conteudo') ?>
<section class="hero-gradient py-5">
    <div class="container py-4 text-center">
        <span class="badge badge-soft rounded-pill px-3 py-2 fs-8 text-uppercase mb-3">
            <i class="bi bi-lightning-charge-fill me-1"></i> Grátis e em 1 minuto
        </span>
        <h1 class="display-6 fw-bold mb-2">Crie sua lista de presentes grátis</h1>
        <p class="fs-6 text-secondary mb-0">
            É grátis e leva cerca de 1 minuto: selecione o tipo de evento, dê um nome e pronto —
            você já cai montando a lista.
        </p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?= view('templates/partials/flash') ?>
        <div class="row g-3 justify-content-center">
            <?= view('templates/partials/tipos_evento_grid', ['tipos' => $tipos]) ?>
        </div>

        <p class="text-center text-muted fs-8 mt-4 mb-0">
            <i class="bi bi-check-circle-fill text-success me-1"></i>
            100% grátis para criar e compartilhar em 1 minuto. Você só recebe o valor quando um convidado presenteia.
        </p>
    </div>
</section>
<?= $this->endSection() ?>
