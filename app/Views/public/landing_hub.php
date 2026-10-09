<?= $this->extend('templates/layouts/public') ?>

<?= $this->section('conteudo') ?>
<style>
    .hub-hero { background: linear-gradient(180deg, #fff 0%, var(--bg) 100%); }
    .hub-card {
        display: block; height: 100%; text-decoration: none; color: var(--ink);
        border: 1px solid rgba(17, 24, 39, .06); border-radius: 1rem; background: #fff;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .hub-card:hover { transform: translateY(-4px); box-shadow: 0 1rem 2rem rgba(17, 24, 39, .10); border-color: var(--brand); }
    .hub-icone {
        width: 54px; height: 54px; border-radius: .95rem; flex: none;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.4rem; background: var(--brand-soft); color: var(--brand-dark);
    }
</style>

<section class="hub-hero py-5">
    <div class="container py-4 text-center" style="max-width: 780px;">
        <span class="badge-soft d-inline-block mb-3 px-3 py-2 rounded-pill">Listas de presentes</span>
        <h1 class="display-5 fw-bold mb-3">Lista de presentes online para o seu evento</h1>
        <p class="lead text-muted mb-4">
            Escolha o tipo de evento e crie uma lista grátis com cotas em PIX, convite digital,
            confirmação de presença e mural de recados.
        </p>
        <a class="btn btn-brand btn-lg px-4" href="<?= site_url('criar-lista-de-presente') ?>">
            <i class="bi bi-rocket-takeoff me-2"></i>Criar minha lista grátis
        </a>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-3">
            <?php foreach ($paginas as $p): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a class="hub-card p-3 p-md-4" href="<?= site_url('lista-de-presentes/' . $p['slug']) ?>">
                        <span class="hub-icone mb-3"><?= esc($p['icone']) ?></span>
                        <h2 class="h6 fw-bold mb-1"><?= esc($p['rotulo']) ?></h2>
                        <span class="fs-8 text-muted">Lista de presentes online e grátis</span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="p-4 p-md-5 rounded-4 text-white text-center" style="background: linear-gradient(135deg, #722ED4, #5B21B6);">
            <h2 class="fw-bold mb-2">Crie a sua lista agora, é grátis</h2>
            <p class="mb-4 opacity-75">Site, convite e lista de presentes no ar em poucos minutos para receber presentes via PIX.</p>
            <a class="btn btn-light btn-lg px-4 fw-semibold" href="<?= site_url('criar-lista-de-presente') ?>">Criar minha lista grátis</a>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
