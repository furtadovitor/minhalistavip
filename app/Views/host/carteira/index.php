<?= $this->extend('templates/layouts/app') ?>

<?php
$u = $usuario;
$val = static function (string $campo, $padrao = '') use ($u) {
    return old($campo, $u->{$campo} ?? $padrao);
};
$dataNasc = old('data_nascimento', ($u !== null && $u->data_nascimento !== null) ? $u->data_nascimento->format('Y-m-d') : '');

$ufs = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'];

$podeResgatar = $repasseCompleto && $saldo >= $valorMinimo;
$motivoBloqueio = null;
if (! $repasseCompleto) {
    $motivoBloqueio = 'Preencha os dados de repasse acima para habilitar o resgate.';
} elseif ($saldo < $valorMinimo) {
    $motivoBloqueio = 'O valor mínimo para resgate é ' . moeda_brl($valorMinimo) . '.';
}
?>

<?= $this->section('conteudo') ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon bg-indigo-100 text-indigo-700"><i class="bi bi-wallet2"></i></span>
                <div>
                    <p class="text-muted text-uppercase fs-8 fw-semibold mb-0">Disponível</p>
                    <p class="h4 fw-bold mb-0"><?= esc(moeda_brl($saldo)) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon" style="background:#FFFBEB;color:#B45309;"><i class="bi bi-hourglass-split"></i></span>
                <div>
                    <p class="text-muted text-uppercase fs-8 fw-semibold mb-0">Aguardando liberação</p>
                    <p class="h5 fw-bold mb-0"><?= esc(moeda_brl($aguardandoLiberacao)) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon" style="background:#ECFDF5;color:#047857;"><i class="bi bi-graph-up-arrow"></i></span>
                <div>
                    <p class="text-muted text-uppercase fs-8 fw-semibold mb-0">Arrecadado</p>
                    <p class="h5 fw-bold mb-0"><?= esc(moeda_brl($arrecadado)) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon" style="background:#F3F4F6;color:#4B5563;"><i class="bi bi-percent"></i></span>
                <div>
                    <p class="text-muted text-uppercase fs-8 fw-semibold mb-0">Taxas da plataforma</p>
                    <p class="h5 fw-bold mb-0"><?= esc(moeda_brl($taxas)) ?></p>
                    <p class="text-muted fs-8 mb-0">Resgatado: <?= esc(moeda_brl($sacado)) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('templates/partials/flash') ?>

<div class="alert alert-light border rounded-4 d-flex gap-2 mb-4 align-items-start">
    <i class="bi bi-shield-lock fs-5 text-brand"></i>
    <div class="fs-7 text-secondary">
        Não se preocupe: esses dados são usados pela plataforma para o processamento do repasse para a sua conta e
        <strong>nunca aparecem no site do evento</strong> para os seus convidados.
    </div>
</div>

