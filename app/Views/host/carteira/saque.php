<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted">
                    Saldo disponível: <strong><?= esc(moeda_brl($saldo)) ?></strong> ·
                    Mínimo para saque: <?= esc(moeda_brl($valorMinimo)) ?>
                </p>

                <form method="post" action="<?= site_url('painel/carteira/saque') ?>" class="row g-3">
                    <?= csrf_field() ?>

                    <div class="col-md-6">
                        <label class="form-label" for="valor">Valor do saque (R$)</label>
                        <input type="number" step="0.01" min="<?= esc((string) $valorMinimo, 'raw') ?>"
                               class="form-control" id="valor" name="valor" required
                               value="<?= esc(old('valor')) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="chave_pix">Chave PIX</label>
                        <input type="text" class="form-control" id="chave_pix" name="chave_pix" required
                               value="<?= esc(old('chave_pix')) ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="observacao">Observação <span class="text-muted">(opcional)</span></label>
                        <textarea class="form-control" id="observacao" name="observacao" rows="2"><?= esc(old('observacao')) ?></textarea>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary">Solicitar saque</button>
                        <a class="btn btn-outline-secondary" href="<?= site_url('painel/carteira') ?>">Voltar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
