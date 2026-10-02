<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4" style="position: sticky; top: 5rem;">
            <div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted fw-semibold mb-3">Nova categoria</h2>
                <form method="post" action="<?= site_url('admin/categorias') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7" for="novo-nome">Nome *</label>
                        <input type="text" class="form-control" id="novo-nome" name="nome" required placeholder="Ex.: Cozinha">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7" for="novo-icone">Ícone <span class="text-muted fw-normal">(classe Bootstrap Icons)</span></label>
                        <input type="text" class="form-control" id="novo-icone" name="icone" placeholder="bi-cup-hot">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7" for="novo-ordem">Ordem</label>
                        <input type="number" class="form-control" id="novo-ordem" name="ordem" value="0">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="novo-ativo" name="ativo" value="1" checked>
                        <label class="form-check-label" for="novo-ativo">Ativa</label>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Criar categoria</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-3">
                <h2 class="h6 mb-0 text-muted text-uppercase fs-8">Categorias do catálogo</h2>
            </div>
            <?php if (empty($categorias)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">Nenhuma categoria cadastrada.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Ícone</th>
                                <th style="width:90px">Ordem</th>
                                <th>Itens</th>
                                <th class="text-center">Ativa</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($categorias as $categoria): ?>
                            <tr>
                                <form method="post" action="<?= site_url('admin/categorias/' . $categoria['id']) ?>">
                                    <?= csrf_field() ?>
                                    <td><input type="text" class="form-control form-control-sm" name="nome" value="<?= esc($categoria['nome']) ?>" required></td>
                                    <td><input type="text" class="form-control form-control-sm" name="icone" value="<?= esc((string) $categoria['icone']) ?>" style="max-width:130px"></td>
                                    <td><input type="number" class="form-control form-control-sm" name="ordem" value="<?= (int) $categoria['ordem'] ?>"></td>
                                    <td class="small text-muted"><?= (int) ($categoria['total_itens'] ?? 0) ?></td>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" name="ativo" value="1" <?= ! empty($categoria['ativo']) ? 'checked' : '' ?>>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                                            <button class="btn btn-sm btn-brand">Salvar</button>
                                            <button class="btn btn-sm btn-outline-<?= ! empty($categoria['ativo']) ? 'warning' : 'success' ?>"
                                                    formaction="<?= site_url('admin/categorias/' . $categoria['id'] . '/alternar') ?>">
                                                <?= ! empty($categoria['ativo']) ? 'Desativar' : 'Ativar' ?>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-icon"
                                                    formaction="<?= site_url('admin/categorias/' . $categoria['id'] . '/excluir') ?>"
                                                    data-confirm="Remover esta categoria? Itens ficarão sem categoria.">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
