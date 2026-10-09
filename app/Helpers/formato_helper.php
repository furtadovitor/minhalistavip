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

if (! function_exists('cor_hex')) {
    /**
     * Normaliza uma cor para "#rrggbb".
     *
     * Aceita valores com "%23" (o "#" URL-encoded), sem "#" e a forma curta
     * (#abc). Devolve $padrao quando o valor é inválido — protege o CSS do tema
     * (uma cor inválida deixava o herói/botões brancos).
     */
    function cor_hex(?string $cor, string $padrao = '#722ED4'): string
    {
        $cor = trim((string) $cor);

        if ($cor === '') {
            return $padrao;
        }

        if (str_contains($cor, '%')) {
            $cor = urldecode($cor);
        }

        $cor = ltrim($cor, '#');

        if (preg_match('/^[0-9a-fA-F]{3}$/', $cor) === 1) {
            $cor = $cor[0] . $cor[0] . $cor[1] . $cor[1] . $cor[2] . $cor[2];
        }

        if (preg_match('/^[0-9a-fA-F]{6}$/', $cor) !== 1) {
            return $padrao;
        }

        return '#' . strtolower($cor);
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

if (! function_exists('tipos_evento')) {
    /**
     * Catálogo central dos tipos de evento (ver TipoEventoService).
     *
     * @return array<string, array{slug: string, rotulo: string, icone: string, tema: string, cor_primaria: string, cor_secundaria: string}>
     */
    function tipos_evento(): array
    {
        return \App\Services\TipoEventoService::todos();
    }
}

if (! function_exists('rotulo_tipo_evento')) {
    function rotulo_tipo_evento(string $tipo): string
    {
        return \App\Services\TipoEventoService::rotulo($tipo);
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

if (! function_exists('rotulo_categoria_acompanhante')) {
    function rotulo_categoria_acompanhante(?string $categoria): string
    {
        return [
            'adulto'  => 'Adulto ou adolescente',
            'crianca' => 'Criança (5 a 12 anos)',
            'bebe'    => 'Bebê (menos de 5 anos)',
        ][(string) $categoria] ?? 'Não informado';
    }
}

if (! function_exists('cor_categoria_acompanhante')) {
    function cor_categoria_acompanhante(?string $categoria): string
    {
        return [
            'adulto'  => 'secondary',
            'crianca' => 'info',
            'bebe'    => 'warning',
        ][(string) $categoria] ?? 'light';
    }
}
