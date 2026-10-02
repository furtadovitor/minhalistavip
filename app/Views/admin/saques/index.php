<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Saques pendentes</h2>
                    <i class="bi bi-hourglass-split text-warning"></i>
                </div>
                <p class="display-6 fw-bold mb-0"><?= (int) $resumo['pendentes_qtd'] ?></p>
                <p class="text-muted fs-8 mb-0"><?= esc(moeda_brl($resumo['pendentes_valor'])) ?> aguardando pagamento</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 text-muted text-uppercase mb-0 fs-8">Saques pagos</h2>
                    <i class="bi bi-check2-circle text-success"></i>
                </div>
                <p class="display-6 fw-bold mb-0"><?= (int) $resumo['pagos_qtd'] ?></p>
                <p class="text-muted fs-8 mb-0"><?= esc(moeda_brl($resumo['pagos_valor'])) ?> transferidos</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-2 fs-8">Como pagar</h2>
                <p class="text-muted fs-8 mb-3">
                    Faça a transferência pelo seu banco (chave PIX abaixo) e marque o saque como pago.
                </p>
                <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('admin') ?>">Painel do SuperAdmin</a>
            </div>
        </div>
    </div>
</div>

<form class="card border-0 shadow-sm rounded-4 mb-3" method="get" action="<?= site_url('admin/saques') ?>">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7 mb-1" for="filtro-status">Status</label>
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
                <button class="btn btn-outline-brand w-100"><i class="bi bi-funnel me-1"></i>Filtrar</button>
            </div>
        </div>
    </div>
</form>

