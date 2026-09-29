<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Presentear · <?= esc($evento->titulo) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        :root {
            --cor-primaria: <?= esc($evento->cor_primaria, 'raw') ?>;
            --cor-secundaria: <?= esc($evento->cor_secundaria, 'raw') ?>;
        }
        .hero { background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria)); color: #fff; }
        .btn-evento { background-color: var(--cor-primaria); border-color: var(--cor-primaria); color: #fff; }
        .btn-evento:hover { filter: brightness(0.92); color: #fff; }
        .titulo-evento { color: var(--cor-primaria); }
    </style>
</head>
<body class="bg-body-tertiary">
<header class="hero py-4">
    <div class="container" style="max-width: 760px;">
        <p class="text-uppercase small mb-1 opacity-75"><?= esc(str_replace('_', ' ', $evento->tipo_evento)) ?></p>
        <h1 class="h3 fw-bold mb-0"><?= esc($evento->titulo) ?></h1>
    </div>
</header>

<main class="container py-4" style="max-width: 760px;">
    <?= view('templates/partials/flash') ?>

    <a href="<?= site_url($evento->slug) ?>" class="d-inline-block mb-3 text-decoration-none">&larr; Voltar para a lista</a>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 titulo-evento mb-1"><?= esc($presente['nome']) ?></h2>
            <?php if (! empty($presente['descricao'])): ?>
                <p class="text-muted small mb-2"><?= esc($presente['descricao']) ?></p>
            <?php endif; ?>
            <p class="mb-1">
                Valor da cota: <strong><?= esc(moeda_brl($presente['valor'])) ?></strong>
                <?php if ((int) $presente['quantidade_meta'] > 1): ?>
                    <span class="text-muted small">
                        · <?= (int) $presente['quantidade_vendida'] ?>/<?= (int) $presente['quantidade_meta'] ?> cotas já presenteadas
                    </span>
                <?php endif; ?>
            </p>
            <p class="text-muted small mb-0"><?= (int) $disponiveis ?> cota(s) disponível(is).</p>
        </div>
    </div>

    <form method="post" action="<?= site_url($evento->slug . '/presentear/' . $presente['id']) ?>"
          id="form-checkout"
          data-valor-unitario="<?= esc((string) $presente['valor'], 'raw') ?>"
          data-percentual="<?= esc((string) $resumo['percentual_taxa'], 'raw') ?>"
          data-quem-paga="<?= esc($evento->quem_paga_taxa, 'raw') ?>">
        <?= csrf_field() ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="h6 text-muted text-uppercase mb-3">Seus dados</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nome">Nome completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" required
                               value="<?= esc(old('nome')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= esc(old('email')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="telefone">Telefone / WhatsApp</label>
                        <input type="text" class="form-control" id="telefone" name="telefone"
                               value="<?= esc(old('telefone')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="quantidade">Quantidade de cotas</label>
                        <input type="number" class="form-control" id="quantidade" name="quantidade"
                               min="1" max="<?= (int) $disponiveis ?>" value="<?= esc(old('quantidade') ?: '1') ?>">
                    </div>
                    <?php if ($evento->permite_recados): ?>
                        <div class="col-12">
                            <label class="form-label" for="mensagem">Mensagem para o mural <span class="text-muted">(opcional)</span></label>
                            <textarea class="form-control" id="mensagem" name="mensagem" rows="3"
                                      placeholder="Deixe um carinho que aparecerá no mural após o pagamento."><?= esc(old('mensagem')) ?></textarea>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="h6 text-muted text-uppercase mb-3">Resumo</h3>
                <dl class="row mb-0">
                    <dt class="col-7 fw-normal">Presente(s)</dt>
                    <dd class="col-5 text-end" id="resumo-presentes"><?= esc(moeda_brl($resumo['valor_presentes'])) ?></dd>

                    <dt class="col-7 fw-normal">
                        Taxa de serviço
                        <span class="text-muted small">(<?= esc(number_format($resumo['percentual_taxa'], 2, ',', '.')) ?>%)</span>
                    </dt>
                    <dd class="col-5 text-end" id="resumo-taxa"><?= esc(moeda_brl($resumo['valor_taxa'])) ?></dd>

                    <dt class="col-7 fw-semibold border-top pt-2 mt-2">Total a pagar</dt>
                    <dd class="col-5 text-end fw-bold border-top pt-2 mt-2" id="resumo-total"><?= esc(moeda_brl($resumo['valor_total'])) ?></dd>
                </dl>
            </div>
        </div>

        <button class="btn btn-evento btn-lg w-100">Gerar PIX e continuar</button>
        <p class="text-center text-muted small mt-2 mb-0">
            Pagamento via PIX processado pela plataforma. O valor é creditado ao organizador.
        </p>
    </form>
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
