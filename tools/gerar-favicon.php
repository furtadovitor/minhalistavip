<?php

/**
 * Gera os assets de favicon da marca "Minha Lista VIP" a partir da imagem-fonte
 * public/logo_mlvp.png, para navegadores e dispositivos que não usam SVG.
 *
 * A imagem é recortada nas bordas transparentes e centralizada num quadrado,
 * garantindo que a marca fique legível também em 16 px.
 *
 * Uso:  php tools/gerar-favicon.php
 *
 * Produz em /public:
 *   - favicon.ico          (16, 32 e 48 px, PNG embutido)
 *   - favicon-192.png      (Android/atalho)
 *   - apple-touch-icon.png (180 px, quadrado e opaco — o iOS arredonda)
 *   - favicon.svg          (mesma imagem embutida, para navegadores com SVG)
 */

$public = dirname(__DIR__) . '/public';
$origem = $public . '/logo_mlvp.png';

if (! extension_loaded('gd')) {
    fwrite(STDERR, "A extensao GD do PHP e necessaria.\n");
    exit(1);
}

if (! is_file($origem)) {
    fwrite(STDERR, "Imagem de origem nao encontrada: {$origem}\n");
    exit(1);
}

/** Carrega o PNG de origem preservando o canal alfa. */
function carregarOrigem(string $arquivo): \GdImage
{
    $img = imagecreatefrompng($arquivo);
    if ($img === false) {
        fwrite(STDERR, "Nao foi possivel ler o PNG: {$arquivo}\n");
        exit(1);
    }

    imagealphablending($img, false);
    imagesavealpha($img, true);

    return $img;
}

/**
 * Recorta as bordas totalmente transparentes e devolve um quadrado com a
 * marca centralizada e uma pequena folga (proporcional ao conteúdo).
 */
function recortarEmQuadrado(\GdImage $src, float $folga = 0.10): \GdImage
{
    $w = imagesx($src);
    $h = imagesy($src);

    $minX = $w;
    $minY = $h;
    $maxX = -1;
    $maxY = -1;

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $alpha = (imagecolorat($src, $x, $y) >> 24) & 0x7F;
            if ($alpha < 120) { // considera visível (opacidade razoável)
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

    $lado = (int) round(max($cw, $ch) * (1 + 2 * $folga));

    $quadrado = imagecreatetruecolor($lado, $lado);
    imagealphablending($quadrado, false);
    imagesavealpha($quadrado, true);
    imagefilledrectangle($quadrado, 0, 0, $lado - 1, $lado - 1,
        imagecolorallocatealpha($quadrado, 0, 0, 0, 127));

    imagecopy(
        $quadrado,
        $src,
        (int) round(($lado - $cw) / 2),
        (int) round(($lado - $ch) / 2),
        $minX,
        $minY,
        $cw,
        $ch
    );

    return $quadrado;
}

/** Reamostra a base quadrada para o tamanho final (borda suave). */
function redimensionar(\GdImage $base, int $size, bool $opaco = false): \GdImage
{
    $final = imagecreatetruecolor($size, $size);
    imagealphablending($final, false);
    imagesavealpha($final, true);

    if ($opaco) {
        imagefilledrectangle($final, 0, 0, $size - 1, $size - 1,
            imagecolorallocate($final, 255, 255, 255));
        imagealphablending($final, true);
    } else {
        imagefilledrectangle($final, 0, 0, $size - 1, $size - 1,
            imagecolorallocatealpha($final, 0, 0, 0, 127));
    }

    imagecopyresampled($final, $base, 0, 0, 0, 0, $size, $size,
        imagesx($base), imagesy($base));

    return $final;
}

function pngBytes(\GdImage $img): string
{
    ob_start();
    imagepng($img);

    return (string) ob_get_clean();
}

/** Monta um .ico com PNGs embutidos (formato suportado por navegadores modernos). */
function montarIco(array $porTamanho): string
{
    $count   = count($porTamanho);
    $entries = '';
    $dados   = '';
    $offset  = 6 + 16 * $count;

    foreach ($porTamanho as $tamanho => $png) {
        $dim = $tamanho >= 256 ? 0 : $tamanho;
        $entries .= pack('CCCC', $dim, $dim, 0, 0)
            . pack('vv', 1, 32)
            . pack('VV', strlen($png), $offset);
        $offset += strlen($png);
        $dados  .= $png;
    }

    return pack('vvv', 0, 1, $count) . $entries . $dados;
}

$origemImg = carregarOrigem($origem);
$base      = recortarEmQuadrado($origemImg);
imagedestroy($origemImg);

// ---- favicon.ico (16, 32, 48) ----
$pngs = [];
foreach ([16, 32, 48] as $tamanho) {
    $img = redimensionar($base, $tamanho);
    $pngs[$tamanho] = pngBytes($img);
    imagedestroy($img);
}
file_put_contents($public . '/favicon.ico', montarIco($pngs));
echo "[ok] public/favicon.ico\n";

// ---- favicon-192.png ----
$img = redimensionar($base, 192);
imagepng($img, $public . '/favicon-192.png');
imagedestroy($img);
echo "[ok] public/favicon-192.png\n";

// ---- apple-touch-icon.png (quadrado e opaco, o iOS arredonda) ----
$img = redimensionar($base, 180, true);
imagepng($img, $public . '/apple-touch-icon.png');
imagedestroy($img);
echo "[ok] public/apple-touch-icon.png\n";

// ---- favicon.svg (imagem embutida, para navegadores com suporte a SVG) ----
$imgSvg  = redimensionar($base, 192);
$svgPng  = base64_encode(pngBytes($imgSvg));
imagedestroy($imgSvg);
$svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 192 192" width="192" height="192" role="img" aria-label="Minha Lista VIP">
    <image width="192" height="192" xlink:href="data:image/png;base64,{$svgPng}"/>
</svg>

SVG;
file_put_contents($public . '/favicon.svg', $svg);
echo "[ok] public/favicon.svg\n";

imagedestroy($base);

echo "Pronto.\n";
