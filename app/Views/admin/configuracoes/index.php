<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<?php
    $rotulos = ['geral' => 'Geral', 'financeiro' => 'Financeiro e taxas', 'pix' => 'PIX e recebimento'];
    $opcoesSelect = [
        'pix_gateway' => ['sandbox' => 'Sandbox (interno)', 'mercadopago' => 'Mercado Pago'],
    ];
    $tipoCampo = static function (string $chave): array {
        if (str_contains($chave, 'token') || str_contains($chave, 'secret') || str_contains($chave, 'access_token')) {
            return ['password', null];
        }
        if (str_contains($chave, 'percentual') || str_contains($chave, 'valor_minimo')) {
            return ['number', '0.01'];
        }
        if (str_contains($chave, 'minutos')) {
            return ['number', '1'];
        }
        return ['text', null];
    };
?>

<form method="post" action="<?= site_url('admin/configuracoes') ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <?php foreach ($grupos as $grupo => $itens): ?>
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body p-4">
                        <h2 class="h6 text-uppercase text-muted fw-semibold mb-3">
                            <?= esc($rotulos[$grupo] ?? ucfirst($grupo)) ?>
                        </h2>

                        <?php foreach ($itens as $indice => $item): ?>
                            <?php [$tipo, $passo] = $tipoCampo((string) $item['chave']); ?>
                            <div class="<?= $indice < count($itens) - 1 ? 'mb-3' : '' ?>">
                                <label class="form-label fw-semibold fs-7" for="<?= esc($item['chave'], 'attr') ?>">
                                    <?= esc($item['descricao'] ?: $item['chave']) ?>
                                </label>
                                <?php if (isset($opcoesSelect[$item['chave']])): ?>
                                    <select class="form-select" id="<?= esc($item['chave'], 'attr') ?>"
                                            name="<?= esc($item['chave'], 'attr') ?>">
                                        <?php foreach ($opcoesSelect[$item['chave']] as $opcaoValor => $opcaoTexto): ?>
                                            <option value="<?= esc($opcaoValor, 'attr') ?>"
                                                <?= (string) $item['valor'] === $opcaoValor ? 'selected' : '' ?>>
                                                <?= esc($opcaoTexto) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <input type="<?= $tipo ?>" <?= $passo !== null ? 'step="' . $passo . '"' : '' ?>
                                           class="form-control" id="<?= esc($item['chave'], 'attr') ?>"
                                           name="<?= esc($item['chave'], 'attr') ?>"
                                           value="<?= esc((string) $item['valor']) ?>">
                                <?php endif; ?>
                                <div class="form-text"><code><?= esc($item['chave']) ?></code></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4" style="position: sticky; top: 5rem;">
                <div class="card-body p-4">
                    <p class="text-muted fs-7">
                        As alterações valem para toda a plataforma. A taxa padrão é usada quando o
                        evento não define um percentual próprio.
                    </p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-brand"><i class="bi bi-check2 me-1"></i>Salvar configurações</button>
                        <a class="btn btn-outline-secondary" href="<?= site_url('admin') ?>">Voltar ao painel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
