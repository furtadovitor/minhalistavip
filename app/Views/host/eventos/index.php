<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">
        <?= count($eventos) ?> evento(s) · <?= (int) $publicados ?> publicado(s)
    </p>
    <a class="btn btn-primary" href="<?= site_url('painel/eventos/novo') ?>">Criar novo evento</a>
</div>

<?php if (empty($eventos)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <p class="mb-1 fw-semibold">Você ainda não criou nenhum evento.</p>
            <p class="text-muted">Crie sua página, monte a lista de presentes e compartilhe o link com os convidados.</p>
            <a class="btn btn-primary" href="<?= site_url('painel/eventos/novo') ?>">Criar meu primeiro evento</a>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
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
                            <div class="text-muted small"><?= esc($evento->slug) ?></div>
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
                                <a class="btn btn-sm btn-outline-primary"
                                   href="<?= site_url('painel/eventos/' . $evento->id . '/presentes') ?>">Presentes</a>
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
                                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
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
