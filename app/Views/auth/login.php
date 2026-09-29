<?= $this->extend('templates/layouts/auth') ?>

<?= $this->section('conteudo') ?>
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <h2 class="h5 mb-3">Entrar na sua conta</h2>

        <form method="post" action="<?= site_url('login') ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="email">E-mail</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= esc(old('email')) ?>" autocomplete="username" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label" for="senha">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha"
                       autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>

        <p class="text-center small mt-3 mb-0">
            Ainda não tem conta? <a href="<?= site_url('registro') ?>">Cadastre-se</a>
        </p>
    </div>
</div>
<?= $this->endSection() ?>
