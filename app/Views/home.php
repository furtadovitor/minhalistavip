<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="p-5 mb-4 bg-white rounded-3 shadow-sm">
    <div class="container-fluid py-3">
        <p class="text-uppercase text-muted small mb-2">Minha Lista VIP</p>
        <h1 class="display-6 fw-bold">Presentes em dinheiro para o seu evento</h1>
        <p class="col-md-8 fs-5 text-muted">
            Crie a lista do seu casamento, chá de bebê, chá de panela ou aniversário em
            <strong><?= esc(parse_url(base_url(), PHP_URL_HOST) ?: 'minhalistavip.com.br') ?>/seuevento</strong>. Os convidados escolhem um presente
            e o valor cai direto na sua conta via PIX.
        </p>
        <a class="btn btn-primary btn-lg" href="<?= site_url('registro') ?>">Criar minha lista</a>
        <a class="btn btn-outline-secondary btn-lg" href="<?= site_url('login') ?>">Já tenho conta</a>
    </div>
</div>
<?= $this->endSection() ?>
