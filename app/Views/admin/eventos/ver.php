<?= $this->extend('templates/layouts/app') ?>

<?php
$horario = $evento->horario !== null && $evento->horario !== '' ? substr((string) $evento->horario, 0, 5) : null;
$dataTexto = $evento->data_evento !== null ? $evento->data_evento->format('d/m/Y') : null;
$percentual = $evento->percentual_taxa !== null ? number_format((float) $evento->percentual_taxa, 2, ',', '.') . '%' : 'Padrão da plataforma';
?>

<?= $this->section('conteudo') ?>

<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body d-flex flex-column flex-md-row gap-3 align-items-md-center">
        <?php if (! empty($evento->imagem_capa)): ?>
            <img src="<?= esc(base_url($evento->imagem_capa), 'attr') ?>" alt="Capa"
                 class="rounded-3 object-fit-cover flex-shrink-0" style="width: 132px; height: 84px;">
        <?php endif; ?>
        <div class="flex-grow-1">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h2 class="h5 fw-bold mb-0"><?= esc($evento->titulo) ?></h2>
                <span class="badge text-bg-<?= esc(cor_status_evento((string) $evento->status), 'attr') ?>">
                    <?= esc(rotulo_status_evento((string) $evento->status)) ?>
                </span>
                <?php if ($evento->arquivado): ?>
                    <span class="badge text-bg-secondary">Arquivada</span>
                <?php endif; ?>
            </div>
            <?php if (! empty($evento->subtitulo)): ?>
                <p class="text-muted fs-7 mb-1"><?= esc($evento->subtitulo) ?></p>
            <?php endif; ?>
            <p class="fs-8 text-muted mb-0">
                <?= esc(rotulo_tipo_evento((string) $evento->tipo_evento)) ?> ·
                <?php if ($evento->status === 'publicado'): ?>
                    <a href="<?= esc(site_url($evento->slug), 'attr') ?>" target="_blank" rel="noopener">
                        /<?= esc($evento->slug) ?> <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                <?php else: ?>
                    /<?= esc($evento->slug) ?> <span class="text-muted">(não publicado)</span>
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
    <?php if ($evento->status === 'publicado'): ?>
        <a class="btn btn-outline-brand" href="<?= esc(site_url($evento->slug), 'attr') ?>" target="_blank" rel="noopener">
            <i class="bi bi-box-arrow-up-right me-1"></i>Ver página pública
        </a>
    <?php endif; ?>
    <form method="post" action="<?= site_url('admin/listas/' . $evento->id . '/publicar') ?>">
        <?= csrf_field() ?>
        <?php if ($evento->status === 'publicado'): ?>
            <button class="btn btn-outline-secondary"><i class="bi bi-eye-slash me-1"></i>Despublicar</button>
        <?php else: ?>
            <button class="btn btn-outline-success"><i class="bi bi-broadcast me-1"></i>Publicar</button>
        <?php endif; ?>
    </form>
    <form method="post" action="<?= site_url('admin/listas/' . $evento->id . '/arquivar') ?>"
          onsubmit="return confirm('<?= $evento->arquivado ? 'Reativar' : 'Arquivar' ?> esta lista?');">
        <?= csrf_field() ?>
        <?php if ($evento->arquivado): ?>
            <button class="btn btn-outline-success"><i class="bi bi-arrow-counterclockwise me-1"></i>Reativar</button>
        <?php else: ?>
            <button class="btn btn-outline-warning"><i class="bi bi-archive me-1"></i>Arquivar</button>
        <?php endif; ?>
    </form>
    <a class="btn btn-outline-secondary ms-md-auto" href="<?= site_url('admin/listas') ?>">
        <i class="bi bi-arrow-left me-1"></i>Voltar para listas
    </a>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <p class="text-muted text-uppercase fs-8 fw-semibold mb-1">Presentes</p>
            <p class="h4 fw-bold mb-0"><?= (int) $numeros['presentes'] ?></p>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <p class="text-muted text-uppercase fs-8 fw-semibold mb-1">Convidados</p>
            <p class="h4 fw-bold mb-0"><?= (int) $numeros['convidados'] ?></p>
            <p class="fs-8 text-success mb-0"><?= (int) $numeros['confirmados'] ?> confirmados</p>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <p class="text-muted text-uppercase fs-8 fw-semibold mb-1">Pedidos pagos</p>
            <p class="h4 fw-bold mb-0"><?= (int) $numeros['pedidos_pagos'] ?></p>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <p class="text-muted text-uppercase fs-8 fw-semibold mb-1">Arrecadado</p>
            <p class="h4 fw-bold mb-0"><?= esc(moeda_brl($numeros['arrecadado'])) ?></p>
            <p class="fs-8 text-muted mb-0"><?= esc(moeda_brl($numeros['taxas'])) ?> em taxas</p>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h3 class="h6 text-uppercase text-muted mb-3">Organizador</h3>
                <?php if ($dono !== null): ?>
                    <p class="fw-semibold mb-0"><?= esc($dono->nome) ?></p>
                    <p class="fs-7 mb-2">
                        <a href="mailto:<?= esc($dono->email, 'attr') ?>"><?= esc($dono->email) ?></a>
                    </p>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge text-bg-<?= $dono->status === 'ativo' ? 'success' : 'secondary' ?>">
                            <?= esc(ucfirst((string) $dono->status)) ?>
                        </span>
                        <a class="btn btn-sm btn-outline-brand" href="<?= site_url('admin/usuarios/' . $dono->id) ?>">
                            Ver ficha do organizador
                        </a>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Organizador não encontrado (registro removido?).</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h3 class="h6 text-uppercase text-muted mb-3">Dados do evento</h3>
                <dl class="row mb-0 fs-7">
                    <dt class="col-5 text-muted fw-normal">Data</dt>
                    <dd class="col-7"><?= esc($dataTexto ?? '—') ?><?= $horario !== null ? ' às ' . esc($horario) : '' ?></dd>

                    <dt class="col-5 text-muted fw-normal">Local</dt>
                    <dd class="col-7"><?= esc($evento->local_nome ?? '—') ?></dd>

                    <dt class="col-5 text-muted fw-normal">Endereço</dt>
                    <dd class="col-7"><?= esc($evento->local_endereco ?? '—') ?></dd>

                    <dt class="col-5 text-muted fw-normal">Limite de convidados</dt>
                    <dd class="col-7"><?= $evento->limite_convidados !== null ? (int) $evento->limite_convidados . ' pessoas' : 'Sem limite' ?></dd>

                    <dt class="col-5 text-muted fw-normal">Taxa</dt>
                    <dd class="col-7"><?= esc($percentual) ?> · <?= esc(rotulo_quem_paga_taxa((string) $evento->quem_paga_taxa)) ?></dd>

                    <dt class="col-5 text-muted fw-normal">Criada em</dt>
                    <dd class="col-7"><?= $evento->criado_em !== null ? esc($evento->criado_em->format('d/m/Y H:i')) : '—' ?></dd>

                    <dt class="col-5 text-muted fw-normal">Publicada em</dt>
                    <dd class="col-7 mb-0"><?= $evento->publicado_em !== null ? esc($evento->publicado_em->format('d/m/Y H:i')) : '—' ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
