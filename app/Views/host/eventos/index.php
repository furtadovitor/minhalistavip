<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted mb-0">
        <?= count($eventos) ?> evento(s) · <?= (int) $publicados ?> publicado(s)
    </p>
    <a class="btn btn-brand" href="<?= site_url('painel/eventos/novo') ?>">
        <i class="bi bi-plus-lg me-1"></i>Novo evento
    </a>
</div>

<?php if (empty($eventos)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <p class="mb-1 fw-semibold">Você ainda não criou nenhum evento.</p>
            <p class="text-muted">Crie sua página, monte a lista de presentes e compartilhe o link com os convidados.</p>
            <a class="btn btn-brand" href="<?= site_url('painel/eventos/novo') ?>">Criar meu primeiro evento</a>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Evento</th>
                        <th>Tipo</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($eventos as $evento): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= esc($evento->titulo) ?></div>
                            <div class="text-muted fs-8"><?= esc($evento->slug) ?></div>
                        </td>
                        <td class="small"><?= esc(rotulo_tipo_evento($evento->tipo_evento)) ?></td>
                        <td class="small">
                            <?= $evento->data_evento !== null ? esc($evento->data_evento->format('d/m/Y')) : '—' ?>
                        </td>
                        <td>
                            <span class="badge text-bg-<?= cor_status_evento($evento->status) ?>">
                                <?= esc(rotulo_status_evento($evento->status)) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap gap-1 justify-content-end">
                                <a class="btn btn-sm btn-outline-brand"
                                   href="<?= site_url('painel/eventos/' . $evento->id . '/presentes') ?>">
                                    <i class="bi bi-gift me-1"></i>Presentes
                                </a>
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="<?= site_url('painel/eventos/' . $evento->id . '/convidados') ?>">
                                    <i class="bi bi-people me-1"></i>Convidados
                                </a>
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="<?= site_url('painel/eventos/' . $evento->id . '/editar') ?>">Editar</a>

                                <?php if ($evento->status === 'publicado'): ?>
                                    <a class="btn btn-sm btn-outline-success" target="_blank"
                                       href="<?= site_url($evento->slug) ?>">Ver página</a>
                                <?php endif; ?>

                                <form method="post" class="d-inline"
                                      action="<?= site_url('painel/eventos/' . $evento->id . '/publicar') ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-<?= $evento->status === 'publicado' ? 'warning' : 'success' ?>">
                                        <?= $evento->status === 'publicado' ? 'Despublicar' : 'Publicar' ?>
                                    </button>
                                </form>

                                <form method="post" class="d-inline"
                                      action="<?= site_url('painel/eventos/' . $evento->id . '/excluir') ?>"
                                      onsubmit="return confirm('Remover este evento e a lista de presentes?');">
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
