<?= $this->extend('templates/layouts/app') ?>

<?php
/** Monta a URL da capa (aceita URL externa ou caminho em uploads/). */
$capaUrl = static fn (string $caminho): string => str_starts_with($caminho, 'http') ? $caminho : base_url($caminho);
?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted mb-0 fs-7">
        Estas listas aparecem na Home e em <code>/demo/{slug}</code>. Deixe apenas as que deseja exibir publicadas.
    </p>
    <div class="d-flex flex-wrap gap-2">
        <form method="post" action="<?= site_url('admin/demos/restaurar') ?>"
              data-confirm="Recriar/atualizar os 3 exemplos padrão da plataforma? Os exemplos com o mesmo endereço serão sobrescritos.">
            <?= csrf_field() ?>
            <button class="btn btn-outline-secondary">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurar padrão
            </button>
        </form>
        <a class="btn btn-brand" href="<?= site_url('admin/demos/novo') ?>">
            <i class="bi bi-plus-lg me-1"></i>Nova lista
        </a>
    </div>
</div>

<?php if (empty($demos)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <div class="mb-2" style="font-size: 2.5rem;">🖼️</div>
            <h2 class="h5 fw-bold mb-1">Nenhuma lista de exemplo</h2>
            <p class="text-muted mb-3">Crie uma lista ou restaure os exemplos padrão da plataforma.</p>
            <a class="btn btn-brand" href="<?= site_url('admin/demos/novo') ?>"><i class="bi bi-plus-lg me-1"></i>Criar lista</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($demos as $demo): ?>
            <?php $ativo = ! empty($demo['ativo']); ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="position-relative">
                        <?php if (! empty($demo['capa'])): ?>
                            <img src="<?= esc($capaUrl((string) $demo['capa']), 'attr') ?>" alt="Capa de <?= esc($demo['titulo']) ?>"
                                 class="w-100 object-fit-cover" style="height: 160px;">
                        <?php else: ?>
                            <div class="w-100 bg-light text-muted d-flex align-items-center justify-content-center" style="height: 160px;">
                                <i class="bi bi-image fs-2"></i>
                            </div>
                        <?php endif; ?>

                        <span class="position-absolute top-0 start-0 m-2 badge text-bg-light fw-semibold">
                            <?= esc((string) $demo['icone']) ?> <?= esc($demo['tipo']) ?>
                        </span>

                        <?php if (! $ativo): ?>
                            <span class="position-absolute top-0 end-0 m-2 badge text-bg-secondary">Oculta</span>
                        <?php endif; ?>
                    </div>

                    <div class="card-body pb-2">
                        <h3 class="h6 fw-bold mb-1"><?= esc($demo['titulo']) ?></h3>
                        <p class="text-muted fs-8 mb-2">
                            <?php if (! empty($demo['data_texto'])): ?><?= esc($demo['data_texto']) ?><?php endif; ?>
                            <?php if (! empty($demo['local'])): ?><?= ! empty($demo['data_texto']) ? ' • ' : '' ?><?= esc($demo['local']) ?><?php endif; ?>
                        </p>
                        <code class="fs-8 text-muted">/demo/<?= esc($demo['slug']) ?></code>
                    </div>

                    <div class="card-footer bg-white border-0 d-flex flex-wrap gap-1 justify-content-end">
                        <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('demo/' . $demo['slug']) ?>"
                           target="_blank" title="Ver no site"><i class="bi bi-box-arrow-up-right"></i></a>

                        <form method="post" action="<?= site_url('admin/demos/' . $demo['id'] . '/alternar') ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-<?= $ativo ? 'warning' : 'success' ?>">
                                <?= $ativo ? 'Ocultar' : 'Publicar' ?>
                            </button>
                        </form>

                        <a class="btn btn-sm btn-brand" href="<?= site_url('admin/demos/' . $demo['id'] . '/editar') ?>">
                            <i class="bi bi-pencil me-1"></i>Editar
                        </a>

                        <form method="post" action="<?= site_url('admin/demos/' . $demo['id'] . '/excluir') ?>"
                              data-confirm="Remover esta lista de exemplo?">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger" title="Remover"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
