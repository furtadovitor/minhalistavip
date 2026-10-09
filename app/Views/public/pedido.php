<?php

/**
 * @var array<string, mixed>|null $cobranca
 * @var \App\Entities\Evento      $evento
 * @var \App\Entities\Pedido      $pedido
 * @var bool                      $bricks
 * @var bool                      $simulacao
 * @var string                    $public_key
 * @var array<string, mixed>|null $presente
 */
$pagamentoPendente = is_array($cobranca)
    && ($cobranca['status'] ?? null) === 'pendente'
    && ! empty($cobranca['gateway_transacao_id']);

$partes  = preg_split('/\s+/', trim((string) $pedido->nome_convidado), 2) ?: [];
$payer   = ['firstName' => $partes[0] ?? 'Convidado'];

if (! empty($partes[1])) {
    $payer['lastName'] = $partes[1];
}

if (filter_var((string) $pedido->email_convidado, FILTER_VALIDATE_EMAIL)) {
    $payer['email'] = (string) $pedido->email_convidado;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?= view('templates/partials/design_evento', [
        'titulo'        => 'Pedido ' . $pedido->protocolo . ' · ' . $evento->titulo,
        'corPrimaria'   => $evento->cor_primaria,
        'corSecundaria' => $evento->cor_secundaria,
        'tema'          => $evento->tema ?? 'classico',
        'seo'           => ['noindex' => true],
    ]) ?>
</head>
<body class="bg-body-tertiary">
<?= view('templates/partials/analytics_body') ?>
<header class="hero py-4">
    <div class="container" style="max-width: 760px;">
        <p class="text-uppercase small mb-1 opacity-75"><?= esc($evento->titulo) ?></p>
        <h1 class="h3 fw-bold mb-0">Pedido <?= esc($pedido->protocolo) ?></h1>
    </div>
</header>

<main class="container py-4" style="max-width: 760px;">
    <?= view('templates/partials/flash') ?>

    <div class="text-center mb-4">
        <span class="badge text-bg-<?= cor_status_pedido($pedido->status) ?> fs-6 px-3 py-2">
            <?= esc(rotulo_status_pedido($pedido->status)) ?>
        </span>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h6 text-uppercase text-muted fw-semibold mb-3">Resumo do pedido</h2>
            <dl class="row mb-0">
                <dt class="col-7 fw-normal">Convidado</dt>
                <dd class="col-5 text-end"><?= esc($pedido->nome_convidado) ?></dd>

                <?php if ($presente !== null): ?>
                    <dt class="col-7 fw-normal">Presente</dt>
                    <dd class="col-5 text-end"><?= esc($presente['nome']) ?></dd>
                <?php endif; ?>

                <dt class="col-7 fw-normal">Quantidade</dt>
                <dd class="col-5 text-end"><?= (int) $pedido->quantidade ?></dd>

                <dt class="col-7 fw-normal">Presente(s)</dt>
                <dd class="col-5 text-end"><?= esc(moeda_brl($pedido->valor_presentes)) ?></dd>

                <dt class="col-7 fw-normal">
                    Taxa de serviço
                    <span class="text-muted fs-8">(<?= esc(number_format((float) $pedido->percentual_taxa, 2, ',', '.')) ?>%)</span>
                </dt>
                <dd class="col-5 text-end"><?= esc(moeda_brl($pedido->valor_taxa)) ?></dd>

                <dt class="col-7 fw-semibold border-top pt-3 mt-3">Total</dt>
                <dd class="col-5 text-end fw-bold border-top pt-3 mt-3"><?= esc(moeda_brl($pedido->valor_total)) ?></dd>
            </dl>
        </div>
    </div>

    <?php if ($pedido->estaPago()): ?>
        <div class="alert alert-success rounded-4">
            <strong><i class="bi bi-check-circle-fill me-1"></i>Pagamento confirmado!</strong>
            Muito obrigado pelo carinho.
            <?php if (! empty($pedido->mensagem)): ?> Sua mensagem já está no mural do evento.<?php endif; ?>
        </div>
        <a class="btn btn-evento w-100 py-2" href="<?= site_url($evento->slug) ?>">Voltar para o evento</a>

    <?php elseif ($pedido->foiCancelado()): ?>
        <div class="alert alert-warning rounded-4 mb-0">
            Este pedido não está mais ativo. Se precisar, faça um novo pedido.
        </div>

    <?php elseif ($pedido->expirado()): ?>
        <div class="alert alert-warning rounded-4 mb-0">
            Este pedido expirou antes do pagamento. Volte ao evento e faça um novo pedido.
        </div>

    <?php elseif ($bricks): ?>
        <div class="card border-0 shadow-sm mb-4" id="card-pagamento">
            <div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted fw-semibold mb-3">Pague com PIX ou cartão</h2>
                <div id="paymentBrick_container"></div>
            </div>
        </div>

        <div id="statusScreenBrick_container" class="<?= $pagamentoPendente ? '' : 'd-none' ?> mb-4"></div>

        <div class="alert alert-danger rounded-4 d-none" id="payment-erro"></div>

        <div class="alert alert-info rounded-4">
            <i class="bi bi-info-circle me-1"></i>Após o pagamento, a confirmação é automática.
            Atualize esta página em alguns instantes.
        </div>

        <a class="btn btn-outline-evento w-100 mb-3" href="<?= site_url($evento->slug . '/pedido/' . $pedido->protocolo . '?atualizar=1') ?>">
            <i class="bi bi-arrow-clockwise me-1"></i>Já paguei, atualizar status
        </a>

        <?php if ($simulacao): ?>
            <div class="card border-warning mb-4">
                <div class="card-body p-4">
                    <p class="fw-semibold mb-1"><i class="bi bi-bug me-1"></i>Modo de teste (sandbox)</p>
                    <p class="text-muted fs-7">
                        Ambiente de desenvolvimento: simule a confirmação do pagamento para validar o crédito
                        na carteira e a publicação no mural.
                    </p>
                    <form method="post" action="<?= site_url($evento->slug . '/pedido/' . $pedido->protocolo . '/simular') ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-warning">Simular pagamento confirmado</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    <?php elseif (! empty($cobranca['copia_e_cola'])): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 text-center">
                <h2 class="h6 text-uppercase text-muted fw-semibold mb-3">Pague com PIX</h2>

                <?php if (! empty($cobranca['qr_code_base64'])): ?>
                    <img src="data:image/png;base64,<?= esc($cobranca['qr_code_base64'], 'attr') ?>"
                         alt="QR Code PIX" width="220" height="220" class="mb-3 rounded">
                <?php else: ?>
                    <div id="qrcode" class="d-flex justify-content-center mb-3"></div>
                <?php endif; ?>

                <p class="text-muted fs-7 mb-2">Ou copie o código PIX abaixo:</p>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="pix-codigo" readonly
                           value="<?= esc($cobranca['copia_e_cola']) ?>">
                    <button class="btn btn-evento" type="button" id="btn-copiar">
                        <i class="bi bi-clipboard me-1"></i>Copiar
                    </button>
                </div>

                <?php if (! empty($cobranca['ticket_url'])): ?>
                    <a class="btn btn-outline-evento btn-sm mb-2" target="_blank" rel="noopener"
                       href="<?= esc($cobranca['ticket_url'], 'attr') ?>">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir no Mercado Pago
                    </a>
                <?php endif; ?>

                <?php if (! empty($cobranca['expira_em'])): ?>
                    <p class="text-muted fs-8 mb-0">
                        Este código expira em <?= esc(date('d/m/Y H:i', strtotime((string) $cobranca['expira_em']))) ?>.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <p class="text-muted fs-8">
            Recebedor: <?= esc($cobranca['recebedor'] ?? 'Minha Lista VIP') ?> ·
            Chave: <?= esc($cobranca['chave'] ?? '') ?>
        </p>

        <div class="alert alert-info rounded-4">
            <i class="bi bi-info-circle me-1"></i>Após o pagamento, a confirmação é automática.
            Atualize esta página em alguns instantes.
        </div>

        <a class="btn btn-outline-evento w-100 mb-3" href="<?= site_url($evento->slug . '/pedido/' . $pedido->protocolo . '?atualizar=1') ?>">
            <i class="bi bi-arrow-clockwise me-1"></i>Já paguei, atualizar status
        </a>

        <?php if ($simulacao): ?>
            <div class="card border-warning mb-4">
                <div class="card-body p-4">
                    <p class="fw-semibold mb-1"><i class="bi bi-bug me-1"></i>Modo de teste (sandbox)</p>
                    <p class="text-muted fs-7">
                        Ambiente de desenvolvimento: simule a confirmação do PIX para validar o crédito
                        na carteira e a publicação no mural.
                    </p>
                    <form method="post" action="<?= site_url($evento->slug . '/pedido/' . $pedido->protocolo . '/simular') ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-warning">Simular pagamento confirmado</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-warning rounded-4 mb-0">
            <?php if (($pedido->gateway ?? '') === 'mercadopago'): ?>
                O pagamento online está temporariamente indisponível. Entre em contato com o organizador.
            <?php else: ?>
                Não encontramos os dados do pagamento deste pedido. Entre em contato com o organizador.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <p class="text-center text-muted fs-8 mt-4 mb-0">
        Guarde o número do pedido: <strong><?= esc($pedido->protocolo) ?></strong>
    </p>
</main>

<footer class="text-center text-muted small py-4">
    Página criada com <a href="<?= site_url('/') ?>" class="text-decoration-none">Minha Lista VIP</a>
</footer>

<?php if ($bricks): ?>
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>
    (function () {
        var publicKey = <?= json_encode($public_key) ?>;
        var endpoint  = <?= json_encode(site_url($evento->slug . '/pedido/' . $pedido->protocolo . '/pagar')) ?>;
        var obrigadoUrl = <?= json_encode(site_url($evento->slug . '/obrigado/' . $pedido->protocolo)) ?>;
        var csrf      = <?= json_encode(csrf_hash()) ?>;
        var existente = <?= $pagamentoPendente ? json_encode((string) $cobranca['gateway_transacao_id']) : 'null' ?>;
        var threeDs   = <?= $pagamentoPendente ? json_encode($cobranca['three_ds_info'] ?? null) : 'null' ?>;
        var payer     = <?= json_encode($payer, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;

        var erroBox = document.getElementById('payment-erro');
        var statusBox = document.getElementById('statusScreenBrick_container');
        var cardPagamento = document.getElementById('card-pagamento');

        if (!window.MercadoPago) {
            return;
        }

        var mp = new MercadoPago(publicKey, { locale: 'pt-BR' });
        var bricks = mp.bricks();

        function mostrarErro(mensagem) {
            if (!erroBox) return;

            erroBox.textContent = mensagem;
            erroBox.classList.remove('d-none');
        }

        function renderStatus(paymentId, info) {
            if (cardPagamento) cardPagamento.classList.add('d-none');
            if (statusBox) statusBox.classList.remove('d-none');

            var initialization = { paymentId: String(paymentId) };

            if (info && info.external_resource_url) {
                initialization.additionalInfo = {
                    externalResourceURL: info.external_resource_url,
                    creq: info.creq
                };
            }

            bricks.create('statusScreen', 'statusScreenBrick_container', {
                initialization: initialization,
                callbacks: {
                    onReady: function () {},
                    onError: function (error) { console.error(error); }
                }
            });
        }

        if (existente) {
            renderStatus(existente, threeDs);
            return;
        }

        bricks.create('payment', 'paymentBrick_container', {
            initialization: {
                amount: <?= json_encode(round((float) $pedido->valor_total, 2)) ?>,
                payer: payer
            },
            customization: {
                paymentMethods: {
                    creditCard: 'all',
                    bankTransfer: 'all'
                },
                visual: { style: { theme: 'bootstrap' } }
            },
            callbacks: {
                onReady: function () {},
                onError: function (error) { console.error(error); },
                onSubmit: function (context) {
                    erroBox.classList.add('d-none');

                    return fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify(context.formData)
                    })
                    .then(function (resposta) {
                        return resposta.json().catch(function () {
                            return { ok: false, mensagem: 'Resposta inválida do servidor.' };
                        });
                    })
                    .then(function (resultado) {
                        if (resultado.status === 'pago') {
                            window.location.replace(obrigadoUrl);
                            return;
                        }

                        if (resultado.status === 'pendente') {
                            renderStatus(resultado.payment_id, resultado.three_ds_info);
                            return;
                        }

                        mostrarErro(resultado.mensagem || 'Pagamento recusado. Tente novamente.');
                    })
                    .catch(function () {
                        mostrarErro('Não foi possível processar o pagamento agora. Tente novamente.');
                    });
                }
            }
        });
    })();
    </script>