<form method="post" action="<?= site_url('painel/carteira/repasse') ?>" class="row g-3 mb-4">
    <?= csrf_field() ?>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Dados do Responsável do Evento</h2>

                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7" for="r-nome">Seu nome completo *</label>
                    <input type="text" class="form-control" id="r-nome" name="nome" value="<?= esc($val('nome')) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7" for="r-email">E-mail *</label>
                    <input type="email" class="form-control" id="r-email" name="email" value="<?= esc($val('email')) ?>" required>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold fs-7" for="r-telefone">Telefone *</label>
                        <input type="text" class="form-control" id="r-telefone" name="telefone"
                               value="<?= esc($val('telefone')) ?>" placeholder="(21) 99100-5822" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold fs-7" for="r-cpf">CPF *</label>
                        <input type="text" class="form-control" id="r-cpf" name="cpf_cnpj"
                               value="<?= esc($val('cpf_cnpj')) ?>" placeholder="000.000.000-00" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold fs-7" for="r-nasc">Data de nascimento *</label>
                        <input type="date" class="form-control" id="r-nasc" name="data_nascimento"
                               value="<?= esc($dataNasc) ?>" required>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Dados Bancários para Transferência</h2>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold fs-7" for="r-tipo-pag">Tipo do Pagamento *</label>
                        <select class="form-select" id="r-tipo-pag" name="tipo_pagamento">
                            <option value="pix" <?= $val('tipo_pagamento', 'pix') === 'pix' ? 'selected' : '' ?>>PIX</option>
                            <option value="ted" <?= $val('tipo_pagamento') === 'ted' ? 'selected' : '' ?>>TED</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold fs-7" for="r-tipo-chave">Tipo da Chave *</label>
                        <select class="form-select" id="r-tipo-chave" name="tipo_chave_pix">
                            <?php foreach ([
                                'cpf' => 'CPF', 'cnpj' => 'CNPJ', 'email' => 'E-mail',
                                'telefone' => 'Telefone', 'aleatoria' => 'Aleatória',
                            ] as $chave => $rotulo): ?>
                                <option value="<?= esc($chave) ?>" <?= $val('tipo_chave_pix') === $chave ? 'selected' : '' ?>>
                                    <?= esc($rotulo) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold fs-7" for="r-chave">Chave *</label>
                        <input type="text" class="form-control" id="r-chave" name="chave_pix"
                               value="<?= esc($val('chave_pix')) ?>" required>
                    </div>
                </div>

                <p class="text-muted fs-8 mb-0 mt-3">
                    <i class="bi bi-info-circle me-1"></i>
                    As informações bancárias devem, preferencialmente, ser do próprio responsável do evento.
                </p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Endereço de Correspondência</h2>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold fs-7" for="r-dest">Destinatário/Responsável *</label>
                        <input type="text" class="form-control" id="r-dest" name="destinatario"
                               value="<?= esc($val('destinatario')) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7" for="r-cep">CEP *</label>
                        <input type="text" class="form-control" id="r-cep" name="cep"
                               value="<?= esc($val('cep')) ?>" placeholder="00000-000" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold fs-7" for="r-end">Endereço *</label>
                        <input type="text" class="form-control" id="r-end" name="endereco"
                               value="<?= esc($val('endereco')) ?>" required>
                    </div>
                    <div class="col-4 col-md-3">
                        <label class="form-label fw-semibold fs-7" for="r-num">Número</label>
                        <input type="text" class="form-control" id="r-num" name="numero" value="<?= esc($val('numero')) ?>">
                    </div>
                    <div class="col-8 col-md-5">
                        <label class="form-label fw-semibold fs-7" for="r-comp">Complemento</label>
                        <input type="text" class="form-control" id="r-comp" name="complemento" value="<?= esc($val('complemento')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7" for="r-bairro">Bairro *</label>
                        <input type="text" class="form-control" id="r-bairro" name="bairro"
                               value="<?= esc($val('bairro')) ?>" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold fs-7" for="r-cidade">Cidade *</label>
                        <input type="text" class="form-control" id="r-cidade" name="cidade"
                               value="<?= esc($val('cidade')) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7" for="r-estado">Estado *</label>
                        <select class="form-select" id="r-estado" name="estado" required>
                            <option value="">UF</option>
                            <?php foreach ($ufs as $uf): ?>
                                <option value="<?= $uf ?>" <?= $val('estado') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <p class="text-muted fs-8 mb-0 mt-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Este será o endereço para envio de mimos e correspondências da plataforma.
                </p>
            </div>
        </div>
    </div>

    <div class="col-12">
        <button class="btn btn-brand">
            <i class="bi bi-check-lg me-1"></i>Salvar dados de repasse
        </button>
        <?php if ($repasseCompleto): ?>
            <span class="text-success fs-8 ms-2"><i class="bi bi-patch-check-fill me-1"></i>Dados completos</span>
        <?php else: ?>
            <span class="text-warning fs-8 ms-2"><i class="bi bi-exclamation-circle me-1"></i>Preencha para habilitar o resgate</span>
        <?php endif; ?>
    </div>
</form>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <h2 class="h6 text-uppercase text-muted mb-2">Valores aguardando liberação</h2>
        <?php if ($aguardandoLiberacao > 0): ?>
            <p class="h4 fw-bold mb-1"><?= esc(moeda_brl($aguardandoLiberacao)) ?></p>
            <p class="text-muted fs-8 mb-0">
                Presentes pagos via cartão de crédito que estão dentro do prazo de liberação da operadora
                (<?= (int) $prazoLiberacao ?> dias).
            </p>
        <?php else: ?>
            <p class="text-muted mb-0">Você não possui valor aguardando liberação.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <div>
                <h2 class="h6 text-uppercase text-muted mb-1">Solicitar resgate dos valores disponíveis</h2>
                <p class="text-muted fs-8 mb-0">Valor mínimo: <?= esc(moeda_brl($valorMinimo)) ?>.</p>
            </div>
            <div class="text-end">
                <p class="text-muted fs-8 text-uppercase mb-0">Disponível para resgate</p>
                <p class="h4 fw-bold mb-0"><?= esc(moeda_brl($saldo)) ?></p>
            </div>
        </div>

        <form method="post" action="<?= site_url('painel/carteira/saque') ?>" class="d-flex flex-wrap gap-2 align-items-center">
            <?= csrf_field() ?>
            <input type="hidden" name="valor" value="<?= esc(number_format($saldo, 2, '.', ''), 'attr') ?>">
            <button class="btn btn-brand btn-lg" <?= $podeResgatar ? '' : 'disabled' ?>>
                <i class="bi bi-cash-coin me-1"></i>Solicitar resgate disponível
            </button>
            <a class="btn btn-outline-secondary" href="<?= site_url('painel/carteira/saque') ?>">Outro valor</a>
        </form>

        <?php if ($motivoBloqueio !== null): ?>
            <p class="text-warning fs-8 mb-0 mt-2"><i class="bi bi-exclamation-circle me-1"></i><?= esc($motivoBloqueio) ?></p>
        <?php else: ?>
            <p class="text-muted fs-8 mb-0 mt-2">
                <i class="bi bi-clock me-1"></i>Após o pedido, nossa equipe costuma realizar o pagamento em até 24 horas úteis.
            </p>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3">
        <h2 class="h6 mb-0 text-muted text-uppercase fs-8">Status de resgates solicitados</h2>
    </div>
    <?php if (empty($saques)): ?>
        <div class="card-body text-center py-4">
            <p class="text-muted mb-0">Você ainda não fez nenhuma solicitação de resgate.</p>
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
                        <td class="text-end fw-semibold"><?= esc(moeda_brl($saque['valor'])) ?></td>
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

<div class="card border-0 shadow-sm rounded-4">
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

<?= $this->endSection() ?>
