<?php

/**
 * Gera os assets da logo "Minha Lista VIP" usados no site (partial
 * templates/partials/logo.php) a partir das imagens-fonte versionadas:
 *
 *   - public/assets/logo_mlvp_real_fonte.png (lockup completo, com margens)
 *   - public/logo_mlvp.png                   (apenas a marca/ícone)
 *
 * As saídas são recortadas nas bordas transparentes ("compactas"), para que a
 * altura definida no partial corresponda ao tamanho visível da logo.
 *
 * Produz em /public/assets:
 *   - logo_mlvp_real.png        (presente + "Minha Lista VIP" + coroa)
 *   - logo_mlvp_real_claro.png  (a mesma logo, com "Minha Lista" em branco)
 *   - logo_mlvp_marca.png       (só o presente — favicon e menu recolhido)
 *
 * Uso:  php tools/gerar-logo.php
 */

$raiz   = dirname(__DIR__);
$assets = $raiz . '/public/assets';

if (! extension_loaded('gd')) {
    fwrite(STDERR, "A extensao GD do PHP e necessaria.\n");
    exit(1);
}

$fonteLockup = $assets . '/logo_mlvp_real_fonte.png';
$fonteMarca  = $raiz . '/public/logo_mlvp.png';

foreach ([$fonteLockup, $fonteMarca] as $fonte) {
    if (! is_file($fonte)) {
        fwrite(STDERR, "Imagem de origem nao encontrada: {$fonte}\n");
        exit(1);
    }
}

/** Carrega um PNG preservando o canal alfa. */
function carregar(string $arquivo): \GdImage
{
    $im = imagecreatefrompng($arquivo);
    if ($im === false) {
        fwrite(STDERR, "Nao foi possivel ler o PNG: {$arquivo}\n");
        exit(1);
    }

    imagealphablending($im, false);
    imagesavealpha($im, true);

    return $im;
}

/** Cria uma tela transparente do tamanho informado. */
function telaTransparente(int $w, int $h): \GdImage
{
    $img = imagecreatetruecolor($w, $h);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagefilledrectangle($img, 0, 0, $w - 1, $h - 1,
        imagecolorallocatealpha($img, 0, 0, 0, 127));

    return $img;
}

/**
 * Recorta as bordas transparentes. Com $quadrado = true, centraliza o conteúdo
 * num quadrado (usado para a marca/ícone); senão devolve a proporção original.
 */
function recortar(\GdImage $im, float $folga = 0.02, bool $quadrado = false): \GdImage
{
    $w = imagesx($im);
    $h = imagesy($im);

    $minX = $w;
    $minY = $h;
    $maxX = -1;
    $maxY = -1;

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $alpha = (imagecolorat($im, $x, $y) >> 24) & 0x7F;
            if ($alpha < 120) {
                if ($x < $minX) {
                    $minX = $x;
                }
                if ($x > $maxX) {
                    $maxX = $x;
                }
                if ($y < $minY) {
                    $minY = $y;
                }
                if ($y > $maxY) {
                    $maxY = $y;
                }
            }
        }
    }

    if ($maxX < $minX || $maxY < $minY) { // imagem totalmente transparente
        $minX = $minY = 0;
        $maxX = $w - 1;
        $maxY = $h - 1;
    }

    $cw = $maxX - $minX + 1;
    $ch = $maxY - $minY + 1;

    if ($quadrado) {
        $lado = (int) round(max($cw, $ch) * (1 + 2 * $folga));
        $outW = $outH = $lado;
    } else {
        $outW = (int) round($cw * (1 + 2 * $folga));
        $outH = (int) round($ch * (1 + 2 * $folga));
    }

    $out = telaTransparente($outW, $outH);
    imagecopy($out, $im,
        (int) round(($outW - $cw) / 2), (int) round(($outH - $ch) / 2),
        $minX, $minY, $cw, $ch);

    return $out;
}

/**
 * Variante clara: converte os pixels em tons de cinza (o texto "Minha Lista")
 * para branco, preservando a marca colorida e o selo "VIP".
 */
function gerarClaro(\GdImage $im): \GdImage
{
    $w = imagesx($im);
    $h = imagesy($im);
    $out = telaTransparente($w, $h);

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $c = imagecolorat($im, $x, $y);
            $a = ($c >> 24) & 0x7F;
            if ($a >= 127) {
                continue; // já transparente
            }

            $r = ($c >> 16) & 0xFF;
            $g = ($c >> 8) & 0xFF;
            $b = $c & 0xFF;
            $sat = max($r, $g, $b) - min($r, $g, $b);
            $lum = max($r, $g, $b);

            // Cinza escuro = texto preto ("Minha Lista"): vira branco.
            if ($sat <= 32 && $lum < 210) {
                $cobertura = 1 - $lum / 255;
                $opacidade = (1 - $a / 127) * $cobertura;
                $aOut = (int) round((1 - $opacidade) * 127);
                imagesetpixel($out, $x, $y, ($aOut << 24) | (255 << 16) | (255 << 8) | 255);
            } else {
                imagesetpixel($out, $x, $y, $c);
            }
        }
    }

    return $out;
}

function salvar(\GdImage $im, string $destino): void
{
    imagepng($im, $destino);
    imagedestroy($im);
}

// ---- marca (ícone) ----
$marca = carregar($fonteMarca);
salvar(recortar($marca, 0.08, true), $assets . '/logo_mlvp_marca.png');
imagedestroy($marca);
echo "[ok] public/assets/logo_mlvp_marca.png\n";

// ---- logo (presente à esquerda + texto + coroa) ----
$lockup = carregar($fonteLockup);
salvar(recortar($lockup, 0.02), $assets . '/logo_mlvp_real.png');
imagedestroy($lockup);
echo "[ok] public/assets/logo_mlvp_real.png\n";

// ---- logo clara (texto branco) ----
$lockup = carregar($fonteLockup);
$claro  = gerarClaro($lockup);
imagedestroy($lockup);
salvar(recortar($claro, 0.02), $assets . '/logo_mlvp_real_claro.png');
imagedestroy($claro);
echo "[ok] public/assets/logo_mlvp_real_claro.png\n";

echo "Pronto.\n";
