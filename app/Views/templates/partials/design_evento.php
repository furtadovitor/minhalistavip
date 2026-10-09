<?php
/**
 * Cabeçalho temático das páginas públicas do evento (hotsite, checkout, pedido).
 *
 * O `tema` define a tipografia e o arredondamento dos cantos, além das cores.
 *
 * @var string|null $titulo
 * @var string|null $corPrimaria
 * @var string|null $corSecundaria
 * @var string|null $tema
 * @var bool|null   $escuro
 */
$titulo        = $titulo ?? 'Evento';
$corPrimaria   = cor_hex($corPrimaria ?? null, '#4F46E5');
$corSecundaria = cor_hex($corSecundaria ?? null, '#10B981');
$escuro        = $escuro ?? false;
$seo           = $seo ?? [];

/** Tipografia e estilo por tema (catálogo central em ModeloService). */
$tema  = $tema ?? 'classico';
$fonte = \App\Services\ModeloService::tipografia((string) $tema);
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($titulo) ?></title>

<?= view('templates/partials/seo', [
    'seo_titulo'    => $titulo,
    'seo_descricao' => $seo['descricao'] ?? null,
    'seo_imagem'    => $seo['imagem'] ?? null,
    'seo_tipo'      => $seo['tipo'] ?? 'website',
    'seo_url'       => $seo['url'] ?? null,
    'seo_noindex'   => $seo['noindex'] ?? false,
    'seo_jsonld'    => $seo['jsonld'] ?? [],
]) ?>

<?php
/** Favicon com cache-busting (?v=timestamp) — ver design_system.php. */
$faviconUrl = static function (string $arquivo): string {
    $caminho = FCPATH . $arquivo;
    $versao  = is_file($caminho) ? (string) filemtime($caminho) : '1';

    return base_url($arquivo) . '?v=' . $versao;
};
?>
<link rel="icon" href="<?= $faviconUrl('favicon.ico') ?>" sizes="any">
<link rel="icon" type="image/svg+xml" href="<?= $faviconUrl('favicon.svg') ?>">
<link rel="apple-touch-icon" href="<?= $faviconUrl('apple-touch-icon.png') ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=<?= $fonte['google'] ?>&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    :root {
        --cor-primaria: <?= $corPrimaria ?>;
        --cor-secundaria: <?= $corSecundaria ?>;
        --raio: <?= $fonte['raio'] ?>;
        --raio-btn: 999px;
        --fonte-titulo: '<?= $fonte['titulo'] ?>';
    }
    body { font-family: '<?= $fonte['corpo'] ?>', system-ui, sans-serif; }
    h1, h2, h3, h4, h5, .font-display { font-family: var(--fonte-titulo), 'Inter', sans-serif; }

    /* Botões e campos padronizados (pill) nas páginas públicas do evento */
    .btn,
    .form-control,
    .form-select {
        border-radius: var(--raio-btn);
    }
    textarea.form-control { border-radius: 1.25rem; }
    .input-group > :first-child {
        border-top-left-radius: var(--raio-btn);
        border-bottom-left-radius: var(--raio-btn);
    }
    .input-group > :last-child {
        border-top-right-radius: var(--raio-btn);
        border-bottom-right-radius: var(--raio-btn);
    }

    .hero { background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria)); color: #fff; }
    .btn-evento { background-color: var(--cor-primaria); border-color: var(--cor-primaria); color: #fff; font-weight: 600; }
    .btn-evento:hover { filter: brightness(0.92); color: #fff; }
    .btn-outline-evento { color: var(--cor-primaria); border-color: var(--cor-primaria); font-weight: 600; }
    .btn-outline-evento:hover { background-color: var(--cor-primaria); color: #fff; }
    .titulo-evento { color: var(--cor-primaria); }
    .card { border-radius: var(--raio); }
    .fs-7 { font-size: .875rem; }
    .fs-8 { font-size: .75rem; }

    .tema-escuro { background-color: #0B0B12; color: #E5E7EB; }
    .tema-escuro .card { background-color: #15151F; color: #E5E7EB; border: 1px solid rgba(255, 255, 255, .08) !important; }
    .tema-escuro .text-muted { color: #9CA3AF !important; }
    .tema-escuro .border-top, .tema-escuro .border-bottom { border-color: rgba(255, 255, 255, .12) !important; }
    .faixa-demo { background: #111827; color: #fff; }
</style>
