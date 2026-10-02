<?php
/**
 * Logo da plataforma: marca (presente) + wordmark.
 *
 * É renderizada inline para usar a tipografia carregada na própria página
 * (Plus Jakarta Sans). O favicon é o mesmo desenho, sem o texto.
 *
 * Uso: <?= view('templates/partials/logo', ['altura' => 30]) ?>
 *      <?= view('templates/partials/logo', ['claro' => true]) ?>  (fundos escuros)
 *
 * @var int|null    $altura  Tamanho da marca (px). Padrão: 34
 * @var bool|null   $claro   Wordmark em tom claro (para fundo escuro)
 * @var string|null $classe  Classes extras no wrapper
 * @var bool|null   $nome    Exibir o nome ao lado da marca (padrão: true)
 * @var int|null    $texto   Tamanho do wordmark (px). Padrão: proporcional à marca
 */
$altura = (int) ($altura ?? 34);
$claro  = (bool) ($claro ?? false);
$nome   = $nome ?? true;
$classe = trim('brand-logo ' . ($claro ? 'brand-logo--claro ' : '') . ($classe ?? ''));
$gid    = 'mlv-grad-' . substr(md5(uniqid('', true)), 0, 8);

$texto = (int) ($texto ?? max(15, (int) round($altura * 0.52)));
?>
<span class="<?= esc($classe) ?>" style="--brand-text-size: <?= $texto ?>px;">
    <svg class="brand-mark" width="<?= $altura ?>" height="<?= $altura ?>" viewBox="0 0 48 48" fill="none"
         aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="<?= $gid ?>" x1="6" y1="2" x2="42" y2="46" gradientUnits="userSpaceOnUse">
                <stop stop-color="#6366F1"/>
                <stop offset="1" stop-color="#7C3AED"/>
            </linearGradient>
        </defs>
        <rect width="48" height="48" rx="13" fill="url(#<?= $gid ?>)"/>
        <path d="M15 33 V15 L24 27 L33 15 V33" fill="none" stroke="#ffffff" stroke-width="5.2"
              stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <?php if ($nome): ?>
        <span class="brand-word">Minha Lista<span class="brand-vip">VIP</span></span>
    <?php endif; ?>
</span>
