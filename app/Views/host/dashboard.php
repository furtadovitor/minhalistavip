<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>

<style>
    .lista-card { transition: transform .18s ease, box-shadow .18s ease; }
    .lista-card:hover { transform: translateY(-4px); box-shadow: 0 1.2rem 2.4rem rgba(17, 24, 39, .12); }
    .lista-capa { height: 148px; background-size: cover; background-position: center; overflow: hidden; }
    .lista-capa .capa-img { width: 100%; height: 100%; object-fit: cover; transition: transform .45s ease; }
    .lista-card:hover .lista-capa .capa-img { transform: scale(1.05); }
    .filtro-vazio { display: none; }
</style>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon bg-indigo-100 text-indigo-700"><i class="bi bi-grid"></i></span>
                <div>
                    <p class="text-muted text-uppercase fs-8 fw-semibold mb-0">Listas ativas</p>
                    <p class="h4 fw-bold mb-0"><?= count($ativos) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon" style="background:#ECFDF5;color:#047857;"><i class="bi bi-broadcast"></i></span>
                <div>
                    <p class="text-muted text-uppercase fs-8 fw-semibold mb-0">Publicadas</p>
                    <p class="h4 fw-bold mb-0"><?= (int) $publicados ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon" style="background:#ECFDF5;color:#047857;"><i class="bi bi-cash-stack"></i></span>
                <div>
                    <p class="text-muted text-uppercase fs-8 fw-semibold mb-0">Arrecadado</p>
                    <p class="h5 fw-bold mb-0"><?= esc(moeda_brl($arrecadado ?? 0)) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon bg-indigo-100 text-indigo-700"><i class="bi bi-wallet2"></i></span>
                <div>
                    <p class="text-muted text-uppercase fs-8 fw-semibold mb-0">Saldo</p>
                    <p class="h5 fw-bold mb-0"><?= esc(moeda_brl($saldo ?? 0)) ?></p>
                    <a href="<?= site_url('painel/carteira') ?>" class="fs-8 text-decoration-none">Ver financeiro &rarr;</a>
                </div>
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
    $gradiente = 'linear-gradient(135deg, ' . cor_hex($evento->cor_primaria, '#4F46E5') . ', ' . cor_hex($evento->cor_secundaria, '#10B981') . ')';
    $base = site_url('painel/eventos/' . $evento->id);
    $publicado = $evento->status === 'publicado';
    $urlHotsite = site_url($evento->slug);
    ?>
    <div class="col-md-6 col-xl-4 lista-card-wrap"
         data-lista-titulo="<?= esc(mb_strtolower($evento->titulo), 'attr') ?>"
         data-lista-tipo="<?= esc(mb_strtolower(rotulo_tipo_evento($evento->tipo_evento)), 'attr') ?>">
        <div class="card lista-card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="lista-capa position-relative"
                 <?php if ($capa): ?>style="background: <?= $gradiente ?>;"<?php else: ?>style="background: <?= $gradiente ?>;"<?php endif; ?>>
                <?php if ($capa): ?>
                    <img src="<?= esc($capa, 'attr') ?>" alt="" class="capa-img">
                <?php endif; ?>
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

                <?php if (! $publicado && ! $arquivado): ?>
                    <p class="fs-8 text-warning mb-3">
                        <i class="bi bi-exclamation-circle me-1"></i>Rascunho — publique para receber convidados.
                    </p>
                <?php endif; ?>

                <div class="mt-auto d-flex flex-wrap align-items-center gap-2">
                    <a class="btn btn-sm btn-brand" href="<?= esc($base) ?>">
                        <i class="bi bi-sliders me-1"></i>Gerenciar
                    </a>
                    <?php if ($publicado): ?>
                        <a class="btn btn-sm btn-outline-success btn-icon" href="<?= esc($urlHotsite) ?>" target="_blank"
                           title="Ver página"><i class="bi bi-box-arrow-up-right"></i></a>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-icon" data-copiar="<?= esc($urlHotsite, 'attr') ?>"
                                title="Copiar link"><i class="bi bi-link-45deg"></i></button>
                    <?php else: ?>
                        <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/publicar') ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-success btn-icon" title="Publicar"><i class="bi bi-broadcast"></i></button>
                        </form>
                    <?php endif; ?>
                    <form method="post" class="ms-auto"
                          action="<?= site_url('painel/eventos/' . $evento->id . '/arquivar') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="arquivar" value="<?= $arquivado ? '0' : '1' ?>">
                        <button class="btn btn-sm btn-outline-secondary btn-icon" title="<?= $arquivado ? 'Reativar' : 'Arquivar' ?>">
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

