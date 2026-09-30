<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Eventos</h2>
                    <i class="bi bi-calendar-event text-brand"></i>
                </div>
                <p class="display-6 fw-bold mb-0"><?= (int) $total ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Publicados</h2>
                    <i class="bi bi-broadcast text-success"></i>
                </div>
                <p class="display-6 fw-bold mb-0"><?= (int) $publicados ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Arrecadado</h2>
                    <i class="bi bi-cash-stack text-success"></i>
                </div>
                <p class="h3 fw-bold mb-0"><?= esc(moeda_brl($arrecadado ?? 0)) ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Saldo</h2>
                    <i class="bi bi-wallet2 text-brand"></i>
                </div>
                <p class="h3 fw-bold mb-0"><?= esc(moeda_brl($saldo ?? 0)) ?></p>
                <a href="<?= site_url('painel/carteira') ?>" class="fs-8 text-decoration-none">Ver carteira &rarr;</a>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted mb-0">Gerencie seus eventos e listas de presentes.</p>
    <a class="btn btn-brand" href="<?= site_url('painel/eventos/novo') ?>">
        <i class="bi bi-plus-lg me-1"></i>Criar novo evento
    </a>
</div>

<?php if (empty($eventos)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <div class="bg-indigo-100 text-indigo-700 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:64px;height:64px;"><i class="bi bi-calendar-plus fs-3"></i></div>
            <p class="mb-1 fw-semibold">Você ainda não criou nenhum evento.</p>
            <p class="text-muted">Crie sua página, monte a lista e compartilhe o link com os convidados.</p>
            <a class="btn btn-brand" href="<?= site_url('painel/eventos/novo') ?>">Criar meu primeiro evento</a>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-3">
            <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Eventos recentes</h2>
        </div>
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
                <?php foreach (array_slice($eventos, 0, 5) as $evento): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= esc($evento->titulo) ?></div>
                            <div class="text-muted fs-8"><?= esc($evento->slug) ?></div>
                        </td>
                        <td class="small"><?= esc(rotulo_tipo_evento($evento->tipo_evento)) ?></td>
                        <td class="small"><?= $evento->data_evento !== null ? esc($evento->data_evento->format('d/m/Y')) : '—' ?></td>
                        <td>
                            <span class="badge text-bg-<?= cor_status_evento($evento->status) ?>">
                                <?= esc(rotulo_status_evento($evento->status)) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-brand"
                               href="<?= site_url('painel/eventos/' . $evento->id . '/presentes') ?>">Presentes</a>
                            <?php if ($evento->status === 'publicado'): ?>
                                <a class="btn btn-sm btn-outline-success" target="_blank"
                                   href="<?= site_url($evento->slug) ?>">Ver página</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (count($eventos) > 5): ?>
            <div class="card-footer bg-white border-0 text-center py-3">
                <a class="text-decoration-none fs-7" href="<?= site_url('painel/eventos') ?>">Ver todos os eventos &rarr;</a>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
