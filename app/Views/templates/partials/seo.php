<?php
/**
 * SEO / compartilhamento: meta description, canonical, Open Graph, Twitter Card
 * e dados estruturados (JSON-LD).
 *
 * Usado pelos partials design_system.php (site/painel) e design_evento.php
 * (hotsite e checkout). Recebe tudo por variáveis:
 *
 *   seo_titulo    string        Título da página (mesmo do <title>)
 *   seo_descricao string|null   Meta description (~150-160 caracteres)
 *   seo_imagem    string|null   Imagem de compartilhamento (relativa ou absoluta)
 *   seo_tipo      string|null   og:type (website|article|event) — padrão website
 *   seo_url       string|null   URL canônica — padrão: URL atual (sem query)
 *   seo_noindex   bool|null     true = não indexar (login, checkout, etc.)
 *   seo_jsonld    array|null    Lista de objetos schema.org
 */
$seoTitulo    = trim((string) ($seo_titulo ?? 'Minha Lista VIP'));
$seoDescricao = trim((string) ($seo_descricao ?? ''));
$seoTipo      = trim((string) ($seo_tipo ?? 'website')) ?: 'website';
$seoUrl       = trim((string) ($seo_url ?? ''));
$seoNoindex   = (bool) ($seo_noindex ?? false);
$seoJson      = (array) ($seo_jsonld ?? []);
$seoSite      = 'Minha Lista VIP';

$urlCanonica = $seoUrl !== '' ? $seoUrl : (string) current_url();
$urlCanonica = preg_replace('/[?#].*$/', '', $urlCanonica);

$imagem = trim((string) ($seo_imagem ?? ''));
if ($imagem === '') {
    $imagem = base_url('assets/logo_mlvp_real.png');
} elseif (! str_starts_with($imagem, 'http')) {
    $imagem = base_url(ltrim($imagem, '/'));
}
?>
<meta name="robots" content="<?= $seoNoindex ? 'noindex, nofollow' : 'index, follow' ?>">
<?php if ($seoDescricao !== ''): ?>
<meta name="description" content="<?= esc($seoDescricao) ?>">
<?php endif; ?>
<link rel="canonical" href="<?= esc($urlCanonica) ?>">

<meta property="og:type" content="<?= esc($seoTipo) ?>">
<meta property="og:site_name" content="<?= esc($seoSite) ?>">
<meta property="og:locale" content="pt_BR">
<meta property="og:title" content="<?= esc($seoTitulo) ?>">
<meta property="og:url" content="<?= esc($urlCanonica) ?>">
<meta property="og:image" content="<?= esc($imagem) ?>">
<?php if ($seoDescricao !== ''): ?>
<meta property="og:description" content="<?= esc($seoDescricao) ?>">
<?php endif; ?>

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= esc($seoTitulo) ?>">
<meta name="twitter:image" content="<?= esc($imagem) ?>">
<?php if ($seoDescricao !== ''): ?>
<meta name="twitter:description" content="<?= esc($seoDescricao) ?>">
<?php endif; ?>

<?php foreach ($seoJson as $schema): ?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<?php endforeach; ?>
