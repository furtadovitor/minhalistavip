<?= $this->extend('templates/layouts/public') ?>

<?php
$valor = static fn (string $campo, $padrao = '') => old($campo, $valores[$campo] ?? $padrao);

$temaSel  = (string) $valor('tema', 'classico');
$tipoSel  = (string) $valor('tipo_evento', $tipo['chave'] ?? '');
$dataSel  = (string) $valor('data_evento');
$horaSel  = (string) $valor('horario');
$localSel = (string) $valor('local_nome');

$acao = $tipo !== null
    ? site_url('criar-lista-de-presente/' . $tipo['slug'])
    : site_url('criar-lista-de-presente');

// 6 tipos principais primeiro, o restante depois.
$chavesPrincipais = ['casamento', 'cha_bebe', 'aniversario', 'cha_panela', 'quinze_anos', 'formatura'];
$tiposPrincipais  = array_intersect_key($tipos, array_flip($chavesPrincipais));
$tiposOutros      = array_diff_key($tipos, $tiposPrincipais);

$slides = [
    [
        'img'    => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1000&q=80',
        'icone'  => '📸',
        'titulo' => 'Uma galeria só de vocês',
        'texto'  => 'Compartilhe fotos suas e do seu evento com familiares e amigos.',
    ],
    [
        'img'    => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=1000&q=80',
        'icone'  => '🔗',
        'titulo' => 'Um link, sem bagunça',
        'texto'  => 'Basta enviar o link. Sem precisar selecionar 30 fotos de uma vez no WhatsApp.',
    ],
    [
        'img'    => 'https://images.unsplash.com/photo-1519689680058-324335c77eba?auto=format&fit=crop&w=1000&q=80',
        'icone'  => '💳',
        'titulo' => 'Pix e Cartão de Crédito',
        'texto'  => 'Aceite Pix e Cartão de Crédito como presente!',
    ],
    [
        'img'    => 'https://images.unsplash.com/photo-1523438885200-e635ba2c371e?auto=format&fit=crop&w=1000&q=80',
        'icone'  => '🎁',
        'titulo' => 'Presente sem erro',
        'texto'  => 'Quer escolher exatamente o que vai receber ou está preocupado que seus convidados esqueçam de comprar o presente? Aceite um Pix.',
    ],
];
?>

