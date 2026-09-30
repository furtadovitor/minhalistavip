<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?= view('templates/partials/design_evento', [
        'titulo'        => 'Presentear · ' . $evento->titulo,
        'corPrimaria'   => $evento->cor_primaria,
        'corSecundaria' => $evento->cor_secundaria,
        'tema'          => $evento->tema ?? 'classico',
    ]) ?>
</head>
<body class="bg-body-tertiary">
<header class="hero py-4">
    <div class="container" style="max-width: 900px;">
        <p class="text-uppercase small mb-1 opacity-75"><?= esc(str_replace('_', ' ', $evento->tipo_evento)) ?></p>
        <h1 class="h3 fw-bold mb-0"><?= esc($evento->titulo) ?></h1>
    </div>
</header>

<main class="container py-4" style="max-width: 900px;">
    <?= view('templates/partials/flash') ?>

    <a href="<?= site_url($evento->slug) ?>" class="d-inline-block mb-3 text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i>Voltar para a lista
    </a>

    <div class="row g-4">
        <div class="col-lg-7">
            <!-- Presente escolhido -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center text-white flex-shrink-0"
                             style="width:52px;height:52px;background-color: var(--cor-primaria);">
                            <i class="bi bi-gift-fill fs-4"></i>
                        </div>
                        <div>
                            <h2 class="h5 mb-1"><?= esc($presente['nome']) ?></h2>
                            <?php if (! empty($presente['descricao'])): ?>
                                <p class="text-muted fs-7 mb-2"><?= esc($presente['descricao']) ?></p>
                            <?php endif; ?>
                            <p class="mb-0 fs-7">
                                Valor da cota: <strong><?= esc(moeda_brl($presente['valor'])) ?></strong>
                                <span class="text-muted">
                                    · <?= (int) $presente['quantidade_vendida'] ?>/<?= (int) $presente['quantidade_meta'] ?> cotas presenteadas
                                    · <?= (int) $disponiveis ?> disponível(is)
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <form method="post" action="<?= site_url($evento->slug . '/presentear/' . $presente['id']) ?>"
                  id="form-checkout"
                  data-valor-unitario="<?= esc((string) $presente['valor'], 'raw') ?>"
                  data-percentual="<?= esc((string) $resumo['percentual_taxa'], 'raw') ?>"
                  data-quem-paga="<?= esc($evento->quem_paga_taxa, 'raw') ?>">
                <?= csrf_field() ?>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h6 text-uppercase text-muted fw-semibold mb-3">Seus dados</h3>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-7" for="nome">Nome completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome" name="nome" required
                                       value="<?= esc(old('nome')) ?>" placeholder="Seu nome">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-7" for="email">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= esc(old('email')) ?>" placeholder="voce@email.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-7" for="telefone">Telefone / WhatsApp</label>
                                <input type="text" class="form-control" id="telefone" name="telefone"
                                       value="<?= esc(old('telefone')) ?>" placeholder="(11) 99999-9999">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-7" for="quantidade">Quantidade de cotas</label>
                                <input type="number" class="form-control" id="quantidade" name="quantidade"
                                       min="1" max="<?= (int) $disponiveis ?>" value="<?= esc(old('quantidade') ?: '1') ?>">
                            </div>
                            <?php if ($evento->permite_recados): ?>
                                <div class="col-12">
                                    <label class="form-label fw-semibold fs-7" for="mensagem">
                                        Mensagem para o mural <span class="text-muted fw-normal">(opcional)</span>
                                    </label>
                                    <textarea class="form-control" id="mensagem" name="mensagem" rows="3"
                                              placeholder="Deixe um carinho que aparecerá no mural após o pagamento."><?= esc(old('mensagem')) ?></textarea>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button class="btn btn-evento btn-lg py-3">
                        <i class="bi bi-qr-code me-2"></i>Gerar PIX e continuar
                    </button>
                </div>
                <p class="text-center text-muted fs-8 mt-2 mb-0">
                    Pagamento processado com segurança. O valor é creditado ao organizador.
                </p>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm" style="position: sticky; top: 1.5rem;">
                <div class="card-body p-4">
                    <h3 class="h6 text-uppercase text-muted fw-semibold mb-3">Resumo</h3>
                    <dl class="row mb-0">
                        <dt class="col-7 fw-normal">Presente(s)</dt>
                        <dd class="col-5 text-end" id="resumo-presentes"><?= esc(moeda_brl($resumo['valor_presentes'])) ?></dd>

                        <dt class="col-7 fw-normal">
                            Taxa de serviço
                            <span class="text-muted fs-8">(<?= esc(number_format($resumo['percentual_taxa'], 2, ',', '.')) ?>%)</span>
                        </dt>
                        <dd class="col-5 text-end" id="resumo-taxa"><?= esc(moeda_brl($resumo['valor_taxa'])) ?></dd>

                        <dt class="col-7 fw-semibold border-top pt-3 mt-3">Total a pagar</dt>
                        <dd class="col-5 text-end fw-bold border-top pt-3 mt-3" id="resumo-total"><?= esc(moeda_brl($resumo['valor_total'])) ?></dd>
                    </dl>

                    <div class="d-flex align-items-center gap-2 text-muted fs-8 mt-3">
                        <i class="bi bi-shield-lock"></i>
                        <span>Pagamento via PIX com confirmação automática.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="text-center text-muted small py-4">
    Página criada com <a href="<?= site_url('/') ?>" class="text-decoration-none">Minha Lista VIP</a>
</footer>

<script>
(function () {
    const form = document.getElementById('form-checkout');
    if (!form) return;

    const unitario = parseFloat(form.dataset.valorUnitario) || 0;
    const quemPaga = form.dataset.quemPaga || 'convidado';
    const percentual = parseFloat(form.dataset.percentual) || 0;

    const input = document.getElementById('quantidade');
    const fmt = (v) => 'R$ ' + v.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    function atualizar() {
        const qtd = Math.max(1, parseInt(input.value || '1', 10));
        const presentes = unitario * qtd;
        const taxa = Math.round(presentes * (percentual / 100) * 100) / 100;
        const total = quemPaga === 'convidado' ? presentes + taxa : presentes;

        document.getElementById('resumo-presentes').textContent = fmt(presentes);
        document.getElementById('resumo-taxa').textContent = fmt(taxa);
        document.getElementById('resumo-total').textContent = fmt(total);
    }

    input.addEventListener('input', atualizar);
    atualizar();
})();
</script>
</body>
</html>
