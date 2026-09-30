<?php
/**
 * Hotsite do evento (página de lista de presentes).
 *
 * @var object           $evento     Evento (Entity) ou objeto equivalente (exemplos)
 * @var list<array>      $presentes
 * @var list<array>      $recados
 * @var 'real'|'demo'    $modo
 * @var bool             $escuro
 * @var string|null      $dataTexto
 * @var string|null      $dataIso
 * @var array{cotas_total:int,cotas_vendidas:int,arrecadado:float,confirmados:int} $stats
 */
$modo    = $modo ?? 'real';
$escuro  = $escuro ?? false;
$stats   = $stats ?? ['cotas_total' => 0, 'cotas_vendidas' => 0, 'arrecadado' => 0.0, 'confirmados' => 0];
$exibirValores = isset($evento->exibir_valores) ? (bool) $evento->exibir_valores : true;

$imgUrl = static function (?string $caminho): ?string {
    $caminho = trim((string) $caminho);
    if ($caminho === '') {
        return null;
    }
    return str_starts_with($caminho, 'http') ? $caminho : base_url($caminho);
};
$capa = $imgUrl($evento->imagem_capa ?? null);

$percentualMeta = null;
if (! empty($evento->meta_valor) && (float) $evento->meta_valor > 0) {
    $percentualMeta = min(100, round($stats['arrecadado'] / (float) $evento->meta_valor * 100));
}

