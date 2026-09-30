<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<form class="card border-0 shadow-sm rounded-4 mb-3" method="get" action="<?= site_url('admin/usuarios') ?>">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-busca">Buscar</label>
                <input type="search" class="form-control" id="f-busca" name="busca" value="<?= esc($filtros['busca'] ?? '') ?>"
                       placeholder="Nome ou e-mail">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-nivel">Nível</label>
                <select class="form-select" id="f-nivel" name="nivel">
                    <option value="">Todos</option>
                    <option value="organizador" <?= ($filtros['nivel'] ?? '') === 'organizador' ? 'selected' : '' ?>>Organizador</option>
                    <option value="superadmin" <?= ($filtros['nivel'] ?? '') === 'superadmin' ? 'selected' : '' ?>>SuperAdmin</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold fs-7 mb-1" for="f-status">Status</label>
                <select class="form-select" id="f-status" name="status">
                    <option value="">Todos</option>
                    <?php foreach (['ativo', 'inativo', 'suspenso'] as $opcao): ?>
                        <option value="<?= esc($opcao) ?>" <?= ($filtros['status'] ?? '') === $opcao ? 'selected' : '' ?>>
                            <?= esc(ucfirst($opcao)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-brand w-100"><i class="bi bi-funnel me-1"></i>Filtrar</button>
            </div>
        </div>
    </div>
</form>

<?php if (empty($usuarios)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <p class="mb-1 fw-semibold">Nenhum usuário encontrado.</p>
            <p class="text-muted mb-0">Ajuste os filtros da busca.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Usuário</th>
                        <th>Nível</th>
                        <th>Status</th>
                        <th>Cadastro</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= esc($u->nome) ?></div>
                            <div class="text-muted fs-8"><?= esc($u->email) ?></div>
                        </td>
                        <td>
                            <span class="badge text-bg-<?= $u->isSuperAdmin() ? 'dark' : 'light' ?> text-<?= $u->isSuperAdmin() ? 'white' : 'dark' ?> border">
                                <?= esc(ucfirst($u->nivel)) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge text-bg-<?= $u->status === 'ativo' ? 'success' : ($u->status === 'suspenso' ? 'danger' : 'secondary') ?>">
                                <?= esc(ucfirst((string) $u->status)) ?>
                            </span>
                        </td>
                        <td class="small text-muted">
                            <?= $u->criado_em !== null ? esc($u->criado_em->format('d/m/Y')) : '—' ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap gap-1 justify-content-end">
                                <a class="btn btn-sm btn-outline-brand" href="<?= site_url('admin/usuarios/' . $u->id) ?>">Ver</a>
                                <?php if ((int) $u->id !== (int) $usuarioAtualId): ?>
                                    <form method="post" class="d-inline" action="<?= site_url('admin/usuarios/' . $u->id . '/alternar') ?>"
                                          onsubmit="return confirm('Alterar o status deste usuário?');">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-<?= $u->status === 'ativo' ? 'danger' : 'success' ?>">
                                            <?= $u->status === 'ativo' ? 'Suspender' : 'Ativar' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
