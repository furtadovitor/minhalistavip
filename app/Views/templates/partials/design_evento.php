<?php
/**
 * Cabeçalho temático das páginas públicas do evento (hotsite, checkout, pedido).
 *
 * Uso: <head><?= view('templates/partials/design_evento', [
 *     'titulo' => '...', 'corPrimaria' => '#...', 'corSecundaria' => '#...', 'escuro' => false,
 * ]) ?></head>
 *
 * @var string|null $titulo
 * @var string|null $corPrimaria
 * @var string|null $corSecundaria
 * @var bool|null   $escuro
 */
$titulo        = $titulo ?? 'Evento';
$corPrimaria   = $corPrimaria ?? '#4F46E5';
$corSecundaria = $corSecundaria ?? '#10B981';
$escuro        = $escuro ?? false;
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($titulo) ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    :root {
        --cor-primaria: <?= $corPrimaria ?>;
        --cor-secundaria: <?= $corSecundaria ?>;
    }
    body { font-family: 'Inter', system-ui, sans-serif; }
    h1, h2, h3, h4, h5, .font-display { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }

    .hero { background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria)); color: #fff; }
    .btn-evento { background-color: var(--cor-primaria); border-color: var(--cor-primaria); color: #fff; font-weight: 600; }
    .btn-evento:hover { filter: brightness(0.92); color: #fff; }
    .btn-outline-evento { color: var(--cor-primaria); border-color: var(--cor-primaria); font-weight: 600; }
    .btn-outline-evento:hover { background-color: var(--cor-primaria); color: #fff; }
    .titulo-evento { color: var(--cor-primaria); }
    .card { border-radius: 1rem; }
    .fs-7 { font-size: .875rem; }
    .fs-8 { font-size: .75rem; }

    .tema-escuro { background-color: #0B0B12; color: #E5E7EB; }
    .tema-escuro .card { background-color: #15151F; color: #E5E7EB; border: 1px solid rgba(255, 255, 255, .08) !important; }
    .tema-escuro .text-muted { color: #9CA3AF !important; }
    .tema-escuro .border-top, .tema-escuro .border-bottom { border-color: rgba(255, 255, 255, .12) !important; }
    .faixa-demo { background: #111827; color: #fff; }
</style>