$urlPublica = site_url($evento->slug);
$textoShare = rawurlencode($evento->titulo . ' — veja a lista de presentes: ' . $urlPublica);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?= view('templates/partials/design_evento', [
        'titulo'        => $evento->titulo,
        'corPrimaria'   => $evento->cor_primaria,
        'corSecundaria' => $evento->cor_secundaria,
        'tema'          => $evento->tema ?? 'classico',
        'escuro'        => $escuro,
    ]) ?>
    <style>
        html { scroll-behavior: smooth; }
        body { overflow-x: hidden; }

        /* ---------- HERO ---------- */
        .hotsite-hero {
            position: relative;
            color: #fff;
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            background-size: cover;
            background-position: center;
            padding: 4.5rem 0 3.5rem;
        }
        .hotsite-hero.has-cover { background-blend-mode: normal; }
        .hotsite-hero::after {
            content: ""; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(0,0,0,.15), rgba(0,0,0,.05));
            pointer-events: none;
        }
        .hotsite-hero > .container { position: relative; z-index: 2; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.35);
            color: #fff; font-weight: 600; font-size: .8rem;
            padding: .35rem .85rem; border-radius: 999px; backdrop-filter: blur(6px);
        }
        .hotsite-hero h1 { font-weight: 800; letter-spacing: -.02em; text-shadow: 0 2px 24px rgba(0,0,0,.25); }
        .hero-meta { display: flex; flex-wrap: wrap; gap: 1.25rem; justify-content: center; opacity: .95; }
        .hero-meta i { opacity: .85; }

        .hero-stats { display: flex; flex-wrap: wrap; gap: .75rem; justify-content: center; }
        .hero-stat {
            background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.28);
            border-radius: 1rem; padding: .7rem 1.1rem; min-width: 120px; backdrop-filter: blur(6px);
        }
        .hero-stat strong { display: block; font-size: 1.35rem; font-weight: 800; line-height: 1.1; }
        .hero-stat span { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; opacity: .9; }

        /* ---------- COUNTDOWN ---------- */
        .countdown { display: flex; gap: .6rem; justify-content: center; }
        .countdown .cx {
            background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.28);
            border-radius: .85rem; padding: .55rem .8rem; min-width: 68px; backdrop-filter: blur(6px);
        }
        .countdown .cx strong { display: block; font-size: 1.5rem; font-weight: 800; line-height: 1; font-variant-numeric: tabular-nums; }
        .countdown .cx span { font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; opacity: .85; }

        .hero-cta .btn { border-radius: 999px; padding: .65rem 1.5rem; font-weight: 700; }
        .btn-glass {
            background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.45); color: #fff;
        }
        .btn-glass:hover { background: rgba(255,255,255,.28); color: #fff; }

        /* ---------- NAV ---------- */
        .hotsite-nav {
            position: sticky; top: 0; z-index: 1030;
            background: rgba(255,255,255,.9); backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,.06);
        }
        .tema-escuro .hotsite-nav { background: rgba(11,11,18,.85); border-color: rgba(255,255,255,.08); }
        .hotsite-nav .nav-pill {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .5rem 1rem; border-radius: 999px; text-decoration: none;
            color: #4b5563; font-weight: 600; font-size: .88rem; white-space: nowrap;
            transition: background .18s ease, color .18s ease;
        }
        .tema-escuro .hotsite-nav .nav-pill { color: #d1d5db; }
        .hotsite-nav .nav-pill:hover { background: rgba(0,0,0,.05); }
        .hotsite-nav .nav-pill.active { background: var(--cor-primaria); color: #fff; }

        section[id] { scroll-margin-top: 74px; }

        /* ---------- SEÇÕES ---------- */
        .secao-titulo { font-weight: 800; letter-spacing: -.02em; }
        .secao-sub { color: #6b7280; }
        .tema-escuro .secao-sub { color: #9ca3af; }

        /* ---------- PROGRESSO DA META ---------- */
        .meta-card { border-radius: var(--raio); }
        .progress { background: rgba(0,0,0,.07); border-radius: 999px; }
        .tema-escuro .progress { background: rgba(255,255,255,.12); }
        .progress-bar { border-radius: 999px; }

        /* ---------- TOOLBAR ---------- */
        .toolbar { display: flex; flex-wrap: wrap; gap: .6rem; }
        .toolbar .form-control, .toolbar .form-select { border-radius: 999px; }

        /* ---------- CARDS DE PRESENTE ---------- */
        .presente-card {
            border: 0; border-radius: var(--raio); overflow: hidden; background: #fff;
            box-shadow: 0 1px 2px rgba(16,24,40,.06), 0 1px 3px rgba(16,24,40,.1);
            transition: transform .22s ease, box-shadow .22s ease;
            display: flex; flex-direction: column; height: 100%;
        }
        .tema-escuro .presente-card { background: #15151F; border: 1px solid rgba(255,255,255,.08); }
        .presente-card:hover { transform: translateY(-6px); box-shadow: 0 18px 40px rgba(16,24,40,.16); }
        .presente-img { position: relative; height: 170px; overflow: hidden; background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria)); }
        .presente-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
        .presente-card:hover .presente-img img { transform: scale(1.06); }
        .presente-img .ph { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,.85); font-size: 2.4rem; }
        .presente-tag {
            position: absolute; top: .75rem; right: .75rem; z-index: 2;
            background: rgba(255,255,255,.92); color: #111827; font-size: .7rem; font-weight: 700;
            padding: .25rem .6rem; border-radius: 999px;
        }
        .presente-body { padding: 1.1rem 1.1rem 1.15rem; display: flex; flex-direction: column; flex: 1; }
        .presente-body h3 { font-size: 1.02rem; font-weight: 700; margin-bottom: .25rem; }
        .presente-desc { font-size: .84rem; color: #6b7280; margin-bottom: .75rem; }
        .tema-escuro .presente-desc { color: #9ca3af; }
        .presente-preco { font-weight: 800; font-size: 1.05rem; color: var(--cor-primaria); }
        .presente-cotas { font-size: .76rem; color: #6b7280; }
        .tema-escuro .presente-cotas { color: #9ca3af; }
        .btn-presentear {
            background: var(--cor-primaria); border: 0; color: #fff; font-weight: 700;
            border-radius: 999px; padding: .5rem 1rem;
        }
        .btn-presentear:hover { filter: brightness(.93); color: #fff; }
        .btn-presentear:disabled { opacity: .55; }

        /* ---------- REVEAL ---------- */
        .reveal { opacity: 0; transform: translateY(18px); transition: opacity .55s ease, transform .55s ease; }
        .reveal.reveal-visible { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) {
            .reveal { opacity: 1; transform: none; transition: none; }
            .presente-card, .presente-card:hover { transition: none; transform: none; }
        }

        /* ---------- RECADOS ---------- */
        .recado-card {
            border: 0; border-radius: var(--raio); background: #fff; padding: 1.1rem 1.2rem;
            box-shadow: 0 1px 2px rgba(16,24,40,.06); position: relative; height: 100%;
        }
        .tema-escuro .recado-card { background: #15151F; border: 1px solid rgba(255,255,255,.08); }
        .recado-card .quote { position: absolute; top: .4rem; right: .9rem; font-size: 3rem; line-height: 1; color: var(--cor-primaria); opacity: .12; font-family: Georgia, serif; }
        .avatar {
            width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: var(--cor-primaria); color: #fff; font-weight: 700;
        }

        .hotsite-footer { background: #111827; color: #9ca3af; }
        .hotsite-footer a { color: #d1d5db; text-decoration: none; }
        .hotsite-footer a:hover { color: #fff; }

        #toast {
            position: fixed; left: 50%; bottom: 1.5rem; transform: translateX(-50%) translateY(120%);
            background: #111827; color: #fff; padding: .7rem 1.1rem; border-radius: 999px;
            font-size: .88rem; z-index: 1080; transition: transform .3s ease; box-shadow: 0 10px 30px rgba(0,0,0,.25);
        }
        #toast.show { transform: translateX(-50%) translateY(0); }
    </style>
</head>
<body class="bg-body-tertiary <?= $escuro ? 'tema-escuro' : '' ?>">

<?php if ($modo === 'demo'): ?>
    <div class="faixa-demo py-2">
        <div class="container d-flex flex-wrap align-items-center justify-content-center gap-2 text-center">
            <span class="fs-7"><i class="bi bi-eye-fill me-1"></i>Você está vendo uma <strong>lista de exemplo</strong>.</span>
            <a class="btn btn-sm btn-light fw-semibold" href="<?= site_url('registro') ?>">Criar a minha lista grátis</a>
        </div>
    </div>
<?php endif; ?>

<!-- ============================ HERO ============================ -->
<header class="hotsite-hero <?= $capa !== null ? 'has-cover' : '' ?>"
    <?php if ($capa !== null): ?>
        style="background-image: linear-gradient(180deg, rgba(17,24,39,.45), rgba(17,24,39,.72)), url('<?= esc($capa) ?>');"
    <?php endif; ?>>
    <div class="container text-center" style="max-width: 860px;">
        <span class="hero-badge text-uppercase mb-3">
            <i class="bi bi-gift-fill"></i><?= esc(str_replace('_', ' ', $evento->tipo_evento)) ?>
        </span>

        <h1 class="display-4 fw-bold mb-2"><?= esc($evento->titulo) ?></h1>

        <?php if (! empty($evento->subtitulo)): ?>
            <p class="lead mb-3 opacity-90"><?= esc($evento->subtitulo) ?></p>
        <?php endif; ?>

        <div class="hero-meta mb-4">
            <?php if (! empty($dataTexto)): ?>
                <span><i class="bi bi-calendar-event me-1"></i><?= esc($dataTexto) ?></span>
            <?php endif; ?>
            <?php if (! empty($evento->local_nome)): ?>
                <span><i class="bi bi-geo-alt me-1"></i><?= esc($evento->local_nome) ?></span>
            <?php endif; ?>
        </div>

        <?php if (! empty($dataIso) && strtotime($dataIso) > time()): ?>
            <div class="countdown mb-4" id="countdown" data-data="<?= esc($dataIso) ?>">
                <div class="cx"><strong data-cd="dias">--</strong><span>dias</span></div>
                <div class="cx"><strong data-cd="horas">--</strong><span>horas</span></div>
                <div class="cx"><strong data-cd="min">--</strong><span>min</span></div>
                <div class="cx"><strong data-cd="seg">--</strong><span>seg</span></div>
            </div>
        <?php endif; ?>

        <div class="hero-stats mb-4">
            <div class="hero-stat">
                <strong><?= (int) $stats['cotas_vendidas'] ?><span class="fs-6 fw-normal opacity-75">/<?= (int) $stats['cotas_total'] ?></span></strong>
                <span>Cotas presenteadas</span>
            </div>
            <?php if (! empty($evento->permite_rsvp)): ?>
                <div class="hero-stat">
                    <strong><?= (int) $stats['confirmados'] ?></strong>
                    <span>Confirmações</span>
                </div>
            <?php endif; ?>
            <?php if ($percentualMeta !== null): ?>
                <div class="hero-stat">
                    <strong><?= (int) $percentualMeta ?>%</strong>
                    <span>Da meta</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="hero-cta d-flex flex-wrap gap-2 justify-content-center">
            <a class="btn btn-light" href="#presentes"><i class="bi bi-gift me-1"></i>Ver presentes</a>
            <a class="btn btn-glass" target="_blank" rel="noopener"
               href="https://wa.me/?text=<?= $textoShare ?>"><i class="bi bi-whatsapp me-1"></i>Compartilhar</a>
            <button class="btn btn-glass" type="button" id="btn-copiar-link"><i class="bi bi-link-45deg me-1"></i>Copiar link</button>
        </div>
    </div>
</header>

<!-- ============================ NAV ============================ -->
<nav class="hotsite-nav py-2">
    <div class="container d-flex gap-1 justify-content-center flex-wrap">
        <a href="#presentes" class="nav-pill active" data-nav="presentes"><i class="bi bi-gift"></i>Presentes</a>
        <?php if (! empty($evento->permite_rsvp)): ?>
            <a href="#presenca" class="nav-pill" data-nav="presenca"><i class="bi bi-check2-circle"></i>Presença</a>
        <?php endif; ?>
        <?php if (! empty($evento->permite_recados)): ?>
            <a href="#recados" class="nav-pill" data-nav="recados"><i class="bi bi-chat-heart"></i>Recados</a>
        <?php endif; ?>
    </div>
</nav>

<main class="container py-4" style="max-width: 1040px;">
    <?= view('templates/partials/flash') ?>

    <?php if (! empty($evento->mensagem_convite)): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4 reveal">
            <div class="card-body p-4">
                <p class="mb-0 lead fs-6"><?= nl2br(esc($evento->mensagem_convite)) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($percentualMeta !== null): ?>
        <div class="card border-0 shadow-sm meta-card mb-4 reveal">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-end mb-2">
                    <div>
                        <p class="text-uppercase fs-8 fw-semibold text-muted mb-1">Meta do evento</p>
                        <p class="h4 fw-bold mb-0"><?= esc(moeda_brl($stats['arrecadado'])) ?>
                            <span class="fs-6 fw-normal text-muted">de <?= esc(moeda_brl($evento->meta_valor)) ?></span>
                        </p>
                    </div>
                    <span class="fs-5 fw-bold" style="color: var(--cor-primaria);"><?= (int) $percentualMeta ?>%</span>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar" role="progressbar" style="width: <?= (int) $percentualMeta ?>%; background-color: var(--cor-primaria);"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ===================== PRESENTES ===================== -->
    <section id="presentes" class="py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <div>
                <h2 class="h3 secao-titulo mb-1">Lista de presentes</h2>
                <p class="secao-sub mb-0">
                    <?= $modo === 'demo'
                        ? 'Assim o convidado escolhe a cota e paga no PIX.'
                        : 'Escolha um presente e faça sua contribuição via PIX.' ?>
                </p>
            </div>
            <span class="text-muted fs-7" id="contador-presentes"></span>
        </div>

        <?php if (empty($presentes)): ?>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <p class="mb-1 fw-semibold">A lista de presentes ainda está sendo preparada.</p>
                    <p class="text-muted mb-0">Volte em breve!</p>
                </div>
            </div>
        <?php else: ?>
            <div class="toolbar mb-4">
                <div class="input-group flex-grow-1" style="min-width: 220px; max-width: 340px;">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="search" class="form-control border-start-0" id="busca-presente" placeholder="Buscar presente...">
                </div>
                <select class="form-select" id="filtro-tipo" style="max-width: 200px;">
                    <option value="">Todos os tipos</option>
                    <option value="ficticio">Cotas em dinheiro</option>
                    <option value="real">Presentes reais</option>
                </select>
                <select class="form-select" id="ordenar" style="max-width: 220px;">
                    <option value="recomendados">Recomendados</option>
                    <option value="menor">Menor valor</option>
                    <option value="maior">Maior valor</option>
                    <option value="mais">Mais presenteados</option>
                </select>
            </div>

            <div class="row g-4" id="grade-presentes">
                <?php foreach ($presentes as $presente): ?>
                    <?php
                        $metaP     = max(1, (int) $presente['quantidade_meta']);
                        $vendidasP = (int) $presente['quantidade_vendida'];
                        $disponivel = $metaP - $vendidasP;
                        $pctP      = min(100, (int) round($vendidasP / $metaP * 100));
                        $tipoP     = $presente['tipo'] ?? 'ficticio';
                        $imgP      = $imgUrl($presente['imagem'] ?? null);
                    ?>
                    <div class="col-sm-6 col-lg-4 presente-item reveal"
                         data-nome="<?= esc(mb_strtolower((string) $presente['nome'])) ?>"
                         data-valor="<?= esc((string) $presente['valor']) ?>"
                         data-tipo="<?= esc($tipoP) ?>"
                         data-vendida="<?= $vendidasP ?>">
                        <article class="presente-card">
                            <div class="presente-img">
                                <?php if ($imgP !== null): ?>
                                    <img src="<?= esc($imgP) ?>" alt="<?= esc($presente['nome']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="ph"><i class="bi <?= $tipoP === 'real' ? 'bi-bag-heart' : 'bi-gift-fill' ?>"></i></div>
                                <?php endif; ?>
                                <span class="presente-tag"><?= $tipoP === 'real' ? 'Loja' : 'Cota' ?></span>
                            </div>
                            <div class="presente-body">
                                <h3><?= esc($presente['nome']) ?></h3>
                                <?php if (! empty($presente['descricao'])): ?>
                                    <p class="presente-desc"><?= esc($presente['descricao']) ?></p>
                                <?php endif; ?>

                                <?php if ($metaP > 1): ?>
                                    <div class="progress mb-1" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar"
                                             style="width: <?= $pctP ?>%; background-color: var(--cor-primaria);"></div>
                                    </div>
                                    <p class="presente-cotas mb-3"><?= $vendidasP ?>/<?= $metaP ?> cotas presenteadas</p>
                                <?php else: ?>
                                    <div class="mb-3"></div>
                                <?php endif; ?>

                                <div class="mt-auto d-flex align-items-center justify-content-between gap-2">
                                    <?php if ($exibirValores): ?>
                                        <span class="presente-preco"><?= esc(moeda_brl($presente['valor'])) ?></span>
                                    <?php else: ?>
                                        <span></span>
                                    <?php endif; ?>

                                    <?php if ($tipoP === 'real'): ?>
                                        <?php if (! empty($presente['link_afiliado'])): ?>
                                            <a class="btn btn-presentear btn-sm" target="_blank" rel="noopener"
                                               href="<?= esc($presente['link_afiliado']) ?>">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>Comprar
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-presentear btn-sm" disabled>Indisponível</button>
                                        <?php endif; ?>
                                    <?php elseif ($modo === 'demo'): ?>
                                        <button class="btn btn-presentear btn-sm" data-bs-toggle="modal" data-bs-target="#modalDemo">
                                            <i class="bi bi-gift me-1"></i>Presentear
                                        </button>
                                    <?php elseif ($evento->status !== 'publicado'): ?>
                                        <button class="btn btn-presentear btn-sm" disabled>Encerrado</button>
                                    <?php elseif ($disponivel < 1): ?>
                                        <button class="btn btn-presentear btn-sm" disabled>Esgotado</button>
                                    <?php else: ?>
                                        <a class="btn btn-presentear btn-sm" href="<?= site_url($evento->slug . '/presentear/' . $presente['id']) ?>">
                                            <i class="bi bi-gift me-1"></i>Presentear
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="sem-resultado" class="text-center text-muted py-5 d-none">
                <i class="bi bi-search fs-2 d-block mb-2 opacity-50"></i>
                Nenhum presente encontrado com esses filtros.
            </div>
        <?php endif; ?>
    </section>

    <!-- ===================== RSVP ===================== -->
    <?php if (! empty($evento->permite_rsvp)): ?>
        <section id="presenca" class="py-5">
            <div class="card border-0 shadow-sm rounded-4 reveal">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h2 class="h3 secao-titulo mb-1">Confirme sua presença</h2>
                        <p class="secao-sub mb-0">Sua resposta ajuda o organizador a preparar tudo com carinho.</p>
                    </div>

                    <?php if ($modo === 'demo'): ?>
                        <div class="text-center">
                            <p class="text-muted">Em uma lista real, o convidado confirma a presença por aqui.</p>
                            <button class="btn btn-presentear" data-bs-toggle="modal" data-bs-target="#modalDemo">Confirmar presença</button>
                        </div>
                    <?php else: ?>
                        <form method="post" action="<?= site_url($evento->slug . '/rsvp') ?>" class="row g-3 justify-content-center">
                            <?= csrf_field() ?>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-7" for="rsvp-nome">Nome</label>
                                <input type="text" class="form-control" id="rsvp-nome" name="nome" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-7" for="rsvp-telefone">Telefone <span class="text-muted fw-normal">(opcional)</span></label>
                                <input type="text" class="form-control" id="rsvp-telefone" name="telefone">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold fs-7" for="rsvp-acompanhantes">Acompanhantes</label>
                                <input type="number" min="0" value="0" class="form-control" id="rsvp-acompanhantes" name="quantidade_acompanhantes">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold fs-7" for="rsvp-status">Você vai?</label>
                                <select class="form-select" id="rsvp-status" name="status">
                                    <option value="confirmado">Sim, estarei presente</option>
                                    <option value="recusado">Não poderei ir</option>
                                </select>
                            </div>
                            <div class="col-12 text-center mt-4">
                                <button class="btn btn-presentear px-4"><i class="bi bi-check2-circle me-1"></i>Confirmar</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ===================== RECADOS ===================== -->
    <?php if (! empty($evento->permite_recados)): ?>
        <section id="recados" class="py-4">
            <div class="text-center mb-4">
                <h2 class="h3 secao-titulo mb-1">Mural de recados</h2>
                <p class="secao-sub mb-0">Deixe uma mensagem carinhosa para o organizador.</p>
            </div>

            <?php if ($modo === 'demo'): ?>
                <div class="text-center mb-4">
                    <button class="btn btn-presentear" data-bs-toggle="modal" data-bs-target="#modalDemo">
                        <i class="bi bi-chat-heart me-1"></i>Deixar um recado
                    </button>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-4 mb-4 reveal">
                    <div class="card-body p-4">
                        <form method="post" action="<?= site_url($evento->slug . '/recado') ?>" class="row g-3">
                            <?= csrf_field() ?>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold fs-7" for="recado-nome">Seu nome</label>
                                <input type="text" class="form-control" id="recado-nome" name="nome_autor" required>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold fs-7" for="recado-mensagem">Mensagem</label>
                                <textarea class="form-control" id="recado-mensagem" name="mensagem" rows="2" required></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button class="btn btn-presentear"><i class="bi bi-send me-1"></i>Enviar recado</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (! empty($recados)): ?>
                <div class="row g-3">
                    <?php foreach ($recados as $recado): ?>
                        <div class="col-md-6 reveal">
                            <div class="recado-card">
                                <span class="quote">&rdquo;</span>
                                <p class="mb-3"><?= nl2br(esc($recado['mensagem'])) ?></p>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar"><?= esc(mb_strtoupper(mb_substr((string) $recado['nome_autor'], 0, 1))) ?></span>
                                    <span class="fw-semibold"><?= esc($recado['nome_autor']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">Ainda não há recados. Seja o primeiro a escrever!</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>

<footer class="hotsite-footer py-4 text-center">
    <p class="mb-0 fs-7">
        Página criada com <a href="<?= site_url('/') ?>">Minha Lista VIP</a>
    </p>
</footer>

<?php if ($modo === 'demo'): ?>
    <div class="modal fade" id="modalDemo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-body text-center p-4">
                    <div class="mb-3" style="font-size: 2.5rem;">🎁</div>
                    <h3 class="h5 fw-bold mb-2">Esta é uma lista de exemplo</h3>
                    <p class="text-muted">
                        Aqui é só demonstração — nenhum pagamento é processado.
                        Crie a sua lista e comece a receber de verdade via PIX.
                    </p>
                    <div class="d-grid gap-2">
                        <a class="btn btn-presentear" href="<?= site_url('registro') ?>">Criar minha lista grátis</a>
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Continuar navegando</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div id="toast" role="status" aria-live="polite"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    // ---------- Countdown ----------
    const cd = document.getElementById('countdown');
    if (cd) {
        const alvo = new Date(cd.dataset.data.replace(' ', 'T')).getTime();
        const campos = {
            dias: cd.querySelector('[data-cd="dias"]'),
            horas: cd.querySelector('[data-cd="horas"]'),
            min: cd.querySelector('[data-cd="min"]'),
            seg: cd.querySelector('[data-cd="seg"]'),
        };
        const pad = (n) => String(n).padStart(2, '0');
        function tique() {
            let diff = alvo - Date.now();
            if (diff < 0) diff = 0;
            const d = Math.floor(diff / 86400000);
            const h = Math.floor(diff % 86400000 / 3600000);
            const m = Math.floor(diff % 3600000 / 60000);
            const s = Math.floor(diff % 60000 / 1000);
            campos.dias.textContent = d;
            campos.horas.textContent = pad(h);
            campos.min.textContent = pad(m);
            campos.seg.textContent = pad(s);
        }
        tique();
        setInterval(tique, 1000);
    }

    // ---------- Reveal ----------
    const revelar = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (e) {
                if (e.isIntersecting) { e.target.classList.add('reveal-visible'); io.unobserve(e.target); }
            });
        }, { threshold: 0.08 });
        revelar.forEach(function (el) { io.observe(el); });
    } else {
        revelar.forEach(function (el) { el.classList.add('reveal-visible'); });
    }

    // ---------- Nav ativa ----------
    const secoes = ['presentes', 'presenca', 'recados']
        .map(function (id) { return document.getElementById(id); })
        .filter(Boolean);
    if (secoes.length && 'IntersectionObserver' in window) {
        const io2 = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (e) {
                if (!e.isIntersecting) return;
                document.querySelectorAll('.hotsite-nav .nav-pill').forEach(function (p) {
                    p.classList.toggle('active', p.dataset.nav === e.target.id);
                });
            });
        }, { rootMargin: '-45% 0px -50% 0px' });
        secoes.forEach(function (s) { io2.observe(s); });
    }

    // ---------- Filtros da lista ----------
    const grade = document.getElementById('grade-presentes');
    if (grade) {
        const itens = Array.prototype.slice.call(grade.querySelectorAll('.presente-item'));
        const busca = document.getElementById('busca-presente');
        const filtro = document.getElementById('filtro-tipo');
        const ordenar = document.getElementById('ordenar');
        const vazio = document.getElementById('sem-resultado');
        const contador = document.getElementById('contador-presentes');

        function aplicar() {
            const termo = (busca.value || '').trim().toLowerCase();
            const tipo = filtro.value;
            let visiveis = 0;

            itens.forEach(function (el) {
                const okTermo = !termo || (el.dataset.nome || '').indexOf(termo) !== -1;
                const okTipo = !tipo || el.dataset.tipo === tipo;
                const mostrar = okTermo && okTipo;
                el.classList.toggle('d-none', !mostrar);
                if (mostrar) visiveis++;
            });

            if (vazio) vazio.classList.toggle('d-none', visiveis !== 0);
            if (contador) contador.textContent = visiveis + ' de ' + itens.length + ' presentes';
        }

        function reordenar() {
            const modo = ordenar.value;
            const ordenados = itens.slice().sort(function (a, b) {
                if (modo === 'menor') return parseFloat(a.dataset.valor) - parseFloat(b.dataset.valor);
                if (modo === 'maior') return parseFloat(b.dataset.valor) - parseFloat(a.dataset.valor);
                if (modo === 'mais') return parseInt(b.dataset.vendida, 10) - parseInt(a.dataset.vendida, 10);
                return 0;
            });
            ordenados.forEach(function (el) { grade.appendChild(el); });
        }

        busca.addEventListener('input', aplicar);
        filtro.addEventListener('change', aplicar);
        ordenar.addEventListener('change', function () { reordenar(); aplicar(); });
        aplicar();
    }

    // ---------- Copiar link ----------
    const toast = document.getElementById('toast');
    function aviso(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(function () { toast.classList.remove('show'); }, 2200);
    }
    const btnCopiar = document.getElementById('btn-copiar-link');
    if (btnCopiar) {
        btnCopiar.addEventListener('click', async function () {
            const url = window.location.href.split('#')[0];
            try {
                await navigator.clipboard.writeText(url);
            } catch (e) {
                const t = document.createElement('textarea');
                t.value = url; document.body.appendChild(t); t.select();
                document.execCommand('copy'); document.body.removeChild(t);
            }
            aviso('Link copiado!');
        });
    }
})();
</script>
</body>
</html>
