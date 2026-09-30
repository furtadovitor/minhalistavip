<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Saldo disponível</h2>
                    <i class="bi bi-wallet2 text-brand"></i>
                </div>
                <p class="h3 fw-bold mb-3"><?= esc(moeda_brl($saldo)) ?></p>
                <a class="btn btn-brand btn-sm" href="<?= site_url('painel/carteira/saque') ?>">
                    <i class="bi bi-cash-coin me-1"></i>Solicitar saque
                </a>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Arrecadado</h2>
                    <i class="bi bi-graph-up-arrow text-success"></i>
                </div>
                <p class="h3 fw-bold mb-0"><?= esc(moeda_brl($arrecadado)) ?></p>
                <p class="text-muted fs-8 mb-0">Soma dos presentes pagos.</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Taxas</h2>
                    <i class="bi bi-percent text-muted"></i>
                </div>
                <p class="h3 fw-bold mb-0"><?= esc(moeda_brl($taxas)) ?></p>
                <p class="text-muted fs-8 mb-0">Comissão da plataforma.</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Sacado</h2>
                    <i class="bi bi-bank text-muted"></i>
                </div>
                <p class="h3 fw-bold mb-0"><?= esc(moeda_brl($sacado)) ?></p>
                <p class="text-muted fs-8 mb-0">Saques solicitados.</p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0 text-muted text-uppercase fs-8">Extrato</h2>
        <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('painel/pedidos') ?>">Ver pedidos</a>
    </div>
    <?php if (empty($extrato)): ?>
        <div class="card-body text-center py-4">
            <p class="text-muted mb-0">Nenhuma movimentação ainda. Os créditos aparecem aqui após o pagamento dos pedidos.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th class="text-end">Valor</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($extrato as $movimentacao): ?>
                    <tr>
                        <td class="small"><?= esc(date('d/m/Y H:i', strtotime((string) $movimentacao['criado_em']))) ?></td>
                        <td class="small"><?= esc(rotulo_tipo_movimentacao((string) $movimentacao['tipo'])) ?></td>
                        <td class="small text-muted"><?= esc($movimentacao['descricao'] ?? '') ?></td>
                        <td class="text-end fw-semibold <?= (float) $movimentacao['valor'] < 0 ? 'text-danger' : 'text-success' ?>">
                            <?= esc(moeda_brl($movimentacao['valor'])) ?>
                        </td>
                        <td class="text-end small text-muted"><?= esc(moeda_brl($movimentacao['saldo_apos'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-3">
        <h2 class="h6 mb-0 text-muted text-uppercase fs-8">Saques</h2>
    </div>
    <?php if (empty($saques)): ?>
        <div class="card-body text-center py-4">
            <p class="text-muted mb-0">Nenhum saque solicitado. O valor mínimo é <?= esc(moeda_brl($valorMinimo)) ?>.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Solicitado em</th>
                        <th>Chave PIX</th>
                        <th class="text-end">Valor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($saques as $saque): ?>
                    <tr>
                        <td class="small text-muted"><?= (int) $saque['id'] ?></td>
                        <td class="small">
                            <?= esc($saque['solicitado_em'] !== null ? date('d/m/Y H:i', strtotime((string) $saque['solicitado_em'])) : '—') ?>
                        </td>
                        <td class="small"><?= esc((string) $saque['chave_pix']) ?></td>
                        <td class="text-end"><?= esc(moeda_brl($saque['valor'])) ?></td>
                        <td>
                            <span class="badge text-bg-<?= cor_status_saque((string) $saque['status']) ?>">
                                <?= esc(rotulo_status_saque((string) $saque['status'])) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
