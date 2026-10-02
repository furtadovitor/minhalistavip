<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<form class="row g-2 align-items-end mb-3" method="get"
      action="<?= site_url('painel/eventos/' . $evento->id . '/recadinhos') ?>">
    <div class="col-sm-4">
        <label class="form-label fs-8 mb-1" for="status">Status</label>
        <select class="form-select form-select-sm" id="status" name="status">
            <option value="">Todos</option>
            <?php foreach (['pendente' => 'Pendentes', 'publicado' => 'Publicados', 'oculto' => 'Ocultos'] as $chave => $rotulo): ?>
                <option value="<?= esc($chave) ?>" <?= ($filtros['status'] ?? null) === $chave ? 'selected' : '' ?>><?= esc($rotulo) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-sm-5">
        <label class="form-label fs-8 mb-1" for="busca">Buscar</label>
        <input type="text" class="form-control form-control-sm" id="busca" name="busca"
               value="<?= esc((string) ($filtros['busca'] ?? '')) ?>" placeholder="Autor ou mensagem">
    </div>
    <div class="col-sm-3">
        <button class="btn btn-sm btn-outline-brand w-100"><i class="bi bi-search me-1"></i>Filtrar</button>
    </div>
</form>

<?php if (empty($recados)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-chat-heart fs-2 d-block mb-2"></i>
            Nenhum recadinho por aqui ainda.
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($recados as $recado): ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold"><?= esc((string) $recado['nome_autor']) ?></span>
                            <span class="badge text-bg-<?= $recado['status'] === 'publicado' ? 'success' : ($recado['status'] === 'oculto' ? 'secondary' : 'warning') ?>">
                                <?= esc(ucfirst((string) $recado['status'])) ?>
                            </span>
                        </div>
                        <p class="mb-3"><?= nl2br(esc((string) $recado['mensagem'])) ?></p>
                        <div class="mt-auto d-flex flex-wrap gap-1 align-items-center">
                            <span class="text-muted fs-8 me-auto"><?= esc((string) $recado['criado_em']) ?></span>

                            <?php if ($recado['status'] !== 'publicado'): ?>
                                <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/recadinhos/' . $recado['id'] . '/publicar') ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg me-1"></i>Publicar</button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/recadinhos/' . $recado['id'] . '/ocultar') ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye-slash me-1"></i>Ocultar</button>
                                </form>
                            <?php endif; ?>

                            <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/recadinhos/' . $recado['id'] . '/excluir') ?>"
                                  data-confirm="Remover este recado?">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger btn-icon"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
