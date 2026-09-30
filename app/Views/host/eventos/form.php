<?= $this->extend('templates/layouts/app') ?>

<?php
$edicao = $evento !== null;
$action = $edicao ? site_url('painel/eventos/' . $evento->id) : site_url('painel/eventos');
$valor  = static fn (string $campo, $padrao = '') => old($campo, $edicao ? ($evento->{$campo} ?? $padrao) : $padrao);

// Campos com tipos especiais precisam de formatação própria para os inputs.
$dataEvento = old('data_evento', $edicao && $evento->data_evento !== null ? $evento->data_evento->format('Y-m-d') : '');
$horario    = old('horario', $edicao ? substr((string) $evento->horario, 0, 5) : '');

$tipos = [
    'casamento'   => 'Casamento',
    'cha_bebe'    => 'Chá de Bebê',
    'cha_fraldas' => 'Chá de Fraldas',
    'cha_panela'  => 'Chá de Panela',
    'aniversario' => 'Aniversário',
    'formatura'   => 'Formatura',
    'corporativo' => 'Corporativo',
    'outro'       => 'Outro',
];

$temas = [
    'classico'  => 'Clássico',
    'casamento' => 'Casamento',
    'cha_bebe'  => 'Chá de Bebê',
    'infantil'  => 'Infantil',
    'moderno'   => 'Moderno',
];
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
                    <h2 class="h6 text-uppercase text-muted mb-3">Financeiro e PIX</h2>

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
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="percentual_taxa">Taxa (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control"
                                   id="percentual_taxa" name="percentual_taxa"
                                   value="<?= esc($valor('percentual_taxa')) ?>" placeholder="Padrão da plataforma">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="meta_valor">Meta (R$)</label>
                            <input type="number" step="0.01" min="0" class="form-control"
                                   id="meta_valor" name="meta_valor" value="<?= esc($valor('meta_valor')) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-5">
                            <label class="form-label" for="pix_tipo">Tipo de chave PIX</label>
                            <select class="form-select" id="pix_tipo" name="pix_tipo">
                                <option value="">—</option>
                                <?php foreach (['cpf' => 'CPF', 'cnpj' => 'CNPJ', 'email' => 'E-mail', 'telefone' => 'Telefone', 'aleatoria' => 'Aleatória'] as $chave => $rotulo): ?>
                                    <option value="<?= esc($chave) ?>" <?= $valor('pix_tipo') === $chave ? 'selected' : '' ?>>
                                        <?= esc($rotulo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label" for="pix_chave">Chave PIX</label>
                            <input type="text" class="form-control" id="pix_chave" name="pix_chave"
                                   value="<?= esc($valor('pix_chave')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="pix_nome">Nome do titular da chave</label>
                            <input type="text" class="form-control" id="pix_nome" name="pix_nome"
                                   value="<?= esc($valor('pix_nome')) ?>">
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

            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Funcionalidades</h2>

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
<?= $this->endSection() ?>
