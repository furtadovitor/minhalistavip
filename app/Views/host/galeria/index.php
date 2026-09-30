<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <h2 class="h6 text-uppercase text-muted mb-3">Adicionar fotos</h2>
        <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/galeria') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label" for="imagens">Imagens</label>
                    <input type="file" class="form-control" id="imagens" name="imagens[]" accept="image/*" multiple required>
                    <div class="form-text">JPG, PNG ou WEBP até 2 MB cada. Selecione várias de uma vez.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="legenda">Legenda (opcional)</label>
                    <input type="text" class="form-control" id="legenda" name="legenda" maxlength="180">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-brand w-100"><i class="bi bi-cloud-upload me-1"></i>Enviar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (empty($fotos)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-images fs-2 d-block mb-2"></i>
            Nenhuma foto na galeria ainda.
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($fotos as $foto): ?>
            <div class="col-sm-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="position-relative" style="height:180px; background:#f3f4f6;">
                        <img src="<?= base_url($foto['imagem']) ?>" alt="" class="w-100 h-100" style="object-fit:cover;">
                        <?php if ((int) $foto['ativo'] !== 1): ?>
                            <span class="badge text-bg-secondary position-absolute" style="top:.5rem; left:.5rem;">Oculta</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/galeria/' . $foto['id']) ?>" class="mb-2">
                            <?= csrf_field() ?>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" name="legenda" maxlength="180"
                                       value="<?= esc((string) $foto['legenda']) ?>" placeholder="Legenda">
                                <input type="number" class="form-control" name="ordem" style="max-width:80px;"
                                       value="<?= (int) $foto['ordem'] ?>" title="Ordem">
                                <button class="btn btn-outline-brand" title="Salvar"><i class="bi bi-check-lg"></i></button>
                            </div>
                        </form>
                        <div class="d-flex gap-1">
                            <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/galeria/' . $foto['id'] . '/alternar') ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-secondary" title="<?= (int) $foto['ativo'] === 1 ? 'Ocultar' : 'Mostrar' ?>">
                                    <i class="bi <?= (int) $foto['ativo'] === 1 ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                </button>
                            </form>
                            <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/galeria/' . $foto['id'] . '/excluir') ?>"
                                  onsubmit="return confirm('Remover esta foto?');">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
