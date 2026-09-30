<?= $this->extend('templates/layouts/app') ?>

<?php
$valor = static fn (string $campo, $padrao = '') => old($campo, $evento->{$campo} ?? $padrao);
?>

<?= $this->section('conteudo') ?>
<form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/forma-pagamento') ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Comissão da plataforma</h2>

                    <div class="mb-3">
                        <label class="form-label" for="quem_paga_taxa">Quem paga a taxa? *</label>
                        <select class="form-select" id="quem_paga_taxa" name="quem_paga_taxa" required>
                            <option value="convidado" <?= $valor('quem_paga_taxa', 'convidado') === 'convidado' ? 'selected' : '' ?>>
                                Convidado (a taxa é acrescentada ao valor)
                            </option>
                            <option value="organizador" <?= $valor('quem_paga_taxa') === 'organizador' ? 'selected' : '' ?>>
                                Organizador (a taxa é descontada do valor líquido)
                            </option>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="percentual_taxa">Taxa personalizada (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control"
                                   id="percentual_taxa" name="percentual_taxa"
                                   value="<?= esc($valor('percentual_taxa')) ?>" placeholder="Deixe vazio para usar a da plataforma">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="meta_valor">Meta de arrecadação (R$)</label>
                            <input type="number" step="0.01" min="0" class="form-control"
                                   id="meta_valor" name="meta_valor" value="<?= esc($valor('meta_valor')) ?>"
                                   placeholder="Opcional">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Recebimento PIX (opcional)</h2>
                    <p class="text-muted fs-7">
                        Se você informar uma chave PIX, os pagamentos desta lista podem ser direcionados a ela.
                        Deixe em branco para usar a chave padrão da plataforma.
                    </p>

                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label" for="pix_tipo">Tipo de chave</label>
                            <select class="form-select" id="pix_tipo" name="pix_tipo">
                                <option value="">—</option>
                                <?php foreach (['cpf' => 'CPF', 'cnpj' => 'CNPJ', 'email' => 'E-mail', 'telefone' => 'Telefone', 'aleatoria' => 'Aleatória'] as $chave => $rotulo): ?>
                                    <option value="<?= esc($chave) ?>" <?= $valor('pix_tipo') === $chave ? 'selected' : '' ?>><?= esc($rotulo) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label" for="pix_chave">Chave PIX</label>
                            <input type="text" class="form-control" id="pix_chave" name="pix_chave" value="<?= esc($valor('pix_chave')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="pix_nome">Nome do titular</label>
                            <input type="text" class="form-control" id="pix_nome" name="pix_nome" value="<?= esc($valor('pix_nome')) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Salvar</button>
                <a class="btn btn-outline-secondary" href="<?= site_url('painel/eventos/' . $evento->id . '/pagamentos') ?>">
                    <i class="bi bi-cash-coin me-1"></i>Ver pagamentos
                </a>
                <a class="btn btn-outline-secondary" href="<?= site_url('painel/eventos/' . $evento->id) ?>">Voltar</a>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
