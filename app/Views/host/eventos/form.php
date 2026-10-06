<?= $this->extend('templates/layouts/app') ?>

<?php
$edicao = $evento !== null;
$action = $edicao ? site_url('painel/eventos/' . $evento->id) : site_url('painel/eventos');
$valor  = static fn (string $campo, $padrao = '') => old($campo, $edicao ? ($evento->{$campo} ?? $padrao) : $padrao);

// Campos com tipos especiais precisam de formatação própria para os inputs.
$dataEvento = old('data_evento', $edicao && $evento->data_evento !== null ? $evento->data_evento->format('Y-m-d') : '');
$horario    = old('horario', $edicao ? substr((string) $evento->horario, 0, 5) : '');

$tipos = [];
foreach (tipos_evento() as $chave => $tipoCatalogo) {
    $tipos[$chave] = $tipoCatalogo['rotulo'];
}

$temas = \App\Services\ModeloService::rotulos();
?>

<?= $this->section('conteudo') ?>
<form method="post" action="<?= $action ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Informações principais</h2>

                    <div class="mb-3">
                        <label class="form-label" for="titulo">Título do evento *</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required
                               value="<?= esc($valor('titulo')) ?>" placeholder="Ex.: Casamento de Ana &amp; João">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="subtitulo">Subtítulo</label>
                        <input type="text" class="form-control" id="subtitulo" name="subtitulo"
                               value="<?= esc($valor('subtitulo')) ?>">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="tipo_evento">Tipo *</label>
                            <select class="form-select" id="tipo_evento" name="tipo_evento" required>
                                <?php foreach ($tipos as $chave => $rotulo): ?>
                                    <option value="<?= esc($chave) ?>" <?= $valor('tipo_evento', 'outro') === $chave ? 'selected' : '' ?>>
                                        <?= esc($rotulo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="data_evento">Data</label>
                            <input type="date" class="form-control" id="data_evento" name="data_evento"
                                   value="<?= esc($dataEvento) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="horario">Horário</label>
                            <input type="time" class="form-control" id="horario" name="horario"
                                   value="<?= esc($horario) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label" for="local_nome">Local</label>
                            <input type="text" class="form-control" id="local_nome" name="local_nome"
                                   value="<?= esc($valor('local_nome')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="local_endereco">Endereço</label>
                            <input type="text" class="form-control" id="local_endereco" name="local_endereco"
                                   value="<?= esc($valor('local_endereco')) ?>">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label" for="mensagem_convite">Mensagem para os convidados</label>
                        <textarea class="form-control" id="mensagem_convite" name="mensagem_convite" rows="3"><?= esc($valor('mensagem_convite')) ?></textarea>
                    </div>

                    <div class="mt-3">
                        <label class="form-label" for="descricao">Descrição interna <span class="text-muted">(não aparece para o convidado)</span></label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="2"><?= esc($valor('descricao')) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Comissão da plataforma</h2>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="quem_paga_taxa">Quem paga a taxa da plataforma? *</label>
                            <select class="form-select" id="quem_paga_taxa" name="quem_paga_taxa" required>
                                <option value="convidado" <?= $valor('quem_paga_taxa', 'convidado') === 'convidado' ? 'selected' : '' ?>>
                                    Convidado (acrescentada ao valor)
                                </option>
                                <option value="organizador" <?= $valor('quem_paga_taxa') === 'organizador' ? 'selected' : '' ?>>
                                    Organizador (descontada do líquido)
                                </option>
                            </select>
                            <div class="form-text">A comissão da plataforma é fixa em 10%.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Aparência</h2>

                    <div class="mb-3">
                        <label class="form-label" for="slug">Endereço público (slug)</label>
                        <div class="input-group">
                            <span class="input-group-text"><?= esc(parse_url(base_url(), PHP_URL_HOST) ?: 'minhalistavip.com.br') ?>/</span>
                            <input type="text" class="form-control" id="slug" name="slug"
                                   value="<?= esc($valor('slug')) ?>" placeholder="gerado automaticamente">
                        </div>
                        <div class="form-text">Deixe em branco para gerar a partir do título.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tema">Tema</label>
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
                            <?php
                            $presets = [];
                            foreach (\App\Services\ModeloService::todos() as $chave => $modelo) {
                                $presets[] = [$modelo['rotulo'], $modelo['cor_primaria'], $modelo['cor_secundaria'], $chave];
                            }
                            ?>
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
                        <div class="form-text">Aplica cores e tipografia sugeridas de uma vez.</div>
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

                    <div class="mt-3">
                        <label class="form-label" for="imagem_capa">Imagem de capa</label>
                        <input type="file" class="form-control" id="imagem_capa" name="imagem_capa"
                               accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">JPG, PNG ou WEBP até 2 MB.</div>

                        <?php if ($edicao && ! empty($evento->imagem_capa)): ?>
                            <img src="<?= base_url($evento->imagem_capa) ?>" alt="Capa atual"
                                 class="img-fluid rounded mt-2">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="remover_capa" name="remover_capa" value="1">
                                <label class="form-check-label small" for="remover_capa">Remover capa atual</label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Pré-visualização -->
            <div class="card border-0 shadow-sm rounded-4 mb-3" id="preview-card">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Pré-visualização</h2>

                    <div class="rounded-3 p-3 text-white text-center mb-3" id="pv-hero">
                        <p class="text-uppercase fs-8 mb-1 opacity-75" id="pv-tipo">Evento</p>
                        <p class="fw-bold mb-1" id="pv-titulo">Título do evento</p>
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

                    <p class="text-muted fs-8 mt-3 mb-0">
                        É assim que as cores e a tipografia do tema aparecem no hotsite.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Funcionalidades</h2>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7" for="limite_convidados">Limite de convidados</label>
                        <input type="number" min="1" class="form-control" id="limite_convidados" name="limite_convidados"
                               value="<?= esc($valor('limite_convidados')) ?>" placeholder="vazio = sem limite">
                        <div class="form-text">Máximo de <strong>pessoas</strong> confirmadas (com acompanhantes). Vazio = ilimitado.</div>
                    </div>

                    <?php
                    $opcoes = [
                        'permite_rsvp'    => 'Aceitar confirmação de presença (RSVP)',
                        'permite_recados' => 'Aceitar recados no mural',
                        'exibir_valores'  => 'Exibir os valores na página pública',
                    ];
                    ?>
                    <?php foreach ($opcoes as $campo => $rotulo): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="<?= esc($campo) ?>"
                                   name="<?= esc($campo) ?>" value="1"
                                   <?= $valor($campo, $edicao ? null : '1') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= esc($campo) ?>"><?= esc($rotulo) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-brand">
                    <?= $edicao ? 'Salvar alterações' : 'Criar evento' ?>
                </button>
                <a class="btn btn-outline-secondary" href="<?= site_url('painel/eventos') ?>">Cancelar</a>
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
    const titulo = el('titulo');
    const tipo = el('tipo_evento');
    const data = el('data_evento');
    const local = el('local_nome');
    const primaria = el('cor_primaria');
    const secundaria = el('cor_secundaria');
    const tema = el('tema');
    const capa = el('imagem_capa');

    let capaUrl = '<?= $edicao && ! empty($evento->imagem_capa) ? base_url($evento->imagem_capa) : '' ?>';

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

        el('pv-titulo').textContent = (titulo && titulo.value) || 'Título do evento';
        el('pv-titulo').style.fontFamily = fontes[t] || fontes.classico;
        el('pv-tipo').textContent = tipo ? (tipo.options[tipo.selectedIndex] || {}).text : 'Evento';

        const dataTxt = data && data.value ? data.value.split('-').reverse().join('/') : '';
        const localTxt = local ? local.value : '';
        el('pv-local').textContent = [dataTxt, localTxt].filter(Boolean).join(' · ') || 'Data · Local';

        el('pv-preco').style.color = p;
        const botao = el('pv-preco').parentElement.querySelector('span.btn');
        if (botao) { botao.style.background = p; }
    }

    [titulo, tipo, data, local, primaria, secundaria, tema].forEach(function (campo) {
        if (campo) {
            campo.addEventListener('input', pintar);
            campo.addEventListener('change', pintar);
        }
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
