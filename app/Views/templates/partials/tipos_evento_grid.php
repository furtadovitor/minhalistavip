<?php
/**
 * Atalhos por tipo de evento — cartões horizontais com o ícone à esquerda.
 * Cada card leva à criação da lista (/criar-lista-de-presente/{slug}).
 *
 * @var array<string, array{slug: string, rotulo: string, icone: string, tema: string, cor_primaria: string, cor_secundaria: string}> $tipos
 */
$tipos = $tipos ?? tipos_evento();
?>
<?php foreach ($tipos as $chave => $tipo): ?>
    <div class="col-12 col-sm-6 col-lg-4">
        <a class="lp-atalho d-flex align-items-center gap-3 h-100 text-decoration-none rounded-4 p-2 pe-3"
           href="<?= esc(site_url('criar-lista-de-presente/' . $tipo['slug']), 'attr') ?>">
            <span class="lp-atalho-icone"
                  style="--atalho-a: <?= esc($tipo['cor_primaria'], 'attr') ?>; --atalho-b: <?= esc($tipo['cor_secundaria'], 'attr') ?>;"
                  aria-hidden="true"><?= esc($tipo['icone']) ?></span>
            <span class="lp-atalho-nome flex-grow-1 fw-semibold text-dark"><?= esc($tipo['rotulo']) ?></span>
            <i class="bi bi-arrow-right lp-atalho-seta" aria-hidden="true"></i>
        </a>
    </div>
<?php endforeach; ?>
