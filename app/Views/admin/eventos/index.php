<?= $this->extend('templates/layouts/app') ?>

<?php
$f = static fn (string $campo): string => (string) ($filtros[$campo] ?? '');

$queryExport = array_filter($filtros, static fn ($v) => $v !== null && $v !== '');
$exportUrl   = site_url('admin/listas/exportar' . ($queryExport !== [] ? '?' . http_build_query($queryExport) : ''));

/** Cartões de resumo (clicáveis) — cada um aplica um filtro. */
$cartoes = [
    ['rotulo' => 'Total de listas', 'valor' => $resumo['total'],      'icone' => 'bi-collection',    'cor' => 'text-brand',   'url' => site_url('admin/listas')],
    ['rotulo' => 'Publicadas',      'valor' => $resumo['publicadas'], 'icone' => 'bi-broadcast',     'cor' => 'text-success', 'url' => site_url('admin/listas?status=publicado')],
    ['rotulo' => 'Rascunhos',       'valor' => $resumo['rascunhos'],  'icone' => 'bi-pencil',        'cor' => 'text-secondary', 'url' => site_url('admin/listas?status=rascunho')],
    ['rotulo' => 'Encerradas',      'valor' => $resumo['encerradas'], 'icone' => 'bi-clock-history', 'cor' => 'text-dark',    'url' => site_url('admin/listas?status=encerrado')],
    ['rotulo' => 'Ativas',          'valor' => $resumo['ativas'],     'icone' => 'bi-check2-circle', 'cor' => 'text-success', 'url' => site_url('admin/listas?arquivado=0')],
    ['rotulo' => 'Arquivadas',      'valor' => $resumo['arquivadas'], 'icone' => 'bi-archive',       'cor' => 'text-warning', 'url' => site_url('admin/listas?arquivado=1')],
];
?>

<?= $this->section('conteudo') ?>

