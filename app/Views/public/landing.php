<?= $this->extend('templates/layouts/public') ?>

<?= $this->section('conteudo') ?>
<style>
    .land-hero { background: linear-gradient(180deg, #fff 0%, var(--bg) 100%); }
    .land-icone {
        width: 54px; height: 54px; border-radius: .95rem; flex: none;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.4rem; background: var(--brand-soft); color: var(--brand-dark);
    }
    .land-card {
        border: 1px solid rgba(17, 24, 39, .06); border-radius: 1rem;
        background: #fff; height: 100%; transition: transform .18s ease, box-shadow .18s ease;
    }
    .land-card:hover { transform: translateY(-4px); box-shadow: 0 1rem 2rem rgba(17, 24, 39, .10); }
    .land-alt { background: #F8F5FF; }
    .land-passo-num {
        width: 34px; height: 34px; border-radius: 50%; flex: none; font-weight: 800;
        display: inline-flex; align-items: center; justify-content: center;
        background: var(--brand); color: #fff;
    }
    .land-accordion .accordion-button { font-weight: 600; }
    .land-accordion .accordion-button:not(.collapsed) { color: var(--brand-dark); background: var(--brand-soft); }
    .land-chip {
        display: inline-flex; align-items: center; gap: .35rem;
        border: 1px solid rgba(17, 24, 39, .1); border-radius: 999px;
        padding: .4rem .85rem; font-size: .84rem; font-weight: 600; color: var(--ink);
        text-decoration: none; background: #fff;
    }
    .land-chip:hover { border-color: var(--brand); color: var(--brand-dark); }
</style>

<!-- ============================== HERO ============================== -->
<section class="land-hero py-5">
    <div class="container py-4" style="max-width: 900px;">
        <span class="badge-soft d-inline-flex align-items-center gap-2 mb-3 px-3 py-2 rounded-pill">
            <span><?= esc($pagina['icone']) ?></span><?= esc($pagina['rotulo']) ?>
        </span>
        <h1 class="display-5 fw-bold mb-3"><?= esc($pagina['h1']) ?></h1>
        <p class="lead text-muted mb-4"><?= esc($pagina['intro']) ?></p>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-brand btn-lg px-4" href="<?= site_url('criar-lista-de-presente/' . $pagina['slug']) ?>">
                <i class="bi bi-rocket-takeoff me-2"></i>Criar minha lista grátis
            </a>
            <a class="btn btn-outline-brand btn-lg px-4" href="<?= site_url('/') . '#exemplos' ?>">Ver um exemplo</a>
        </div>
        <div class="d-flex flex-wrap gap-3 mt-4 fs-7 text-muted">
            <span><i class="bi bi-check-circle-fill text-brand me-1"></i>Sem mensalidade</span>
            <span><i class="bi bi-check-circle-fill text-brand me-1"></i>Convidado não precisa de conta</span>
            <span><i class="bi bi-check-circle-fill text-brand me-1"></i>Receba por PIX</span>
        </div>
    </div>
</section>

<!-- ============================ BENEFÍCIOS ============================ -->
<section class="py-5">
    <div class="container">
        <h2 class="h3 fw-bold text-center mb-4">Por que usar a Minha Lista VIP</h2>
        <div class="row g-4">
            <?php foreach ($pagina['beneficios'] as $b): ?>
                <div class="col-md-6">
                    <div class="land-card p-4 d-flex gap-3">
                        <span class="land-icone"><i class="bi <?= esc($b['icone'], 'attr') ?>"></i></span>
                        <div>
                            <h3 class="h6 fw-bold mb-1"><?= esc($b['titulo']) ?></h3>
                            <p class="text-muted mb-0 fs-7"><?= esc($b['texto']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- =========================== COMO FUNCIONA =========================== -->
<section class="py-5 land-alt">
    <div class="container" style="max-width: 820px;">
        <h2 class="h3 fw-bold text-center mb-4">Como funciona em 3 passos</h2>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($pagina['passos'] as $i => $passo): ?>
                <div class="d-flex align-items-start gap-3 bg-white rounded-4 shadow-sm p-3 p-md-4">
                    <span class="land-passo-num"><?= $i + 1 ?></span>
                    <p class="mb-0 pt-1"><?= esc($passo) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================== FAQ ============================== -->
<section class="py-5">
    <div class="container" style="max-width: 820px;">
        <h2 class="h3 fw-bold text-center mb-4">Perguntas frequentes</h2>
        <div class="accordion land-accordion" id="faqLanding">
            <?php foreach ($pagina['faq'] as $i => $item): ?>
                <div class="accordion-item border-0 shadow-sm rounded-4 mb-2 overflow-hidden">
                    <h3 class="accordion-header">
                        <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button"
                                data-bs-toggle="collapse" data-bs-target="#faqL<?= $i ?>"
                                aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
                            <?= esc($item['p']) ?>
                        </button>
                    </h3>
                    <div id="faqL<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                         data-bs-parent="#faqLanding">
                        <div class="accordion-body text-muted"><?= esc($item['r']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ========================== OUTROS TIPOS ========================== -->
<?php if (! empty($outras)): ?>
<section class="py-5 land-alt">
    <div class="container">
        <h2 class="h5 fw-bold mb-3">Listas de presentes para outros eventos</h2>
        <div class="d-flex flex-wrap gap-2">
            <a class="land-chip" href="<?= site_url('lista-de-presentes') ?>"><i class="bi bi-grid"></i>Todos os tipos</a>
            <?php foreach ($outras as $o): ?>
                <a class="land-chip" href="<?= site_url('lista-de-presentes/' . $o['slug']) ?>">
                    <span><?= esc($o['icone']) ?></span><?= esc($o['rotulo']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================ CTA FINAL ============================ -->
<section class="py-5">
    <div class="container">
        <div class="p-4 p-md-5 rounded-4 text-white text-center" style="background: linear-gradient(135deg, #722ED4, #5B21B6);">
            <h2 class="fw-bold mb-2">Crie sua lista de <?= esc($pagina['rotulo']) ?> agora, é grátis</h2>
            <p class="mb-4 opacity-75">Pronta em poucos minutos, com convite, RSVP e presente via PIX.</p>
            <a class="btn btn-light btn-lg px-4 fw-semibold" href="<?= site_url('criar-lista-de-presente/' . $pagina['slug']) ?>">Criar minha lista grátis</a>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
