<?= $this->extend('templates/layouts/app') ?>

<?php
$status = (string) $convidado['status'];
$badge = ['pendente' => 'warning', 'confirmado' => 'success', 'recusado' => 'secondary'][$status] ?? 'secondary';
$rotulo = ['pendente' => 'Aguardando aprovação', 'confirmado' => 'Confirmado', 'recusado' => 'Recusado'][$status] ?? ucfirst($status);
$base = site_url('painel/eventos/' . $evento->id . '/convidados/' . $convidado['id']);
$adultos = 0;
$criancas = 0;
$bebes = 0;
$semCategoria = 0;
foreach ($acompanhantes as $a) {
    $categoria = $a['categoria'] ?? null;

    if ($categoria === 'adulto') {
        $adultos++;
    } elseif ($categoria === 'crianca') {
        $criancas++;
    } elseif ($categoria === 'bebe') {
        $bebes++;
    } elseif (($a['menor'] ?? null) !== null) {
        (int) $a['menor'] === 1 ? $criancas++ : $adultos++;
    } else {
        $semCategoria++;
    }
}
?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <p class="text-muted mb-0">
            <?= esc($evento->titulo) ?> ·
            <span class="badge text-bg-<?= $badge ?>"><?= esc($rotulo) ?></span>
        </p>
        <p class="text-muted fs-8 mb-0">Acompanhantes com nome completo e categoria (adulto, criança ou bebê).</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="<?= site_url('painel/eventos/' . $evento->id . '/convidados') ?>">Voltar</a>
        <?php if ($status !== 'confirmado'): ?>
            <form method="post" action="<?= $base ?>/aprovar" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-success"><i class="bi bi-check2 me-1"></i>Aprovar</button>
            </form>
        <?php endif; ?>
        <?php if ($status !== 'recusado'): ?>
            <form method="post" action="<?= $base ?>/recusar" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-outline-warning">Recusar</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted fw-semibold mb-3">Dados do convidado</h2>
                <dl class="row mb-0 fs-7">
                    <dt class="col-5 text-muted fw-normal">Nome</dt>
                    <dd class="col-7"><?= esc($convidado['nome']) ?></dd>

                    <dt class="col-5 text-muted fw-normal">Telefone</dt>
                    <dd class="col-7"><?= esc((string) $convidado['telefone'] ?: '—') ?></dd>

                    <dt class="col-5 text-muted fw-normal">E-mail</dt>
                    <dd class="col-7 text-break"><?= esc((string) $convidado['email'] ?: '—') ?></dd>

                    <dt class="col-5 text-muted fw-normal">Acompanhantes (declarados)</dt>
                    <dd class="col-7"><?= (int) $convidado['quantidade_acompanhantes'] ?></dd>

                    <dt class="col-5 text-muted fw-normal">Pessoas (titular + acomp.)</dt>
                    <dd class="col-7 fw-semibold"><?= (int) $convidado['quantidade_acompanhantes'] + 1 ?></dd>

                    <?php if (! empty($convidado['observacao'])): ?>
                        <dt class="col-5 text-muted fw-normal">Observação</dt>
                        <dd class="col-7"><?= nl2br(esc($convidado['observacao'])) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h2 class="h6 text-uppercase text-muted fw-semibold mb-0">Acompanhantes detalhados</h2>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge text-bg-secondary"><?= $adultos ?> adulto/adolescente</span>
                        <span class="badge text-bg-info text-dark"><?= $criancas ?> criança(s)</span>
                        <span class="badge text-bg-warning text-dark"><?= $bebes ?> bebê(s)</span>
                        <?php if ($semCategoria > 0): ?>
                            <span class="badge text-bg-light border"><?= $semCategoria ?> sem categoria</span>
                        <?php endif; ?>
                    </div>
                </div>

                <form method="post" action="<?= $base ?>/acompanhantes" class="row g-2 align-items-end mb-3">
                    <?= csrf_field() ?>
                    <div class="col-sm-5">
                        <label class="form-label fw-semibold fs-7 mb-1" for="a-nome">Nome completo *</label>
                        <input type="text" class="form-control" id="a-nome" name="nome" required placeholder="Ex.: Maria Fernanda Souza">
                    </div>
                    <div class="col-sm-5">
                        <label class="form-label fw-semibold fs-7 mb-1" for="a-categoria">Categoria</label>
                        <select class="form-select" id="a-categoria" name="categoria">
                            <option value="adulto">Adulto ou adolescente</option>
                            <option value="crianca">Criança (5 a 12 anos)</option>
                            <option value="bebe">Bebê (menos de 5 anos)</option>
                        </select>
                    </div>
                    <div class="col-sm-2 d-grid">
                        <button class="btn btn-brand"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </form>

                <?php if (empty($acompanhantes)): ?>
                    <p class="text-muted fs-7 mb-0">
                        Nenhum acompanhante detalhado ainda. O convidado declarou
                        <strong><?= (int) $convidado['quantidade_acompanhantes'] ?></strong> acompanhante(s).
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome completo</th>
                                    <th>Categoria</th>
                                    <th>Classificação</th>
                                    <th class="text-end">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($acompanhantes as $a): ?>
                                <?php $cat = $a['categoria'] ?? null; ?>
                                <tr>
                                    <td class="fw-semibold"><?= esc($a['nome']) ?></td>
                                    <td>
                                        <span class="badge text-bg-<?= cor_categoria_acompanhante($cat) ?> <?= in_array($cat, ['crianca', 'bebe'], true) ? 'text-dark' : '' ?>">
                                            <?= esc(rotulo_categoria_acompanhante($cat)) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($a['menor'] === null): ?>
                                            <span class="badge text-bg-light border">Não informado</span>
                                        <?php elseif ((int) $a['menor'] === 1): ?>
                                            <span class="badge text-bg-info text-dark">Menor de idade</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary">Maior de idade</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <form method="post" class="d-inline"
                                              action="<?= $base . '/acompanhantes/' . $a['id'] . '/remover' ?>"
                                              onsubmit="return confirm('Remover este acompanhante?');">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-danger btn-icon"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <p class="text-muted fs-8 mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    A classificação é automática: <strong>criança</strong> e <strong>bebê</strong> contam como menores de idade.
                </p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
