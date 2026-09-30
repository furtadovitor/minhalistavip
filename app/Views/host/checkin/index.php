<?= $this->extend('templates/layouts/app') ?>

<?php
$retorno      = http_build_query(array_filter([
    'busca'    => $busca !== '' ? $busca : null,
    'ausentes' => $somenteAusentes ? '1' : null,
]));
$pessoasTotal = (int) $resumo['pessoas_confirmadas'];
$presentes    = (int) $resumo['pessoas_presentes'];
$percentual   = $pessoasTotal > 0 ? min(100, (int) round($presentes / $pessoasTotal * 100)) : 0;
?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <p class="text-muted mb-0">
            <?= esc($evento->titulo) ?> ·
            <span class="badge text-bg-<?= cor_status_evento($evento->status) ?>">
                <?= esc(rotulo_status_evento($evento->status)) ?>
            </span>
        </p>
        <p class="text-muted fs-8 mb-0">Marque a chegada dos convidados confirmados no dia do evento.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="<?= site_url('painel/eventos/' . $evento->id . '/convidados') ?>">
            <i class="bi bi-people me-1"></i>Lista de convidados
        </a>
        <a class="btn btn-outline-secondary" href="<?= site_url('painel/eventos') ?>">Voltar</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-1 fs-8">Presentes</h2>
                <p class="h3 fw-bold mb-0 text-success"><?= $presentes ?></p>
                <p class="text-muted fs-8 mb-0">de <?= $pessoasTotal ?> pessoa(s) confirmada(s)</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-1 fs-8">Ainda não chegaram</h2>
                <p class="h3 fw-bold mb-0 text-warning"><?= max(0, $pessoasTotal - $presentes) ?></p>
                <p class="text-muted fs-8 mb-0">Considerando acompanhantes</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-1 fs-8">Check-ins</h2>
                <p class="h3 fw-bold mb-0"><?= (int) $resumo['presentes'] ?></p>
                <p class="text-muted fs-8 mb-0">de <?= (int) $resumo['confirmados'] ?> confirmação(ões)</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-1 fs-8">Menores</h2>
                <p class="h3 fw-bold mb-0"><?= (int) $acompanhantes['criancas'] + (int) $acompanhantes['bebes'] ?></p>
                <p class="text-muted fs-8 mb-0"><?= (int) $acompanhantes['criancas'] ?> criança(s) · <?= (int) $acompanhantes['bebes'] ?> bebê(s)</p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end mb-2">
            <span class="text-muted fs-8 text-uppercase fw-semibold">Presença confirmada no local</span>
            <span class="fw-bold"><?= $presentes ?>/<?= $pessoasTotal ?> (<?= $percentual ?>%)</span>
        </div>
        <div class="progress" style="height: 10px;">
            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percentual ?>%;"></div>
        </div>
    </div>
</div>

<form class="card border-0 shadow-sm rounded-4 mb-3" method="get"
      action="<?= site_url('painel/eventos/' . $evento->id . '/checkin') ?>">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label fw-semibold fs-7 mb-1" for="busca">Buscar convidado</label>
                <input type="search" class="form-control form-control-lg" id="busca" name="busca"
                       value="<?= esc($busca) ?>" placeholder="Nome, e-mail ou telefone" autofocus>
            </div>
            <div class="col-md-3">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" id="ausentes" name="ausentes" value="1"
                           <?= $somenteAusentes ? 'checked' : '' ?>>
                    <label class="form-check-label fs-7" for="ausentes">Só quem ainda não chegou</label>
                </div>
            </div>
            <div class="col-md-3 d-grid">
                <button class="btn btn-outline-brand"><i class="bi bi-funnel me-1"></i>Filtrar</button>
            </div>
        </div>
    </div>
</form>

<?php if (empty($confirmados)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <div class="bg-indigo-100 text-indigo-700 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:64px;height:64px;"><i class="bi bi-clipboard-check fs-3"></i></div>
            <p class="mb-1 fw-semibold">
                <?= $busca !== '' ? 'Nenhum convidado encontrado para essa busca.' : 'Nenhum convidado confirmado.' ?>
            </p>
            <p class="text-muted mb-0">Aprove as confirmações na lista de convidados para fazer o check-in.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($confirmados as $c): ?>
            <?php
            $presente  = ! empty($c['check_in_em']);
            $horaCheck = $presente ? date('H:i', strtotime((string) $c['check_in_em'])) : null;
            $pessoas   = (int) $c['quantidade_acompanhantes'] + 1;
            ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 <?= $presente ? 'bg-success-subtle' : 'shadow-sm' ?> rounded-4 h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div style="min-width:0;">
                                <h3 class="h6 fw-bold mb-0 text-truncate" title="<?= esc($c['nome']) ?>"><?= esc($c['nome']) ?></h3>
                                <?php if (! empty($c['telefone'])): ?>
                                    <div class="text-muted fs-8"><i class="bi bi-telephone me-1"></i><?= esc($c['telefone']) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if ($presente): ?>
                                <span class="badge text-bg-success flex-shrink-0"><i class="bi bi-check-lg me-1"></i><?= esc($horaCheck) ?></span>
                            <?php else: ?>
                                <span class="badge text-bg-warning flex-shrink-0">Ausente</span>
                            <?php endif; ?>
                        </div>

                        <p class="text-muted fs-8 mt-2 mb-3">
                            <?= $pessoas ?> pessoa(s)
                            <?php if ((int) $c['quantidade_acompanhantes'] > 0): ?>
                                · <?= (int) $c['quantidade_acompanhantes'] ?> acompanhante(s)
                            <?php endif; ?>
                            · <a class="text-decoration-none" href="<?= site_url('painel/eventos/' . $evento->id . '/convidados/' . $c['id']) ?>">detalhes</a>
                        </p>

                        <div class="mt-auto">
                            <?php if ($presente): ?>
                                <form method="post" class="d-grid"
                                      action="<?= site_url('painel/eventos/' . $evento->id . '/checkin/' . $c['id'] . '/desfazer') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="retorno" value="<?= esc($retorno, 'attr') ?>">
                                    <button class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i>Desfazer check-in</button>
                                </form>
                            <?php else: ?>
                                <form method="post" class="d-grid"
                                      action="<?= site_url('painel/eventos/' . $evento->id . '/checkin/' . $c['id']) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="retorno" value="<?= esc($retorno, 'attr') ?>">
                                    <button class="btn btn-brand btn-lg"><i class="bi bi-check2-circle me-1"></i>Fazer check-in</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
