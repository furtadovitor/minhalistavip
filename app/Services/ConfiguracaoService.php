<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Acesso à tabela global chave/valor `configuracoes`, com cache em memória
 * por requisição. Usado para taxas, chaves PIX e parâmetros de saque.
 */
class ConfiguracaoService
{
    /**
     * @var array<string, string|null>
     */
    private array $cache = [];

    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function valor(string $chave, ?string $default = null): ?string
    {
        if (! array_key_exists($chave, $this->cache)) {
            $row = $this->db->table('configuracoes')
                ->select('valor')
                ->where('chave', $chave)
                ->get()
                ->getRow();

            $this->cache[$chave] = $row->valor ?? null;
        }

        $valor = $this->cache[$chave];

        return ($valor === null || $valor === '') ? $default : $valor;
    }

    public function texto(string $chave, string $default = ''): string
    {
        return (string) $this->valor($chave, $default);
    }

    public function decimal(string $chave, float $default = 0.0): float
    {
        $valor = $this->valor($chave);

        return $valor !== null && is_numeric($valor) ? (float) $valor : $default;
    }

    public function inteiro(string $chave, int $default = 0): int
    {
        $valor = $this->valor($chave);

        return $valor !== null && is_numeric($valor) ? (int) $valor : $default;
    }
}
