<?= $this->extend('templates/layouts/app') ?>

<?php
$marcado = static function (string $campo, bool $padrao = true) use ($evento): bool {
    $antigo = old($campo);

    if ($antigo !== null) {
        return (bool) $antigo;
    }

    return (bool) ($evento->{$campo} ?? $padrao);
};
?>

<?= $this->section('conteudo') ?>
<form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/funcionalidades') ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">O que os convidados podem fazer</h2>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="permite_rsvp"
                               name="permite_rsvp" value="1" <?= $marcado('permite_rsvp') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="permite_rsvp">
                            <span class="fw-semibold">Confirmação de presença (RSVP)</span>
                            <span class="d-block text-muted fs-8">Permite confirmar presença e informar acompanhantes.</span>
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="permite_recados"
                               name="permite_recados" value="1" <?= $marcado('permite_recados') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="permite_recados">
                            <span class="fw-semibold">Mural de recados</span>
                            <span class="d-block text-muted fs-8">Convidados podem deixar mensagens; você aprova antes de publicar.</span>
                        </label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="exibir_valores"
                               name="exibir_valores" value="1" <?= $marcado('exibir_valores') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="exibir_valores">
                            <span class="fw-semibold">Exibir valores na página pública</span>
                            <span class="d-block text-muted fs-8">Desative para mostrar apenas "presentear sem valor".</span>
                        </label>
                    </div>

                    <hr class="my-4">

                    <label class="form-label fw-semibold" for="limite_convidados">Limite de convidados</label>
                    <input type="number" min="1" class="form-control" id="limite_convidados" name="limite_convidados"
                           value="<?= esc(old('limite_convidados', $evento->limite_convidados)) ?>" placeholder="vazio = sem limite">
                    <div class="form-text">Máximo de <strong>pessoas</strong> confirmadas (com acompanhantes).</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Salvar</button>
                <a class="btn btn-outline-secondary" href="<?= site_url('painel/eventos/' . $evento->id) ?>">Voltar</a>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
