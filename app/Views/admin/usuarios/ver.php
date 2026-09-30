<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <p class="text-muted mb-0">
            <?= esc($alvo->email) ?> ·
            <span class="badge text-bg-<?= $alvo->status === 'ativo' ? 'success' : 'danger' ?>">
                <?= esc(ucfirst((string) $alvo->status)) ?>
            </span>
            <span class="badge text-bg-light border text-dark"><?= esc(ucfirst($alvo->nivel)) ?></span>
        </p>
        <p class="text-muted fs-8 mb-0">
            Cadastro em <?= $alvo->criado_em !== null ? esc($alvo->criado_em->format('d/m/Y')) : '—' ?>
        </p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= site_url('admin/usuarios') ?>">Voltar</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-1 fs-8">Arrecadado</h2>
                <p class="h3 fw-bold mb-0"><?= esc(moeda_brl($arrecadado)) ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-1 fs-8">Saldo</h2>
                <p class="h3 fw-bold mb-0"><?= esc(moeda_brl($saldo)) ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-1 fs-8">Taxas da plataforma</h2>
                <p class="h3 fw-bold mb-0"><?= esc(moeda_brl($taxas)) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-header bg-white border-0 pt-3">
        <h2 class="h6 mb-0 text-muted text-uppercase fs-8">Eventos (<?= count($eventos) ?>)</h2>
    </div>
    <?php if (empty($eventos)): ?>
        <div class="card-body text-center py-4"><p class="text-muted mb-0">Nenhum evento cadastrado.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Evento</th><th>Slug</th><th>Status</th><th class="text-end">Ações</th></tr>
                </thead>
                <tbody>
                <?php foreach ($eventos as $evento): ?>
                    <tr>
                        <td class="small"><?= esc($evento->titulo) ?></td>
                        <td class="small text-muted"><?= esc($evento->slug) ?></td>
                        <td>
                            <span class="badge text-bg-<?= cor_status_evento($evento->status) ?>">
                                <?= esc(rotulo_status_evento($evento->status)) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php if ($evento->status !== 'rascunho'): ?>
                                <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= site_url($evento->slug) ?>">Ver página</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-3">
        <h2 class="h6 mb-0 text-muted text-uppercase fs-8">Saques (<?= count($saques) ?>)</h2>
    </div>
    <?php if (empty($saques)): ?>
        <div class="card-body text-center py-4"><p class="text-muted mb-0">Nenhum saque solicitado.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>#</th><th>Chave PIX</th><th class="text-end">Valor</th><th>Status</th><th>Solicitado</th></tr>
                </thead>
                <tbody>
                <?php foreach ($saques as $saque): ?>
                    <tr>
                        <td class="small text-muted"><?= (int) $saque['id'] ?></td>
                        <td class="small"><?= esc((string) $saque['chave_pix']) ?></td>
                        <td class="text-end"><?= esc(moeda_brl($saque['valor'])) ?></td>
                        <td>
                            <span class="badge text-bg-<?= cor_status_saque((string) $saque['status']) ?>">
                                <?= esc(rotulo_status_saque((string) $saque['status'])) ?>
                            </span>
                        </td>
                        <td class="small text-muted">
                            <?= $saque['solicitado_em'] !== null ? esc(date('d/m/Y H:i', strtotime((string) $saque['solicitado_em']))) : '—' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
