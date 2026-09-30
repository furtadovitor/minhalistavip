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

    /**
     * Todas as configurações, agrupadas por `grupo`.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public function todas(): array
    {
        $linhas = $this->db->table('configuracoes')
            ->orderBy('grupo', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $grupos = [];

        foreach ($linhas as $linha) {
            $grupos[$linha['grupo']][] = $linha;
        }

        return $grupos;
    }

    /**
     * Cria ou atualiza uma configuração (upsert).
     */
    public function definir(string $chave, ?string $valor, string $grupo = 'geral', ?string $descricao = null): void
    {
        $agora = date('Y-m-d H:i:s');
        $builder = $this->db->table('configuracoes');

        if ($builder->where('chave', $chave)->countAllResults() > 0) {
            $this->db->table('configuracoes')->where('chave', $chave)->update([
                'valor'         => $valor,
                'atualizado_em' => $agora,
            ]);
        } else {
            $this->db->table('configuracoes')->insert([
                'chave'         => $chave,
                'valor'         => $valor,
                'grupo'         => $grupo,
                'descricao'     => $descricao,
                'criado_em'     => $agora,
                'atualizado_em' => $agora,
            ]);
        }

        $this->cache[$chave] = $valor;
    }
}
