<?= $this->extend('templates/layouts/app') ?>

<?php
$edicao = $demo !== null;
$action = $edicao ? site_url('admin/demos/' . $demo['id']) : site_url('admin/demos');
$campo  = static fn (string $chave, $padrao = '') => old($chave, $edicao ? ($demo[$chave] ?? $padrao) : $padrao);

$capaUrl = static fn (string $caminho): string => str_starts_with($caminho, 'http') ? $caminho : base_url($caminho);

/** Repopula as linhas a partir do formulário (old) ou do registro editado. */
$itens = [];
if (old('itens_nome') !== null) {
    $nomes = (array) old('itens_nome');
    $descs = (array) old('itens_descricao');
    $vals  = (array) old('itens_valor');
    $metas = (array) old('itens_meta');
    $vends = (array) old('itens_vendida');
    foreach ($nomes as $i => $nome) {
        $itens[] = [
            'nome'      => (string) $nome,
            'descricao' => (string) ($descs[$i] ?? ''),
            'valor'     => (string) ($vals[$i] ?? ''),
            'meta'      => (string) ($metas[$i] ?? '1'),
            'vendida'   => (string) ($vends[$i] ?? '0'),
        ];
    }
} elseif ($edicao) {
    $itens = $demo['itens'] ?? [];
} else {
    $itens = [['nome' => '', 'descricao' => '', 'valor' => '', 'meta' => '1', 'vendida' => '0']];
}

$recados = [];
if (old('recados_mensagem') !== null) {
    $autores   = (array) old('recados_autor');
    $mensagens = (array) old('recados_mensagem');
    foreach ($mensagens as $i => $mensagem) {
        $recados[] = ['autor' => (string) ($autores[$i] ?? ''), 'mensagem' => (string) $mensagem];
    }
} elseif ($edicao) {
    $recados = $demo['recados'] ?? [];
}

$galeria = [];
if (old('galeria_imagem') !== null) {
    $imagens  = (array) old('galeria_imagem');
    $legendas = (array) old('galeria_legenda');
    foreach ($imagens as $i => $imagem) {
        $galeria[] = ['imagem' => (string) $imagem, 'legenda' => (string) ($legendas[$i] ?? '')];
    }
} elseif ($edicao) {
    $galeria = $demo['galeria'] ?? [];
}

$temas  = ['classico' => 'Clássico', 'casamento' => 'Casamento', 'cha_bebe' => 'Chá de bebê', 'infantil' => 'Infantil', 'moderno' => 'Moderno'];
$badges = ['primary' => 'Azul', 'info' => 'Ciano', 'success' => 'Verde', 'warning' => 'Amarelo', 'danger' => 'Vermelho', 'dark' => 'Escuro', 'secondary' => 'Cinza'];

$tiposRotulos = array_values(array_map(static fn (array $t): string => $t['rotulo'], tipos_evento()));
?>

