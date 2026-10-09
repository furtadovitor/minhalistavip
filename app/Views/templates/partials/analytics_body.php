<?php
/** GTM (noscript) — deve ficar logo após a abertura do <body>. */
$gtm = trim((string) config('Analytics')->gtmId);
?>
<?php if ($gtm !== ''): ?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= esc($gtm, 'attr') ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php endif; ?>
