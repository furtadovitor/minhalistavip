<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted mb-0">
        <?= count($eventos) ?> evento(s) · <?= (int) $publicados ?> publicado(s)
    </p>
    <a class="btn btn-brand" href="<?= site_url('painel/eventos/novo') ?>">
        <i class="bi bi-plus-lg me-1"></i>Novo evento
    </a>
</div>

<?php if (empty($eventos)): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <div class="bg-indigo-100 text-indigo-700 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:64px;height:64px;"><i class="bi bi-calendar-event fs-3"></i></div>
            <p class="mb-1 fw-semibold">Você ainda não criou nenhum evento.</p>
            <p class="text-muted">Crie sua página, monte a lista de presentes e compartilhe o link com os convidados.</p>
            <a class="btn btn-brand" href="<?= site_url('painel/eventos/novo') ?>">Criar meu primeiro evento</a>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Evento</th>
                        <th>Tipo</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($eventos as $evento):
                    $capa = ! empty($evento->imagem_capa) ? base_url($evento->imagem_capa) : null;
                    $grad = 'linear-gradient(135deg, ' . esc($evento->cor_primaria ?: '#4F46E5', 'attr') . ', ' . esc($evento->cor_secundaria ?: '#10B981', 'attr') . ')';
                    $publicado = $evento->status === 'publicado';
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 flex-shrink-0 overflow-hidden"
                                     style="width:48px;height:48px;background:<?= $grad ?>;">
                                    <?php if ($capa): ?>
                                        <img src="<?= esc($capa, 'attr') ?>" alt="" class="w-100 h-100 object-fit-cover">
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?= esc($evento->titulo) ?></div>
                                    <div class="text-muted fs-8">/<?= esc($evento->slug) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="small"><?= esc(rotulo_tipo_evento($evento->tipo_evento)) ?></td>
                        <td class="small">
                            <?= $evento->data_evento !== null ? esc($evento->data_evento->format('d/m/Y')) : '—' ?>
                        </td>
                        <td>
                            <span class="badge text-bg-<?= cor_status_evento($evento->status) ?>">
                                <?= esc(rotulo_status_evento($evento->status)) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex align-items-center gap-1">
                                <a class="btn btn-sm btn-brand" href="<?= site_url('painel/eventos/' . $evento->id) ?>">
                                    <i class="bi bi-sliders me-1"></i>Gerenciar
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary btn-icon" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false" title="Mais ações">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="<?= site_url('painel/eventos/' . $evento->id . '/presentes') ?>">
                                                <i class="bi bi-gift me-2"></i>Presentes
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= site_url('painel/eventos/' . $evento->id . '/convidados') ?>">
                                                <i class="bi bi-people me-2"></i>Convidados
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= site_url('painel/eventos/' . $evento->id . '/editar') ?>">
                                                <i class="bi bi-pencil me-2"></i>Editar
                                            </a>
                                        </li>
                                        <?php if ($publicado): ?>
                                            <li>
                                                <a class="dropdown-item" target="_blank" href="<?= site_url($evento->slug) ?>">
                                                    <i class="bi bi-box-arrow-up-right me-2"></i>Ver página
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/publicar') ?>">
                                                <?= csrf_field() ?>
                                                <button class="dropdown-item">
                                                    <i class="bi <?= $publicado ? 'bi-eye-slash' : 'bi-broadcast' ?> me-2"></i>
                                                    <?= $publicado ? 'Despublicar' : 'Publicar' ?>
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/excluir') ?>"
                                                  onsubmit="return confirm('Remover este evento e a lista de presentes?');">
                                                <?= csrf_field() ?>
                                                <button class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Excluir
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
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
