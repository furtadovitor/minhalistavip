<?= $this->extend('templates/layouts/app') ?>

<?php
$link    = site_url($evento->slug);
$texto   = 'Você está convidado(a) para ' . $evento->titulo . '! Veja a lista de presentes: ' . $link;
$whats   = 'https://api.whatsapp.com/send?text=' . rawurlencode($texto);
?>

<?= $this->section('conteudo') ?>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Link da lista</h2>

                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="link-lista" value="<?= esc($link, 'attr') ?>" readonly>
                    <button class="btn btn-brand" type="button" id="btn-copiar" data-link="<?= esc($link, 'attr') ?>">
                        <i class="bi bi-clipboard me-1"></i>Copiar
                    </button>
                </div>

                <p class="text-muted fs-7 mb-3">Envie este link para seus convidados. Eles não precisam criar conta.</p>

                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-success" href="<?= esc($whats, 'attr') ?>" target="_blank">
                        <i class="bi bi-whatsapp me-1"></i>Compartilhar no WhatsApp
                    </a>
                    <button class="btn btn-outline-secondary" type="button" id="btn-nativo">
                        <i class="bi bi-share me-1"></i>Mais opções
                    </button>
                    <?php if ($evento->status === 'publicado'): ?>
                        <a class="btn btn-outline-brand" href="<?= esc($link, 'attr') ?>" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Abrir lista
                        </a>
                    <?php else: ?>
                        <span class="align-self-center badge text-bg-warning">Publique a lista para o link funcionar</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Código QR</h2>
                <p class="text-muted fs-7">Imprima ou mostre no convite para acesso rápido.</p>
                <div id="qrcode" class="d-inline-block p-2 bg-white border rounded-3"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Mensagem sugerida</h2>
                <textarea class="form-control" rows="7" readonly><?= esc($texto) ?></textarea>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>
<script>
(function () {
    var link = <?= json_encode($link) ?>;

    var btnCopiar = document.getElementById('btn-copiar');
    if (btnCopiar) {
        btnCopiar.addEventListener('click', function () {
            navigator.clipboard.writeText(link).then(function () {
                btnCopiar.innerHTML = '<i class="bi bi-check-lg me-1"></i>Copiado!';
                setTimeout(function () { btnCopiar.innerHTML = '<i class="bi bi-clipboard me-1"></i>Copiar'; }, 1800);
            });
        });
    }

    var btnNativo = document.getElementById('btn-nativo');
    if (btnNativo) {
        btnNativo.addEventListener('click', function () {
            if (navigator.share) {
                navigator.share({ title: <?= json_encode($evento->titulo, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, url: link });
            } else {
                navigator.clipboard.writeText(link);
            }
        });
    }

    var alvo = document.getElementById('qrcode');
    if (alvo && window.QRCode) {
        new QRCode(alvo, { text: link, width: 180, height: 180, correctLevel: QRCode.CorrectLevel.M });
    }
})();
</script>
<?= $this->endSection() ?>
