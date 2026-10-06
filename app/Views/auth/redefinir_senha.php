<?= $this->extend('templates/layouts/auth') ?>

<?= $this->section('conteudo') ?>
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4 p-md-5">
        <h1 class="h4 fw-bold mb-1">Definir nova senha</h1>
        <p class="text-muted fs-7 mb-4">Escolha uma senha com pelo menos 6 caracteres.</p>

        <form method="post" action="<?= site_url('redefinir-senha') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= esc($token, 'attr') ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold fs-7" for="senha">Nova senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" class="form-control" id="senha" name="senha"
                           placeholder="Nova senha" autocomplete="new-password" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold fs-7" for="senha_confirmacao">Confirme a nova senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock-fill text-muted"></i></span>
                    <input type="password" class="form-control" id="senha_confirmacao" name="senha_confirmacao"
                           placeholder="Repita a senha" autocomplete="new-password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-brand w-100 py-2">
                <i class="bi bi-check-lg me-2"></i>Salvar nova senha
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