<div class="row g-3 mb-3">
    <?php foreach ($cartoes as $c): ?>
        <div class="col-6 col-md-4 col-xl-2">
            <a class="card border-0 shadow-sm rounded-4 h-100 text-decoration-none transition-hover"
               href="<?= esc($c['url'], 'attr') ?>">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted text-uppercase fs-8 fw-semibold"><?= esc($c['rotulo']) ?></span>
                        <i class="bi <?= esc($c['icone'], 'attr') ?> <?= esc($c['cor'], 'attr') ?>"></i>
                    </div>
                    <p class="h3 fw-bold mb-0 text-dark"><?= (int) $c['valor'] ?></p>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<form class="card border-0 shadow-sm rounded-4 mb-3" method="get" action="<?= site_url('admin/listas') ?>">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-busca">Buscar</label>
                <input type="search" class="form-control" id="f-busca" name="busca" value="<?= esc($f('busca')) ?>"
                       placeholder="Título, slug ou organizador">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-status">Status</label>
                <select class="form-select" id="f-status" name="status">
                    <option value="">Todos</option>
                    <?php foreach (['rascunho', 'publicado', 'encerrado'] as $opcao): ?>
                        <option value="<?= esc($opcao) ?>" <?= $f('status') === $opcao ? 'selected' : '' ?>>
                            <?= esc(rotulo_status_evento($opcao)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-situacao">Situação</label>
                <select class="form-select" id="f-situacao" name="arquivado">
                    <option value="">Todas</option>
                    <option value="0" <?= $f('arquivado') === '0' ? 'selected' : '' ?>>Ativas</option>
                    <option value="1" <?= $f('arquivado') === '1' ? 'selected' : '' ?>>Arquivadas</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-tipo">Tipo</label>
                <select class="form-select" id="f-tipo" name="tipo_evento">
                    <option value="">Todos</option>
                    <?php foreach ($tipos as $chave => $tipo): ?>
                        <option value="<?= esc($chave) ?>" <?= $f('tipo_evento') === (string) $chave ? 'selected' : '' ?>>
                            <?= esc($tipo['rotulo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-org">Organizador</label>
                <select class="form-select" id="f-org" name="organizador">
                    <option value="">Todos</option>
                    <?php foreach ($orgs as $org): ?>
                        <option value="<?= (int) $org->id ?>" <?= $f('organizador') === (string) $org->id ? 'selected' : '' ?>>
                            <?= esc($org->nome) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-periodo">Período</label>
                <select class="form-select" id="f-periodo" name="periodo">
                    <option value="">Sempre</option>
                    <option value="7" <?= $f('periodo') === '7' ? 'selected' : '' ?>>Últimos 7 dias</option>
                    <option value="30" <?= $f('periodo') === '30' ? 'selected' : '' ?>>Últimos 30 dias</option>
                    <option value="90" <?= $f('periodo') === '90' ? 'selected' : '' ?>>Últimos 90 dias</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-brand flex-grow-1"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/listas') ?>" title="Limpar filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </div>
    </div>
</form>

<?php if (empty($listas)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <p class="mb-1 fw-semibold">Nenhuma lista encontrada.</p>
            <p class="text-muted mb-0">Ajuste os filtros da busca.</p>
        </div>
    </div>
<?php else: ?>
    <form id="loteForm" class="d-none" method="post" action="<?= site_url('admin/listas/lote') ?>"><?= csrf_field() ?></form>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm" name="acao" form="loteForm" style="max-width: 190px;" required>
                <option value="">Ação em lote...</option>
                <option value="publicar">Publicar</option>
                <option value="despublicar">Despublicar</option>
                <option value="arquivar">Arquivar</option>
                <option value="reativar">Reativar</option>
            </select>
            <button class="btn btn-sm btn-brand" type="submit" form="loteForm"
                    onclick="return confirm('Aplicar a ação selecionada às listas marcadas?');">
                <i class="bi bi-lightning-charge me-1"></i>Aplicar
            </button>
            <span class="text-muted fs-8" id="lote-contador"></span>
        </div>
        <a class="btn btn-sm btn-outline-secondary ms-auto" href="<?= esc($exportUrl, 'attr') ?>">
            <i class="bi bi-filetype-csv me-1"></i>Exportar CSV
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input class="form-check-input" type="checkbox" id="checkTodos"
                                   aria-label="Selecionar todas as listas">
                        </th>
                        <th>Lista</th>
                        <th>Organizador</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Situação</th>
                        <th>Criada em</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($listas as $lista): ?>
                    <tr>
                        <td>
                            <input class="form-check-input check-lista" type="checkbox" name="ids[]"
                                   value="<?= (int) $lista->id ?>" form="loteForm"
                                   aria-label="Selecionar lista <?= esc($lista->titulo) ?>">
                        </td>
                        <td>
                            <div class="fw-semibold"><?= esc($lista->titulo) ?></div>
                            <div class="fs-8 text-muted">
                                <?php if ($lista->status === 'publicado'): ?>
                                    <a href="<?= esc(site_url($lista->slug), 'attr') ?>" target="_blank" rel="noopener">
                                        /<?= esc($lista->slug) ?> <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                <?php else: ?>
                                    /<?= esc($lista->slug) ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if (! empty($lista->organizador_nome)): ?>
                                <div class="fw-semibold fs-7"><?= esc($lista->organizador_nome) ?></div>
                                <a class="fs-8 text-muted" href="<?= site_url('admin/usuarios/' . $lista->usuario_id) ?>">
                                    <?= esc($lista->organizador_email) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted fs-8">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="fs-7"><?= esc(rotulo_tipo_evento((string) $lista->tipo_evento)) ?></td>
                        <td>
                            <span class="badge text-bg-<?= esc(cor_status_evento((string) $lista->status), 'attr') ?>">
                                <?= esc(rotulo_status_evento((string) $lista->status)) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($lista->arquivado): ?>
                                <span class="badge text-bg-secondary">Arquivada</span>
                            <?php else: ?>
                                <span class="badge text-bg-light border text-dark">Ativa</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted">
                            <?= $lista->criado_em !== null ? esc($lista->criado_em->format('d/m/Y')) : '—' ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap gap-1 justify-content-end">
                                <a class="btn btn-sm btn-outline-brand" href="<?= site_url('admin/listas/' . $lista->id) ?>">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <form method="post" class="d-inline"
                                      action="<?= site_url('admin/listas/' . $lista->id . '/publicar') ?>">
                                    <?= csrf_field() ?>
                                    <?php if ($lista->status === 'publicado'): ?>
                                        <button class="btn btn-sm btn-outline-secondary" title="Despublicar">
                                            <i class="bi bi-eye-slash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-success" title="Publicar">
                                            <i class="bi bi-broadcast"></i>
                                        </button>
                                    <?php endif; ?>
                                </form>

                                <form method="post" class="d-inline"
                                      action="<?= site_url('admin/listas/' . $lista->id . '/arquivar') ?>"
                                      onsubmit="return confirm('<?= $lista->arquivado ? 'Reativar' : 'Arquivar' ?> esta lista?');">
                                    <?= csrf_field() ?>
                                    <?php if ($lista->arquivado): ?>
                                        <button class="btn btn-sm btn-outline-success" title="Reativar">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-warning" title="Arquivar">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        <?= $pager->links('listas', 'default_full') ?>
    </div>
<?php endif; ?>

<script>
(function () {
    var todos = document.getElementById('checkTodos');
    if (!todos) { return; }

    var caixas   = Array.prototype.slice.call(document.querySelectorAll('.check-lista'));
    var contador = document.getElementById('lote-contador');

    function atualizar() {
        var marcadas = caixas.filter(function (c) { return c.checked; }).length;
        if (contador) {
            contador.textContent = marcadas ? marcadas + ' selecionada(s)' : '';
        }
        todos.checked = marcadas > 0 && marcadas === caixas.length;
        todos.indeterminate = marcadas > 0 && marcadas < caixas.length;
    }

    todos.addEventListener('change', function () {
        caixas.forEach(function (c) { c.checked = todos.checked; });
        atualizar();
    });
    caixas.forEach(function (c) { c.addEventListener('change', atualizar); });
    atualizar();
})();
</script>

<?= $this->endSection() ?>
