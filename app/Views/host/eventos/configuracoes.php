<?= $this->extend('templates/layouts/app') ?>

<?= $this->section('conteudo') ?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Endereço público</h2>

                <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/configuracoes') ?>">
                    <?= csrf_field() ?>
                    <label class="form-label" for="slug">Endereço da lista (slug)</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= esc(parse_url(base_url(), PHP_URL_HOST) ?: 'minhalistavip.com.br') ?>/</span>
                        <input type="text" class="form-control" id="slug" name="slug"
                               value="<?= esc(old('slug', $evento->slug)) ?>">
                    </div>
                    <div class="form-text">Use um endereço curto e fácil de lembrar. Alterar o link invalida o anterior.</div>
                    <button class="btn btn-brand mt-3"><i class="bi bi-check-lg me-1"></i>Salvar endereço</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Publicação</h2>
                <p class="text-muted fs-7">
                    Status atual:
                    <span class="badge text-bg-<?= cor_status_evento($evento->status) ?>"><?= esc(rotulo_status_evento($evento->status)) ?></span>
                    <?php if ($evento->arquivado): ?><span class="badge text-bg-secondary">Arquivada</span><?php endif; ?>
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/publicar') ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-outline-<?= $evento->status === 'publicado' ? 'warning' : 'success' ?>">
                            <i class="bi bi-broadcast me-1"></i><?= $evento->status === 'publicado' ? 'Despublicar' : 'Publicar lista' ?>
                        </button>
                    </form>

                    <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/arquivar') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="arquivar" value="<?= $evento->arquivado ? '0' : '1' ?>">
                        <button class="btn btn-outline-secondary">
                            <i class="bi <?= $evento->arquivado ? 'bi-arrow-counterclockwise' : 'bi-archive' ?> me-1"></i>
                            <?= $evento->arquivado ? 'Reativar lista' : 'Arquivar lista' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 border-danger-subtle">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-danger mb-3">Zona de risco</h2>
                <p class="text-muted fs-7">A exclusão remove a lista e os presentes associados. Não pode ser desfeita.</p>
                <form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/excluir') ?>"
                      onsubmit="return confirm('Excluir definitivamente esta lista?');">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-danger w-100"><i class="bi bi-trash me-1"></i>Excluir lista</button>
                </form>
            </div>
        </div>

        <a class="btn btn-outline-secondary w-100 mt-3" href="<?= site_url('painel/eventos/' . $evento->id) ?>">Voltar</a>
    </div>
</div>
<?= $this->endSection() ?>
