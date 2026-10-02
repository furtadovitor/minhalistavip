<?= $this->extend('templates/layouts/app') ?>

<?php
$valor = static fn (string $campo, $padrao = '') => old($campo, $evento->{$campo} ?? $padrao);
$dataEvento = old('data_evento', $evento->data_evento !== null ? $evento->data_evento->format('Y-m-d') : '');
$horario    = old('horario', substr((string) $evento->horario, 0, 5));
?>

<?= $this->section('conteudo') ?>
<form method="post" action="<?= site_url('painel/eventos/' . $evento->id . '/informacoes') ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Dados do evento</h2>

                    <div class="mb-3">
                        <label class="form-label" for="titulo">Título *</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required
                               value="<?= esc($valor('titulo')) ?>" placeholder="Ex.: Casamento de Ana &amp; João">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="subtitulo">Subtítulo</label>
                        <input type="text" class="form-control" id="subtitulo" name="subtitulo"
                               value="<?= esc($valor('subtitulo')) ?>">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="tipo_evento">Tipo *</label>
                            <select class="form-select" id="tipo_evento" name="tipo_evento" required>
                                <?php foreach ($tipos as $chave => $rotulo): ?>
                                    <option value="<?= esc($chave) ?>" <?= $valor('tipo_evento', 'outro') === $chave ? 'selected' : '' ?>>
                                        <?= esc($rotulo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="data_evento">Data</label>
                            <input type="date" class="form-control" id="data_evento" name="data_evento" value="<?= esc($dataEvento) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="horario">Horário</label>
                            <input type="time" class="form-control" id="horario" name="horario" value="<?= esc($horario) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label" for="local_nome">Local</label>
                            <input type="text" class="form-control" id="local_nome" name="local_nome" value="<?= esc($valor('local_nome')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="local_endereco">Endereço</label>
                            <input type="text" class="form-control" id="local_endereco" name="local_endereco" value="<?= esc($valor('local_endereco')) ?>">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label" for="mensagem_convite">Mensagem para os convidados</label>
                        <textarea class="form-control" id="mensagem_convite" name="mensagem_convite" rows="3"><?= esc($valor('mensagem_convite')) ?></textarea>
                    </div>

                    <div class="mt-3">
                        <label class="form-label" for="descricao">Descrição interna <span class="text-muted">(não aparece para o convidado)</span></label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="2"><?= esc($valor('descricao')) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Salvar alterações</button>
                <a class="btn btn-outline-secondary" href="<?= site_url('painel/eventos/' . $evento->id) ?>">Voltar</a>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
