<?php

/**
 * Helpers de formatação reutilizados nas views.
 */

if (! function_exists('moeda_brl')) {
    function moeda_brl(float|int|string|null $valor): string
    {
        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
    }
}

if (! function_exists('rotulo_status_evento')) {
    function rotulo_status_evento(string $status): string
    {
        return [
            'rascunho'  => 'Rascunho',
            'publicado' => 'Publicado',
            'encerrado' => 'Encerrado',
        ][$status] ?? ucfirst($status);
    }
}

if (! function_exists('cor_status_evento')) {
    function cor_status_evento(string $status): string
    {
        return [
            'rascunho'  => 'secondary',
            'publicado' => 'success',
            'encerrado' => 'dark',
        ][$status] ?? 'secondary';
    }
}

if (! function_exists('rotulo_tipo_evento')) {
    function rotulo_tipo_evento(string $tipo): string
    {
        return [
            'casamento'   => 'Casamento',
            'cha_bebe'    => 'Chá de Bebê',
            'cha_fraldas' => 'Chá de Fraldas',
            'cha_panela'  => 'Chá de Panela',
            'aniversario' => 'Aniversário',
            'formatura'   => 'Formatura',
            'corporativo' => 'Corporativo',
            'outro'       => 'Outro',
        ][$tipo] ?? ucfirst($tipo);
    }
}

if (! function_exists('rotulo_tipo_presente')) {
    function rotulo_tipo_presente(string $tipo): string
    {
        return $tipo === 'real' ? 'Presente real (afiliado)' : 'Cota em dinheiro';
    }
}

if (! function_exists('rotulo_quem_paga_taxa')) {
    function rotulo_quem_paga_taxa(string $quemPaga): string
    {
        return $quemPaga === 'organizador' ? 'Descontada do organizador' : 'Paga pelo convidado';
    }
}

if (! function_exists('rotulo_status_pedido')) {
    function rotulo_status_pedido(string $status): string
    {
        return [
            'pendente'    => 'Aguardando pagamento',
            'pago'        => 'Pago',
            'cancelado'   => 'Cancelado',
            'expirado'    => 'Expirado',
            'reembolsado' => 'Reembolsado',
        ][$status] ?? ucfirst($status);
    }
}

if (! function_exists('cor_status_pedido')) {
    function cor_status_pedido(string $status): string
    {
        return [
            'pendente'    => 'warning',
            'pago'        => 'success',
            'cancelado'   => 'secondary',
            'expirado'    => 'dark',
            'reembolsado' => 'info',
        ][$status] ?? 'secondary';
    }
}

if (! function_exists('rotulo_tipo_movimentacao')) {
    function rotulo_tipo_movimentacao(string $tipo): string
    {
        return [
            'credito' => 'Crédito de presente',
            'taxa'    => 'Taxa da plataforma',
            'saque'   => 'Saque',
            'estorno' => 'Estorno',
            'ajuste'  => 'Ajuste',
        ][$tipo] ?? ucfirst($tipo);
    }
}

if (! function_exists('rotulo_status_saque')) {
    function rotulo_status_saque(string $status): string
    {
        return [
            'solicitado'  => 'Solicitado',
            'processando' => 'Processando',
            'pago'        => 'Pago',
            'recusado'    => 'Recusado',
            'cancelado'   => 'Cancelado',
        ][$status] ?? ucfirst($status);
    }
}

if (! function_exists('cor_status_saque')) {
    function cor_status_saque(string $status): string
    {
        return [
            'solicitado'  => 'warning',
            'processando' => 'info',
            'pago'        => 'success',
            'recusado'    => 'danger',
            'cancelado'   => 'secondary',
        ][$status] ?? 'secondary';
    }
}
