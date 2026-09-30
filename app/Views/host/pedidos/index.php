<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<?php
    $mapaEventos = [];
    foreach ($eventos as $evento) {
        $mapaEventos[(int) $evento->id] = ['titulo' => $evento->titulo, 'slug' => $evento->slug];
    }
    $statusOpcoes = ['', 'pendente', 'pago', 'cancelado', 'expirado', 'reembolsado'];
?>

<form class="card border-0 shadow-sm rounded-4 mb-3" method="get" action="<?= site_url('painel/pedidos') ?>">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold fs-7 mb-1" for="filtro-evento">Evento</label>
                <select class="form-select" id="filtro-evento" name="evento">
                    <option value="">Todos</option>
                    <?php foreach ($eventos as $evento): ?>
                        <option value="<?= (int) $evento->id ?>" <?= (string) ($filtros['evento_id'] ?? '') === (string) $evento->id ? 'selected' : '' ?>>
                            <?= esc($evento->titulo) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7 mb-1" for="filtro-status">Status</label>
                <select class="form-select" id="filtro-status" name="status">
                    <?php foreach ($statusOpcoes as $opcao): ?>
                        <option value="<?= esc($opcao) ?>" <?= (string) ($filtros['status'] ?? '') === $opcao ? 'selected' : '' ?>>
                            <?= $opcao === '' ? 'Todos' : esc(rotulo_status_pedido($opcao)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-brand w-100"><i class="bi bi-funnel me-1"></i>Filtrar</button>
            </div>
        </div>
    </div>
</form>

<?php if (empty($pedidos)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <div class="bg-indigo-100 text-indigo-700 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:64px;height:64px;"><i class="bi bi-bag-check fs-3"></i></div>
            <p class="mb-1 fw-semibold">Nenhum pedido encontrado.</p>
            <p class="text-muted mb-0">Compartilhe o link do seu evento para começar a receber presentes.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Pedido</th>
                        <th>Evento</th>
                        <th>Convidado</th>
                        <th class="text-center">Cotas</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td class="small">
                            <?php $slugEvento = $mapaEventos[(int) $pedido->evento_id]['slug'] ?? null; ?>
                            <?php if ($slugEvento !== null): ?>
                                <a href="<?= site_url($slugEvento . '/pedido/' . $pedido->protocolo) ?>"
                                   target="_blank" class="text-decoration-none fw-semibold"><?= esc($pedido->protocolo) ?></a>
                            <?php else: ?>
                                <?= esc($pedido->protocolo) ?>
                            <?php endif; ?>
                        </td>
                        <td class="small"><?= esc($mapaEventos[(int) $pedido->evento_id]['titulo'] ?? '—') ?></td>
                        <td class="small">
                            <div><?= esc($pedido->nome_convidado) ?></div>
                            <?php if (! empty($pedido->email_convidado)): ?>
                                <div class="text-muted fs-8"><?= esc($pedido->email_convidado) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center small"><?= (int) $pedido->quantidade ?></td>
                        <td class="text-end"><?= esc(moeda_brl($pedido->valor_total)) ?></td>
                        <td>
                            <span class="badge text-bg-<?= cor_status_pedido((string) $pedido->status) ?>">
                                <?= esc(rotulo_status_pedido((string) $pedido->status)) ?>
                            </span>
                        </td>
                        <td class="small text-muted">
                            <?= $pedido->criado_em !== null ? esc($pedido->criado_em->format('d/m/Y H:i')) : '—' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
