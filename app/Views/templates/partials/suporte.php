<?php
/**
 * Modal "Fale com uma pessoa de verdade" (e-mail/WhatsApp), aberto pelo chat
 * de suporte do painel do organizador.
 *
 * TODO: definir os canais reais de atendimento.
 * Basta trocar os dois valores abaixo (e-mail e WhatsApp). O link do WhatsApp
 * aceita número com máscara — os dígitos são extraídos automaticamente.
 */
$suporteEmail    = 'xxxx';
$suporteWhatsapp = 'xxxx';

$whatsDigitos = preg_replace('/\D+/', '', (string) $suporteWhatsapp);

// Remove o DDI 55 se já vier incluso (evita duplicar no link).
if (strlen($whatsDigitos) > 11 && str_starts_with($whatsDigitos, '55')) {
    $whatsDigitos = substr($whatsDigitos, 2);
}

$whatsLink = $whatsDigitos !== '' ? 'https://wa.me/55' . $whatsDigitos : '#';
$emailLink = 'mailto:' . $suporteEmail . '?subject=' . rawurlencode('Suporte - Minha Lista VIP');
?>
<style>
    .suporte-icone {
        width: 64px; height: 64px; border-radius: 50%; margin: 0 auto;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.8rem; background: var(--brand-soft); color: var(--brand);
    }
    .suporte-canal {
        display: flex; align-items: center; gap: .85rem;
        padding: .85rem 1rem; border: 1px solid #E5E7EB; border-radius: .9rem;
        text-decoration: none; transition: border-color .15s ease, background .15s ease;
    }
    .suporte-canal:hover { border-color: var(--brand); background: var(--brand-soft); }
    .suporte-canal-icone {
        width: 42px; height: 42px; border-radius: .75rem; flex: none;
        display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem;
    }
    .suporte-canal-icone.mail { background: var(--brand-soft); color: var(--brand); }
    .suporte-canal-icone.whats { background: #25D366; color: #fff; }
</style>

<div class="modal fade" id="modalSuporte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 position-relative">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" style="z-index: 2;"
                    data-bs-dismiss="modal" aria-label="Fechar"></button>

            <div class="modal-body p-4 p-sm-5 text-center">
                <span class="suporte-icone"><i class="bi bi-headset"></i></span>
                <h5 class="fw-bold mt-3 mb-1">Fale com uma pessoa de verdade</h5>
                <p class="text-muted mb-4">Escolha o canal que preferir. Nosso suporte lê todas as mensagens.</p>

                <div class="d-grid gap-2 text-start">
                    <a class="suporte-canal" href="<?= esc($emailLink) ?>">
                        <span class="suporte-canal-icone mail"><i class="bi bi-envelope-fill"></i></span>
                        <span class="flex-grow-1">
                            <span class="d-block fw-semibold text-dark">E-mail</span>
                            <span class="d-block text-muted fs-7"><?= esc($suporteEmail) ?></span>
                        </span>
                        <i class="bi bi-arrow-right text-muted"></i>
                    </a>

                    <a class="suporte-canal" href="<?= esc($whatsLink) ?>" target="_blank" rel="noopener">
                        <span class="suporte-canal-icone whats"><i class="bi bi-whatsapp"></i></span>
                        <span class="flex-grow-1">
                            <span class="d-block fw-semibold text-dark">WhatsApp</span>
                            <span class="d-block text-muted fs-7"><?= esc($suporteWhatsapp) ?></span>
                        </span>
                        <i class="bi bi-arrow-right text-muted"></i>
                    </a>
                </div>

                <p class="text-muted fs-8 mt-4 mb-0">Atendimento de segunda a sexta, das 9h às 18h.</p>
            </div>
        </div>
    </div>
</div>