<?php if (empty($saques)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <p class="mb-1 fw-semibold">Nenhum saque encontrado.</p>
            <p class="text-muted mb-0">Os pedidos dos organizadores aparecem aqui assim que forem solicitados.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4">
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
                    <?php
                    $status = (string) $saque['status'];
                    $repasseOk = ! empty($saque['repasse_destinatario']) && ! empty($saque['repasse_cep'])
                        && ! empty($saque['repasse_endereco']) && ! empty($saque['repasse_cidade'])
                        && ! empty($saque['repasse_estado']) && ! empty($saque['repasse_chave_pix'])
                        && ! empty($saque['organizador_cpf']);
                    $nasc   = ! empty($saque['organizador_nascimento']) ? date('d/m/Y', strtotime((string) $saque['organizador_nascimento'])) : '';
                    $cepFmt = ! empty($saque['repasse_cep']) ? preg_replace('/(\d{5})(\d{3})/', '$1-$2', (string) $saque['repasse_cep']) : '';
                    $rotulosChave = ['cpf' => 'CPF', 'cnpj' => 'CNPJ', 'email' => 'E-mail', 'telefone' => 'Telefone', 'aleatoria' => 'Aleatória'];
                    $tipoChaveLbl = $rotulosChave[$saque['repasse_tipo_chave'] ?? ''] ?? ucfirst((string) ($saque['repasse_tipo_chave'] ?? ''));
                    ?>
                    <tr>
                        <td class="small text-muted"><?= (int) $saque['id'] ?></td>
                        <td class="small">
                            <div><?= esc($saque['organizador_nome']) ?></div>
                            <div class="text-muted fs-8 mb-1"><?= esc($saque['organizador_email']) ?></div>
                            <button type="button" class="btn btn-sm <?= $repasseOk ? 'btn-outline-brand' : 'btn-outline-warning' ?>"
                                    data-bs-toggle="modal" data-bs-target="#modalRepasse"
                                    data-saque="<?= esc('#' . $saque['id'] . ' · ' . moeda_brl($saque['valor'])) ?>"
                                    data-nome="<?= esc((string) $saque['organizador_nome']) ?>"
                                    data-email="<?= esc((string) $saque['organizador_email']) ?>"
                                    data-telefone="<?= esc((string) ($saque['organizador_telefone'] ?? '')) ?>"
                                    data-cpf="<?= esc((string) ($saque['organizador_cpf'] ?? '')) ?>"
                                    data-nascimento="<?= esc($nasc) ?>"
                                    data-tipo-pagamento="<?= esc(strtoupper((string) ($saque['repasse_tipo_pagamento'] ?? ''))) ?>"
                                    data-tipo-chave="<?= esc($tipoChaveLbl) ?>"
                                    data-chave="<?= esc((string) ($saque['repasse_chave_pix'] ?? '')) ?>"
                                    data-destinatario="<?= esc((string) ($saque['repasse_destinatario'] ?? '')) ?>"
                                    data-cep="<?= esc($cepFmt) ?>"
                                    data-endereco="<?= esc((string) ($saque['repasse_endereco'] ?? '')) ?>"
                                    data-numero="<?= esc((string) ($saque['repasse_numero'] ?? '')) ?>"
                                    data-complemento="<?= esc((string) ($saque['repasse_complemento'] ?? '')) ?>"
                                    data-bairro="<?= esc((string) ($saque['repasse_bairro'] ?? '')) ?>"
                                    data-cidade="<?= esc((string) ($saque['repasse_cidade'] ?? '')) ?>"
                                    data-estado="<?= esc((string) ($saque['repasse_estado'] ?? '')) ?>">
                                <i class="bi bi-person-vcard me-1"></i>Dados de repasse
                                <?php if (! $repasseOk): ?><span class="badge text-bg-warning ms-1">incompleto</span><?php endif; ?>
                            </button>
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
                                          onsubmit="return confirm('Confirmar que o PIX de <?= esc(moeda_brl($saque['valor'])) ?> foi transferido?');">
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

<div class="modal fade" id="modalRepasse" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-bold mb-0">Dados para o repasse</h5>
                    <p class="text-muted fs-8 mb-0" id="rp-saque"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-0">
                <div id="rp-aviso" class="alert alert-warning rounded-4 d-none">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    O organizador ainda não preencheu todos os dados de repasse. Confirme os dados antes de pagar.
                </div>

                <button type="button" class="btn btn-sm btn-outline-brand mb-3" id="rp-copiar">
                    <i class="bi bi-clipboard me-1"></i>Copiar dados
                </button>

                <h6 class="text-uppercase text-muted fs-8 fw-semibold">Responsável</h6>
                <dl class="row mb-3 fs-7">
                    <dt class="col-4 col-md-3 text-muted fw-normal">Nome</dt><dd class="col-8 col-md-9 mb-1" id="rp-nome">—</dd>
                    <dt class="col-4 col-md-3 text-muted fw-normal">E-mail</dt><dd class="col-8 col-md-9 mb-1" id="rp-email">—</dd>
                    <dt class="col-4 col-md-3 text-muted fw-normal">Telefone</dt><dd class="col-8 col-md-9 mb-1" id="rp-telefone">—</dd>
                    <dt class="col-4 col-md-3 text-muted fw-normal">CPF/CNPJ</dt><dd class="col-8 col-md-9 mb-1" id="rp-cpf">—</dd>
                    <dt class="col-4 col-md-3 text-muted fw-normal">Nascimento</dt><dd class="col-8 col-md-9 mb-0" id="rp-nascimento">—</dd>
                </dl>

                <h6 class="text-uppercase text-muted fs-8 fw-semibold">Dados bancários</h6>
                <dl class="row mb-3 fs-7">
                    <dt class="col-4 col-md-3 text-muted fw-normal">Tipo</dt><dd class="col-8 col-md-9 mb-1" id="rp-tipo-pagamento">—</dd>
                    <dt class="col-4 col-md-3 text-muted fw-normal">Tipo da chave</dt><dd class="col-8 col-md-9 mb-1" id="rp-tipo-chave">—</dd>
                    <dt class="col-4 col-md-3 text-muted fw-normal">Chave</dt><dd class="col-8 col-md-9 mb-0 fw-semibold" id="rp-chave">—</dd>
                </dl>

                <h6 class="text-uppercase text-muted fs-8 fw-semibold">Endereço de correspondência</h6>
                <dl class="row mb-0 fs-7">
                    <dt class="col-4 col-md-3 text-muted fw-normal">Destinatário</dt><dd class="col-8 col-md-9 mb-1" id="rp-destinatario">—</dd>
                    <dt class="col-4 col-md-3 text-muted fw-normal">CEP</dt><dd class="col-8 col-md-9 mb-1" id="rp-cep">—</dd>
                    <dt class="col-4 col-md-3 text-muted fw-normal">Endereço</dt><dd class="col-8 col-md-9 mb-1" id="rp-endereco">—</dd>
                    <dt class="col-4 col-md-3 text-muted fw-normal">Bairro</dt><dd class="col-8 col-md-9 mb-1" id="rp-bairro">—</dd>
                    <dt class="col-4 col-md-3 text-muted fw-normal">Cidade/UF</dt><dd class="col-8 col-md-9 mb-0" id="rp-cidade">—</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('modalRepasse');
    if (!modal) { return; }

    function fmtCpf(v) {
        v = (v || '').replace(/\D/g, '');
        if (v.length === 11) { return v.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4'); }
        if (v.length === 14) { return v.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5'); }
        return v || '—';
    }
    function fmtTel(v) {
        v = (v || '').replace(/\D/g, '');
        if (v.length === 11) { return v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3'); }
        if (v.length === 10) { return v.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3'); }
        return v || '—';
    }
    function set(id, val) {
        var el = document.getElementById(id);
        if (el) { el.textContent = (val === null || val === undefined || val === '') ? '—' : val; }
    }

    var atual = {};

    modal.addEventListener('show.bs.modal', function (ev) {
        var b = ev.relatedTarget;
        if (!b) { return; }
        var d = b.dataset;
        atual = d;

        set('rp-saque', d.saque);
        set('rp-nome', d.nome);
        set('rp-email', d.email);
        set('rp-telefone', fmtTel(d.telefone));
        set('rp-cpf', fmtCpf(d.cpf));
        set('rp-nascimento', d.nascimento);
        set('rp-tipo-pagamento', d.tipoPagamento);
        set('rp-tipo-chave', d.tipoChave);
        set('rp-chave', d.chave);
        set('rp-destinatario', d.destinatario);
        set('rp-cep', d.cep);
        set('rp-endereco', [d.endereco, d.numero, d.complemento].filter(Boolean).join(', '));
        set('rp-bairro', d.bairro);
        set('rp-cidade', [d.cidade, d.estado].filter(Boolean).join('/'));

        var incompleto = ! d.chave || ! d.endereco || ! d.cep || ! d.cidade;
        var aviso = document.getElementById('rp-aviso');
        if (aviso) { aviso.classList.toggle('d-none', ! incompleto); }
    });

    var copiar = document.getElementById('rp-copiar');
    if (copiar) {
        copiar.addEventListener('click', function () {
            var linhas = [
                'Responsável: ' + (atual.nome || ''),
                'E-mail: ' + (atual.email || ''),
                'Telefone: ' + fmtTel(atual.telefone),
                'CPF/CNPJ: ' + fmtCpf(atual.cpf),
                'Nascimento: ' + (atual.nascimento || ''),
                'Pagamento: ' + (atual.tipoPagamento || '') + ' / ' + (atual.tipoChave || '') + ' / ' + (atual.chave || ''),
                'Endereço: ' + [atual.endereco, atual.numero, atual.complemento, atual.bairro]
                    .filter(Boolean).join(', ') + ' - ' + [atual.cidade, atual.estado].filter(Boolean).join('/'),
                'CEP: ' + (atual.cep || ''),
            ];
            var antigo = copiar.innerHTML;
            navigator.clipboard.writeText(linhas.join('\n')).then(function () {
                copiar.innerHTML = '<i class="bi bi-check2 me-1"></i>Copiado!';
                setTimeout(function () { copiar.innerHTML = antigo; }, 1500);
            });
        });
    }
})();
</script>

<?= $this->endSection() ?>
