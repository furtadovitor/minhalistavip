<?php

/**
 * Gera a imagem padrão de compartilhamento (Open Graph) da plataforma, usada
 * quando a página não tem uma imagem própria (ex.: hotsite sem capa).
 *
 * Uso:  php tools/gerar-og.php
 * Saída: public/assets/og-default.png  (1200x630)
 */

$raiz   = dirname(__DIR__);
$assets = $raiz . '/public/assets';
$logo   = $assets . '/logo_mlvp_real.png';
$destino = $assets . '/og-default.png';

if (! extension_loaded('gd')) {
    fwrite(STDERR, "A extensao GD do PHP e necessaria.\n");
    exit(1);
}

if (! is_file($logo)) {
    fwrite(STDERR, "Logo nao encontrada: {$logo}\n");
    exit(1);
}

$largura = 1200;
$altura  = 630;

$img = imagecreatetruecolor($largura, $altura);
imagealphablending($img, true);
imagesavealpha($img, true);

// Fundo: gradiente vertical suave (roxo bem claro -> branco).
$topo = [0xEE, 0xE6, 0xFD];
$base = [0xFF, 0xFF, 0xFF];

for ($y = 0; $y < $altura; $y++) {
    $t = $y / ($altura - 1);
    $r = (int) round($topo[0] + ($base[0] - $topo[0]) * $t);
    $g = (int) round($topo[1] + ($base[1] - $topo[1]) * $t);
    $b = (int) round($topo[2] + ($base[2] - $topo[2]) * $t);
    $cor = imagecolorallocate($img, $r, $g, $b);
    imagefilledrectangle($img, 0, $y, $largura - 1, $y, $cor);
}

// Logo centralizada (ocupa ~58% da largura).
$logoImg = imagecreatefrompng($logo);
$logoW   = imagesx($logoImg);
$logoH   = imagesy($logoImg);

$alvoW = (int) ($largura * 0.58);
$alvoH = (int) round($logoH * ($alvoW / $logoW));
$posX  = (int) (($largura - $alvoW) / 2);
$posY  = (int) (($altura - $alvoH) / 2);

imagealphablending($img, true);
imagecopyresampled($img, $logoImg, $posX, $posY, 0, 0, $alvoW, $alvoH, $logoW, $logoH);

imagepng($img, $destino);
imagedestroy($img);
imagedestroy($logoImg);

echo "[ok] public/assets/og-default.png ({$largura}x{$altura})\n";
