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
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h2 class="h6 text-uppercase text-muted mb-0">Resumo da lista</h2>
                    <?php if ($evento->status === 'publicado'): ?>
                        <a class="btn btn-sm btn-outline-success" href="<?= site_url($evento->slug) ?>" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Ver página pública
                        </a>
                    <?php else: ?>
                        <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/publicar') ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-brand"><i class="bi bi-broadcast me-1"></i>Publicar lista</button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="row g-4">
                    <div class="col-md-6">
                        <dl class="row mb-0 fs-7">
                            <dt class="col-5 text-muted fw-normal">Endereço público</dt>
                            <dd class="col-7 text-truncate">/<?= esc($evento->slug) ?></dd>

                            <dt class="col-5 text-muted fw-normal">Tipo</dt>
                            <dd class="col-7"><?= esc(rotulo_tipo_evento($evento->tipo_evento)) ?></dd>

                            <dt class="col-5 text-muted fw-normal">Data</dt>
                            <dd class="col-7"><?= $evento->data_evento !== null ? esc($evento->data_evento->format('d/m/Y')) : '—' ?></dd>

                            <dt class="col-5 text-muted fw-normal">Horário</dt>
                            <dd class="col-7"><?= $evento->horario ? esc(substr((string) $evento->horario, 0, 5)) : '—' ?></dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <dl class="row mb-0 fs-7">
                            <dt class="col-5 text-muted fw-normal">Status</dt>
                            <dd class="col-7">
                                <span class="badge text-bg-<?= cor_status_evento($evento->status) ?>"><?= esc(rotulo_status_evento($evento->status)) ?></span>
                                <?php if ($evento->arquivado): ?><span class="badge text-bg-secondary">Arquivada</span><?php endif; ?>
                            </dd>

                            <dt class="col-5 text-muted fw-normal">Meta</dt>
                            <dd class="col-7"><?= $evento->meta_valor !== null ? esc(moeda_brl($evento->meta_valor)) : '—' ?></dd>

                            <dt class="col-5 text-muted fw-normal">Local</dt>
                            <dd class="col-7"><?= $evento->local_nome !== null && $evento->local_nome !== '' ? esc($evento->local_nome) : '—' ?></dd>

                            <dt class="col-5 text-muted fw-normal">Endereço</dt>
                            <dd class="col-7"><?= $evento->local_endereco !== null && $evento->local_endereco !== '' ? esc($evento->local_endereco) : '—' ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
