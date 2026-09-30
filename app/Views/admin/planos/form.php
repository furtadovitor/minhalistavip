<?= $this->extend('templates/layouts/app') ?>

<?php
$edicao  = $plano !== null;
$action  = $edicao ? site_url('admin/planos/' . $plano['id']) : site_url('admin/planos');
$campo   = static fn (string $chave, $padrao = '') => old($chave, $edicao ? ($plano[$chave] ?? $padrao) : $padrao);
$recursos = $edicao && ! empty($plano['recursos']) ? (json_decode((string) $plano['recursos'], true) ?: []) : [];
$periodos = ['mensal' => 'Mensal', 'anual' => 'Anual', 'vitalicio' => 'Vitalício'];
?>

<?= $this->section('conteudo') ?>
<form method="post" action="<?= $action ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted fw-semibold mb-3">Dados do plano</h2>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-7" for="nome">Nome *</label>
                            <input type="text" class="form-control" id="nome" name="nome" required
                                   value="<?= esc($campo('nome')) ?>" placeholder="Ex.: Premium">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-7" for="slug">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug"
                                   value="<?= esc($campo('slug')) ?>" placeholder="gerado automaticamente">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold fs-7" for="descricao">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="2"><?= esc($campo('descricao')) ?></textarea>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-7" for="preco">Preço (R$) *</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="preco" name="preco" required
                                   value="<?= esc($campo('preco', '0')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-7" for="periodo">Período *</label>
                            <select class="form-select" id="periodo" name="periodo" required>
                                <?php foreach ($periodos as $chave => $rotulo): ?>
                                    <option value="<?= esc($chave) ?>" <?= $campo('periodo', 'mensal') === $chave ? 'selected' : '' ?>>
                                        <?= esc($rotulo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-7" for="limite_eventos">Limite de eventos</label>
                            <input type="number" min="1" class="form-control" id="limite_eventos" name="limite_eventos"
                                   value="<?= esc($campo('limite_eventos')) ?>" placeholder="vazio = ilimitado">
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-7" for="percentual_taxa">Taxa do plano (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control"
                                   id="percentual_taxa" name="percentual_taxa"
                                   value="<?= esc($campo('percentual_taxa')) ?>" placeholder="vazio = padrão">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted fw-semibold mb-3">Recursos inclusos</h2>
                    <?php foreach (['rsvp' => 'RSVP (confirmação de presença)', 'recados' => 'Mural de recados', 'destaque' => 'Destaque na plataforma'] as $chave => $rotulo): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="recurso_<?= esc($chave) ?>"
                                   name="recurso_<?= esc($chave) ?>" value="1"
                                   <?= ! empty($recursos[$chave]) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="recurso_<?= esc($chave) ?>"><?= esc($rotulo) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7" for="ordem">Ordem</label>
                        <input type="number" class="form-control" id="ordem" name="ordem" value="<?= esc($campo('ordem', '0')) ?>">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1"
                               <?= $campo('ativo', $edicao ? null : '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="ativo">Plano ativo</label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-brand"><?= $edicao ? 'Salvar alterações' : 'Criar plano' ?></button>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/planos') ?>">Cancelar</a>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
