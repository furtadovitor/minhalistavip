<?= $this->extend('templates/layouts/auth') ?>

<?= $this->section('conteudo') ?>
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4 p-md-5">
        <h1 class="h4 fw-bold mb-1">Recuperar senha</h1>
        <p class="text-muted fs-7 mb-4">Informe seu e-mail e enviaremos um link para você criar uma nova senha.</p>

        <form method="post" action="<?= site_url('esqueci-senha') ?>">
            <?= csrf_field() ?>

            <div class="mb-4">
                <label class="form-label fw-semibold fs-7" for="email">E-mail</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= esc(old('email')) ?>" placeholder="voce@email.com"
                           autocomplete="username" required autofocus>
                </div>
            </div>

            <button type="submit" class="btn btn-brand w-100 py-2">
                <i class="bi bi-send me-2"></i>Enviar link
            </button>
        </form>

        <p class="text-center small mt-4 mb-0">
            <a href="<?= site_url('login') ?>" class="text-brand fw-semibold text-decoration-none">Voltar para o login</a>
        </p>
    </div>
</div>
<?= $this->endSection() ?>
