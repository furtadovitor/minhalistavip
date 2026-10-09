<?php
/**
 * Logo da plataforma: lockup "Minha Lista VIP" (marca + wordmark).
 *
 * Usa os assets rasterizados em public/assets (ver tools/gerar-logo.php).
 * Para fundos escuros use ['claro' => true]; com ['nome' => false] renderiza
 * apenas a marca (ícone), sem o nome.
 *
 * Uso: <?= view('templates/partials/logo', ['altura' => 30]) ?>
 *      <?= view('templates/partials/logo', ['claro' => true]) ?>   (fundos escuros)
 *      <?= view('templates/partials/logo', ['nome' => false]) ?>   (só a marca)
 *
 * @var int|null    $altura  Altura da logo em px. Padrão: 34
 * @var bool|null   $claro   Versão clara do lockup (para fundo escuro)
 * @var string|null $classe  Classes extras no wrapper
 * @var bool|null   $nome    Exibir o nome ao lado da marca (padrão: true)
 */
$altura = (int) ($altura ?? 34);
$claro  = (bool) ($claro ?? false);
$nome   = $nome ?? true;
$classe = trim('brand-logo ' . ($claro ? 'brand-logo--claro ' : '') . ($classe ?? ''));

$marca = 'assets/logo_mlvp_marca.png';
$full  = $claro ? 'assets/logo_mlvp_real_claro.png' : 'assets/logo_mlvp_real.png';
$estilo = 'height: ' . $altura . 'px; width: auto;';
?>
<span class="<?= esc($classe) ?>">
    <?php if ($nome): ?>
        <img class="brand-img brand-img-full" src="<?= base_url($full) ?>" alt="Minha Lista VIP"
             height="<?= $altura ?>" style="<?= $estilo ?>">
        <img class="brand-img brand-img-mark" src="<?= base_url($marca) ?>" alt="Minha Lista VIP"
             height="<?= $altura ?>" style="<?= $estilo ?>">
    <?php else: ?>
        <img class="brand-img" src="<?= base_url($marca) ?>" alt="Minha Lista VIP"
             height="<?= $altura ?>" style="<?= $estilo ?>">
    <?php endif; ?>
</span>
