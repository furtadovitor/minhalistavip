<?= $this->extend('templates/layouts/app') ?>

<?php
$badgeStatus = [
    'pendente'   => 'warning',
    'confirmado' => 'success',
    'recusado'   => 'secondary',
];
$rotuloStatus = [
    'pendente'   => 'Aguardando',
    'confirmado' => 'Confirmado',
    'recusado'   => 'Recusado',
];
$retorno = http_build_query(array_filter([
    'busca'     => $filtros['busca'] ?? null,
    'status'    => $filtros['status'] ?? null,
    'presenca'  => $filtros['presenca'] ?? null,
    'categoria' => $filtros['categoria'] ?? null,
    'ordem'     => in_array($filtros['ordem'] ?? '', ['antigos', 'nome', 'status'], true) ? $filtros['ordem'] : null,
]));
$temFiltro = $retorno !== '';
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
        <p class="text-muted fs-8 mb-0">Confirmações de presença com homologação e limite de convidados.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="<?= site_url('painel/eventos') ?>">Voltar</a>
        <a class="btn btn-outline-brand" href="<?= site_url('painel/eventos/' . $evento->id . '/convidados/exportar') ?>">
            <i class="bi bi-filetype-csv me-1"></i>Exportar CSV
        </a>
        <a class="btn btn-brand" href="<?= site_url('painel/eventos/' . $evento->id . '/checkin') ?>">
            <i class="bi bi-clipboard-check me-1"></i>Fazer check-in
        </a>
        <?php if ($evento->status === 'publicado'): ?>
            <a class="btn btn-outline-success" target="_blank" href="<?= site_url($evento->slug) ?>#presenca">
                <i class="bi bi-box-arrow-up-right me-1"></i>Ver página
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon" style="background:#ECFDF5;color:#047857;"><i class="bi bi-people-fill"></i></span>
                <div>
                    <p class="text-muted text-uppercase fs-8 fw-semibold mb-0">Confirmados</p>
                    <p class="h4 fw-bold mb-0"><?= (int) $resumo['pessoas_confirmadas'] ?></p>
                    <p class="text-muted fs-8 mb-0">
                        <?= (int) $resumo['confirmados'] ?> confirmação(ões) ·
                        <?= (int) $acompanhantesResumo['criancas'] ?> criança(s) / <?= (int) $acompanhantesResumo['bebes'] ?> bebê(s)
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon" style="background:#FFFBEB;color:#B45309;"><i class="bi bi-hourglass-split"></i></span>
                <div>
                    <p class="text-muted text-uppercase fs-8 fw-semibold mb-0">Aguardando</p>
                    <p class="h4 fw-bold mb-0 text-warning"><?= (int) $resumo['pendentes'] ?></p>
                    <p class="text-muted fs-8 mb-0"><?= (int) $resumo['pessoas_pendentes'] ?> pessoa(s) no total</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon" style="background:#F3F4F6;color:#4B5563;"><i class="bi bi-hand-thumbs-down"></i></span>
                <div>
                    <p class="text-muted text-uppercase fs-8 fw-semibold mb-0">Recusados</p>
                    <p class="h4 fw-bold mb-0 text-muted"><?= (int) $resumo['recusados'] ?></p>
                    <p class="text-muted fs-8 mb-0">Não poderão comparecer</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon bg-indigo-100 text-indigo-700"><i class="bi bi-speedometer2"></i></span>
                <div>
                    <p class="text-muted text-uppercase fs-8 fw-semibold mb-0">Limite</p>
                    <?php if ($resumo['limite'] !== null): ?>
                        <p class="h4 fw-bold mb-0"><?= (int) $resumo['limite'] ?></p>
                        <p class="text-muted fs-8 mb-0"><?= (int) $resumo['vagas'] ?> vaga(s) restante(s)</p>
                    <?php else: ?>
                        <p class="h5 fw-bold mb-0">Ilimitado</p>
                        <p class="text-muted fs-8 mb-0">Defina um limite no evento</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($resumo['limite'] !== null): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-end mb-2">
                <span class="text-muted fs-8 text-uppercase fw-semibold">Ocupação do limite</span>
                <span class="fw-bold"><?= (int) $resumo['pessoas_confirmadas'] ?>/<?= (int) $resumo['limite'] ?> (<?= (int) $resumo['percentual'] ?>%)</span>
            </div>
            <div class="progress" style="height: 10px;">
                <div class="progress-bar bg-<?= (int) $resumo['percentual'] >= 100 ? 'danger' : 'success' ?>"
                     role="progressbar" style="width: <?= (int) $resumo['percentual'] ?>%;"></div>
            </div>
            <?php if ((int) $resumo['percentual'] >= 100): ?>
                <p class="text-danger fs-8 mb-0 mt-2"><i class="bi bi-exclamation-triangle me-1"></i>Limite atingido — as confirmações públicas estão encerradas.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-header bg-white border-0 pt-3">
        <h2 class="h6 mb-0 text-muted text-uppercase fs-8">Adicionar convidado (já confirmado)</h2>
    </div>
    <div class="card-body">
        <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/convidados') ?>" class="row g-2 align-items-end">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7 mb-1" for="c-nome">Nome *</label>
                <input type="text" class="form-control" id="c-nome" name="nome" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7 mb-1" for="c-email">E-mail</label>
                <input type="email" class="form-control" id="c-email" name="email">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold fs-7 mb-1" for="c-telefone">Telefone</label>
                <input type="text" class="form-control" id="c-telefone" name="telefone">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold fs-7 mb-1" for="c-acomp">Acompanhantes</label>
                <input type="number" min="0" value="0" class="form-control" id="c-acomp" name="quantidade_acompanhantes">
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Adicionar</button>
            </div>
        </form>
    </div>