<?= $this->section('conteudo') ?>
<style>
    @import url("<?= \App\Services\ModeloService::googleFontsUrl() ?>");

    .criar-split { background: #f6f7fb; }

    /* ---------- Coluna visual ---------- */
    .criar-visual { position: relative; overflow: hidden; background: #111827; }
    @media (min-width: 992px) {
        .criar-visual { position: sticky; top: 49px; height: calc(100vh - 49px); }
    }
    @media (max-width: 991.98px) {
        .criar-visual { height: 250px; }
    }

    .criar-slide { position: absolute; inset: 0; opacity: 0; transition: opacity .7s ease; }
    .criar-slide.is-active { opacity: 1; }
    .criar-slide-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .criar-slide::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(180deg, rgba(17,24,39,.15) 0%, rgba(17,24,39,.55) 55%, rgba(17,24,39,.9) 100%);
    }
    .criar-slide-text {
        position: absolute; left: 0; right: 0; bottom: 0; z-index: 2;
        padding: 2.25rem 2.25rem 3.25rem; color: #fff;
    }
    @media (max-width: 991.98px) { .criar-slide-text { padding: 1.1rem 1.25rem 1.6rem; } }
    .criar-slide-icone {
        display: inline-flex; align-items: center; justify-content: center;
        width: 46px; height: 46px; border-radius: 50%; margin-bottom: .75rem;
        background: rgba(255,255,255,.16); backdrop-filter: blur(4px); font-size: 1.35rem;
    }
    .criar-slide-text h2 { font-size: clamp(1.15rem, 2.4vw, 1.7rem); font-weight: 700; margin-bottom: .4rem; }
    .criar-slide-text p { font-size: clamp(.85rem, 1.5vw, 1rem); opacity: .92; margin: 0; max-width: 42ch; }

    .criar-brand {
        position: absolute; top: 1.25rem; left: 1.5rem; z-index: 3;
        color: #fff; font-weight: 700; letter-spacing: .02em; font-size: .9rem;
        display: inline-flex; align-items: center; gap: .5rem;
        background: rgba(17,24,39,.35); padding: .35rem .8rem; border-radius: 999px; backdrop-filter: blur(4px);
    }

    .criar-dots { position: absolute; right: 1.5rem; bottom: 1.25rem; z-index: 3; display: flex; gap: .4rem; }
    .criar-dot {
        width: 9px; height: 9px; border-radius: 50%; border: 0; padding: 0;
        background: rgba(255,255,255,.45); transition: all .25s ease;
    }
    .criar-dot.is-active { background: #fff; width: 24px; border-radius: 999px; }

    /* ---------- Coluna do formulário ---------- */
    .criar-form { max-width: 580px; margin: 0 auto; padding: 2.5rem 1.5rem; }
    @media (max-width: 991.98px) { .criar-form { padding: 1.75rem 1.15rem; } }

    .criar-step {
        display: inline-flex; align-items: center; gap: .4rem;
        font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em;
        color: var(--brand); margin-bottom: .5rem;
    }

    .criar-aviso {
        display: flex; align-items: flex-start; gap: .6rem;
        background: color-mix(in srgb, var(--brand) 8%, #fff);
        border: 1px solid color-mix(in srgb, var(--brand) 22%, #fff);
        border-radius: .85rem; padding: .8rem .95rem;
        font-size: .82rem; color: #374151; margin-bottom: 1.1rem;
    }
    .criar-aviso i { color: var(--brand); font-size: 1.1rem; line-height: 1.2; }

    /* Seletor de modelos */
    .modelo-scroll { position: relative; }
    .modelo-grid {
        display: grid; grid-template-columns: repeat(2, 1fr); gap: .6rem;
        max-height: 300px; overflow-y: auto; padding: .3rem .35rem .3rem .15rem;
        scrollbar-width: thin;
    }
    .modelo-card { position: relative; margin: 0; cursor: pointer; display: block; }
    .modelo-card input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .modelo-box {
        display: block; border: 2px solid #E5E7EB; border-radius: .9rem; overflow: hidden;
        background: #fff; transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }
    .modelo-card:hover .modelo-box { border-color: rgba(0,0,0,.22); transform: translateY(-1px); }
    .modelo-card input:focus-visible + .modelo-box { outline: 2px solid var(--brand); outline-offset: 2px; }
    .modelo-card input:checked + .modelo-box { border-color: var(--brand); box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand) 16%, transparent); }
    .modelo-preview { height: 62px; display: flex; align-items: center; justify-content: center; color: #fff; }
    .modelo-aa { font-size: 1.75rem; line-height: 1; text-shadow: 0 1px 4px rgba(0,0,0,.28); }
    .modelo-nome { display: block; padding: .5rem .65rem; font-size: .8rem; font-weight: 600; color: #374151; }
    .modelo-check {
        display: none; position: absolute; top: .45rem; right: .45rem; z-index: 2;
        width: 22px; height: 22px; border-radius: 50%; background: var(--brand); color: #fff;
        align-items: center; justify-content: center; font-size: .72rem;
        box-shadow: 0 2px 8px rgba(0,0,0,.25);
    }
    .modelo-card input:checked + .modelo-box .modelo-check { display: flex; }
    .modelo-hint { font-size: .72rem; color: #9CA3AF; }
    .modelo-fade {
        position: absolute; left: 0; right: 0; bottom: 0; height: 26px; pointer-events: none;
        background: linear-gradient(transparent, #f6f7fb);
    }
</style>

<section class="criar-split">
    <div class="row g-0">
        <!-- ============ Visual (esquerda) ============ -->
        <div class="col-lg-6">
            <div class="criar-visual" id="criarCarousel">
                <span class="criar-brand"><i class="bi bi-gift-fill"></i> Minha Lista VIP</span>

                <?php foreach ($slides as $i => $slide): ?>
                    <div class="criar-slide <?= $i === 0 ? 'is-active' : '' ?>">
                        <img class="criar-slide-img" src="<?= esc($slide['img'], 'attr') ?>" alt=""
                             loading="<?= $i === 0 ? 'eager' : 'lazy' ?>"
                             onerror="this.onerror=null;this.src='https://picsum.photos/seed/mlv-slide-<?= $i ?>/1000/1300';">
                        <div class="criar-slide-text">
                            <span class="criar-slide-icone"><?= $slide['icone'] ?></span>
                            <h2><?= esc($slide['titulo']) ?></h2>
                            <p><?= esc($slide['texto']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="criar-dots" role="tablist" aria-label="Destaques">
                    <?php foreach ($slides as $i => $slide): ?>
                        <button type="button" class="criar-dot <?= $i === 0 ? 'is-active' : '' ?>"
                                data-slide="<?= $i ?>" aria-label="Destaque <?= $i + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ============ Formulário (direita) ============ -->
        <div class="col-lg-6">
            <div class="criar-form">
                <?= view('templates/partials/flash') ?>

                <span class="criar-step"><i class="bi bi-1-circle-fill"></i> Passo 1 de 2 · Dados da lista</span>
                <h1 class="h3 fw-bold mb-1">Crie sua lista de presentes</h1>
                <p class="text-muted mb-4">
                    Preencha só os dados principais. Fique tranquilo(a): você
                    <strong>pode alterar tudo depois</strong>.
                </p>

                <form method="post" action="<?= $acao ?>" id="form-criar-lista">
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="titulo">Nome da lista *</label>
                        <input type="text" class="form-control form-control-lg" id="titulo" name="titulo"
                               value="<?= esc($valor('titulo')) ?>" required autofocus
                               placeholder="Ex.: <?= esc($tipo['rotulo'] ?? 'Casamento') ?> da Ana e do João">
                        <div class="form-text">É o título que aparece para os seus convidados.</div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <label class="form-label fw-semibold mb-0">Escolha um modelo *</label>
                            <span class="modelo-hint">Role para ver mais</span>
                        </div>

                        <div class="modelo-scroll">
                            <div class="modelo-grid" id="modeloGrid">
                                <?php foreach ($modelos as $chave => $modelo): ?>
                                    <?php $fonte = \App\Services\ModeloService::tipografia($chave); ?>
                                    <label class="modelo-card">
                                        <input type="radio" name="tema" value="<?= esc($chave, 'attr') ?>"
                                               <?= $temaSel === $chave ? 'checked' : '' ?>>
                                        <span class="modelo-box">
                                            <span class="modelo-preview"
                                                  style="background: linear-gradient(135deg, <?= esc($modelo['cor_primaria'], 'attr') ?>, <?= esc($modelo['cor_secundaria'], 'attr') ?>);">
                                                <span class="modelo-aa" style="font-family: '<?= esc($fonte['titulo'], 'attr') ?>', system-ui, sans-serif;">Aa</span>
                                            </span>
                                            <span class="modelo-nome"><?= esc($modelo['rotulo']) ?></span>
                                            <span class="modelo-check"><i class="bi bi-check-lg"></i></span>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <span class="modelo-fade"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="tipo_evento">Tipo de evento</label>
                        <select class="form-select" id="tipo_evento" name="tipo_evento">
                            <option value="">Selecione…</option>
                            <optgroup label="Principais">
                                <?php foreach ($tiposPrincipais as $chave => $dadosTipo): ?>
                                    <option value="<?= esc($chave, 'attr') ?>" <?= $tipoSel === $chave ? 'selected' : '' ?>>
                                        <?= esc($dadosTipo['icone'] . ' ' . $dadosTipo['rotulo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Outros">
                                <?php foreach ($tiposOutros as $chave => $dadosTipo): ?>
                                    <option value="<?= esc($chave, 'attr') ?>" <?= $tipoSel === $chave ? 'selected' : '' ?>>
                                        <?= esc($dadosTipo['icone'] . ' ' . $dadosTipo['rotulo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="data_evento">Data do evento</label>
                            <input type="date" class="form-control" id="data_evento" name="data_evento"
                                   value="<?= esc($dataSel) ?>" min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="horario">Hora do evento</label>
                            <input type="time" class="form-control" id="horario" name="horario"
                                   value="<?= esc($horaSel) ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="local_nome">Local do evento <span class="text-muted fw-normal">(opcional)</span></label>
                        <input type="text" class="form-control" id="local_nome" name="local_nome"
                               value="<?= esc($localSel) ?>" placeholder="Ex.: Ilhabela, SP">
                    </div>

                    <div class="criar-aviso">
                        <i class="bi bi-pencil-square"></i>
                        <span>
                            Você pode <strong>alterar tudo depois</strong> — nome, modelo, tipo de evento,
                            data, hora, local e os presentes.
                        </span>
                    </div>

                    <button type="submit" class="btn btn-brand btn-lg w-100">
                        Avançar <i class="bi bi-arrow-right ms-1"></i>
                    </button>

                    <p class="text-muted fs-8 text-center mt-3 mb-0">
                        <?php if ($logado): ?>
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            Sua lista será criada como rascunho — você publica quando quiser.
                        <?php else: ?>
                            <i class="bi bi-2-circle me-1"></i>Próximo passo: entrar ou criar sua conta grátis.
                            Já tem conta?
                            <a href="<?= site_url('login') ?>" class="text-brand fw-semibold text-decoration-none">Entrar</a>
                        <?php endif; ?>
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    // ---------------- Carrossel de destaques ----------------
    var root = document.getElementById('criarCarousel');
    if (root) {
        var slides = Array.prototype.slice.call(root.querySelectorAll('.criar-slide'));
        var dots   = Array.prototype.slice.call(root.querySelectorAll('.criar-dot'));
        var atual  = 0;
        var timer  = null;
        var reduzir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function ir(n) {
            atual = (n + slides.length) % slides.length;
            slides.forEach(function (s, i) { s.classList.toggle('is-active', i === atual); });
            dots.forEach(function (d, i) { d.classList.toggle('is-active', i === atual); });
        }
        function parar() { if (timer) { clearInterval(timer); timer = null; } }
        function iniciar() {
            if (reduzir || slides.length < 2) { return; }
            parar();
            timer = setInterval(function () { ir(atual + 1); }, 6000);
        }

        dots.forEach(function (d, i) {
            d.addEventListener('click', function () { ir(i); iniciar(); });
        });
        root.addEventListener('mouseenter', parar);
        root.addEventListener('mouseleave', iniciar);

        ir(0);
        iniciar();
    }

    // -------- Deixa o modelo selecionado visível na lista --------
    var grid = document.getElementById('modeloGrid');
    if (grid) {
        var marcado = grid.querySelector('input:checked');
        if (marcado) {
            var card = marcado.closest('.modelo-card');
            if (card && card.offsetTop > grid.clientHeight - card.offsetHeight) {
                grid.scrollTop = card.offsetTop - 8;
            }
        }
    }

    // -------- Alinha o painel visual à altura real do navbar --------
    var visual = document.getElementById('criarCarousel');
    var navbar = document.querySelector('.navbar');
    function ajustarVisual() {
        if (!visual || !navbar) { return; }
        if (window.innerWidth < 992) {
            visual.style.top = '';
            visual.style.height = '';
            return;
        }
        var altura = Math.round(navbar.getBoundingClientRect().height);
        visual.style.top = altura + 'px';
        visual.style.height = 'calc(100vh - ' + altura + 'px)';
    }
    ajustarVisual();
    window.addEventListener('resize', ajustarVisual);
})();
</script>
<?= $this->endSection() ?>
