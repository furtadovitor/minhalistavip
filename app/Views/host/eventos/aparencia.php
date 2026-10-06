<?= $this->extend('templates/layouts/app') ?>

<?php
$valor = static fn (string $campo, $padrao = '') => old($campo, $evento->{$campo} ?? $padrao);

$presets = [];
foreach (\App\Services\ModeloService::todos() as $chave => $modelo) {
    $presets[] = [$modelo['rotulo'], $modelo['cor_primaria'], $modelo['cor_secundaria'], $chave];
}
?>

<?= $this->section('conteudo') ?>
<form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/aparencia') ?>" enctype="multipart/form-data" id="form-aparencia">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Tema e cores</h2>

                    <div class="mb-3">
                        <label class="form-label" for="tema">Tipografia / tema</label>
                        <select class="form-select" id="tema" name="tema">
                            <?php foreach ($temas as $chave => $rotulo): ?>
                                <option value="<?= esc($chave) ?>" <?= $valor('tema', 'classico') === $chave ? 'selected' : '' ?>>
                                    <?= esc($rotulo) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Paletas prontas</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($presets as $preset): ?>
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary preset-cor d-inline-flex align-items-center gap-1"
                                        data-primaria="<?= esc($preset[1], 'attr') ?>"
                                        data-secundaria="<?= esc($preset[2], 'attr') ?>"
                                        data-tema="<?= esc($preset[3], 'attr') ?>">
                                    <span class="d-inline-block rounded-circle"
                                          style="width:14px;height:14px;background: linear-gradient(135deg, <?= esc($preset[1], 'attr') ?>, <?= esc($preset[2], 'attr') ?>);"></span>
                                    <?= esc($preset[0]) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="cor_primaria">Cor primária</label>
                            <input type="color" class="form-control form-control-color w-100"
                                   id="cor_primaria" name="cor_primaria" value="<?= esc($valor('cor_primaria', '#8e44ad')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="cor_secundaria">Cor secundária</label>
                            <input type="color" class="form-control form-control-color w-100"
                                   id="cor_secundaria" name="cor_secundaria" value="<?= esc($valor('cor_secundaria', '#f39c12')) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Imagem de capa</h2>

                    <input type="file" class="form-control" id="imagem_capa" name="imagem_capa"
                           accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">JPG, PNG ou WEBP até 2 MB. Recomendado: imagem horizontal.</div>

                    <?php if (! empty($evento->imagem_capa)): ?>
                        <img src="<?= base_url($evento->imagem_capa) ?>" alt="Capa atual" class="img-fluid rounded mt-3">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="remover_capa" name="remover_capa" value="1">
                            <label class="form-check-label small" for="remover_capa">Remover capa atual</label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 mb-3" id="preview-card">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Pré-visualização</h2>

                    <div class="rounded-3 p-4 text-white text-center mb-3" id="pv-hero">
                        <p class="text-uppercase fs-8 mb-1 opacity-75" id="pv-tipo">Evento</p>
                        <p class="fw-bold mb-1 fs-5" id="pv-titulo">Título do evento</p>
                        <p class="fs-8 mb-0 opacity-75" id="pv-local">Data · Local</p>
                    </div>

                    <div class="border rounded-3 p-3">
                        <p class="fw-semibold mb-1 fs-7">Presente de exemplo</p>
                        <p class="text-muted fs-8 mb-2">Descrição do presente</p>
                        <div class="progress mb-2" style="height:6px;">
                            <div class="progress-bar" style="width:40%; background: var(--pv-primaria);"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold" id="pv-preco" style="color: var(--pv-primaria);">R$ 100,00</span>
                            <span class="btn btn-sm text-white" style="background: var(--pv-primaria);">Presentear</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Salvar aparência</button>
                <a class="btn btn-outline-secondary" href="<?= site_url('painel/eventos/' . $evento->id) ?>">Voltar</a>
            </div>
        </div>
    </div>
</form>

<script>
(function () {
    const pvCard = document.getElementById('preview-card');
    if (!pvCard) { return; }

    const fontes = {
        classico: "'Playfair Display', Georgia, serif",
        casamento: "'Cormorant Garamond', Georgia, serif",
        cha_bebe: "'Poppins', sans-serif",
        infantil: "'Baloo 2', 'Comic Sans MS', cursive",
        moderno: "'Space Grotesk', 'Segoe UI', sans-serif",
    };

    const el = (id) => document.getElementById(id);
    const primaria = el('cor_primaria');
    const secundaria = el('cor_secundaria');
    const tema = el('tema');
    const capa = el('imagem_capa');

    let capaUrl = '<?= ! empty($evento->imagem_capa) ? base_url($evento->imagem_capa) : '' ?>';

    function pintar() {
        const p = (primaria && primaria.value) || '#4F46E5';
        const s = (secundaria && secundaria.value) || '#10B981';
        const t = (tema && tema.value) || 'classico';

        pvCard.style.setProperty('--pv-primaria', p);
        pvCard.style.setProperty('--pv-secundaria', s);

        const hero = el('pv-hero');
        const overlay = 'linear-gradient(180deg, rgba(17,24,39,.45), rgba(17,24,39,.72))';
        hero.style.background = capaUrl
            ? overlay + ", url('" + capaUrl + "') center/cover"
            : 'linear-gradient(135deg, ' + p + ', ' + s + ')';

        el('pv-titulo').textContent = <?= json_encode($evento->titulo, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        el('pv-titulo').style.fontFamily = fontes[t] || fontes.classico;
        el('pv-preco').style.color = p;
        const botao = el('pv-preco').parentElement.querySelector('span.btn');
        if (botao) { botao.style.background = p; }
    }

    [primaria, secundaria, tema].forEach(function (campo) {
        if (campo) { campo.addEventListener('input', pintar); campo.addEventListener('change', pintar); }
    });

    if (capa) {
        capa.addEventListener('change', function () {
            const arquivo = capa.files && capa.files[0];
            if (!arquivo) { return; }
            const leitor = new FileReader();
            leitor.onload = function (e) { capaUrl = e.target.result; pintar(); };
            leitor.readAsDataURL(arquivo);
        });
    }

    document.querySelectorAll('.preset-cor').forEach(function (botao) {
        botao.addEventListener('click', function () {
            primaria.value = botao.dataset.primaria;
            secundaria.value = botao.dataset.secundaria;
            tema.value = botao.dataset.tema;
            pintar();
        });
    });

    pintar();
})();
</script>
<?= $this->endSection() ?>
