<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <p class="text-muted mb-0">
            <?= esc($evento->titulo) ?> ·
            <span class="badge text-bg-<?= cor_status_evento($evento->status) ?>">
                <?= esc(rotulo_status_evento($evento->status)) ?>
            </span>
        </p>
        <p class="text-muted fs-8 mb-0">
            Página pública: <a href="<?= site_url($evento->slug) ?>" target="_blank"><?= esc($evento->slug) ?></a>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="<?= site_url('painel/eventos') ?>">Voltar</a>
        <a class="btn btn-outline-brand" href="<?= site_url('painel/eventos/' . $evento->id . '/presentes/catalogo') ?>">
            <i class="bi bi-collection me-1"></i>Clonar do catálogo
        </a>
        <a class="btn btn-brand" href="<?= site_url('painel/eventos/' . $evento->id . '/presentes/novo') ?>">
            <i class="bi bi-plus-lg me-1"></i>Novo presente
        </a>
    </div>
</div>

<?php if (empty($presentes)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <div class="bg-indigo-100 text-indigo-700 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:64px;height:64px;"><i class="bi bi-gift fs-3"></i></div>
            <p class="mb-1 fw-semibold">A lista de presentes está vazia.</p>
            <p class="text-muted">Adicione itens manualmente ou clone do catálogo global da plataforma.</p>
            <a class="btn btn-brand" href="<?= site_url('painel/eventos/' . $evento->id . '/presentes/catalogo') ?>">
                Clonar do catálogo
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Presente</th>
                        <th>Tipo</th>
                        <th class="text-end">Valor</th>
                        <th class="text-center">Cotas</th>
                        <th class="text-center">Ativo</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($presentes as $presente): ?>
                    <tr>
                        <td class="text-muted small"><?= (int) $presente['ordem'] ?></td>
                        <td>
                            <div class="fw-semibold"><?= esc($presente['nome']) ?></div>
                            <?php if (! empty($presente['descricao'])): ?>
                                <div class="text-muted fs-8"><?= esc($presente['descricao']) ?></div>
                            <?php endif; ?>
                            <?php if (! empty($presente['catalogo_id'])): ?>
                                <span class="badge text-bg-light border fs-8">clonado do catálogo</span>
                            <?php endif; ?>
                        </td>
                        <td class="small"><?= esc(rotulo_tipo_presente((string) $presente['tipo'])) ?></td>
                        <td class="text-end"><?= esc(moeda_brl($presente['valor'])) ?></td>
                        <td class="text-center small">
                            <?= (int) $presente['quantidade_vendida'] ?>/<?= (int) $presente['quantidade_meta'] ?>
                        </td>
                        <td class="text-center">
                            <?php if (! empty($presente['ativo'])): ?>
                                <span class="badge text-bg-success">Sim</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Não</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap gap-1 justify-content-end">
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="<?= site_url('painel/eventos/' . $evento->id . '/presentes/' . $presente['id'] . '/editar') ?>">
                                    Editar
                                </a>

                                <form method="post" class="d-inline"
                                      action="<?= site_url('painel/eventos/' . $evento->id . '/presentes/' . $presente['id'] . '/alternar') ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-<?= ! empty($presente['ativo']) ? 'warning' : 'success' ?>">
                                        <?= ! empty($presente['ativo']) ? 'Desativar' : 'Ativar' ?>
                                    </button>
                                </form>

                                <form method="post" class="d-inline"
                                      action="<?= site_url('painel/eventos/' . $evento->id . '/presentes/' . $presente['id'] . '/excluir') ?>"
                                      onsubmit="return confirm('Remover este presente da lista?');">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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
