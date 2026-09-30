<?= $this->extend('templates/layouts/app') ?>

<?php
$edicao = $item !== null;
$action = $edicao ? site_url('admin/catalogo/' . $item['id']) : site_url('admin/catalogo');
$campo  = static fn (string $chave, $padrao = '') => old($chave, $edicao ? ($item[$chave] ?? $padrao) : $padrao);
?>

<?= $this->section('conteudo') ?>
<form method="post" action="<?= $action ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted fw-semibold mb-3">Item do catálogo</h2>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7" for="nome">Nome *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required
                               value="<?= esc($campo('nome')) ?>" placeholder="Ex.: Jogo de Panelas">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7" for="descricao">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"><?= esc($campo('descricao')) ?></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-7" for="categoria_id">Categoria</label>
                            <select class="form-select" id="categoria_id" name="categoria_id">
                                <option value="">—</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= (int) $categoria['id'] ?>"
                                        <?= (int) $campo('categoria_id') === (int) $categoria['id'] ? 'selected' : '' ?>>
                                        <?= esc($categoria['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-7" for="tipo">Tipo *</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="ficticio" <?= $campo('tipo', 'ficticio') === 'ficticio' ? 'selected' : '' ?>>
                                    Cota em dinheiro (PIX)
                                </option>
                                <option value="real" <?= $campo('tipo') === 'real' ? 'selected' : '' ?>>
                                    Presente real (link de afiliado)
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-7" for="valor_sugerido">Valor sugerido (R$)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="valor_sugerido"
                                   name="valor_sugerido" value="<?= esc($campo('valor_sugerido')) ?>">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold fs-7" for="link_afiliado">
                            Link de afiliado <span class="text-muted fw-normal">(para presentes reais)</span>
                        </label>
                        <input type="url" class="form-control" id="link_afiliado" name="link_afiliado"
                               value="<?= esc($campo('link_afiliado')) ?>" placeholder="https://...">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7" for="ordem">Ordem de exibição</label>
                        <input type="number" class="form-control" id="ordem" name="ordem" value="<?= esc($campo('ordem', '0')) ?>">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1"
                               <?= $campo('ativo', $edicao ? null : '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="ativo">Disponível para clonagem</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="destaque" name="destaque" value="1"
                               <?= $campo('destaque') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="destaque">Destacar no catálogo</label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-brand">
                    <?= $edicao ? 'Salvar alterações' : 'Adicionar ao catálogo' ?>
                </button>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/catalogo') ?>">Cancelar</a>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
