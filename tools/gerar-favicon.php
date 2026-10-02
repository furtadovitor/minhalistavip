<?php

/**
 * Gera os assets rasterizados da marca "Minha Lista VIP" (mesmo desenho do
 * public/favicon.svg) para navegadores e dispositivos que não usam SVG.
 *
 * Uso:  php tools/gerar-favicon.php
 *
 * Produz em /public:
 *   - favicon.ico         (16, 32 e 48 px)
 *   - favicon-192.png     (Android/atalho)
 *   - apple-touch-icon.png (180 px, quadrado — o iOS arredonda)
 */

$public = dirname(__DIR__) . '/public';

if (! extension_loaded('gd')) {
    fwrite(STDERR, "A extensao GD do PHP e necessaria.\n");
    exit(1);
}

/** Cor do gradiente (#6366F1 -> #7C3AED) na linha y. */
function gradiente(int $y, int $size): array
{
    $t = $size > 1 ? $y / ($size - 1) : 0;

    return [
        (int) round(0x63 + (0x7C - 0x63) * $t),
        (int) round(0x66 + (0x3A - 0x66) * $t),
        (int) round(0xF1 + (0xED - 0xF1) * $t),
    ];
}

/** Quadrado arredondado (raio em unidades do design de 48). */
function dentroQuadArredondado(float $x, float $y, float $size, float $raio): bool
{
    if ($x < 0 || $y < 0 || $x > $size || $y > $size) {
        return false;
    }

    $cx = min(max($x, $raio), $size - $raio);
    $cy = min(max($y, $raio), $size - $raio);
    $dx = $x - $cx;
    $dy = $y - $cy;

    return $dx * $dx + $dy * $dy <= $raio * $raio;
}

/**
 * Desenha a marca num quadrado de $size px. $raio em unidades de 48
 * (13 = padrão arredondado; 0 = quadrado cheio para o apple-touch-icon).
 */
function renderMarca(int $size, float $raio = 13.0): \GdImage
{
    $img = imagecreatetruecolor($size, $size);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagefilledrectangle($img, 0, 0, $size - 1, $size - 1,
        imagecolorallocatealpha($img, 0, 0, 0, 127));

    $esc = $size / 48.0;

    // Fundo: quadrado arredondado com gradiente.
    for ($y = 0; $y < $size; $y++) {
        [$r, $g, $b] = gradiente($y, $size);
        $corFundo = imagecolorallocatealpha($img, $r, $g, $b, 0);

        for ($x = 0; $x < $size; $x++) {
            if (dentroQuadArredondado($x / $esc, $y / $esc, 48.0, $raio)) {
                imagesetpixel($img, $x, $y, $corFundo);
            }
        }
    }

    // Monograma "M" em branco (traço grosso com cantos/caps arredondados).
    $pontos = [[15.0, 33.0], [15.0, 15.0], [24.0, 27.0], [33.0, 15.0], [33.0, 33.0]];
    $thick  = max(2, (int) round(5.2 * $esc));
    $branco = imagecolorallocatealpha($img, 255, 255, 255, 0);

    imagesetthickness($img, $thick);
    for ($i = 0; $i < count($pontos) - 1; $i++) {
        imageline(
            $img,
            (int) round($pontos[$i][0] * $esc), (int) round($pontos[$i][1] * $esc),
            (int) round($pontos[$i + 1][0] * $esc), (int) round($pontos[$i + 1][1] * $esc),
            $branco
        );
    }
    foreach ($pontos as $p) {
        imagefilledellipse($img, (int) round($p[0] * $esc), (int) round($p[1] * $esc), $thick, $thick, $branco);
    }

    return $img;
}

/** Reamostra para o tamanho final (borda suave). */
function redimensionar(int $size, float $raio): \GdImage
{
    $base = renderMarca(512, $raio);
    $final = imagecreatetruecolor($size, $size);
    imagealphablending($final, false);
    imagesavealpha($final, true);
    imagecopyresampled($final, $base, 0, 0, 0, 0, $size, $size, 512, 512);
    imagedestroy($base);

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

// ---- favicon.ico (16, 32, 48) ----
$pngs = [];
foreach ([16, 32, 48] as $tamanho) {
    $img = redimensionar($tamanho, 13.0);
    $pngs[$tamanho] = pngBytes($img);
    imagedestroy($img);
}
file_put_contents($public . '/favicon.ico', montarIco($pngs));
echo "[ok] public/favicon.ico\n";

// ---- favicon-192.png ----
$img = redimensionar(192, 13.0);
imagepng($img, $public . '/favicon-192.png');
imagedestroy($img);
echo "[ok] public/favicon-192.png\n";

// ---- apple-touch-icon.png (quadrado, iOS arredonda) ----
$img = redimensionar(180, 0.0);
imagepng($img, $public . '/apple-touch-icon.png');
imagedestroy($img);
echo "[ok] public/apple-touch-icon.png\n";

echo "Pronto.\n";
