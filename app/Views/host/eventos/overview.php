<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Arrecadado</h2>
                    <i class="bi bi-cash-stack text-success"></i>
                </div>
                <p class="h4 fw-bold mb-0"><?= esc(moeda_brl($financeiro['arrecadado'])) ?></p>
                <span class="text-muted fs-8"><?= (int) $financeiro['pagos'] ?> pedido(s) pago(s)</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Presentes</h2>
                    <i class="bi bi-gift text-brand"></i>
                </div>
                <p class="h4 fw-bold mb-0"><?= (int) $presentesTotal ?></p>
                <a href="<?= site_url('painel/eventos/' . $evento->id . '/presentes') ?>" class="fs-8 text-decoration-none">Gerenciar &rarr;</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Confirmados</h2>
                    <i class="bi bi-people text-brand"></i>
                </div>
                <p class="h4 fw-bold mb-0"><?= (int) $convidados['pessoas_confirmadas'] ?></p>
                <span class="text-muted fs-8">
                    <?= (int) $convidados['pessoas_pendentes'] ?> aguardando
                    <?= $convidados['limite'] !== null ? '· limite ' . (int) $convidados['limite'] : '' ?>
                </span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Galeria</h2>
                    <i class="bi bi-images text-brand"></i>
                </div>
                <p class="h4 fw-bold mb-0"><?= (int) $galeriaTotal ?></p>
                <a href="<?= site_url('painel/eventos/' . $evento->id . '/galeria') ?>" class="fs-8 text-decoration-none">Gerenciar &rarr;</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Resumo da lista</h2>
                <dl class="row mb-0 fs-7">
                    <dt class="col-5 text-muted fw-normal">Endereço público</dt>
                    <dd class="col-7 text-truncate">/<?= esc($evento->slug) ?></dd>

                    <dt class="col-5 text-muted fw-normal">Tipo</dt>
                    <dd class="col-7"><?= esc(rotulo_tipo_evento($evento->tipo_evento)) ?></dd>

                    <dt class="col-5 text-muted fw-normal">Data</dt>
                    <dd class="col-7"><?= $evento->data_evento !== null ? esc($evento->data_evento->format('d/m/Y')) : '—' ?></dd>

                    <dt class="col-5 text-muted fw-normal">Status</dt>
                    <dd class="col-7">
                        <span class="badge text-bg-<?= cor_status_evento($evento->status) ?>"><?= esc(rotulo_status_evento($evento->status)) ?></span>
                        <?php if ($evento->arquivado): ?><span class="badge text-bg-secondary">Arquivada</span><?php endif; ?>
                    </dd>

                    <dt class="col-5 text-muted fw-normal">Meta</dt>
                    <dd class="col-7"><?= $evento->meta_valor !== null ? esc(moeda_brl($evento->meta_valor)) : '—' ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Ações rápidas</h2>
                <div class="d-grid gap-2">
                    <a class="btn btn-outline-brand text-start" href="<?= site_url('painel/eventos/' . $evento->id . '/presentes') ?>">
                        <i class="bi bi-gift me-2"></i>Gerenciar presentes
                    </a>
                    <a class="btn btn-outline-brand text-start" href="<?= site_url('painel/eventos/' . $evento->id . '/convidados') ?>">
                        <i class="bi bi-people me-2"></i>Lista de convidados
                    </a>
                    <a class="btn btn-outline-brand text-start" href="<?= site_url('painel/eventos/' . $evento->id . '/compartilhar') ?>">
                        <i class="bi bi-share me-2"></i>Compartilhar a lista
                    </a>
                    <?php if ($evento->status === 'publicado'): ?>
                        <a class="btn btn-outline-success text-start" href="<?= site_url($evento->slug) ?>" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-2"></i>Ver página pública
                        </a>
                    <?php else: ?>
                        <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/publicar') ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-brand w-100 text-start">
                                <i class="bi bi-broadcast me-2"></i>Publicar lista
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
