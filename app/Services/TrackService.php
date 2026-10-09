<?php

namespace App\Services;

/**
 * Fila de eventos de conversão (GA4/Meta) para disparo no próximo <head>.
 *
 * Os controllers só "enfileiram" o evento; o partial templates/partials/analytics.php
 * consome a fila e empurra para o dataLayer (funciona tanto em requisições GET
 * quanto após um redirect de POST).
 */
class TrackService
{
    private const SESSAO = 'mlv_track';

    /**
     * Enfileira um evento de conversão.
     *
     * @param array<string, mixed> $params Parâmetros do evento (value, items, ...)
     */
    public static function evento(string $nome, array $params = []): void
    {
        if ($nome === '') {
            return;
        }

        $fila = session()->get(self::SESSAO);

        if (! is_array($fila)) {
            $fila = [];
        }

        $fila[] = ['name' => $nome, 'params' => $params];

        session()->set(self::SESSAO, $fila);
    }

    /**
     * Lê e limpa a fila (chamado uma vez, no <head>).
     *
     * @return list<array{name: string, params: array<string, mixed>}>
     */
    public static function consumir(): array
    {
        $fila = session()->get(self::SESSAO);

        session()->remove(self::SESSAO);

        return is_array($fila) ? $fila : [];
    }
}
