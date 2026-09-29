<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($evento->titulo) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        :root {
            --cor-primaria: <?= esc($evento->cor_primaria, 'raw') ?>;
            --cor-secundaria: <?= esc($evento->cor_secundaria, 'raw') ?>;
        }
        .hero {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            color: #fff;
        }
        .btn-evento { background-color: var(--cor-primaria); border-color: var(--cor-primaria); color: #fff; }
        .btn-evento:hover { filter: brightness(0.92); color: #fff; }
        .titulo-evento { color: var(--cor-primaria); }
    </style>
</head>
<body class="bg-body-tertiary">
<header class="hero py-5">
    <div class="container text-center">
        <p class="text-uppercase small mb-1 opacity-75"><?= esc(str_replace('_', ' ', $evento->tipo_evento)) ?></p>
        <h1 class="display-5 fw-bold mb-2"><?= esc($evento->titulo) ?></h1>
        <?php if (! empty($evento->subtitulo)): ?>
            <p class="lead mb-3"><?= esc($evento->subtitulo) ?></p>
        <?php endif; ?>

        <?php if ($evento->data_evento): ?>
            <p class="mb-0">
                <?= esc($evento->data_evento->format('d/m/Y')) ?>
                <?php if (! empty($evento->horario)): ?> às <?= esc(substr((string) $evento->horario, 0, 5)) ?><?php endif; ?>
            </p>
        <?php endif; ?>
        <?php if (! empty($evento->local_nome)): ?>
            <p class="mb-0 opacity-75"><?= esc($evento->local_nome) ?></p>
        <?php endif; ?>
    </div>
</header>

<main class="container py-4" style="max-width: 900px;">
    <?= view('templates/partials/flash') ?>

    <?php if (! empty($evento->mensagem_convite)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <p class="mb-0"><?= nl2br(esc($evento->mensagem_convite)) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="h4 titulo-evento mb-3">Lista de presentes</h2>

    <?php if (empty($presentes)): ?>
        <p class="text-muted">A lista de presentes ainda está sendo preparada.</p>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($presentes as $presente): ?>
                <div class="col-sm-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex flex-column">
                            <h3 class="h6"><?= esc($presente['nome']) ?></h3>
                            <?php if (! empty($presente['descricao'])): ?>
                                <p class="text-muted small"><?= esc($presente['descricao']) ?></p>
                            <?php endif; ?>

                            <div class="mt-auto">
                                <?php if ($evento->exibir_valores): ?>
                                    <p class="fw-bold mb-1">R$ <?= number_format((float) $presente['valor'], 2, ',', '.') ?></p>
                                <?php endif; ?>

                                <?php
                                    $meta       = (int) $presente['quantidade_meta'];
                                    $vendidas   = (int) $presente['quantidade_vendida'];
                                    $disponivel = $meta - $vendidas;
                                ?>

                                <?php if ($meta > 1): ?>
                                    <p class="text-muted small mb-2">
                                        <?= $vendidas ?>/<?= $meta ?> cotas presenteadas
                                    </p>
                                <?php endif; ?>

                                <?php if (($presente['tipo'] ?? 'ficticio') === 'real'): ?>
                                    <?php if (! empty($presente['link_afiliado'])): ?>
                                        <a class="btn btn-evento btn-sm w-100" target="_blank" rel="noopener"
                                           href="<?= esc($presente['link_afiliado'], 'attr') ?>">Comprar na loja</a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm w-100" disabled>Indisponível</button>
                                    <?php endif; ?>
                                <?php elseif ($evento->status !== 'publicado'): ?>
                                    <button class="btn btn-secondary btn-sm w-100" disabled>Recebimento encerrado</button>
                                <?php elseif ($disponivel < 1): ?>
                                    <button class="btn btn-secondary btn-sm w-100" disabled>Esgotado</button>
                                <?php else: ?>
                                    <a class="btn btn-evento btn-sm w-100"
                                       href="<?= site_url($evento->slug . '/presentear/' . $presente['id']) ?>">Presentear</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($evento->permite_rsvp): ?>
        <h2 class="h4 titulo-evento mt-5 mb-3">Confirme sua presença</h2>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="post" action="<?= site_url($evento->slug . '/rsvp') ?>" class="row g-3">
                    <?= csrf_field() ?>
                    <div class="col-md-6">
                        <label class="form-label" for="rsvp-nome">Nome</label>
                        <input type="text" class="form-control" id="rsvp-nome" name="nome" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="rsvp-telefone">Telefone <span class="text-muted">(opcional)</span></label>
                        <input type="text" class="form-control" id="rsvp-telefone" name="telefone">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="rsvp-acompanhantes">Acompanhantes</label>
                        <input type="number" min="0" value="0" class="form-control" id="rsvp-acompanhantes" name="quantidade_acompanhantes">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="rsvp-status">Você vai?</label>
                        <select class="form-select" id="rsvp-status" name="status">
                            <option value="confirmado">Sim, estarei presente</option>
                            <option value="recusado">Não poderei ir</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-evento">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($evento->permite_recados): ?>
        <h2 class="h4 titulo-evento mb-3">Deixe um recado</h2>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="post" action="<?= site_url($evento->slug . '/recado') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="recado-nome">Seu nome</label>
                        <input type="text" class="form-control" id="recado-nome" name="nome_autor" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="recado-mensagem">Mensagem</label>
                        <textarea class="form-control" id="recado-mensagem" name="mensagem" rows="3" required></textarea>
                    </div>
                    <button class="btn btn-evento">Enviar recado</button>
                </form>
            </div>
        </div>

        <?php if (! empty($recados)): ?>
            <div class="row g-3">
                <?php foreach ($recados as $recado): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <p class="mb-2"><?= nl2br(esc($recado['mensagem'])) ?></p>
                                <p class="text-muted small mb-0">— <?= esc($recado['nome_autor']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<footer class="text-center text-muted small py-4">
    Página criada com <a href="<?= site_url('/') ?>" class="text-decoration-none">Minha Lista VIP</a>
</footer>
</body>
</html>
