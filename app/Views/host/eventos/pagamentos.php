<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <h2 class="h6 text-muted text-uppercase mb-2 fs-8">Arrecadado</h2>
            <p class="h4 fw-bold mb-0"><?= esc(moeda_brl($resumo['arrecadado'])) ?></p>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <h2 class="h6 text-muted text-uppercase mb-2 fs-8">Pedidos pagos</h2>
            <p class="h4 fw-bold mb-0"><?= (int) $resumo['pagos'] ?></p>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <h2 class="h6 text-muted text-uppercase mb-2 fs-8">Pendentes</h2>
            <p class="h4 fw-bold mb-0"><?= (int) $resumo['pendentes'] ?></p>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
            <h2 class="h6 text-muted text-uppercase mb-2 fs-8">Taxas</h2>
            <p class="h4 fw-bold mb-0"><?= esc(moeda_brl($resumo['taxas'])) ?></p>
        </div></div>
    </div>
</div>

<?php if (empty($pedidos)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-cash-coin fs-2 d-block mb-2"></i>
            Nenhum pagamento recebido ainda.
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Protocolo</th>
                        <th>Convidado</th>
                        <th>Valor</th>
                        <th>Taxa</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td class="fs-8 text-muted"><?= esc($pedido->protocolo) ?></td>
                        <td class="fw-semibold"><?= esc($pedido->nome_convidado) ?></td>
                        <td><?= esc(moeda_brl($pedido->valor_total)) ?></td>
                        <td class="text-muted"><?= esc(moeda_brl($pedido->valor_taxa)) ?></td>
                        <td>
                            <span class="badge text-bg-<?= cor_status_pedido($pedido->status) ?>">
                                <?= esc(rotulo_status_pedido($pedido->status)) ?>
                            </span>
                        </td>
                        <td class="fs-8 text-muted"><?= esc($pedido->criado_em?->format('d/m/Y H:i') ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
