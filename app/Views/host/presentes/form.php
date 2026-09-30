<?= $this->extend('templates/layouts/app') ?>

<?php
$edicao = $presente !== null;
$action = $edicao
    ? site_url('painel/eventos/' . $evento->id . '/presentes/' . $presente['id'])
    : site_url('painel/eventos/' . $evento->id . '/presentes');

$campo = static fn (string $chave, $padrao = '') => old($chave, $edicao ? ($presente[$chave] ?? $padrao) : $padrao);
?>

<?= $this->section('conteudo') ?>
<div class="mb-3">
    <p class="text-muted mb-0">Evento: <strong><?= esc($evento->titulo) ?></strong></p>
</div>

<form method="post" action="<?= $action ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="nome">Nome do presente *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required
                               value="<?= esc($campo('nome')) ?>" placeholder="Ex.: Cota da lua de mel">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="descricao">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"><?= esc($campo('descricao')) ?></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="tipo">Tipo *</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="ficticio" <?= $campo('tipo', 'ficticio') === 'ficticio' ? 'selected' : '' ?>>
                                    Cota em dinheiro (PIX)
                                </option>
                                <option value="real" <?= $campo('tipo') === 'real' ? 'selected' : '' ?>>
                                    Presente real (redireciona para link)
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="valor">Valor (R$) *</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="valor" name="valor"
                                   required value="<?= esc($campo('valor', '0')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="quantidade_meta">Cotas</label>
                            <input type="number" min="1" class="form-control" id="quantidade_meta" name="quantidade_meta"
                                   value="<?= esc($campo('quantidade_meta', '1')) ?>">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label" for="link_afiliado">Link de afiliado <span class="text-muted">(só para presentes reais)</span></label>
                        <input type="url" class="form-control" id="link_afiliado" name="link_afiliado"
                               value="<?= esc($campo('link_afiliado')) ?>" placeholder="https://...">
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold fs-7" for="imagem">Imagem do presente</label>
                        <input type="file" class="form-control" id="imagem" name="imagem"
                               accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">JPG, PNG ou WEBP até 2 MB. Aparece no card da página pública.</div>

                        <?php if ($edicao && ! empty($presente['imagem'])): ?>
                            <img src="<?= base_url($presente['imagem']) ?>" alt="Imagem atual"
                                 class="img-fluid rounded-3 mt-2 border" style="max-height: 150px;">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="remover_imagem" name="remover_imagem" value="1">
                                <label class="form-check-label fs-7" for="remover_imagem">Remover imagem atual</label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="ordem">Ordem de exibição</label>
                        <input type="number" class="form-control" id="ordem" name="ordem"
                               value="<?= esc($campo('ordem', '0')) ?>">
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1"
                               <?= $campo('ativo', $edicao ? null : '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="ativo">Visível na página pública</label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-brand">
                    <?= $edicao ? 'Salvar alterações' : 'Adicionar presente' ?>
                </button>
                <a class="btn btn-outline-secondary"
                   href="<?= site_url('painel/eventos/' . $evento->id . '/presentes') ?>">Cancelar</a>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
