<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <div class="flex-grow-1">
                        <p class="text-muted fs-8 text-uppercase mb-1">Disponível para resgate</p>
                        <p class="h4 fw-bold mb-0"><?= esc(moeda_brl($saldo)) ?></p>
                    </div>
                    <div class="text-end">
                        <p class="text-muted fs-8 text-uppercase mb-1">Mínimo para resgate</p>
                        <p class="h6 fw-semibold mb-0"><?= esc(moeda_brl($valorMinimo)) ?></p>
                    </div>
                </div>

                <?php if (! $repasseCompleto): ?>
                    <div class="alert alert-warning rounded-4">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Preencha os <strong>dados de repasse</strong> (responsável, bancários e endereço)
                        antes de solicitar o resgate.
                    </div>
                    <a class="btn btn-brand" href="<?= site_url('painel/carteira') ?>">
                        <i class="bi bi-person-vcard me-1"></i>Preencher dados de repasse
                    </a>
                <?php else: ?>
                    <form method="post" action="<?= site_url('painel/carteira/saque') ?>" class="row g-3">
                        <?= csrf_field() ?>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-7" for="valor">Valor do resgate (R$)</label>
                            <input type="number" step="0.01" min="<?= esc((string) $valorMinimo, 'raw') ?>"
                                   class="form-control" id="valor" name="valor" required
                                   value="<?= esc(old('valor')) ?>" placeholder="0,00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-7" for="chave_pix">Chave PIX</label>
                            <input type="text" class="form-control" id="chave_pix" name="chave_pix"
                                   value="<?= esc(old('chave_pix', $usuario->chave_pix ?? '')) ?>">
                            <div class="form-text">
                                Tipo: <?= esc(ucfirst((string) ($usuario->tipo_chave_pix ?? '—'))) ?>.
                                Deixe em branco para usar a chave salva.
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold fs-7" for="observacao">
                                Observação <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <textarea class="form-control" id="observacao" name="observacao" rows="2"><?= esc(old('observacao')) ?></textarea>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-brand"><i class="bi bi-cash-coin me-1"></i>Solicitar resgate</button>
                            <a class="btn btn-outline-secondary" href="<?= site_url('painel/carteira') ?>">Voltar</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
