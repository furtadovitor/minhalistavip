<?php
/**
 * Rastreamento (GTM / GA4 / Meta Pixel) + eventos de conversão.
 *
 * Incluído no <head> (design_system.php / design_evento.php). Os eventos
 * enfileirados via App\Services\TrackService são disparados aqui.
 */
$cfg   = config('Analytics');
$gtm   = trim((string) $cfg->gtmId);
$ga4   = trim((string) $cfg->ga4Id);
$pixel = trim((string) $cfg->metaPixelId);
$eventos = \App\Services\TrackService::consumir();
?>
<?php if ($gtm !== ''): ?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?= esc($gtm, 'attr') ?>');</script>
<!-- End Google Tag Manager -->
<?php elseif ($ga4 !== ''): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= esc($ga4, 'attr') ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= esc($ga4, 'attr') ?>');</script>
<?php endif; ?>
<script>
    window.dataLayer = window.dataLayer || [];
    window.mlvTrack = function (nome, params) {
        params = params || {};
        window.dataLayer.push(Object.assign({ event: nome }, params));
        if (typeof gtag === 'function') { gtag('event', nome, params); }
        if (typeof fbq === 'function') {
            var mapa = { sign_up: 'CompleteRegistration', create_list: 'Lead', begin_checkout: 'InitiateCheckout', purchase: 'Purchase' };
            fbq('track', mapa[nome] || nome, params);
        }
    };
</script>
<?php if ($pixel !== ''): ?>
<!-- Meta Pixel -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init','<?= esc($pixel, 'attr') ?>');fbq('track','PageView');
</script>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?= esc($pixel, 'attr') ?>&ev=PageView&noscript=1"></noscript>
<!-- End Meta Pixel -->
<?php endif; ?>
<?php if ($eventos !== []): ?>
<script>
<?php foreach ($eventos as $evento): ?>
    window.mlvTrack(<?= json_encode($evento['name'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>, <?= json_encode($evento['params'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
<?php endforeach; ?>
</script>
<?php endif; ?>