<?= $this->section('conteudo') ?>
<form method="post" action="<?= $action ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <!-- Informações -->
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted fw-semibold mb-3">Informações da lista</h2>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold fs-7" for="titulo">Título *</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required
                                   value="<?= esc($campo('titulo')) ?>" placeholder="Ex.: Marina & Gabriel">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-7" for="tipo">Tipo de evento</label>
                            <input type="text" class="form-control" id="tipo" name="tipo" list="lista-tipos"
                                   value="<?= esc($campo('tipo')) ?>" placeholder="Ex.: Casamento">
                            <datalist id="lista-tipos">
                                <?php foreach ($tiposRotulos as $rotulo): ?>
                                    <option value="<?= esc($rotulo, 'attr') ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold fs-7" for="slug">Endereço (slug)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">/demo/</span>
                            <input type="text" class="form-control" id="slug" name="slug"
                                   value="<?= esc($campo('slug')) ?>" placeholder="gerado-automaticamente">
                        </div>
                        <div class="form-text">Vazio = gerado a partir do título.</div>
                    </div>

                    <div class="row g-3 mt-0">
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold fs-7" for="icone">Ícone</label>
                            <input type="text" class="form-control" id="icone" name="icone"
                                   value="<?= esc($campo('icone')) ?>" placeholder="💍">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold fs-7" for="badge">Cor do selo</label>
                            <select class="form-select" id="badge" name="badge">
                                <?php foreach ($badges as $valor => $rotulo): ?>
                                    <option value="<?= esc($valor, 'attr') ?>" <?= $campo('badge', 'primary') === $valor ? 'selected' : '' ?>><?= esc($rotulo) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold fs-7" for="data_texto">Data (texto)</label>
                            <input type="text" class="form-control" id="data_texto" name="data_texto"
                                   value="<?= esc($campo('data_texto')) ?>" placeholder="12 de Outubro">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold fs-7" for="local">Local</label>
                            <input type="text" class="form-control" id="local" name="local"
                                   value="<?= esc($campo('local')) ?>" placeholder="Ilhabela, SP">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold fs-7" for="resumo">Resumo (card da Home)</label>
                        <textarea class="form-control" id="resumo" name="resumo" rows="2"><?= esc($campo('resumo')) ?></textarea>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold fs-7" for="descricao">Descrição (topo do hotsite)</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="2"><?= esc($campo('descricao')) ?></textarea>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold fs-7" for="mensagem_convite">Mensagem de convite</label>
                        <textarea class="form-control" id="mensagem_convite" name="mensagem_convite" rows="2"><?= esc($campo('mensagem_convite')) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Aparência -->
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted fw-semibold mb-3">Aparência</h2>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7" for="capa">Imagem de capa (URL)</label>
                        <input type="text" class="form-control" id="capa" name="capa"
                               value="<?= esc($campo('capa')) ?>" placeholder="https://...">
                        <div class="form-text">Ou envie um arquivo abaixo (JPG, PNG ou WEBP até 2 MB).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7" for="capa_arquivo">Enviar capa</label>
                        <input type="file" class="form-control" id="capa_arquivo" name="capa_arquivo"
                               accept="image/jpeg,image/png,image/webp">
                        <?php $capaAtual = (string) $campo('capa'); ?>
                        <?php if ($capaAtual !== ''): ?>
                            <img src="<?= esc($capaUrl($capaAtual), 'attr') ?>" alt="Capa atual"
                                 class="img-fluid rounded-3 mt-2 border" style="max-height: 140px;">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="remover_capa" name="remover_capa" value="1">
                                <label class="form-check-label fs-7" for="remover_capa">Remover capa atual</label>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold fs-7" for="cor_primaria">Cor primária</label>
                            <input type="color" class="form-control form-control-color w-100" id="cor_primaria"
                                   name="cor_primaria" value="<?= esc($campo('cor_primaria', '#722ED4')) ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold fs-7" for="cor_secundaria">Cor secundária</label>
                            <input type="color" class="form-control form-control-color w-100" id="cor_secundaria"
                                   name="cor_secundaria" value="<?= esc($campo('cor_secundaria', '#7C3AED')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold fs-7" for="tema">Tema</label>
                            <select class="form-select" id="tema" name="tema">
                                <?php foreach ($temas as $valor => $rotulo): ?>
                                    <option value="<?= esc($valor, 'attr') ?>" <?= $campo('tema', 'classico') === $valor ? 'selected' : '' ?>><?= esc($rotulo) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="escuro" name="escuro" value="1"
                                       <?= $campo('escuro') ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold fs-7" for="escuro">Tema escuro</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Presentes / cotas -->
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h6 text-uppercase text-muted fw-semibold mb-0">Presentes / cotas</h2>
                        <button type="button" class="btn btn-sm btn-outline-brand" data-add="lista-itens" data-tpl="tpl-item">
                            <i class="bi bi-plus-lg me-1"></i>Adicionar
                        </button>
                    </div>

                    <div data-linhas id="lista-itens">
                        <?php foreach ($itens as $item): ?>
                            <div class="row g-2 align-items-start border rounded-3 p-2 mb-2" data-linha>
                                <div class="col-md-5">
                                    <input type="text" class="form-control form-control-sm" name="itens_nome[]"
                                           value="<?= esc($item['nome']) ?>" placeholder="Nome da cota *">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control form-control-sm" name="itens_descricao[]"
                                           value="<?= esc($item['descricao']) ?>" placeholder="Descrição">
                                </div>
                                <div class="col-4 col-md-1">
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                           name="itens_valor[]" value="<?= esc($item['valor']) ?>" placeholder="R$">
                                </div>
                                <div class="col-4 col-md-1">
                                    <input type="number" min="1" class="form-control form-control-sm"
                                           name="itens_meta[]" value="<?= esc($item['meta'] ?? 1) ?>" placeholder="Meta">
                                </div>
                                <div class="col-4 col-md-1 d-flex gap-1">
                                    <input type="number" min="0" class="form-control form-control-sm"
                                           name="itens_vendida[]" value="<?= esc($item['vendida'] ?? 0) ?>" placeholder="Vend.">
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-remover aria-label="Remover linha">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-muted fs-8 mb-0">Valor em reais; “meta” é o número de cotas e “vend.” quantas já foram presenteadas (números fictícios do exemplo).</p>
                </div>
            </div>

            <!-- Recados -->
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h6 text-uppercase text-muted fw-semibold mb-0">Recados do mural</h2>
                        <button type="button" class="btn btn-sm btn-outline-brand" data-add="lista-recados" data-tpl="tpl-recado">
                            <i class="bi bi-plus-lg me-1"></i>Adicionar
                        </button>
                    </div>

                    <div data-linhas id="lista-recados">
                        <?php foreach ($recados as $recado): ?>
                            <div class="row g-2 align-items-start border rounded-3 p-2 mb-2" data-linha>
                                <div class="col-md-3">
                                    <input type="text" class="form-control form-control-sm" name="recados_autor[]"
                                           value="<?= esc($recado['autor']) ?>" placeholder="Autor">
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control form-control-sm" name="recados_mensagem[]"
                                           value="<?= esc($recado['mensagem']) ?>" placeholder="Mensagem">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-remover aria-label="Remover linha">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Galeria -->
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h6 text-uppercase text-muted fw-semibold mb-0">Galeria de fotos</h2>
                        <button type="button" class="btn btn-sm btn-outline-brand" data-add="lista-galeria" data-tpl="tpl-galeria">
                            <i class="bi bi-plus-lg me-1"></i>Adicionar
                        </button>
                    </div>

                    <div data-linhas id="lista-galeria">
                        <?php foreach ($galeria as $foto): ?>
                            <div class="row g-2 align-items-start border rounded-3 p-2 mb-2" data-linha>
                                <div class="col-md-6">
                                    <input type="text" class="form-control form-control-sm" name="galeria_imagem[]"
                                           value="<?= esc($foto['imagem']) ?>" placeholder="URL da imagem (https://...)">
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control form-control-sm" name="galeria_legenda[]"
                                           value="<?= esc($foto['legenda']) ?>" placeholder="Legenda">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-remover aria-label="Remover linha">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-muted fs-8 mb-0">Use URLs de imagens (as listas de exemplo usam imagens externas).</p>
                </div>
            </div>
        </div>

        <!-- Publicação -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-3" style="position: sticky; top: 76px;">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted fw-semibold mb-3">Publicação</h2>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="ativo" name="ativo" value="1"
                               <?= $campo('ativo', $edicao ? null : '1') ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold fs-7" for="ativo">Exibir no site</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7" for="ordem">Ordem de exibição</label>
                        <input type="number" class="form-control" id="ordem" name="ordem"
                               value="<?= esc($campo('ordem', $edicao ? 0 : $proximaOrdem)) ?>">
                        <div class="form-text">Menor número aparece primeiro.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7" for="presentes">
                            Presentes <span class="text-muted fw-normal">(exibido no card)</span>
                        </label>
                        <input type="number" min="0" class="form-control" id="presentes" name="presentes"
                               value="<?= esc($campo('presentes')) ?>" placeholder="automático">
                        <div class="form-text">Vazio = soma das cotas (metas).</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-brand">
                            <?= $edicao ? 'Salvar alterações' : 'Criar lista de exemplo' ?>
                        </button>
                        <a class="btn btn-outline-secondary" href="<?= site_url('admin/demos') ?>">Cancelar</a>
                        <?php if ($edicao): ?>
                            <a class="btn btn-outline-secondary" href="<?= site_url('demo/' . $demo['slug']) ?>" target="_blank">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Ver no site
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates das linhas dinâmicas -->
    <template id="tpl-item">
        <div class="row g-2 align-items-start border rounded-3 p-2 mb-2" data-linha>
            <div class="col-md-5"><input type="text" class="form-control form-control-sm" name="itens_nome[]" placeholder="Nome da cota *"></div>
            <div class="col-md-4"><input type="text" class="form-control form-control-sm" name="itens_descricao[]" placeholder="Descrição"></div>
            <div class="col-4 col-md-1"><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="itens_valor[]" placeholder="R$"></div>
            <div class="col-4 col-md-1"><input type="number" min="1" class="form-control form-control-sm" name="itens_meta[]" value="1" placeholder="Meta"></div>
            <div class="col-4 col-md-1 d-flex gap-1">
                <input type="number" min="0" class="form-control form-control-sm" name="itens_vendida[]" value="0" placeholder="Vend.">
                <button type="button" class="btn btn-sm btn-outline-danger" data-remover aria-label="Remover linha"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
    </template>

    <template id="tpl-recado">
        <div class="row g-2 align-items-start border rounded-3 p-2 mb-2" data-linha>
            <div class="col-md-3"><input type="text" class="form-control form-control-sm" name="recados_autor[]" placeholder="Autor"></div>
            <div class="col-md-8"><input type="text" class="form-control form-control-sm" name="recados_mensagem[]" placeholder="Mensagem"></div>
            <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger" data-remover aria-label="Remover linha"><i class="bi bi-x-lg"></i></button></div>
        </div>
    </template>

    <template id="tpl-galeria">
        <div class="row g-2 align-items-start border rounded-3 p-2 mb-2" data-linha>
            <div class="col-md-6"><input type="text" class="form-control form-control-sm" name="galeria_imagem[]" placeholder="URL da imagem (https://...)"></div>
            <div class="col-md-5"><input type="text" class="form-control form-control-sm" name="galeria_legenda[]" placeholder="Legenda"></div>
            <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger" data-remover aria-label="Remover linha"><i class="bi bi-x-lg"></i></button></div>
        </div>
    </template>
</form>

<script>
(function () {
    // Adiciona uma linha clonando o <template> correspondente.
    document.querySelectorAll('[data-add]').forEach(function (botao) {
        botao.addEventListener('click', function () {
            var alvo   = document.getElementById(botao.dataset.add);
            var modelo = document.getElementById(botao.dataset.tpl);

            if (! alvo || ! modelo) { return; }

            alvo.appendChild(modelo.content.cloneNode(true));
            var ultima = alvo.lastElementChild;
            if (ultima) { ultima.querySelector('input')?.focus(); }
        });
    });

    // Remove a linha do botão clicado.
    document.querySelectorAll('[data-linhas]').forEach(function (container) {
        container.addEventListener('click', function (evento) {
            var botao = evento.target.closest('[data-remover]');
            if (botao) { botao.closest('[data-linha]')?.remove(); }
        });
    });
})();
</script>
<?= $this->endSection() ?>
