<?= $this->extend('templates/layouts/app') ?>

<?php
$valor = static fn (string $campo, $padrao = '') => old($campo, $evento->{$campo} ?? $padrao);
?>

<?= $this->section('conteudo') ?>
<form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/forma-pagamento') ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Comissão da plataforma</h2>

                    <div class="mb-1">
                        <label class="form-label" for="quem_paga_taxa">Quem paga a taxa? *</label>
                        <select class="form-select" id="quem_paga_taxa" name="quem_paga_taxa" required>
                            <option value="convidado" <?= $valor('quem_paga_taxa', 'convidado') === 'convidado' ? 'selected' : '' ?>>
                                Convidado (a taxa é acrescentada ao valor)
                            </option>
                            <option value="organizador" <?= $valor('quem_paga_taxa') === 'organizador' ? 'selected' : '' ?>>
                                Organizador (a taxa é descontada do valor líquido)
                            </option>
                        </select>
                        <div class="form-text">A comissão da plataforma é fixa em 10%.</div>
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
