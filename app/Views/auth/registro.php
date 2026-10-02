<?= $this->extend('templates/layouts/auth') ?>

<?= $this->section('conteudo') ?>
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4 p-md-5">
        <h1 class="h4 fw-bold mb-1">Criar conta de organizador</h1>
        <p class="text-muted fs-7 mb-4">Em poucos minutos sua lista de presentes está no ar.</p>

        <?= view('templates/partials/google_login') ?>

        <form method="post" action="<?= site_url('registro') ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label fw-semibold fs-7" for="nome">Nome completo</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" class="form-control" id="nome" name="nome"
                           value="<?= esc(old('nome')) ?>" placeholder="Seu nome" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold fs-7" for="email">E-mail</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= esc(old('email')) ?>" placeholder="voce@email.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold fs-7" for="telefone">
                    Telefone / WhatsApp <span class="text-muted fw-normal">(opcional)</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-whatsapp text-muted"></i></span>
                    <input type="text" class="form-control" id="telefone" name="telefone"
                           value="<?= esc(old('telefone')) ?>" placeholder="(11) 99999-9999">
                </div>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-sm-6">
                    <label class="form-label fw-semibold fs-7" for="senha">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha"
                           placeholder="Mín. 6 caracteres" required>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-semibold fs-7" for="senha_confirmacao">Confirmar senha</label>
                    <input type="password" class="form-control" id="senha_confirmacao" name="senha_confirmacao"
                           placeholder="Repita a senha" required>
                </div>
            </div>

            <button type="submit" class="btn btn-brand w-100 py-2">
                <i class="bi bi-rocket-takeoff me-2"></i>Criar conta grátis
            </button>

            <p class="text-muted fs-8 text-center mt-3 mb-0">
                Ao criar a conta você concorda com os termos de uso da plataforma.
            </p>
        </form>

        <p class="text-center small mt-4 mb-0">
            Já tem conta?
            <a href="<?= site_url('login') ?>" class="text-brand fw-semibold text-decoration-none">Entrar</a>
        </p>
    </div>
</div>

<p class="text-center mt-3 mb-0">
    <a href="<?= site_url('/') ?>" class="text-muted fs-7 text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i>Voltar para o site
    </a>
</p>
<?= $this->endSection() ?>
