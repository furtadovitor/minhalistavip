<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted mb-0"><?= count($planos) ?> plano(s) de assinatura.</p>
    <a class="btn btn-brand" href="<?= site_url('admin/planos/novo') ?>">
        <i class="bi bi-plus-lg me-1"></i>Novo plano
    </a>
</div>

<?php if (empty($planos)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <p class="mb-1 fw-semibold">Nenhum plano cadastrado.</p>
            <p class="text-muted mb-0">Crie planos de assinatura para a receita recorrente da plataforma.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Plano</th>
                        <th>Período</th>
                        <th class="text-end">Preço</th>
                        <th class="text-end">Taxa</th>
                        <th class="text-center">Limite de eventos</th>
                        <th class="text-center">Ativo</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($planos as $plano): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= esc($plano['nome']) ?></div>
                            <div class="text-muted fs-8"><?= esc($plano['slug']) ?></div>
                        </td>
                        <td class="small"><?= esc(ucfirst((string) $plano['periodo'])) ?></td>
                        <td class="text-end"><?= esc(moeda_brl($plano['preco'])) ?></td>
                        <td class="text-end small">
                            <?= $plano['percentual_taxa'] !== null ? esc(number_format((float) $plano['percentual_taxa'], 2, ',', '.')) . '%' : 'Padrão' ?>
                        </td>
                        <td class="text-center small">
                            <?= $plano['limite_eventos'] !== null ? (int) $plano['limite_eventos'] : 'Ilimitado' ?>
                        </td>
                        <td class="text-center">
                            <span class="badge text-bg-<?= ! empty($plano['ativo']) ? 'success' : 'secondary' ?>">
                                <?= ! empty($plano['ativo']) ? 'Sim' : 'Não' ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap gap-1 justify-content-end">
                                <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('admin/planos/' . $plano['id'] . '/editar') ?>">Editar</a>
                                <form method="post" class="d-inline" action="<?= site_url('admin/planos/' . $plano['id'] . '/alternar') ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-<?= ! empty($plano['ativo']) ? 'warning' : 'success' ?>">
                                        <?= ! empty($plano['ativo']) ? 'Desativar' : 'Ativar' ?>
                                    </button>
                                </form>
                                <form method="post" class="d-inline" action="<?= site_url('admin/planos/' . $plano['id'] . '/excluir') ?>"
                                      onsubmit="return confirm('Remover este plano?');">
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
