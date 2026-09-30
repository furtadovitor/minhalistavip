<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-1 fs-8">Arrecadado (presentes)</h2>
                <p class="h3 fw-bold mb-0"><?= esc(moeda_brl($arrecadado)) ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-1 fs-8">Taxas retidas</h2>
                <p class="h3 fw-bold mb-0 text-success"><?= esc(moeda_brl($taxas)) ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-1 fs-8">Saques pagos</h2>
                <p class="h3 fw-bold mb-0"><?= esc(moeda_brl($saquesPagos)) ?></p>
                <p class="text-muted fs-8 mb-0">Pendentes: <?= esc(moeda_brl($saquesPendentes)) ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-1 fs-8">Pedidos pagos</h2>
                <p class="h3 fw-bold mb-0"><?= (int) $pedidosPagos ?></p>
                <p class="text-muted fs-8 mb-0">Ticket médio: <?= esc(moeda_brl($ticketMedio)) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info rounded-4">
    <i class="bi bi-wallet2 me-1"></i>
    Resultado da plataforma (taxas retidas − saques pagos):
    <strong><?= esc(moeda_brl($saldoPlataforma)) ?></strong>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-3">
        <h2 class="h6 mb-0 text-muted text-uppercase fs-8">Últimos pagamentos confirmados</h2>
    </div>
    <?php if (empty($ultimos)): ?>
        <div class="card-body text-center py-4">
            <p class="text-muted mb-0">Nenhum pagamento confirmado ainda.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Pedido</th>
                        <th>Evento</th>
                        <th>Organizador</th>
                        <th>Convidado</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Taxa</th>
                        <th>Pago em</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($ultimos as $pedido): ?>
                    <tr>
                        <td class="small">
                            <a class="text-decoration-none fw-semibold" target="_blank"
                               href="<?= site_url($pedido['evento_slug'] . '/pedido/' . $pedido['protocolo']) ?>">
                                <?= esc($pedido['protocolo']) ?>
                            </a>
                        </td>
                        <td class="small"><?= esc($pedido['evento_titulo']) ?></td>
                        <td class="small"><?= esc($pedido['organizador_nome']) ?></td>
                        <td class="small"><?= esc($pedido['nome_convidado']) ?></td>
                        <td class="text-end"><?= esc(moeda_brl($pedido['valor_total'])) ?></td>
                        <td class="text-end text-success">
                            <?= $pedido['quem_paga_taxa'] === 'organizador' ? esc(moeda_brl($pedido['valor_taxa'])) : '—' ?>
                        </td>
                        <td class="small text-muted">
                            <?= $pedido['pago_em'] !== null ? esc(date('d/m/Y H:i', strtotime((string) $pedido['pago_em']))) : '—' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