<div class="row g-2 align-items-center mb-3">
    <div class="col-12 col-md-5">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="search" class="form-control border-start-0" id="busca-lista"
                   placeholder="Buscar lista pelo nome..." aria-label="Buscar lista">
        </div>
    </div>
    <div class="col-8 col-md-3">
        <select class="form-select" id="filtro-lista-tipo" aria-label="Filtrar por tipo">
            <option value="">Todos os tipos</option>
            <?php
            $tiposUsados = [];
            foreach (array_merge($ativos, $arquivados) as $ev) {
                $tiposUsados[$ev->tipo_evento] = rotulo_tipo_evento($ev->tipo_evento);
            }
            asort($tiposUsados);
            foreach ($tiposUsados as $chave => $rotulo): ?>
                <option value="<?= esc(mb_strtolower($rotulo), 'attr') ?>"><?= esc($rotulo) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<ul class="nav nav-pills gap-1 mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pane-ativas" type="button">
            Ativas <span class="badge text-bg-light ms-1" data-tab-count="ativas"><?= count($ativos) ?></span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pane-arquivadas" type="button">
            Arquivadas <span class="badge text-bg-light ms-1" data-tab-count="arquivadas"><?= count($arquivados) ?></span>
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
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <a class="btn btn-brand" href="<?= site_url('painel/eventos/novo') ?>">
                            <i class="bi bi-plus-lg me-1"></i>Criar minha primeira lista
                        </a>
                        <a class="btn btn-outline-brand" href="<?= site_url('exemplos') ?>" target="_blank">Ver exemplos</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3" data-lista-grid>
                <?php foreach ($ativos as $evento) { $renderCard($evento, false); } ?>
            </div>
            <div class="card border-0 shadow-sm rounded-4 filtro-vazio">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-search fs-2 d-block mb-2 opacity-50"></i>
                    Nenhuma lista ativa corresponde à busca.
                </div>
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
            <div class="row g-3" data-lista-grid>
                <?php foreach ($arquivados as $evento) { $renderCard($evento, true); } ?>
            </div>
            <div class="card border-0 shadow-sm rounded-4 filtro-vazio">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-search fs-2 d-block mb-2 opacity-50"></i>
                    Nenhuma lista arquivada corresponde à busca.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    var busca = document.getElementById('busca-lista');
    var tipo  = document.getElementById('filtro-lista-tipo');
    if (!busca || !tipo) { return; }

    var grids = Array.prototype.slice.call(document.querySelectorAll('[data-lista-grid]'));

    function filtrar() {
        var termo = (busca.value || '').trim().toLowerCase();
        var t     = tipo.value;

        grids.forEach(function (grid) {
            var cards = Array.prototype.slice.call(grid.querySelectorAll('.lista-card-wrap'));
            var visiveis = 0;
            cards.forEach(function (card) {
                var okTermo = !termo || (card.dataset.listaTitulo || '').indexOf(termo) !== -1;
                var okTipo  = !t || card.dataset.listaTipo === t;
                var mostrar = okTermo && okTipo;
                card.classList.toggle('d-none', !mostrar);
                if (mostrar) { visiveis++; }
            });

            var vazio = grid.parentElement.querySelector('.filtro-vazio');
            if (vazio) {
                vazio.classList.toggle('filtro-vazio', visiveis !== 0);
                vazio.classList.toggle('d-block', visiveis === 0);
            }

            var chave = grid.parentElement.id === 'pane-arquivadas' ? 'arquivadas' : 'ativas';
            var badge = document.querySelector('[data-tab-count="' + chave + '"]');
            if (badge) { badge.textContent = visiveis; }
        });
    }

    busca.addEventListener('input', filtrar);
    tipo.addEventListener('change', filtrar);

    // Copiar link do hotsite
    document.querySelectorAll('[data-copiar]').forEach(function (botao) {
        botao.addEventListener('click', async function () {
            var url = botao.getAttribute('data-copiar');
            var icone = botao.querySelector('i');
            try {
                await navigator.clipboard.writeText(url);
            } catch (e) {
                var t = document.createElement('textarea');
                t.value = url; document.body.appendChild(t); t.select();
                document.execCommand('copy'); document.body.removeChild(t);
            }
            if (icone) {
                var antigo = icone.className;
                icone.className = 'bi bi-check2 text-success';
                setTimeout(function () { icone.className = antigo; }, 1500);
            }
        });
    });
})();
</script>

<?= $this->endSection() ?>