</div>

<form class="card border-0 shadow-sm rounded-4 mb-3" method="get" action="<?= site_url('painel/eventos/' . $evento->id . '/convidados') ?>">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-busca">Buscar</label>
                <input type="search" class="form-control" id="f-busca" name="busca" value="<?= esc($filtros['busca'] ?? '') ?>" placeholder="Nome do convidado ou acompanhante, e-mail, telefone">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-status">Status</label>
                <select class="form-select" id="f-status" name="status">
                    <option value="">Todos</option>
                    <?php foreach (['pendente', 'confirmado', 'recusado'] as $opcao): ?>
                        <option value="<?= esc($opcao) ?>" <?= ($filtros['status'] ?? '') === $opcao ? 'selected' : '' ?>>
                            <?= esc($rotuloStatus[$opcao]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-presenca">Presença</label>
                <select class="form-select" id="f-presenca" name="presenca">
                    <option value="">Todos</option>
                    <option value="presentes" <?= ($filtros['presenca'] ?? '') === 'presentes' ? 'selected' : '' ?>>Já chegaram</option>
                    <option value="ausentes" <?= ($filtros['presenca'] ?? '') === 'ausentes' ? 'selected' : '' ?>>Ainda não chegaram</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-categoria">Acompanhantes</label>
                <select class="form-select" id="f-categoria" name="categoria">
                    <option value="">Todos</option>
                    <option value="crianca" <?= ($filtros['categoria'] ?? '') === 'crianca' ? 'selected' : '' ?>>Com criança</option>
                    <option value="bebe" <?= ($filtros['categoria'] ?? '') === 'bebe' ? 'selected' : '' ?>>Com bebê</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-ordem">Ordenar por</label>
                <select class="form-select" id="f-ordem" name="ordem">
                    <option value="recentes" <?= ($filtros['ordem'] ?? 'recentes') === 'recentes' ? 'selected' : '' ?>>Mais recentes</option>
                    <option value="antigos" <?= ($filtros['ordem'] ?? '') === 'antigos' ? 'selected' : '' ?>>Mais antigos</option>
                    <option value="nome" <?= ($filtros['ordem'] ?? '') === 'nome' ? 'selected' : '' ?>>Nome (A–Z)</option>
                    <option value="status" <?= ($filtros['ordem'] ?? '') === 'status' ? 'selected' : '' ?>>Status</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button class="btn btn-outline-brand flex-grow-1"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <?php if ($temFiltro): ?>
                    <a class="btn btn-outline-secondary" href="<?= site_url('painel/eventos/' . $evento->id . '/convidados') ?>" title="Limpar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($temFiltro): ?>
            <p class="text-muted fs-8 mb-0 mt-2">
                <i class="bi bi-funnel me-1"></i><?= count($convidados) ?> resultado(s) para os filtros aplicados.
            </p>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($convidados)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <div class="bg-indigo-100 text-indigo-700 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:64px;height:64px;"><i class="bi bi-people fs-3"></i></div>
            <p class="mb-1 fw-semibold">
                <?= $temFiltro ? 'Nenhum convidado encontrado com esses filtros.' : 'Nenhum convidado ainda.' ?>
            </p>
            <p class="text-muted mb-0">
                <?= $temFiltro
                    ? 'Ajuste a busca ou limpe os filtros para ver a lista completa.'
                    : 'Compartilhe o link do evento — as confirmações aparecem aqui para você aprovar.' ?>
            </p>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Convidado</th>
                        <th>Contato</th>
                        <th>Acompanhantes</th>
                        <th class="text-center">Pessoas</th>
                        <th>Status</th>
                        <th>Enviado</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($convidados as $c): ?>
                    <?php
                    $status        = (string) $c['status'];
                    $acompanhantes = $acompanhantesPorConvidado[(int) $c['id']] ?? [];
                    $nomes         = array_column($acompanhantes, 'nome');
                    ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= esc($c['nome']) ?></div>
                            <?php if (! empty($c['observacao'])): ?>
                                <div class="text-muted fs-8"><?= esc($c['observacao']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted">
                            <?php if (! empty($c['telefone'])): ?><div><i class="bi bi-telephone me-1"></i><?= esc($c['telefone']) ?></div><?php endif; ?>
                            <?php if (! empty($c['email'])): ?><div><i class="bi bi-envelope me-1"></i><?= esc($c['email']) ?></div><?php endif; ?>
                        </td>
                        <td class="small">
                            <div class="fw-semibold"><?= (int) $c['quantidade_acompanhantes'] ?></div>
                            <?php if (! empty($nomes)): ?>
                                <div class="text-muted fs-8 text-truncate" style="max-width: 200px;" title="<?= esc(implode(', ', $nomes)) ?>">
                                    <?= esc(implode(', ', array_slice($nomes, 0, 2))) ?><?= count($nomes) > 2 ? ' +' . (count($nomes) - 2) : '' ?>
                                </div>
                            <?php elseif ((int) $c['quantidade_acompanhantes'] > 0): ?>
                                <div class="fs-8"><a href="<?= site_url('painel/eventos/' . $evento->id . '/convidados/' . $c['id']) ?>">detalhar</a></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center fw-semibold"><?= (int) $c['quantidade_acompanhantes'] + 1 ?></td>
                        <td>
                            <span class="badge text-bg-<?= $badgeStatus[$status] ?? 'secondary' ?>">
                                <?= esc($rotuloStatus[$status] ?? ucfirst($status)) ?>
                            </span>
                            <?php if (! empty($c['check_in_em'])): ?>
                                <div class="fs-8 text-success mt-1">
                                    <i class="bi bi-check2-circle me-1"></i>Check-in
                                    <?= esc(date('d/m H:i', strtotime((string) $c['check_in_em']))) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted">
                            <?= $c['criado_em'] !== null ? esc(date('d/m/Y H:i', strtotime((string) $c['criado_em']))) : '—' ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap gap-1 justify-content-end">
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="<?= site_url('painel/eventos/' . $evento->id . '/convidados/' . $c['id']) ?>">
                                    <i class="bi bi-person-lines-fill me-1"></i>Acompanhantes
                                </a>
                                <?php if ($status !== 'confirmado'): ?>
                                    <form method="post" class="d-inline"
                                          action="<?= site_url('painel/eventos/' . $evento->id . '/convidados/' . $c['id'] . '/aprovar') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="retorno" value="<?= esc($retorno, 'attr') ?>">
                                        <button class="btn btn-sm btn-success"><i class="bi bi-check2 me-1"></i>Aprovar</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($status !== 'recusado'): ?>
                                    <form method="post" class="d-inline"
                                          action="<?= site_url('painel/eventos/' . $evento->id . '/convidados/' . $c['id'] . '/recusar') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="retorno" value="<?= esc($retorno, 'attr') ?>">
                                        <button class="btn btn-sm btn-outline-warning">Recusar</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" class="d-inline"
                                      action="<?= site_url('painel/eventos/' . $evento->id . '/convidados/' . $c['id'] . '/remover') ?>"
                                      onsubmit="return confirm('Remover este convidado da lista?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="retorno" value="<?= esc($retorno, 'attr') ?>">
                                    <button class="btn btn-sm btn-outline-danger btn-icon"><i class="bi bi-trash"></i></button>
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
