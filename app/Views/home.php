<?= $this->extend('templates/layouts/public') ?>

<?= $this->section('conteudo') ?>

<style>
    /* =====================================================================
       Landing page (estilos escopados na Home) — prefixo .lp-
       ===================================================================== */
    .lp-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(900px 420px at 8% -15%, rgba(79, 70, 229, .20), transparent 60%),
            radial-gradient(760px 420px at 95% -5%, rgba(16, 185, 129, .16), transparent 55%),
            linear-gradient(180deg, #ffffff 0%, var(--bg) 100%);
    }
    .lp-hero::after {
        content: "";
        position: absolute;
        inset: auto -10% -40% auto;
        width: 480px;
        height: 480px;
        background: radial-gradient(closest-side, rgba(79, 70, 229, .10), transparent);
        pointer-events: none;
    }
    .lp-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: #fff;
        border: 1px solid rgba(79, 70, 229, .18);
        box-shadow: 0 .5rem 1.2rem rgba(17, 24, 39, .06);
        color: var(--brand-dark);
        font-weight: 600;
        font-size: .78rem;
        padding: .4rem .85rem;
        border-radius: 999px;
    }
    .lp-check { color: var(--success); }

    /* ---- Mockup do site do evento ---- */
    .lp-mock {
        position: relative;
        background: #fff;
        border-radius: 1.1rem;
        box-shadow: 0 1.6rem 3.4rem rgba(17, 24, 39, .16);
        overflow: hidden;
        border: 1px solid rgba(17, 24, 39, .06);
    }
    .lp-mock-bar {
        display: flex;
        align-items: center;
        gap: .4rem;
        padding: .6rem .8rem;
        background: #F3F4F6;
        border-bottom: 1px solid rgba(17, 24, 39, .06);
    }
    .lp-dot { width: 10px; height: 10px; border-radius: 50%; background: #D1D5DB; }
    .lp-dot:nth-child(1) { background: #FCA5A5; }
    .lp-dot:nth-child(2) { background: #FCD34D; }
    .lp-dot:nth-child(3) { background: #86EFAC; }
    .lp-mock-url {
        margin-left: .5rem;
        flex: 1;
        background: #fff;
        border-radius: 999px;
        font-size: .68rem;
        color: var(--muted);
        padding: .25rem .7rem;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .lp-mock-cover {
        position: relative;
        height: 176px;
        background-size: cover;
        background-position: center;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 1rem;
    }
    .lp-mock-tag {
        position: absolute;
        top: .8rem;
        left: .8rem;
        background: rgba(255, 255, 255, .92);
        color: #111827;
        font-size: .68rem;
        font-weight: 700;
        padding: .2rem .6rem;
        border-radius: 999px;
    }
    .lp-gift-icon {
        width: 40px;
        height: 40px;
        border-radius: .7rem;
        background: var(--brand-soft);
        color: var(--brand-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        flex: none;
        font-size: 1.05rem;
    }
    .lp-float-badge,
    .lp-float-pix {
        position: absolute;
        background: #fff;
        border-radius: .8rem;
        box-shadow: 0 .8rem 1.8rem rgba(17, 24, 39, .16);
        font-size: .78rem;
        font-weight: 700;
        padding: .5rem .75rem;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }
    .lp-float-badge { top: 3.2rem; left: .5rem; color: var(--brand-dark); }
    .lp-float-pix { bottom: 3.4rem; right: .5rem; color: #047857; }
    @media (min-width: 992px) {
        .lp-float-badge { left: -1rem; }
        .lp-float-pix { right: -1rem; }
    }

    /* ---- Faixa de confiança ---- */
    .lp-strip {
        background: #111827;
        color: #E5E7EB;
    }
    .lp-strip-item {
        display: flex;
        align-items: center;
        gap: .6rem;
        font-size: .85rem;
        font-weight: 500;
    }
    .lp-strip-item i { color: #A5B4FC; font-size: 1.15rem; }

    /* ---- Seção / cartões genéricos ---- */
    .lp-eyebrow {
        display: inline-block;
        background: var(--brand-soft);
        color: var(--brand-dark);
        font-weight: 700;
        font-size: .72rem;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: .35rem .75rem;
        border-radius: 999px;
    }
    .lp-card {
        border: 1px solid rgba(17, 24, 39, .06);
        border-radius: 1rem;
        background: #fff;
        height: 100%;
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .lp-card:hover { transform: translateY(-4px); box-shadow: 0 1rem 2rem rgba(17, 24, 39, .10); }
    .lp-icon {
        width: 52px;
        height: 52px;
        border-radius: .9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        background: var(--brand-soft);
        color: var(--brand-dark);
    }

    /* ---- Passos ---- */
    .lp-step-num {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--brand);
        color: #fff;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: none;
    }
    .lp-time {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #047857;
        background: #ECFDF5;
        border-radius: 999px;
        padding: .25rem .6rem;
    }

    /* ---- Selos de destaque (grátis / 1 minuto) ---- */
    .lp-flag {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .8rem;
        font-weight: 700;
        padding: .35rem .7rem;
        border-radius: 999px;
        background: #F3F4F6;
        color: #374151;
    }
    .lp-flag-green { background: #ECFDF5; color: #047857; }
    .lp-claim {
        background: linear-gradient(135deg, #10B981, #047857);
        box-shadow: 0 .9rem 2rem rgba(4, 120, 87, .25);
    }

    /* ---- Depoimentos ---- */
    .lp-stars { color: #F59E0B; font-size: .85rem; letter-spacing: .12em; }
    .lp-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #fff;
        flex: none;
    }
    .lp-testimonial {
        border: 1px solid rgba(17, 24, 39, .06);
        border-radius: 1rem;
        background: #fff;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow-wrap: break-word;
    }

    /* ---- Carrossel (depoimentos e exemplos) ---- */
    .lp-carousel { position: relative; }
    .lp-carousel-track {
        display: flex;
        gap: 1.25rem;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-snap-type: x mandatory;
        scroll-padding-left: .25rem;
        padding: .25rem .25rem 1.25rem;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .lp-carousel-track::-webkit-scrollbar { display: none; }
    .lp-carousel-track > * {
        scroll-snap-align: start;
        flex: 0 0 86%;
        min-width: 0;
    }
    @media (min-width: 576px) { .lp-carousel-track > * { flex-basis: 62%; } }
    @media (min-width: 768px) { .lp-carousel-track > * { flex-basis: 47%; } }
    @media (min-width: 992px) {
        .lp-carousel-track > * { flex-basis: calc(33.333% - 0.9rem); }
        .lp-carousel--duo .lp-carousel-track > * { flex-basis: calc(47% - 0.7rem); }
    }
    .lp-carousel-nav {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .75rem;
        margin-top: .25rem;
    }
    .lp-carousel--static .lp-carousel-nav { display: none; }
    .lp-nav-btn {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        border: 1px solid rgba(17, 24, 39, .12);
        background: #fff;
        color: var(--brand-dark);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }
    .lp-nav-btn:hover:not(:disabled) { background: var(--brand); color: #fff; border-color: var(--brand); }
    .lp-nav-btn:disabled { opacity: .35; cursor: default; }
    .lp-dots { display: flex; align-items: center; gap: .4rem; }
    .lp-dot {
        width: 8px;
        height: 8px;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: #D1D5DB;
        transition: width .2s ease, background .2s ease;
    }
    .lp-dot.is-active { width: 22px; border-radius: 999px; background: var(--brand); }
    .lp-carousel .card { height: 100%; }

    /* ---- Ajustes de responsivo / anti-overflow ---- */
    .lp-hero h1 { font-size: clamp(1.85rem, 4.8vw, 3rem); line-height: 1.16; overflow-wrap: break-word; }
    .lp-flag { max-width: 100%; }
    .lp-claim { overflow-wrap: break-word; }
    /* Botões longos quebram a linha em vez de estourar a margem */
    .btn { max-width: 100%; }
    .lp-strip-item { justify-content: center; }

    /* ---- Animações (respeitam prefers-reduced-motion) ---- */
    .lp-js .lp-reveal:not(.is-visible) { opacity: 0; }
    .lp-js .lp-reveal.is-visible {
        animation: lp-in .55s cubic-bezier(.22, .61, .36, 1) backwards;
        animation-delay: var(--lp-delay, 0ms);
    }

    /* Micro-interações de usabilidade */
    .lp-arrow { display: inline-block; transition: transform .18s ease; }
    .btn:hover .lp-arrow { transform: translateX(3px); }
    .lp-icon { transition: transform .2s ease; }
    .lp-card:hover .lp-icon { transform: scale(1.07); }
    .btn {
        transition: transform .12s ease, color .15s ease, background-color .15s ease,
            border-color .15s ease, box-shadow .15s ease;
    }
    .btn:active { transform: translateY(1px); }
    .lp-nav-btn:focus-visible,
    .lp-dot:focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; }

    @media (prefers-reduced-motion: no-preference) {
        .lp-hero::after { animation: lp-float 16s ease-in-out infinite; }
        .lp-progress-fill { animation: lp-grow .9s cubic-bezier(.22, .61, .36, 1) both; }
    }
    @keyframes lp-in {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes lp-grow {
        from { transform: scaleX(0); }
        to { transform: scaleX(1); }
    }
    @keyframes lp-float {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(-24px, -18px) scale(1.06); }
    }

    @media (prefers-reduced-motion: reduce) {
        .lp-js .lp-reveal:not(.is-visible) { opacity: 1; }
        .lp-js .lp-reveal.is-visible { animation: none; }
        .lp-progress-fill, .lp-hero::after { animation: none !important; }
        .btn:active { transform: none; }
        .lp-arrow, .lp-icon, .btn { transition: none; }
    }

    /* ---- Adereços ---- */
    .lp-quote { border-left: 3px solid var(--brand); }
    .accordion-button:not(.collapsed) { color: var(--brand-dark); background: var(--brand-soft); }
    .accordion-button:focus { box-shadow: 0 0 0 .2rem rgba(79, 70, 229, .15); }

    @media (max-width: 575.98px) {
        .lp-float-badge { left: .4rem; }
        .lp-float-pix { right: .4rem; }
    }
</style>

<script>document.documentElement.classList.add('lp-js');</script>

<!-- ============================== HERO ============================== -->
<section class="lp-hero py-5">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-reveal-stagger>
                <span class="lp-reveal lp-pill mb-3">
                    <i class="bi bi-lightning-charge-fill"></i> Grátis de verdade • sua lista pronta em 1 minuto
                </span>
                <h1 class="lp-reveal display-5 fw-bold lh-sm mb-3">
                    Crie o <span class="text-brand">site</span>, o <span class="text-brand">convite</span>
                    e a <span class="text-brand">lista de presentes</span> do seu evento
                </h1>
                <p class="lp-reveal fs-5 text-secondary mb-3">
                    Monte tudo em <strong>menos de 1 minuto</strong> e <strong>sem pagar nada</strong>.
                    Site personalizado, convite online com confirmação de presença e lista de presentes
                    com recebimento por PIX — sem mensalidade.
                </p>
                <div class="lp-reveal d-flex flex-wrap gap-2 mb-3">
                    <span class="lp-flag lp-flag-green"><i class="bi bi-check-lg"></i> É grátis</span>
                    <span class="lp-flag lp-flag-green"><i class="bi bi-stopwatch"></i> 1 minuto para criar</span>
                    <span class="lp-flag"><i class="bi bi-credit-card"></i> Sem cartão de crédito</span>
                </div>
                <div class="lp-reveal d-flex flex-wrap gap-2 mb-4">
                    <a class="btn btn-brand btn-lg px-4" href="<?= site_url('criar-lista-de-presente') ?>">
                        <i class="bi bi-rocket-takeoff me-2"></i>Criar minha lista grátis
                    </a>
                    <a class="btn btn-outline-brand btn-lg px-4" href="<?= ! empty($demos) ? site_url('demo/' . $demos[0]['slug']) : site_url('/') . '#exemplos' ?>">
                        Ver um exemplo
                    </a>
                </div>
                <div class="lp-reveal d-flex flex-wrap gap-3 fs-7 text-muted">
                    <span><i class="bi bi-check-circle-fill lp-check me-1"></i>Sem mensalidade</span>
                    <span><i class="bi bi-check-circle-fill lp-check me-1"></i>Convidado não precisa de conta</span>
                    <span><i class="bi bi-check-circle-fill lp-check me-1"></i>Receba por PIX</span>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="lp-reveal position-relative mx-auto" style="max-width: 480px; --lp-delay: 160ms;">
                    <div class="lp-mock">
                        <div class="lp-mock-bar">
                            <span class="lp-dot"></span><span class="lp-dot"></span><span class="lp-dot"></span>
                            <span class="lp-mock-url"><i class="bi bi-lock-fill"></i> minhalistavip.com.br/marina-e-gabriel</span>
                        </div>
                        <div class="lp-mock-cover"
                             style="background-image: linear-gradient(180deg, rgba(17,24,39,.10), rgba(17,24,39,.78)), url('https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80');">
                            <span class="lp-mock-tag">💍 Casamento</span>
                            <p class="mb-0 fw-bold fs-4 text-white">Marina &amp; Gabriel</p>
                            <p class="mb-0 fs-8 text-white-50">
                                <i class="bi bi-calendar-event me-1"></i>12 de Outubro · Ilhabela, SP
                            </p>
                        </div>
                        <div class="p-3">
                            <p class="text-uppercase fs-8 text-muted fw-semibold mb-2">Lista de presentes</p>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="lp-gift-icon"><i class="bi bi-airplane"></i></div>
                                <div class="flex-grow-1">
                                    <p class="mb-1 fw-semibold fs-7">Cota de Lua de Mel</p>
                                    <div class="progress" style="height:6px;">
                                        <div class="lp-progress-fill progress-bar bg-brand" style="width:30%;"></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-1 fw-bold fs-7 text-brand">R$ 300</p>
                                    <span class="btn btn-brand btn-sm px-2 py-0 fs-8">Presentear</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="lp-gift-icon"><i class="bi bi-cash-coin"></i></div>
                                <div class="flex-grow-1">
                                    <p class="mb-1 fw-semibold fs-7">Cota Livre em Dinheiro</p>
                                    <div class="progress" style="height:6px;">
                                        <div class="lp-progress-fill progress-bar bg-brand" style="width:55%;"></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-1 fw-bold fs-7 text-brand">R$ 50</p>
                                    <span class="btn btn-brand btn-sm px-2 py-0 fs-8">Presentear</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="lp-float-badge"><i class="bi bi-magic text-brand"></i> Grátis</span>
                    <span class="lp-float-pix"><i class="bi bi-qr-code"></i> Receba por PIX</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ======================= FAIXA DE CONFIANÇA ======================= -->
<section class="lp-strip py-3">
    <div class="container">
        <div class="row g-3 text-center text-sm-start justify-content-center">
            <div class="col-6 col-md-3"><span class="lp-strip-item justify-content-center justify-content-sm-start"><i class="bi bi-piggy-bank"></i> É grátis para criar</span></div>
            <div class="col-6 col-md-3"><span class="lp-strip-item justify-content-center justify-content-sm-start"><i class="bi bi-stopwatch"></i> Sua lista em 1 minuto</span></div>
            <div class="col-6 col-md-3"><span class="lp-strip-item justify-content-center justify-content-sm-start"><i class="bi bi-people"></i> RSVP de convidados</span></div>
            <div class="col-6 col-md-3"><span class="lp-strip-item justify-content-center justify-content-sm-start"><i class="bi bi-qr-code-scan"></i> Pagamento por PIX</span></div>
        </div>
    </div>
</section>

<!-- ========================= COMO FUNCIONA ========================= -->
<section class="py-5" id="como-funciona">
    <div class="container py-4">
        <div class="lp-reveal text-center mb-5" style="max-width: 720px; margin: 0 auto;">
            <span class="lp-eyebrow mb-2">É grátis e leva 1 minuto</span>
            <h2 class="fw-bold fs-2 mt-3 text-dark">Sua lista no ar em 3 passos rápidos</h2>
            <p class="text-muted fs-6 mb-0">
                Sem instalar nada, sem conhecimento técnico e sem mensalidade. Em menos de 1 minuto
                você cria a lista e compartilha a sua página.
            </p>
        </div>

        <div class="row g-4" data-reveal-stagger>
            <div class="col-md-4 lp-reveal">
                <div class="lp-card p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="lp-step-num">1</span>
                        <span class="lp-time">~30 segundos</span>
                    </div>
                    <h3 class="h6 fw-bold mb-2">Escolha a ocasião</h3>
                    <p class="fs-7 text-muted mb-0">
                        Casamento, aniversário, chá de bebê, formatura e muito mais — já com tema e
                        cores prontos para você começar.
                    </p>
                </div>
            </div>
            <div class="col-md-4 lp-reveal">
                <div class="lp-card p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="lp-step-num">2</span>
                        <span class="lp-time">~1 minuto</span>
                    </div>
                    <h3 class="h6 fw-bold mb-2">Personalize o site e o convite</h3>
                    <p class="fs-7 text-muted mb-0">
                        Adicione capa, título, data, local e a mensagem do convite. Escolha as cores
                        e a tipografia com um clique.
                    </p>
                </div>
            </div>
            <div class="col-md-4 lp-reveal">
                <div class="lp-card p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="lp-step-num">3</span>
                        <span class="lp-time">na hora</span>
                    </div>
                    <h3 class="h6 fw-bold mb-2">Compartilhe e receba</h3>
                    <p class="fs-7 text-muted mb-0">
                        Envie o link no WhatsApp. Os convidados confirmam presença e presenteiam via
                        PIX — o valor entra na sua carteira.
                    </p>
                </div>
            </div>
        </div>

        <div class="lp-reveal lp-claim mt-4 p-4 rounded-4 text-white d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3 text-center text-md-start">
                <i class="bi bi-stopwatch fs-1 d-none d-md-block"></i>
                <div>
                    <p class="fw-bold fs-5 mb-1">Criar a sua lista é grátis e leva cerca de 1 minuto</p>
                    <p class="mb-0 opacity-75 fs-7">Sem cadastro complicado, sem cartão de crédito e sem instalar nada.</p>
                </div>
            </div>
            <a class="btn btn-light btn-lg fw-semibold flex-shrink-0" href="<?= site_url('criar-lista-de-presente') ?>">
                <i class="bi bi-rocket-takeoff me-2"></i>Começar agora grátis
            </a>
        </div>
    </div>
</section>

<!-- ============================ PILARES ============================ -->
<section class="py-5 bg-light" id="pilares">
    <div class="container py-4">
        <div class="lp-reveal text-center mb-5" style="max-width: 720px; margin: 0 auto;">
            <span class="lp-eyebrow mb-2">Três ferramentas, uma só plataforma</span>
            <h2 class="fw-bold fs-2 mt-3 text-dark">Tudo o que a sua festa precisa</h2>
        </div>

        <div class="row g-4" data-reveal-stagger>
            <div class="col-md-4 lp-reveal">
                <div class="lp-card p-4">
                    <div class="lp-icon mb-3"><i class="bi bi-window-stack"></i></div>
                    <h3 class="h6 fw-bold mb-2">Site personalizado</h3>
                    <p class="fs-7 text-muted mb-3">
                        Uma página só sua, com capa, cores, tipografia e galeria de fotos — no
                        endereço <strong>minhalistavip.com.br/seu-evento</strong>.
                    </p>
                    <ul class="fs-7 text-secondary ps-3 mb-0">
                        <li>5 temas e paletas prontas</li>
                        <li>Informações da festa (data, local e endereço)</li>
                        <li>Galeria de fotos</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4 lp-reveal">
                <div class="lp-card p-4">
                    <div class="lp-icon mb-3"><i class="bi bi-envelope-heart"></i></div>
                    <h3 class="h6 fw-bold mb-2">Convite online</h3>
                    <p class="fs-7 text-muted mb-3">
                        Convite com link próprio e mensagem carinhosa, além da confirmação de
                        presença (RSVP) em tempo real.
                    </p>
                    <ul class="fs-7 text-secondary ps-3 mb-0">
                        <li>Mensagem personalizada aos convidados</li>
                        <li>RSVP com acompanhantes e limite de vagas</li>
                        <li>Mural de recados moderado</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4 lp-reveal">
                <div class="lp-card p-4">
                    <div class="lp-icon mb-3"><i class="bi bi-gift"></i></div>
                    <h3 class="h6 fw-bold mb-2">Lista de presentes</h3>
                    <p class="fs-7 text-muted mb-3">
                        Cotas em dinheiro, presentes reais ou cota livre. O convidado paga por PIX e
                        o valor cai na sua conta.
                    </p>
                    <ul class="fs-7 text-secondary ps-3 mb-0">
                        <li>Cotas com meta e progresso</li>
                        <li>Carteira com extrato e saque por PIX</li>
                        <li>Opção de ocultar os valores</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== ATALHOS POR TIPO DE EVENTO ===================== -->
<section class="py-5" id="atalhos">
    <div class="container py-4">
        <div class="lp-reveal text-center mb-5">
            <span class="lp-eyebrow mb-2">Atalhos</span>
            <h2 class="fw-bold fs-2 mt-3 text-dark">Crie sua lista de presentes</h2>
            <p class="text-muted fs-6 mb-0">
                Selecione o tipo de evento, escolha nome e descrição e comece a montar a lista.
            </p>
        </div>

        <div class="row g-3 justify-content-center">
            <?= view('templates/partials/tipos_evento_grid') ?>
        </div>
    </div>
</section>

<!-- ============================ RECURSOS ============================ -->
<section class="py-5 bg-light" id="recursos">
    <div class="container py-4">
        <div class="lp-reveal text-center mb-5" style="max-width: 720px; margin: 0 auto;">
            <span class="lp-eyebrow mb-2">Recursos</span>
            <h2 class="fw-bold fs-2 mt-3 text-dark">Muito além de uma lista de presentes</h2>
            <p class="text-muted fs-6 mb-0">
                Ferramentas pensadas para organizar a festa inteira — do convite ao recebimento.
            </p>
        </div>

        <div class="row g-4" data-reveal-stagger>
            <?php
            $recursos = [
                ['bi-palette', 'Temas e cores', 'Escolha entre temas prontos e personalize as cores do seu site.'],
                ['bi-gift', 'Lista de presentes', 'Cotas em dinheiro, presentes reais por link ou cota livre em qualquer valor.'],
                ['bi-calendar-check', 'Confirmação de presença', 'Receba os RSVPs em tempo real, com acompanhantes por categoria e limite de vagas.'],
                ['bi-geo-alt', 'Informações e local', 'Data, horário, local e endereço sempre visíveis para os convidados.'],
                ['bi-images', 'Galeria de fotos', 'Compartilhe os melhores momentos do casal, do bebê ou da festa.'],
                ['bi-chat-heart', 'Mural de recados', 'Receba mensagens carinhosas e aprove antes de publicar.'],
                ['bi-clipboard-check', 'Check-in no dia', 'Busque o convidado e marque a presença direto do celular.'],
                ['bi-qr-code-scan', 'Pagamento por PIX', 'Cobrança rápida e segura, com confirmação automática do pagamento.'],
            ];
            foreach ($recursos as $r): ?>
                <div class="col-sm-6 col-lg-3 lp-reveal">
                    <div class="lp-card p-4">
                        <div class="lp-icon mb-3"><i class="bi <?= esc($r[0], 'attr') ?>"></i></div>
                        <h3 class="h6 fw-bold mb-2"><?= esc($r[1]) ?></h3>
                        <p class="fs-7 text-muted mb-0"><?= esc($r[2]) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ================== PARA O ORGANIZADOR / CONVIDADO ================== -->
<section class="py-5">
    <div class="container py-4">
        <div class="row g-4" data-reveal-stagger>
            <div class="col-md-6 lp-reveal">
                <div class="p-4 rounded-4 h-100" style="background: var(--brand-soft);">
                    <h3 class="h6 fw-bold text-indigo-700 mb-3"><i class="bi bi-heart-fill me-2"></i>Para o organizador</h3>
                    <ul class="fs-7 mb-0 ps-3">
                        <li class="mb-1">Cotas em dinheiro, presentes reais por link e cotas livres.</li>
                        <li class="mb-1">Site e convite personalizados, com RSVP e mural de recados.</li>
                        <li class="mb-1">Carteira com extrato, taxas transparentes e saque via PIX.</li>
                        <li class="mb-0">Check-in dos convidados no dia da festa.</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 lp-reveal">
                <div class="p-4 rounded-4 h-100" style="background: #ECFDF5;">
                    <h3 class="h6 fw-bold text-success mb-3"><i class="bi bi-people-fill me-2"></i>Para o convidado</h3>
                    <ul class="fs-7 mb-0 ps-3">
                        <li class="mb-1">Escolhe a cota sem precisar criar conta.</li>
                        <li class="mb-1">Confirma presença e informa acompanhantes em segundos.</li>
                        <li class="mb-1">Pagamento rápido por PIX (QR Code e Copia e Cola).</li>
                        <li class="mb-0">Deixa um recado no mural e acompanha o status do pedido.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ======================= LISTAS DE EXEMPLO ==================== -->
<?php if (! empty($demos)): ?>
<section class="py-5 bg-light" id="exemplos">
    <div class="container py-4">
        <div class="lp-reveal text-center mb-5">
            <span class="lp-eyebrow mb-2">Inspire-se</span>
            <h2 class="fw-bold fs-2 mt-3 text-dark">Veja como fica a sua lista</h2>
            <p class="text-muted fs-6">
                Navegue pelas <?= count($demos) ?> <?= count($demos) === 1 ? 'lista de exemplo' : 'listas de exemplo' ?> e veja como é fácil e elegante presentear.
                As listas de clientes reais são privadas.
            </p>
        </div>

        <div class="lp-carousel lp-carousel--duo" data-carousel>
            <div class="lp-carousel-track" data-track data-reveal-stagger>
                <?php foreach ($demos as $demo): ?>
                    <div class="lp-reveal card border-0 shadow-sm rounded-4 overflow-hidden transition-hover">
                        <div class="position-relative">
                            <img src="<?= esc($demo['capa'], 'attr') ?>" class="card-img-top object-fit-cover"
                                 alt="Exemplo <?= esc($demo['tipo']) ?>" style="height: 220px;">
                            <span class="position-absolute top-0 end-0 m-3 badge bg-<?= esc($demo['badge'], 'attr') ?> text-<?= $demo['badge'] === 'warning' ? 'dark' : 'white' ?> px-3 py-2 rounded-pill fw-bold fs-8">
                                <?= esc($demo['icone']) ?> <?= esc($demo['tipo']) ?>
                            </span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <h3 class="card-title fw-bold fs-5 text-dark mb-1"><?= esc($demo['titulo']) ?></h3>
                            <p class="text-muted fs-7 mb-3"><?= esc($demo['data']) ?> • <?= esc($demo['local']) ?></p>
                            <p class="card-text fs-7 text-secondary flex-grow-1"><?= esc($demo['resumo']) ?></p>
                            <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                                <span class="fs-8 text-muted"><?= esc($demo['presentes']) ?> presentes</span>
                                <a href="<?= site_url('demo/' . $demo['slug']) ?>" class="btn btn-outline-brand btn-sm fw-semibold px-3">
                                    Ver exemplo <span class="lp-arrow">&rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="lp-carousel-nav">
                <button type="button" class="lp-nav-btn" data-prev aria-label="Exemplo anterior"><i class="bi bi-arrow-left"></i></button>
                <div class="lp-dots" data-dots></div>
                <button type="button" class="lp-nav-btn" data-next aria-label="Próximo exemplo"><i class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <p class="lp-reveal text-center mt-4 mb-0">
            <a class="btn btn-outline-secondary px-4" href="<?= site_url('criar-lista-de-presente') ?>">
                Quero uma lista assim para o meu evento
            </a>
        </p>
    </div>
</section>
<?php endif; ?>

<!-- ========================== DEPOIMENTOS ========================== -->
<section class="py-5" id="depoimentos">
    <div class="container py-4">
        <div class="lp-reveal text-center mb-5" style="max-width: 720px; margin: 0 auto;">
            <span class="lp-eyebrow mb-2">Depoimentos</span>
            <h2 class="fw-bold fs-2 mt-3 text-dark">Quem organiza, recomenda</h2>
            <p class="text-muted fs-6 mb-0">
                Veja o que dizem quem já criou a lista e recebeu presentes pela plataforma.
            </p>
        </div>

        <?php
        /*
         * ATENÇÃO: depoimentos de exemplo (placeholder).
         * Substitua por relatos reais e autorizados antes de divulgar o site.
         */
        $depoimentos = [
            ['nome' => 'Amanda Ribeiro', 'cidade' => 'São Paulo/SP', 'evento' => 'Chá de bebê', 'cor' => '#06B6D4',
             'texto' => 'Fiz a lista em uma noite! O site ficou pronto em minutos e eu só precisei mandar o link no grupo da família. Muito prático.'],
            ['nome' => 'Ricardo e Ana', 'cidade' => 'Campinas/SP', 'evento' => 'Casamento', 'cor' => '#D97706',
             'texto' => 'Personalizamos as cores e a capa com a nossa foto. Os convidados elogiaram bastante e receber os presentes por PIX foi bem simples.'],
            ['nome' => 'Patrícia Nunes', 'cidade' => 'Belo Horizonte/MG', 'evento' => 'Festa de 15 anos', 'cor' => '#EC4899',
             'texto' => 'O melhor é que foi grátis para criar. Organizei a lista inteira e ainda conseguia ver quem já havia presenteado.'],
            ['nome' => 'Juliana Prado', 'cidade' => 'Curitiba/PR', 'evento' => 'Formatura', 'cor' => '#8B5CF6',
             'texto' => 'Adorei poder confirmar presença pelo site e deixar recado no mural. Deu tudo certo e foi rápido de montar.'],
            ['nome' => 'Marcos Vinícius', 'cidade' => 'Recife/PE', 'evento' => 'Chá revelação', 'cor' => '#10B981',
             'texto' => 'Muito fácil de usar no celular. Criei a lista enquanto tomava um café e compartilhei na hora com os convidados.'],
            ['nome' => 'Fernanda Lopes', 'cidade' => 'Florianópolis/SC', 'evento' => 'Chá de casa nova', 'cor' => '#4F46E5',
             'texto' => 'A cota livre foi ótima: cada um contribuiu com o valor que podia. O valor caiu direto na minha carteira, sem complicação.'],
        ];
        ?>

        <div class="lp-carousel" data-carousel>
            <div class="lp-carousel-track" data-track data-reveal-stagger>
                <?php foreach ($depoimentos as $d):
                    $iniciais = '';
                    foreach (preg_split('/\s+/', $d['nome']) as $parte) {
                        if ($parte !== '') {
                            $iniciais .= mb_strtoupper(mb_substr($parte, 0, 1));
                        }
                    }
                    $iniciais = mb_substr($iniciais, 0, 2);
                    ?>
                    <article class="lp-reveal lp-testimonial p-4">
                        <div class="lp-stars mb-2" aria-label="5 de 5 estrelas">★★★★★</div>
                        <p class="fs-7 text-secondary flex-grow-1 mb-4">“<?= esc($d['texto']) ?>”</p>
                        <div class="d-flex align-items-center gap-3 pt-3 border-top mt-auto">
                            <span class="lp-avatar" style="background: <?= esc($d['cor'], 'attr') ?>;"><?= esc($iniciais) ?></span>
                            <div>
                                <p class="fw-semibold mb-0 fs-7"><?= esc($d['nome']) ?></p>
                                <p class="text-muted fs-8 mb-0"><?= esc($d['cidade']) ?> · <?= esc($d['evento']) ?></p>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="lp-carousel-nav">
                <button type="button" class="lp-nav-btn" data-prev aria-label="Depoimento anterior"><i class="bi bi-arrow-left"></i></button>
                <div class="lp-dots" data-dots></div>
                <button type="button" class="lp-nav-btn" data-next aria-label="Próximo depoimento"><i class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <p class="lp-reveal text-center mt-4 mb-0">
            <a class="btn btn-brand px-4" href="<?= site_url('criar-lista-de-presente') ?>">
                <i class="bi bi-rocket-takeoff me-2"></i>Criar minha lista grátis em 1 minuto
            </a>
        </p>
    </div>
</section>

<!-- ============================ DÚVIDAS ============================ -->
<section class="py-5" id="duvidas">
    <div class="container py-4" style="max-width: 860px;">
        <div class="lp-reveal text-center mb-5">
            <span class="lp-eyebrow mb-2">Perguntas frequentes</span>
            <h2 class="fw-bold fs-2 mt-3 text-dark">Ainda com dúvidas?</h2>
        </div>

        <div class="accordion" id="faq" data-reveal-stagger>
            <?php
            $faq = [
                ['Criar a lista é realmente grátis?', 'Sim. Criar, personalizar e compartilhar o site, o convite e a lista de presentes é 100% grátis e sem mensalidade. Uma taxa é aplicada somente quando você recebe um presente — os detalhes aparecem na sua carteira, com valores transparentes.'],
                ['Preciso instalar algum programa?', 'Não. Tudo funciona pelo navegador, no computador ou no celular. Você cria a lista, personaliza o site e acompanha os convidados online.'],
                ['Meus convidados precisam criar conta?', 'Não. Apenas o organizador tem conta. O convidado acessa o link do evento, escolhe o presente, confirma presença e paga por PIX — sem cadastro.'],
                ['Como recebo o dinheiro dos presentes?', 'Os presentes em dinheiro entram na sua carteira assim que o pagamento é confirmado e você solicita o saque por PIX quando quiser, direto pelo painel.'],
                ['Posso personalizar o site do meu evento?', 'Sim. Você define a imagem de capa, o título, a data, o local, a mensagem do convite, o tema, as cores e ainda adiciona fotos na galeria.'],
                ['Serve para qualquer tipo de evento?', 'Sim! Casamento, aniversário, chá de bebê, chá revelação, formatura, festa de 15 anos, chá de casa nova, festa do pet e muitas outras ocasiões.'],
            ];
            foreach ($faq as $i => $item): $n = $i + 1; ?>
                <div class="lp-reveal accordion-item border-0 mb-2 rounded-3 overflow-hidden shadow-sm">
                    <h3 class="accordion-header" id="faq-h-<?= $n ?>">
                        <button class="accordion-button <?= $i === 0 ? '' : 'collapsed' ?> fw-semibold" type="button"
                                data-bs-toggle="collapse" data-bs-target="#faq-c-<?= $n ?>"
                                aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="faq-c-<?= $n ?>">
                            <?= esc($item[0]) ?>
                        </button>
                    </h3>
                    <div id="faq-c-<?= $n ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                         aria-labelledby="faq-h-<?= $n ?>" data-bs-parent="#faq">
                        <div class="accordion-body fs-7 text-secondary"><?= esc($item[1]) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== BUSCA DO CONVIDADO ===================== -->
<section class="py-5 bg-light" id="buscar">
    <div class="container" style="max-width: 820px;">
        <div class="lp-reveal card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5 text-center">
                <h2 class="h4 fw-bold mb-2">Foi convidado?</h2>
                <p class="text-muted mb-4">
                    Cole o link que o organizador enviou, ou digite o código do seu pedido,
                    para acessar a lista.
                </p>

                <?= view('templates/partials/flash') ?>

                <form method="post" action="<?= site_url('buscar') ?>" class="row g-2 justify-content-center">
                    <?= csrf_field() ?>
                    <div class="col-md-8">
                        <input type="text" class="form-control form-control-lg" name="termo"
                               placeholder="ex.: minhalistavip.com.br/casamento-ana-e-joao ou MLV260929ABCD12"
                               value="<?= esc(old('termo')) ?>" required>
                    </div>
                    <div class="col-md-4 d-grid">
                        <button class="btn btn-brand btn-lg"><i class="bi bi-search me-2"></i>Buscar lista</button>
                    </div>
                </form>
                <p class="fs-8 text-muted mt-3 mb-0">
                    Dica: o link costuma vir assim <em>minhalistavip.com.br/<strong>nome-do-evento</strong></em>.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ========================== CTA FINAL ========================= -->
<section class="py-5">
    <div class="container">
        <div class="lp-reveal p-4 p-md-5 rounded-4 text-white text-center" style="background: linear-gradient(135deg, #4F46E5, #4338CA);">
            <h2 class="fw-bold mb-2">Crie a sua lista agora, é grátis</h2>
            <p class="mb-4 opacity-75">Site, convite e lista de presentes no ar em poucos minutos para receber presentes via PIX.</p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a class="btn btn-light btn-lg px-4 fw-semibold" href="<?= site_url('criar-lista-de-presente') ?>">Criar minha lista grátis</a>
                <a class="btn btn-outline-light btn-lg px-4" href="<?= site_url('login') ?>">Já tenho conta</a>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var reduceMotion = window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function initCarousel(root) {
        var track = root.querySelector('[data-track]');
        if (!track) { return; }

        var slides = Array.prototype.slice.call(track.children);
        if (!slides.length) { return; }

        var prev = root.querySelector('[data-prev]');
        var next = root.querySelector('[data-next]');
        var dotsWrap = root.querySelector('[data-dots]');
        var dots = [];
        var dotCount = 0;

        function gap() {
            var styles = window.getComputedStyle(track);
            var value = parseFloat(styles.columnGap || styles.gap);
            return isNaN(value) ? 0 : value;
        }
        function step() {
            return slides[0].getBoundingClientRect().width + gap();
        }
        function maxScroll() {
            return Math.max(0, track.scrollWidth - track.clientWidth);
        }
        function canScroll() {
            return maxScroll() > 4;
        }
        function pages() {
            if (!canScroll() || step() <= 0) { return 1; }
            return Math.ceil(maxScroll() / step()) + 1;
        }
        function goTo(position) {
            var target = Math.min(position * step(), maxScroll());
            track.scrollTo({ left: target, behavior: reduceMotion ? 'auto' : 'smooth' });
        }
        function buildDots(total) {
            if (!dotsWrap) { return; }
            dotsWrap.innerHTML = '';
            dots = [];
            for (var i = 0; i < total; i++) {
                (function (index) {
                    var dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = 'lp-dot';
                    dot.setAttribute('aria-label', 'Ir para a posição ' + (index + 1));
                    dot.addEventListener('click', function () { goTo(index); });
                    dotsWrap.appendChild(dot);
                    dots.push(dot);
                })(i);
            }
            dotCount = total;
        }
        function render() {
            var scrollable = canScroll();
            root.classList.toggle('lp-carousel--static', !scrollable);
            if (prev) { prev.disabled = !scrollable || track.scrollLeft <= 2; }
            if (next) { next.disabled = !scrollable || track.scrollLeft >= maxScroll() - 2; }

            var total = pages();
            if (total !== dotCount) { buildDots(total); }

            var currentStep = step();
            var active = currentStep > 0 ? Math.round(track.scrollLeft / currentStep) : 0;
            active = Math.max(0, Math.min(dots.length - 1, active));
            dots.forEach(function (dot, i) {
                dot.classList.toggle('is-active', i === active);
            });
        }

        if (prev) {
            prev.addEventListener('click', function () {
                goTo(Math.round(track.scrollLeft / step()) - 1);
            });
        }
        if (next) {
            next.addEventListener('click', function () {
                goTo(Math.round(track.scrollLeft / step()) + 1);
            });
        }

        var frame = null;
        track.addEventListener('scroll', function () {
            if (frame) { window.cancelAnimationFrame(frame); }
            frame = window.requestAnimationFrame(render);
        }, { passive: true });
        window.addEventListener('resize', render);

        render();
    }

    function initReveal() {
        var items = document.querySelectorAll('.lp-reveal');
        if (!items.length) { return; }

        var revealAll = function () {
            Array.prototype.forEach.call(items, function (el) { el.classList.add('is-visible'); });
        };

        if (reduceMotion || !('IntersectionObserver' in window)) {
            revealAll();
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) { return; }

                var target = entry.target;
                if (target.hasAttribute('data-reveal-stagger')) {
                    Array.prototype.forEach.call(target.children, function (child, index) {
                        window.setTimeout(function () { child.classList.add('is-visible'); }, index * 70);
                    });
                } else {
                    target.classList.add('is-visible');
                }
                observer.unobserve(target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

        Array.prototype.forEach.call(document.querySelectorAll('[data-reveal-stagger]'), function (group) {
            if (group.querySelector('.lp-reveal')) { observer.observe(group); }
        });
        Array.prototype.forEach.call(items, function (el) {
            if (!el.closest('[data-reveal-stagger]')) { observer.observe(el); }
        });
    }

    // A revelação roda primeiro: mesmo que o carrossel falhe, nada fica escondido.
    initReveal();

    try {
        var carousels = document.querySelectorAll('[data-carousel]');
        Array.prototype.forEach.call(carousels, initCarousel);
    } catch (error) {
        // O carrossel é progressivo: se falhar, o conteúdo segue visível e rolável.
    }
})();
</script>

<?= $this->endSection() ?>
