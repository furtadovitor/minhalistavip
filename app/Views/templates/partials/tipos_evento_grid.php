<?php
/**
 * Grade de atalhos por tipo de evento. Cada card leva à criação da lista
 * (/criar-lista-de-presente/{slug}).
 *
 * @var array<string, array{slug: string, rotulo: string, icone: string, tema: string, cor_primaria: string, cor_secundaria: string}> $tipos
 */
$tipos = $tipos ?? tipos_evento();
?>
<?php foreach ($tipos as $chave => $tipo): ?>
    <div class="col-6 col-md-4 col-lg-3">
        <a class="card h-100 border-0 shadow-sm rounded-4 text-decoration-none text-center transition-hover atalho-tipo"
           href="<?= esc(site_url('criar-lista-de-presente/' . $tipo['slug']), 'attr') ?>">
            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                <span class="atalho-icone rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                      style="background: <?= esc($tipo['cor_primaria'], 'attr') ?>1A; font-size: 1.6rem; width: 56px; height: 56px;">
                    <?= esc($tipo['icone']) ?>
                </span>
                <span class="fw-semibold fs-7 text-dark"><?= esc($tipo['rotulo']) ?></span>
            </div>
        </a>
    </div>
<?php endforeach; ?>
