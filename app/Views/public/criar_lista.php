<?= $this->extend('templates/layouts/public') ?>

<?php
$tituloValor    = old('titulo', $valores['titulo'] ?? '');
$descricaoValor = old('descricao', $valores['descricao'] ?? '');
?>

<?= $this->section('conteudo') ?>
<section class="py-5">
    <div class="container" style="max-width: 720px;">
        <a class="text-muted fs-7 text-decoration-none d-inline-block mb-3"
           href="<?= site_url('criar-lista-de-presente') ?>">
            <i class="bi bi-arrow-left me-1"></i>Trocar o tipo de evento
        </a>

        <div class="d-flex align-items-center gap-3 mb-4">
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
                  style="background: <?= esc($tipo['cor_primaria'], 'attr') ?>1A; font-size: 2rem; width: 72px; height: 72px;">
                <?= esc($tipo['icone']) ?>
            </span>
            <div>
                <p class="text-muted text-uppercase fs-8 fw-semibold mb-0">Nova lista</p>
                <h1 class="h3 fw-bold mb-0"><?= esc($tipo['rotulo']) ?></h1>
            </div>
        </div>

        <?= view('templates/partials/flash') ?>

        <?php if (! $logado): ?>
            <div class="alert alert-warning rounded-4 fs-7 d-flex gap-2">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                    Para publicar a lista você precisa <strong>entrar ou criar uma conta grátis</strong>.
                    Preencha os dados abaixo e nós cuidamos do resto: após o login a lista é criada automaticamente.
                </div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <form method="post" action="<?= site_url('criar-lista-de-presente/' . $tipo['slug']) ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="titulo">Nome da lista *</label>
                        <input type="text" class="form-control form-control-lg" id="titulo" name="titulo"
                               value="<?= esc($tituloValor) ?>" required autofocus
                               placeholder="Ex.: <?= esc($tipo['rotulo']) ?> da Ana e do João">
                        <div class="form-text">É o título que aparece para os seus convidados.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="descricao">Descrição <span class="text-muted fw-normal">(opcional)</span></label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"
                                  placeholder="Conte um pouco sobre a ocasião, o que você deseja ganhar, etc."><?= esc($descricaoValor) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-brand btn-lg w-100">
                        <i class="bi bi-rocket-takeoff me-2"></i>
                        <?= $logado ? 'Criar minha lista agora' : 'Continuar e criar minha lista' ?>
                    </button>
                </form>

                <p class="text-muted fs-8 text-center mt-3 mb-0">
                    <?php if ($logado): ?>
                        Sua lista será criada como rascunho — você publica quando quiser.
                    <?php else: ?>
                        Já tem conta? <a href="<?= site_url('login') ?>" class="text-brand fw-semibold text-decoration-none">Entrar</a>
                        · &nbsp;Novo por aqui? <a href="<?= site_url('registro') ?>" class="text-brand fw-semibold text-decoration-none">Criar conta grátis</a>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="row g-3 mt-4 text-center">
            <div class="col-md-4">
                <p class="fs-7 text-muted mb-0"><i class="bi bi-sliders text-brand me-1"></i> Personalize depois</p>
            </div>
            <div class="col-md-4">
                <p class="fs-7 text-muted mb-0"><i class="bi bi-shield-check text-brand me-1"></i> Grátis para criar</p>
            </div>
            <div class="col-md-4">
                <p class="fs-7 text-muted mb-0"><i class="bi bi-lightning-charge text-brand me-1"></i> Pronta em minutos</p>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
