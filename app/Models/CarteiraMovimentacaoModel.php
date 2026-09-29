<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Extrato da carteira do organizador.
 * Tabela sem updated_em: o timestamp de criação é setado manualmente.
 */
class CarteiraMovimentacaoModel extends Model
{
    protected $table         = 'carteira_movimentacoes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'usuario_id',
        'evento_id',
        'pedido_id',
        'tipo',
        'valor',
        'saldo_apos',
        'descricao',
        'criado_em',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function doUsuario(int $usuarioId, int $limite = 100): array
    {
        return $this->where('usuario_id', $usuarioId)
            ->orderBy('id', 'DESC')
            ->limit($limite)
            ->findAll();
    }

    /**
     * Soma dos lançamentos por tipo (valores com sinal: taxa/saque são negativos).
     *
     * @param list<string> $tipos
     */
    public function soma(int $usuarioId, array $tipos): float
    {
        if ($tipos === []) {
            return 0.0;
        }

        $row = $this->db->table('carteira_movimentacoes')
            ->selectSum('valor', 'total')
            ->where('usuario_id', $usuarioId)
            ->whereIn('tipo', $tipos)
            ->get()
            ->getRow();

        return (float) ($row->total ?? 0);
    }
}
