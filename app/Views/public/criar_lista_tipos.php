<?= $this->extend('templates/layouts/public') ?>

<?= $this->section('conteudo') ?>
<section class="hero-gradient py-5">
    <div class="container py-4 text-center">
        <span class="badge badge-soft rounded-pill px-3 py-2 fs-8 text-uppercase mb-3">
            <i class="bi bi-magic me-1"></i> Comece escolhendo a ocasião
        </span>
        <h1 class="display-6 fw-bold mb-2">Crie sua lista de presentes grátis</h1>
        <p class="fs-6 text-secondary mb-0">
            Selecione o tipo de evento, dê um nome e uma descrição e pronto — você já cai montando a lista.
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
            É grátis para criar e compartilhar. Você só recebe o valor quando um convidado presenteia.
        </p>
    </div>
</section>
<?= $this->endSection() ?>