<?php else: ?>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
    (function () {
        const alvo = document.getElementById('qrcode');
        const codigo = document.getElementById('pix-codigo');

        if (alvo && codigo && typeof QRCode !== 'undefined') {
            new QRCode(alvo, { text: codigo.value, width: 220, height: 220, correctLevel: QRCode.CorrectLevel.M });
        }

        const botao = document.getElementById('btn-copiar');
        if (botao && codigo) {
            botao.addEventListener('click', async function () {
                try {
                    await navigator.clipboard.writeText(codigo.value);
                } catch (e) {
                    codigo.select();
                    document.execCommand('copy');
                }
                botao.innerHTML = '<i class="bi bi-check2 me-1"></i>Copiado!';
                setTimeout(() => { botao.innerHTML = '<i class="bi bi-clipboard me-1"></i>Copiar'; }, 2000);
            });
        }
    })();
    </script>
<?php endif; ?>

<?php if ($pedido->estaPendente() && ! $pedido->expirado()): ?>
    <script>
    (function () {
        var statusUrl = <?= json_encode(site_url($evento->slug . '/pedido/' . $pedido->protocolo . '/status')) ?>;
        var tentativas = 0;
        var maxTentativas = 360; // ~30 min a 5s

        function checar() {
            if (tentativas++ >= maxTentativas) {
                return;
            }

            // Consulta o gateway a cada 4 checagens (~20s); nas demais, só o banco.
            var url = (tentativas % 4 === 0) ? statusUrl + '?consultar=1' : statusUrl;

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (resposta) { return resposta.json(); })
                .then(function (dados) {
                    if (dados && dados.pago && dados.redirect) {
                        window.location.replace(dados.redirect);
                        return;
                    }

                    setTimeout(checar, 5000);
                })
                .catch(function () { setTimeout(checar, 5000); });
        }

        setTimeout(checar, 5000);
    })();
    </script>
<?php endif; ?>
</body>
</html>
