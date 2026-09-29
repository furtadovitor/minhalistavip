<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase">Eventos</h2>
                <p class="display-6 mb-0"><?= (int) $total ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase">Publicados</h2>
                <p class="display-6 mb-0"><?= (int) $publicados ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase">Arrecadado</h2>
                <p class="display-6 mb-0"><?= esc(moeda_brl($arrecadado ?? 0)) ?></p>
                <p class="text-muted small mb-0">
                    Saldo disponível: <?= esc(moeda_brl($saldo ?? 0)) ?> ·
                    <a href="<?= site_url('painel/carteira') ?>">carteira</a>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Gerencie seus eventos e listas de presentes.</p>
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
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($eventos as $evento): ?>
                    <tr>
                        <td><?= esc($evento->titulo) ?></td>
                        <td class="small"><?= esc(rotulo_tipo_evento($evento->tipo_evento)) ?></td>
                        <td class="small"><?= $evento->data_evento !== null ? esc($evento->data_evento->format('d/m/Y')) : '—' ?></td>
                        <td>
                            <span class="badge text-bg-<?= cor_status_evento($evento->status) ?>">
                                <?= esc(rotulo_status_evento($evento->status)) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary"
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
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
