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
$rsvpEncerrado = $rsvpEncerrado ?? false;
$maxAcompanhantes = $maxAcompanhantes ?? 20;
$galeria   = $galeria ?? [];
$oldNomes      = array_values((array) (old('acompanhantes_nome') ?? []));
$oldCategorias = array_values((array) (old('acompanhantes_categoria') ?? []));
$oldStatus     = (string) (old('status') ?? '');
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
$navInicial = ! empty($evento->permite_rsvp) ? 'presenca' : 'presentes';
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

        .hero-cta .btn { padding: .65rem 1.5rem; font-weight: 700; }
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
            padding: .5rem 1rem;
        }
        .btn-presentear:hover { filter: brightness(.93); color: #fff; }
        .btn-presentear:disabled { opacity: .55; }

        /* ---------- REVEAL (progressive enhancement) ----------
           O estado "escondido" só vale quando o JS está ativo (classe .hotsite-js
           no <html>), então sem JS o conteúdo permanece visível. */
        .hotsite-js .reveal:not(.reveal-visible) { opacity: 0; }
        .reveal.reveal-visible { animation: hs-in .55s cubic-bezier(.22, .61, .36, 1) backwards; }
        @keyframes hs-in {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: reduce) {
            .hotsite-js .reveal:not(.reveal-visible) { opacity: 1; }
            .reveal.reveal-visible { animation: none; }
            .presente-card, .presente-card:hover, .galeria-item:hover img { transition: none; }
        }

        /* ---------- HERO: chips, título e contagem ---------- */
        .hotsite-hero h1 { font-size: clamp(1.9rem, 5.2vw, 3.5rem); }
        .hero-meta { gap: .6rem; }
        .hero-meta-chip {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.28);
            border-radius: 999px; padding: .35rem .85rem; font-size: .85rem; font-weight: 500;
            backdrop-filter: blur(6px);
        }
        .countdown-label {
            font-size: .76rem; text-transform: uppercase; letter-spacing: .08em;
            opacity: .85; margin-bottom: .5rem;
        }

        /* ---------- ESGOTADO / DISPONIBILIDADE ---------- */
        .presente-img .esgotado-overlay {
            position: absolute; inset: 0; z-index: 3;
            background: rgba(17,24,39,.55); color: #fff; font-weight: 700;
            display: flex; align-items: center; justify-content: center; gap: .4rem;
            font-size: .95rem; letter-spacing: .03em; backdrop-filter: blur(1px);
        }
        .presente-faltam { color: var(--cor-primaria); font-weight: 700; }

        /* ---------- ACESSIBILIDADE (foco visível) ---------- */
        .hotsite-nav .nav-pill:focus-visible,
        .galeria-item:focus-visible,
        .hotsite-hero .btn:focus-visible,
        .presente-card a:focus-visible,
        .presente-card button:focus-visible,
        .btn-confirmar-presenca:focus-visible,
        .btn-nao-vou:focus-visible {
            outline: 3px solid color-mix(in srgb, var(--cor-primaria) 55%, transparent);
            outline-offset: 2px;
        }

        /* ---------- FAB compartilhar (mobile) ---------- */
        .fab-share {
            position: fixed; right: 1rem; bottom: 1rem; z-index: 1050;
            width: 54px; height: 54px; border-radius: 50%;
            background: #25D366; color: #fff; border: 0;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.5rem; box-shadow: 0 10px 24px rgba(0,0,0,.25);
            transition: transform .15s ease, filter .15s ease;
        }
        .fab-share:hover { color: #fff; filter: brightness(.95); transform: translateY(-2px); }
        @media (min-width: 992px) { .fab-share { display: none; } }

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

        /* ---------- GALERIA ---------- */
        .galeria-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: .75rem;
        }
        .galeria-item {
            position: relative; border: 0; padding: 0; background: none; cursor: pointer;
            border-radius: var(--raio); overflow: hidden; aspect-ratio: 1 / 1;
        }
        .galeria-item img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .35s ease; }
        .galeria-item:hover img { transform: scale(1.06); }
        .galeria-legenda {
            position: absolute; left: 0; right: 0; bottom: 0; padding: .6rem .65rem;
            color: #fff; font-size: .78rem; text-align: left; line-height: 1.2;
            background: linear-gradient(180deg, transparent, rgba(0,0,0,.7));
        }

        /* ---------- RSVP ---------- */
        .rsvp-icone {
            width: 64px; height: 64px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 2rem; margin-bottom: .6rem;
            background: color-mix(in srgb, var(--cor-primaria) 12%, #fff);
        }
        .btn-confirmar-presenca {
            background: linear-gradient(135deg, #16a34a, #059669);
            border: 0; color: #fff; font-weight: 700;
            padding: .8rem 1.8rem;
            box-shadow: 0 8px 20px rgba(22,163,74,.25);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .btn-confirmar-presenca:hover { color: #fff; transform: translateY(-2px); box-shadow: 0 12px 26px rgba(22,163,74,.35); }
        .btn-nao-vou {
            background: #fff; border: 1px solid rgba(0,0,0,.12); color: #4b5563; font-weight: 700;
            padding: .8rem 1.8rem;
            transition: transform .15s ease, background .15s ease;
        }
        .btn-nao-vou:hover { background: #f3f4f6; color: #111827; transform: translateY(-2px); }
        .tema-escuro .btn-nao-vou { background: transparent; color: #e5e7eb; border-color: rgba(255,255,255,.25); }
        .tema-escuro .btn-nao-vou:hover { background: rgba(255,255,255,.08); color: #fff; }

        .rsvp-modal-icone {
            width: 56px; height: 56px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.7rem;
            background: color-mix(in srgb, var(--cor-primaria) 12%, #fff);
        }
        .acompanhante-row { background: #f9fafb; }
        .tema-escuro .acompanhante-row { background: #15151F; border-color: rgba(255,255,255,.12) !important; }

        /* O formulário do RSVP é filho direto do .modal-content. Como o Bootstrap
           só limita a altura do .modal-body quando ele é filho direto do
           .modal-content, sem isto o corpo cresce além da tela e o conteúdo é
           cortado (o modal não rola no mobile). */
        #modalRsvp .modal-content > form {
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        /* Cartões de categoria do acompanhante */
        .cat-opcoes { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
        @media (max-width: 575.98px) { .cat-opcoes { grid-template-columns: 1fr; } }
        .cat-opcao { position: relative; margin: 0; cursor: pointer; }
        .cat-opcao input { position: absolute; opacity: 0; width: 1px; height: 1px; }
        .cat-box {
            display: flex; flex-direction: column; align-items: center; gap: .15rem; height: 100%;
            border: 2px solid #E5E7EB; border-radius: .9rem; padding: .7rem .5rem; text-align: center;
            transition: all .15s ease; background: #fff;
        }
        .tema-escuro .cat-box { background: #15151F; border-color: rgba(255,255,255,.14); }
        .cat-opcao:hover .cat-box { border-color: rgba(0,0,0,.25); }
        .cat-opcao input:focus-visible + .cat-box { outline: 2px solid var(--cor-primaria); outline-offset: 2px; }
        .cat-opcao input:checked + .cat-box {
            border-color: var(--cor-primaria);
            background: color-mix(in srgb, var(--cor-primaria) 12%, #fff);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--cor-primaria) 14%, transparent);
        }
        .tema-escuro .cat-opcao input:checked + .cat-box { background: color-mix(in srgb, var(--cor-primaria) 26%, #15151F); }
        .cat-icone { font-size: 1.6rem; line-height: 1; }
        .cat-titulo { font-weight: 700; font-size: .82rem; }
        .cat-sub { font-size: .7rem; color: #6B7280; line-height: 1.1; }
        .tema-escuro .cat-sub { color: #9ca3af; }

        .hotsite-footer { background: #111827; color: #9ca3af; }
        .hotsite-footer a { color: #d1d5db; text-decoration: none; }
        .hotsite-footer a:hover { color: #fff; }

        #toast {
            position: fixed; left: 50%; bottom: 1.5rem; transform: translateX(-50%) translateY(150%);
            background: #111827; color: #fff; padding: .7rem 1.1rem; border-radius: 999px;
            font-size: .88rem; z-index: 1080; transition: transform .3s ease, opacity .3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,.25);
            opacity: 0; visibility: hidden; pointer-events: none;
        }
        #toast.show { transform: translateX(-50%) translateY(0); opacity: 1; visibility: visible; }
    </style>
    <script>document.documentElement.classList.add('hotsite-js');</script>
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
                <span class="hero-meta-chip"><i class="bi bi-calendar-event"></i><?= esc($dataTexto) ?></span>
            <?php endif; ?>
            <?php if (! empty($evento->local_nome)): ?>
                <span class="hero-meta-chip"><i class="bi bi-geo-alt"></i><?= esc($evento->local_nome) ?></span>
            <?php endif; ?>
        </div>

        <?php if (! empty($dataIso) && strtotime($dataIso) > time()): ?>
            <p class="countdown-label">Contagem regressiva</p>
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
            <?php if (! empty($evento->permite_rsvp) && $modo !== 'demo' && ! $rsvpEncerrado): ?>
                <a class="btn btn-light" href="#presenca"><i class="bi bi-check2-circle me-1"></i>Confirmar presença</a>
            <?php endif; ?>
            <a class="btn btn-<?= (! empty($evento->permite_rsvp) && $modo !== 'demo' && ! $rsvpEncerrado) ? 'glass' : 'light' ?>" href="#presentes"><i class="bi bi-gift me-1"></i>Ver presentes</a>
            <a class="btn btn-glass" target="_blank" rel="noopener"
               href="https://wa.me/?text=<?= $textoShare ?>"><i class="bi bi-whatsapp me-1"></i>Compartilhar</a>
            <button class="btn btn-glass" type="button" id="btn-copiar-link"><i class="bi bi-link-45deg me-1"></i>Copiar link</button>
        </div>
    </div>
</header>

<!-- ============================ NAV ============================ -->
<nav class="hotsite-nav py-2">
    <div class="container d-flex gap-1 justify-content-center flex-wrap">
        <a href="#presentes" class="nav-pill<?= $navInicial === 'presentes' ? ' active' : '' ?>" data-nav="presentes"><i class="bi bi-gift"></i>Presentes</a>
        <?php if (! empty($evento->permite_rsvp)): ?>
            <a href="#presenca" class="nav-pill<?= $navInicial === 'presenca' ? ' active' : '' ?>" data-nav="presenca"><i class="bi bi-check2-circle"></i>Presença</a>
        <?php endif; ?>
        <?php if (! empty($galeria)): ?>
            <a href="#galeria" class="nav-pill" data-nav="galeria"><i class="bi bi-images"></i>Galeria</a>
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

    <!-- ===================== PRESENÇA (RSVP) ===================== -->
    <?php if (! empty($evento->permite_rsvp)): ?>
        <section id="presenca" class="py-3">
            <div class="card border-0 shadow-sm rounded-4 reveal">
                <div class="card-body p-4 p-md-5 text-center">
                    <span class="rsvp-icone">💌</span>
                    <h2 class="h3 secao-titulo mb-1">Confirme sua presença</h2>
                    <?php if (! empty($evento->limite_convidados)): ?>
                        <p class="secao-sub mb-4">
                            <?= (int) $stats['confirmados'] ?> confirmação(ões) ·
                            limite de <?= (int) $evento->limite_convidados ?> convidados
                        </p>
                    <?php else: ?>
                        <p class="secao-sub mb-4">Sua resposta ajuda o organizador a preparar tudo com carinho.</p>
                    <?php endif; ?>

                    <?php if ($modo === 'demo'): ?>
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                            <button type="button" class="btn btn-confirmar-presenca btn-lg" data-bs-toggle="modal" data-bs-target="#modalDemo">
                                <i class="bi bi-hand-thumbs-up-fill me-1"></i>Sim, estarei lá
                            </button>
                            <button type="button" class="btn btn-nao-vou btn-lg" data-bs-toggle="modal" data-bs-target="#modalDemo">
                                <i class="bi bi-hand-thumbs-down-fill me-1"></i>Não posso ir
                            </button>
                        </div>
                    <?php elseif ($rsvpEncerrado): ?>
                        <div class="alert alert-warning rounded-4 text-center mb-0">
                            <i class="bi bi-people-fill me-1"></i>
                            As confirmações estão <strong>encerradas</strong>: o limite de convidados foi atingido.
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                            <button type="button" class="btn btn-confirmar-presenca btn-lg"
                                    data-bs-toggle="modal" data-bs-target="#modalRsvp" data-vai="1">
                                <i class="bi bi-hand-thumbs-up-fill me-1"></i>Sim, estarei lá
                            </button>
                            <button type="button" class="btn btn-nao-vou btn-lg"
                                    data-bs-toggle="modal" data-bs-target="#modalRsvp" data-vai="0">
                                <i class="bi bi-hand-thumbs-down-fill me-1"></i>Não posso ir
                            </button>
                        </div>
                        <p class="text-muted fs-8 mt-3 mb-0">Você poderá adicionar acompanhantes na confirmação.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
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
                                <?php if ($disponivel < 1): ?>
                                    <span class="esgotado-overlay"><i class="bi bi-check2-circle"></i> Esgotado</span>
                                <?php endif; ?>
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
                                    <p class="presente-cotas mb-3">
                                        <?= $vendidasP ?>/<?= $metaP ?> cotas
                                        <?php if ($disponivel > 0): ?>
                                            · <span class="presente-faltam">faltam <?= $disponivel ?></span>
                                        <?php endif; ?>
                                    </p>
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

    <!-- ===================== GALERIA ===================== -->
    <?php if (! empty($galeria)): ?>
        <section id="galeria" class="py-4">
            <div class="text-center mb-4">
                <h2 class="h3 secao-titulo mb-1">Galeria de fotos</h2>
                <p class="secao-sub mb-0">Momentos especiais compartilhados pelo organizador.</p>
            </div>
            <div class="galeria-grid">
                <?php foreach ($galeria as $foto): ?>
                    <?php $fotoUrl = $imgUrl($foto['imagem'] ?? null); ?>
                    <?php if ($fotoUrl === null): ?><?php continue; ?><?php endif; ?>
                    <button type="button" class="galeria-item reveal" data-bs-toggle="modal" data-bs-target="#modalGaleria"
                            data-img="<?= esc($fotoUrl, 'attr') ?>"
                            data-legenda="<?= esc((string) ($foto['legenda'] ?? ''), 'attr') ?>">
                        <img src="<?= esc($fotoUrl, 'attr') ?>" alt="<?= esc((string) ($foto['legenda'] ?? 'Foto do evento'), 'attr') ?>" loading="lazy">
                        <?php if (! empty($foto['legenda'])): ?>
                            <span class="galeria-legenda"><?= esc((string) $foto['legenda']) ?></span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
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

<?php if (! empty($galeria)): ?>
    <div class="modal fade" id="modalGaleria" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4" style="background: rgba(17,24,39,.96);">
                <div class="modal-body p-3 text-center">
                    <img src="" alt="" id="galeria-img" class="img-fluid rounded-3" style="max-height: 78vh;">
                    <p class="text-white-50 mt-3 mb-0" id="galeria-legenda"></p>
                    <button type="button" class="btn btn-light btn-sm mt-3" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($modo !== 'demo' && ! empty($evento->permite_rsvp) && ! $rsvpEncerrado): ?>
    <div class="modal fade" id="modalRsvp" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4 position-relative">
                <form method="post" action="<?= site_url($evento->slug . '/rsvp') ?>" id="form-rsvp"
                      data-max="<?= (int) $maxAcompanhantes ?>"
                      data-old-nomes="<?= esc(json_encode($oldNomes), 'attr') ?>"
                      data-old-categorias="<?= esc(json_encode($oldCategorias), 'attr') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" id="rsvp-status" value="confirmado">

                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Fechar" style="z-index: 2;"></button>

                    <div class="modal-body pt-4 px-4">
                        <div class="text-center mb-4">
                            <span class="rsvp-modal-icone" id="rsvp-icone">💌</span>
                            <h5 class="fw-bold mt-2 mb-1" id="rsvp-titulo">Confirmar presença</h5>
                            <p class="text-muted mb-0" id="rsvp-intro"></p>
                        </div>

                        <div id="rsvp-bloco-dados">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-7" for="rsvp-nome">
                                        <i class="bi bi-person me-1"></i>Seu nome *
                                    </label>
                                    <input type="text" class="form-control" id="rsvp-nome" name="nome"
                                           value="<?= esc(old('nome')) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-7" for="rsvp-telefone">
                                        <i class="bi bi-whatsapp me-1"></i>Telefone <span class="text-muted fw-normal">(opcional)</span>
                                    </label>
                                    <input type="text" class="form-control" id="rsvp-telefone" name="telefone"
                                           value="<?= esc(old('telefone')) ?>">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4" id="rsvp-divisor">

                        <div id="rsvp-bloco-acompanhantes">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <span class="fw-semibold"><i class="bi bi-people me-1"></i>Acompanhantes</span>
                                <button type="button" class="btn btn-sm btn-outline-brand" id="rsvp-add-acompanhante">
                                    <i class="bi bi-plus-lg me-1"></i>Adicionar acompanhante
                                </button>
                            </div>
                            <div id="rsvp-acompanhantes-rows" class="d-flex flex-column"></div>
                            <p class="text-muted fs-8 mb-0 mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Informe o nome e a categoria de cada acompanhante. Crianças e bebês contam como menores.
                            </p>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-presentear px-4" id="rsvp-submit">Confirmar presença</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<button type="button" class="fab-share" id="fab-share" aria-label="Compartilhar lista no WhatsApp">
    <i class="bi bi-whatsapp"></i>
</button>

<div id="toast" role="status" aria-live="polite"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($modo !== 'demo'): ?>
    <?= view('templates/partials/chat', [
        'canal'         => 'site',
        'eventoId'      => $evento->id ?? null,
        'variante'      => 'hotsite',
        'corPrimaria'   => $evento->cor_primaria ?? null,
        'corSecundaria' => $evento->cor_secundaria ?? null,
    ]) ?>
<?php endif; ?>
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

    // ---------- Reveal (com cascata) ----------
    const revelar = Array.prototype.slice.call(document.querySelectorAll('.reveal'));
    const semMovimento = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (semMovimento || ! ('IntersectionObserver' in window)) {
        revelar.forEach(function (el) { el.classList.add('reveal-visible'); });
    } else {
        const io = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (e) {
                if (! e.isIntersecting) { return; }
                const el  = e.target;
                const pai = el.parentElement;
                let atraso = 0;

                if (pai) {
                    const emGrade = pai.id === 'grade-presentes'
                        || pai.classList.contains('galeria-grid')
                        || pai.classList.contains('row');
                    if (emGrade) {
                        const irmaos = Array.prototype.filter.call(pai.children, function (c) {
                            return c.classList.contains('reveal');
                        });
                        atraso = Math.max(0, irmaos.indexOf(el)) * 60;
                    }
                }

                window.setTimeout(function () { el.classList.add('reveal-visible'); }, atraso);
                io.unobserve(el);
            });
        }, { threshold: 0.08 });
        revelar.forEach(function (el) { io.observe(el); });
    }

    // ---------- Nav ativa ----------
    const secoes = ['presentes', 'galeria', 'presenca', 'recados']
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

    // ---------- FAB compartilhar (mobile) ----------
    const fab = document.getElementById('fab-share');
    if (fab) {
        fab.addEventListener('click', function () {
            const url = window.location.href.split('#')[0];
            window.open(
                'https://wa.me/?text=' + encodeURIComponent(document.title + ' — ' + url),
                '_blank',
                'noopener'
            );
        });
    }
})();
</script>

<script>
(function () {
    const form = document.getElementById('form-rsvp');
    if (!form) { return; }

    const status  = document.getElementById('rsvp-status');
    const titulo  = document.getElementById('rsvp-titulo');
    const intro   = document.getElementById('rsvp-intro');
    const icone   = document.getElementById('rsvp-icone');
    const divisor = document.getElementById('rsvp-divisor');
    const submit  = document.getElementById('rsvp-submit');
    const bloco   = document.getElementById('rsvp-bloco-acompanhantes');
    const rows    = document.getElementById('rsvp-acompanhantes-rows');
    const addBtn  = document.getElementById('rsvp-add-acompanhante');
    const max     = parseInt(form.dataset.max, 10) || 20;
    const oldNomes = JSON.parse(form.dataset.oldNomes || '[]');
    const oldCategorias = JSON.parse(form.dataset.oldCategorias || '[]');

    const CATEGORIAS = [
        ['adulto',  '🧑', 'Adulto',  'ou adolescente'],
        ['crianca', '🧒', 'Criança', '5 a 12 anos'],
        ['bebe',    '👶', 'Bebê',    'menos de 5 anos']
    ];

    function renumerar() {
        Array.prototype.forEach.call(rows.children, function (row, i) {
            row.querySelector('[data-campo="nome"]').name = 'acompanhantes_nome[' + i + ']';
            row.querySelectorAll('[data-campo="categoria"]').forEach(function (radio) {
                radio.name = 'acompanhantes_categoria[' + i + ']';
            });
            if (! row.querySelector('[data-campo="categoria"]:checked')) {
                const primeiro = row.querySelector('[data-campo="categoria"]');
                if (primeiro) { primeiro.checked = true; }
            }
        });

        addBtn.disabled = rows.children.length >= max;
    }

    function criarLinha(nome, categoria) {
        const row = document.createElement('div');
        row.className = 'acompanhante-row border rounded-4 p-3 mb-3';

        let cards = '';
        CATEGORIAS.forEach(function (cat, idx) {
            const id = 'cat-' + Date.now() + '-' + idx + '-' + Math.random().toString(36).slice(2, 6);
            cards +=
                '<label class="cat-opcao">' +
                    '<input type="radio" data-campo="categoria" value="' + cat[0] + '" id="' + id + '"' +
                    (categoria === cat[0] ? ' checked' : '') + '>' +
                    '<span class="cat-box">' +
                        '<span class="cat-icone">' + cat[1] + '</span>' +
                        '<span class="cat-titulo">' + cat[2] + '</span>' +
                        '<span class="cat-sub">' + cat[3] + '</span>' +
                    '</span>' +
                '</label>';
        });

        row.innerHTML =
            '<div class="d-flex justify-content-between align-items-center mb-2">' +
                '<span class="fs-8 fw-semibold text-muted text-uppercase">Acompanhante</span>' +
                '<button type="button" class="btn btn-sm btn-link text-danger p-0" data-remover>' +
                    '<i class="bi bi-trash me-1"></i>Remover' +
                '</button>' +
            '</div>' +
            '<div class="mb-3">' +
                '<label class="form-label fs-7 fw-semibold mb-1"><i class="bi bi-person me-1"></i>Nome completo</label>' +
                '<input type="text" class="form-control" data-campo="nome" placeholder="Ex.: Maria Fernanda Souza" required>' +
            '</div>' +
            '<label class="form-label fs-7 fw-semibold mb-1"><i class="bi bi-people me-1"></i>Categoria</label>' +
            '<div class="cat-opcoes">' + cards + '</div>';

        row.querySelector('[data-campo="nome"]').value = nome || '';
        row.querySelector('[data-remover]').addEventListener('click', function () {
            row.remove();
            renumerar();
        });

        rows.appendChild(row);
    }

    addBtn.addEventListener('click', function () {
        if (rows.children.length >= max) { return; }
        criarLinha('', 'adulto');
        renumerar();

        // Leva o novo cartão à vista (no mobile o modal agora rola).
        const nova = rows.lastElementChild;
        if (nova) {
            const reduzir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            nova.scrollIntoView({ behavior: reduzir ? 'auto' : 'smooth', block: 'nearest' });
        }
    });

    // Restaura linhas de uma submissão anterior (erros de validação).
    if (oldNomes.length) {
        oldNomes.forEach(function (nome, i) {
            criarLinha(nome, oldCategorias[i] || 'adulto');
        });
    }
    renumerar();

    // Ao abrir o modal, decide entre "vou" e "não vou".
    const modal = document.getElementById('modalRsvp');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (evento) {
            const vai = ! (evento.relatedTarget && evento.relatedTarget.dataset.vai === '0');
            status.value = vai ? 'confirmado' : 'recusado';

            bloco.classList.toggle('d-none', !vai);
            if (divisor) { divisor.classList.toggle('d-none', !vai); }

            icone.textContent = vai ? '🎉' : '👋';
            titulo.textContent = vai ? 'Confirmar presença' : 'Confirmar ausência';
            submit.textContent = vai ? 'Confirmar presença' : 'Confirmar ausência';
            intro.textContent = vai
                ? 'Que alegria! Confirme seus dados e adicione acompanhantes, se houver.'
                : 'Sentiremos sua falta! Confirme abaixo que você não poderá ir.';

            rows.querySelectorAll('input').forEach(function (el) { el.disabled = !vai; });
        });
    }
})();
</script>

<?php if (! empty($galeria)): ?>
<script>
(function () {
    const modal = document.getElementById('modalGaleria');
    if (!modal) { return; }

    modal.addEventListener('show.bs.modal', function (evento) {
        const botao = evento.relatedTarget;
        if (!botao) { return; }

        const img = document.getElementById('galeria-img');
        const leg = document.getElementById('galeria-legenda');
        const texto = botao.getAttribute('data-legenda') || '';

        img.src = botao.getAttribute('data-img') || '';
        leg.textContent = texto;
        leg.style.display = texto ? '' : 'none';
    });
})();
</script>
<?php endif; ?>
</body>
</html>
