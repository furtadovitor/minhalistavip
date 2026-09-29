<?= $this->extend('templates/layouts/auth') ?>

<?= $this->section('conteudo') ?>
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <h2 class="h5 mb-3">Criar conta de organizador</h2>

        <form method="post" action="<?= site_url('registro') ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="nome">Nome completo</label>
                <input type="text" class="form-control" id="nome" name="nome"
                       value="<?= esc(old('nome')) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">E-mail</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= esc(old('email')) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="telefone">Telefone / WhatsApp <span class="text-muted">(opcional)</span></label>
                <input type="text" class="form-control" id="telefone" name="telefone"
                       value="<?= esc(old('telefone')) ?>">
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label" for="senha">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" required>
                </div>
                <div class="col-6">
                    <label class="form-label" for="senha_confirmacao">Confirmar</label>
                    <input type="password" class="form-control" id="senha_confirmacao" name="senha_confirmacao" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Criar conta</button>
        </form>

        <p class="text-center small mt-3 mb-0">
            Já tem conta? <a href="<?= site_url('login') ?>">Entrar</a>
        </p>
    </div>
</div>
<?= $this->endSection() ?>
