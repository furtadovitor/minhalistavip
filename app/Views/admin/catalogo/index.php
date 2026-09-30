<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted mb-0">
        <?= count($itens) ?> item(ns) · estes itens podem ser clonados pelos organizadores.
    </p>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="<?= site_url('admin/categorias') ?>">
            <i class="bi bi-tags me-1"></i>Categorias
        </a>
        <a class="btn btn-brand" href="<?= site_url('admin/catalogo/novo') ?>">
            <i class="bi bi-plus-lg me-1"></i>Novo item
        </a>
    </div>
</div>

<form class="card border-0 shadow-sm rounded-4 mb-3" method="get" action="<?= site_url('admin/catalogo') ?>">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-categoria">Categoria</label>
                <select class="form-select" id="f-categoria" name="categoria">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= (int) $categoria['id'] ?>" <?= (int) ($filtros['categoria_id'] ?? 0) === (int) $categoria['id'] ? 'selected' : '' ?>>
                            <?= esc($categoria['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-busca">Buscar</label>
                <input type="search" class="form-control" id="f-busca" name="busca" value="<?= esc($filtros['busca'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-ativo">Situação</label>
                <select class="form-select" id="f-ativo" name="ativo">
                    <option value="">Todas</option>
                    <option value="1" <?= (string) ($filtros['ativo'] ?? '') === '1' ? 'selected' : '' ?>>Ativos</option>
                    <option value="0" <?= (string) ($filtros['ativo'] ?? '') === '0' ? 'selected' : '' ?>>Inativos</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-brand w-100"><i class="bi bi-funnel me-1"></i>Filtrar</button>
            </div>
        </div>
    </div>
</form>

<?php if (empty($itens)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <p class="mb-1 fw-semibold">Nenhum item encontrado.</p>
            <p class="text-muted mb-0">Ajuste os filtros ou cadastre um novo item no catálogo.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Categoria</th>
                        <th>Tipo</th>
                        <th class="text-end">Valor sugerido</th>
                        <th class="text-center">Destaque</th>
                        <th class="text-center">Ativo</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($itens as $item): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= esc($item['nome']) ?></div>
                            <?php if (! empty($item['descricao'])): ?>
                                <div class="text-muted fs-8"><?= esc($item['descricao']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="small"><?= esc($item['categoria_nome'] ?? '—') ?></td>
                        <td class="small"><?= esc(rotulo_tipo_presente((string) $item['tipo'])) ?></td>
                        <td class="text-end">
                            <?= $item['valor_sugerido'] !== null ? esc(moeda_brl($item['valor_sugerido'])) : '—' ?>
                        </td>
                        <td class="text-center">
                            <?= ! empty($item['destaque']) ? '<i class="bi bi-star-fill text-warning"></i>' : '<span class="text-muted">—</span>' ?>
                        </td>
                        <td class="text-center">
                            <span class="badge text-bg-<?= ! empty($item['ativo']) ? 'success' : 'secondary' ?>">
                                <?= ! empty($item['ativo']) ? 'Sim' : 'Não' ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap gap-1 justify-content-end">
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="<?= site_url('admin/catalogo/' . $item['id'] . '/editar') ?>">Editar</a>
                                <form method="post" class="d-inline"
                                      action="<?= site_url('admin/catalogo/' . $item['id'] . '/alternar') ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-<?= ! empty($item['ativo']) ? 'warning' : 'success' ?>">
                                        <?= ! empty($item['ativo']) ? 'Desativar' : 'Ativar' ?>
                                    </button>
                                </form>
                                <form method="post" class="d-inline"
                                      action="<?= site_url('admin/catalogo/' . $item['id'] . '/excluir') ?>"
                                      onsubmit="return confirm('Remover este item do catálogo global?');">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger btn-icon"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
