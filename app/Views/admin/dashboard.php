<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row g-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Organizadores</h2>
                    <i class="bi bi-people text-brand"></i>
                </div>
                <p class="display-6 fw-bold mb-0"><?= (int) $organizadores ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Eventos publicados</h2>
                    <i class="bi bi-broadcast text-success"></i>
                </div>
                <p class="display-6 fw-bold mb-0"><?= (int) $eventosPublicados ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Pedidos pagos</h2>
                    <i class="bi bi-bag-check text-success"></i>
                </div>
                <p class="display-6 fw-bold mb-0"><?= (int) $pedidosPagos ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Saques pendentes</h2>
                    <i class="bi bi-cash-coin text-warning"></i>
                </div>
                <p class="display-6 fw-bold mb-0"><?= (int) $resumoSaques['pendentes_qtd'] ?></p>
                <p class="text-muted fs-8 mb-0"><?= esc(moeda_brl($resumoSaques['pendentes_valor'])) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
    <a class="btn btn-brand" href="<?= site_url('admin/listas') ?>">
        <i class="bi bi-list-ul me-1"></i>Ver todas as listas
    </a>
    <a class="btn btn-outline-brand" href="<?= site_url('admin/saques') ?>">
        <i class="bi bi-cash-coin me-1"></i>Gerenciar saques
    </a>
    <a class="btn btn-outline-brand" href="<?= site_url('admin/saques?status=solicitado') ?>">
        <i class="bi bi-hourglass-split me-1"></i>Saques a pagar
    </a>
</div>

<div class="alert alert-info rounded-4 mt-4 mb-0">
    <i class="bi bi-info-circle me-1"></i>
    A gestão de <strong>listas</strong>, saques, catálogo, usuários, planos e conciliação
    financeira está disponível no menu lateral.
</div>
<?= $this->endSection() ?>
