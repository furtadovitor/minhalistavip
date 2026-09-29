<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <p class="text-muted mb-0">Catálogo global para o evento <strong><?= esc($evento->titulo) ?></strong></p>
        <p class="text-muted small mb-0">Selecione os itens e eles serão copiados para a sua lista (editáveis depois).</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= site_url('painel/eventos/' . $evento->id . '/presentes') ?>">
        Voltar para a lista
    </a>
</div>

<form method="get" action="<?= site_url('painel/eventos/' . $evento->id . '/presentes/catalogo') ?>"
      class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label" for="categoria">Categoria</label>
                <select class="form-select" id="categoria" name="categoria">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= (int) $categoria['id'] ?>"
                            <?= (int) ($filtros['categoria_id'] ?? 0) === (int) $categoria['id'] ? 'selected' : '' ?>>
                            <?= esc($categoria['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label" for="busca">Buscar por nome</label>
                <input type="search" class="form-control" id="busca" name="busca"
                       value="<?= esc($filtros['busca'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-outline-primary">Filtrar</button>
            </div>
        </div>
    </div>
</form>

<?php if (empty($itens)): ?>
    <div class="alert alert-info">
        Nenhum item do catálogo encontrado com esses filtros.
        Cadastre itens no catálogo global (área do SuperAdmin) ou use
        <a href="<?= site_url('painel/eventos/' . $evento->id . '/presentes/novo') ?>">um presente customizado</a>.
    </div>
<?php else: ?>
    <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/presentes/clonar') ?>">
        <?= csrf_field() ?>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:44px">
                                <input class="form-check-input" type="checkbox" id="selecionar-todos">
                            </th>
                            <th>Presente</th>
                            <th>Categoria</th>
                            <th>Tipo</th>
                            <th class="text-end">Valor sugerido</th>
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($itens as $item): ?>
                        <?php $jaUsado = in_array((int) $item['id'], $jaUsados, true); ?>
                        <tr>
                            <td>
                                <input class="form-check-input item-catalogo" type="checkbox"
                                       name="catalogo_ids[]" value="<?= (int) $item['id'] ?>"
                                       id="item-<?= (int) $item['id'] ?>" <?= $jaUsado ? 'disabled' : '' ?>>
                            </td>
                            <td>
                                <label for="item-<?= (int) $item['id'] ?>" class="mb-0">
                                    <span class="fw-semibold"><?= esc($item['nome']) ?></span>
                                </label>
                                <?php if (! empty($item['descricao'])): ?>
                                    <div class="text-muted small"><?= esc($item['descricao']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= esc($item['categoria_nome'] ?? '—') ?></td>
                            <td class="small"><?= esc(rotulo_tipo_presente((string) $item['tipo'])) ?></td>
                            <td class="text-end">
                                <?= $item['valor_sugerido'] !== null ? esc(moeda_brl($item['valor_sugerido'])) : '—' ?>
                            </td>
                            <td>
                                <?php if ($jaUsado): ?>
                                    <span class="badge text-bg-light border">já adicionado</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">disponível</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a class="btn btn-outline-secondary"
               href="<?= site_url('painel/eventos/' . $evento->id . '/presentes') ?>">Cancelar</a>
            <button class="btn btn-primary">Adicionar selecionados</button>
        </div>
    </form>
<?php endif; ?>

<script>
    (function () {
        var todos = document.getElementById('selecionar-todos');
        if (!todos) { return; }
        todos.addEventListener('change', function () {
            document.querySelectorAll('.item-catalogo:not(:disabled)').forEach(function (caixa) {
                caixa.checked = todos.checked;
            });
        });
    })();
</script>
<?= $this->endSection() ?>
