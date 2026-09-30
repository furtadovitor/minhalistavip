<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Listas ativas</h2>
                    <i class="bi bi-grid text-brand"></i>
                </div>
                <p class="display-6 fw-bold mb-0"><?= count($ativos) ?></p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Publicadas</h2>
                    <i class="bi bi-broadcast text-success"></i>
                </div>
                <p class="display-6 fw-bold mb-0"><?= (int) $publicados ?></p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Arrecadado</h2>
                    <i class="bi bi-cash-stack text-success"></i>
                </div>
                <p class="h4 fw-bold mb-0"><?= esc(moeda_brl($arrecadado ?? 0)) ?></p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Saldo</h2>
                    <i class="bi bi-wallet2 text-brand"></i>
                </div>
                <p class="h4 fw-bold mb-0"><?= esc(moeda_brl($saldo ?? 0)) ?></p>
                <a href="<?= site_url('painel/carteira') ?>" class="fs-8 text-decoration-none">Ver carteira &rarr;</a>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted mb-0">Crie, gerencie e compartilhe as listas de presentes dos seus eventos.</p>
    <a class="btn btn-brand" href="<?= site_url('painel/eventos/novo') ?>">
        <i class="bi bi-plus-lg me-1"></i>Criar nova lista
    </a>
</div>

<?php
$renderCard = static function ($evento, bool $arquivado): void {
    $capa = ! empty($evento->imagem_capa) ? base_url($evento->imagem_capa) : null;
    $gradiente = 'linear-gradient(135deg, ' . esc($evento->cor_primaria ?: '#4F46E5', 'attr') . ', ' . esc($evento->cor_secundaria ?: '#10B981', 'attr') . ')';
    $base = site_url('painel/eventos/' . $evento->id);
    ?>
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden transition-hover">
            <div class="position-relative" style="height:140px; background: <?= $capa ? "url('" . esc($capa, 'attr') . "') center/cover" : $gradiente ?>;">
                <span class="badge text-bg-<?= cor_status_evento($evento->status) ?> position-absolute"
                      style="top:.6rem; left:.6rem;">
                    <?= esc(rotulo_status_evento($evento->status)) ?>
                </span>
                <?php if ($arquivado): ?>
                    <span class="badge text-bg-secondary position-absolute" style="top:.6rem; right:.6rem;">Arquivada</span>
                <?php endif; ?>
            </div>
            <div class="card-body d-flex flex-column">
                <h3 class="h6 fw-bold mb-1 text-truncate" title="<?= esc($evento->titulo) ?>"><?= esc($evento->titulo) ?></h3>
                <p class="text-muted fs-8 mb-3">
                    <?= esc(rotulo_tipo_evento($evento->tipo_evento)) ?>
                    <?php if ($evento->data_evento !== null): ?>
                        · <?= esc($evento->data_evento->format('d/m/Y')) ?>
                    <?php endif; ?>
                </p>
                <div class="mt-auto d-flex flex-wrap gap-1">
                    <a class="btn btn-sm btn-brand" href="<?= esc($base) ?>">
                        <i class="bi bi-sliders me-1"></i>Gerenciar
                    </a>
                    <?php if ($evento->status === 'publicado'): ?>
                        <a class="btn btn-sm btn-outline-success" href="<?= site_url($evento->slug) ?>" target="_blank" title="Ver página">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    <?php endif; ?>
                    <form method="post" class="ms-auto"
                          action="<?= site_url('painel/eventos/' . $evento->id . '/arquivar') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="arquivar" value="<?= $arquivado ? '0' : '1' ?>">
                        <button class="btn btn-sm btn-outline-secondary" title="<?= $arquivado ? 'Reativar' : 'Arquivar' ?>">
                            <i class="bi <?= $arquivado ? 'bi-arrow-counterclockwise' : 'bi-archive' ?>"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
};
?>

<ul class="nav nav-pills gap-1 mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pane-ativas" type="button">
            Ativas <span class="badge text-bg-light ms-1"><?= count($ativos) ?></span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pane-arquivadas" type="button">
            Arquivadas <span class="badge text-bg-light ms-1"><?= count($arquivados) ?></span>
        </button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="pane-ativas">
        <?php if (empty($ativos)): ?>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <div class="bg-indigo-100 text-indigo-700 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                         style="width:64px;height:64px;"><i class="bi bi-gift fs-3"></i></div>
                    <p class="mb-1 fw-semibold">Você ainda não tem listas ativas.</p>
                    <p class="text-muted">Crie sua página, monte a lista e compartilhe o link com os convidados.</p>
                    <a class="btn btn-brand" href="<?= site_url('painel/eventos/novo') ?>">Criar minha primeira lista</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($ativos as $evento) { $renderCard($evento, false); } ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="tab-pane fade" id="pane-arquivadas">
        <?php if (empty($arquivados)): ?>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-archive fs-2 d-block mb-2"></i>
                    Nenhuma lista arquivada.
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($arquivados as $evento) { $renderCard($evento, true); } ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
