<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row g-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase">Organizadores</h2>
                <p class="display-6 mb-0"><?= (int) $organizadores ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase">Eventos publicados</h2>
                <p class="display-6 mb-0"><?= (int) $eventosPublicados ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase">Pedidos pagos</h2>
                <p class="display-6 mb-0"><?= (int) $pedidosPagos ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase">Saques pendentes</h2>
                <p class="display-6 mb-0"><?= (int) $resumoSaques['pendentes_qtd'] ?></p>
                <p class="text-muted small mb-0"><?= esc(moeda_brl($resumoSaques['pendentes_valor'])) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
    <a class="btn btn-primary" href="<?= site_url('admin/saques') ?>">Gerenciar saques</a>
    <a class="btn btn-outline-secondary" href="<?= site_url('admin/saques?status=solicitado') ?>">Saques a pagar</a>
</div>

<div class="alert alert-info mt-4 mb-0">
    Área do SuperAdmin: a gestão de saques está disponível. Os módulos de taxas, catálogo global,
    usuários, planos e conciliação financeira entram nas próximas etapas.
</div>
<?= $this->endSection() ?>
