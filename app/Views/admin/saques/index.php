<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase">Saques pendentes</h2>
                <p class="display-6 mb-0"><?= (int) $resumo['pendentes_qtd'] ?></p>
                <p class="text-muted small mb-0"><?= esc(moeda_brl($resumo['pendentes_valor'])) ?> aguardando pagamento</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase">Saques pagos</h2>
                <p class="display-6 mb-0"><?= (int) $resumo['pagos_qtd'] ?></p>
                <p class="text-muted small mb-0"><?= esc(moeda_brl($resumo['pagos_valor'])) ?> transferidos</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase">Acesso</h2>
                <p class="text-muted small mb-2">Pague pelo seu internet banking/PIX e marque o saque como pago.</p>
                <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('admin') ?>">Painel do SuperAdmin</a>
            </div>
        </div>
    </div>
</div>

<form class="row g-2 align-items-end mb-3" method="get" action="<?= site_url('admin/saques') ?>">
    <div class="col-md-3">
        <label class="form-label small mb-1" for="filtro-status">Status</label>
        <select class="form-select" id="filtro-status" name="status">
            <option value="">Todos</option>
            <?php foreach (['solicitado', 'processando', 'pago', 'recusado', 'cancelado'] as $opcao): ?>
                <option value="<?= esc($opcao) ?>" <?= (string) ($filtros['status'] ?? '') === $opcao ? 'selected' : '' ?>>
                    <?= esc(rotulo_status_saque($opcao)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-outline-secondary w-100">Filtrar</button>
    </div>
</form>

<?php if (empty($saques)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <p class="mb-1 fw-semibold">Nenhum saque encontrado.</p>
            <p class="text-muted mb-0">Os pedidos dos organizadores aparecem aqui assim que forem solicitados.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Organizador</th>
                        <th class="text-end">Valor</th>
                        <th>Chave PIX</th>
                        <th>Solicitado</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($saques as $saque): ?>
                    <?php $status = (string) $saque['status']; ?>
                    <tr>
                        <td class="small text-muted"><?= (int) $saque['id'] ?></td>
                        <td class="small">
                            <div><?= esc($saque['organizador_nome']) ?></div>
                            <div class="text-muted"><?= esc($saque['organizador_email']) ?></div>
                        </td>
                        <td class="text-end fw-semibold"><?= esc(moeda_brl($saque['valor'])) ?></td>
                        <td class="small"><?= esc((string) $saque['chave_pix']) ?></td>
                        <td class="small text-muted">
                            <?= $saque['solicitado_em'] !== null ? esc(date('d/m/Y H:i', strtotime((string) $saque['solicitado_em']))) : '—' ?>
                        </td>
                        <td>
                            <span class="badge text-bg-<?= cor_status_saque($status) ?>">
                                <?= esc(rotulo_status_saque($status)) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php if (in_array($status, ['solicitado', 'processando'], true)): ?>
                                <div class="d-flex flex-wrap gap-1 justify-content-end">
                                    <?php if ($status === 'solicitado'): ?>
                                        <form method="post" action="<?= site_url('admin/saques/' . $saque['id'] . '/processar') ?>">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-info">Processar</button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="post" action="<?= site_url('admin/saques/' . $saque['id'] . '/pagar') ?>"
                                          onsubmit="return confirm('Confirmar que o PIX de <?= esc(moeda_brl($saque['valor']), 'attr') ?> foi transferido?');">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-success">Marcar como pago</button>
                                    </form>
                                </div>

                                <form method="post" class="d-flex gap-1 justify-content-end mt-1"
                                      action="<?= site_url('admin/saques/' . $saque['id'] . '/recusar') ?>"
                                      onsubmit="return confirm('Recusar este saque? O valor volta para a carteira do organizador.');">
                                    <?= csrf_field() ?>
                                    <input type="text" class="form-control form-control-sm" name="motivo"
                                           placeholder="Motivo da recusa" style="max-width: 180px;">
                                    <button class="btn btn-sm btn-outline-danger">Recusar</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">
                                    <?= $saque['processado_em'] !== null ? esc(date('d/m/Y H:i', strtotime((string) $saque['processado_em']))) : '—' ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
