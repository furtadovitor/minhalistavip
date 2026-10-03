<?php
/**
 * Navegação do workspace da lista (Lista / Dinheiro / Personalização).
 * Fica FORA do menu global, ao lado do conteúdo do evento.
 *
 * @var \App\Entities\Evento $evento
 */
$atual = uri_string();
$base  = 'painel/eventos/' . (int) $evento->id;

$estaAtivo = static function (string $rota) use ($atual): bool {
    return $atual === $rota || str_starts_with($atual, $rota . '/');
};

$grupos = [
    'Lista' => [
        ['sub' => 'presentes', 'icone' => 'bi-gift', 'rotulo' => 'Presentes'],
        ['sub' => 'galeria', 'icone' => 'bi-images', 'rotulo' => 'Galeria'],
        ['sub' => 'recadinhos', 'icone' => 'bi-chat-heart', 'rotulo' => 'Recadinhos'],
        ['sub' => 'convidados', 'icone' => 'bi-people', 'rotulo' => 'Convidados'],
        ['sub' => 'checkin', 'icone' => 'bi-clipboard-check', 'rotulo' => 'Check-in'],
        ['sub' => 'compartilhar', 'icone' => 'bi-share', 'rotulo' => 'Compartilhar'],
    ],
    'Dinheiro' => [
        ['sub' => 'pagamentos', 'icone' => 'bi-cash-coin', 'rotulo' => 'Pagamentos'],
        ['sub' => 'forma-pagamento', 'icone' => 'bi-credit-card', 'rotulo' => 'Forma de pagamento'],
    ],
    'Personalização' => [
        ['sub' => 'funcionalidades', 'icone' => 'bi-sliders', 'rotulo' => 'Funcionalidades'],
        ['sub' => 'aparencia', 'icone' => 'bi-palette', 'rotulo' => 'Aparência'],
        ['sub' => 'informacoes', 'icone' => 'bi-info-circle', 'rotulo' => 'Informações do evento'],
        ['sub' => 'configuracoes', 'icone' => 'bi-gear', 'rotulo' => 'Configurações'],
    ],
];
?>
<a class="evento-voltar" href="<?= site_url('painel') ?>">
    <i class="bi bi-arrow-left"></i>Minhas listas
</a>

<div class="evento-head">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
        <span class="evento-eyebrow">Lista atual</span>
        <div class="d-flex align-items-center gap-1">
            <?php if ($evento->arquivado): ?>
                <span class="badge text-bg-secondary">Arquivada</span>
            <?php endif; ?>
            <span class="badge text-bg-<?= cor_status_evento($evento->status) ?>"><?= esc(rotulo_status_evento($evento->status)) ?></span>
        </div>
    </div>
    <a class="titulo text-truncate d-block" href="<?= site_url($base) ?>" title="<?= esc($evento->titulo) ?>">
        <?= esc($evento->titulo) ?>
    </a>
    <div class="slug text-truncate">/<?= esc($evento->slug) ?></div>
</div>

<nav class="evento-nav d-none d-lg-flex flex-column">
    <?php foreach ($grupos as $rotulo => $itens): ?>
        <div class="evento-group">
            <div class="evento-nav-label"><?= esc($rotulo) ?></div>
            <?php foreach ($itens as $item): ?>
                <?php $rota = $base . '/' . $item['sub']; ?>
                <a class="evento-nav-link<?= $estaAtivo($rota) ? ' active' : '' ?>" href="<?= site_url($rota) ?>">
                    <i class="bi <?= esc($item['icone']) ?>"></i><?= esc($item['rotulo']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</nav>

<!-- Mobile: dropdown "Menu da lista" -->
<div class="dropdown d-lg-none">
    <button class="btn btn-outline-brand w-100 d-flex align-items-center justify-content-between"
            type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <span><i class="bi bi-list me-1"></i>Menu da lista</span>
        <i class="bi bi-chevron-down"></i>
    </button>
    <ul class="dropdown-menu w-100 shadow-sm" style="max-height: 70vh; overflow: auto;">
        <?php foreach ($grupos as $rotulo => $itens): ?>
            <li><h6 class="dropdown-header"><?= esc($rotulo) ?></h6></li>
            <?php foreach ($itens as $item): ?>
                <?php $rota = $base . '/' . $item['sub']; ?>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2<?= $estaAtivo($rota) ? ' active' : '' ?>"
                       href="<?= site_url($rota) ?>">
                        <i class="bi <?= esc($item['icone']) ?>"></i><?= esc($item['rotulo']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </ul>
</div>
