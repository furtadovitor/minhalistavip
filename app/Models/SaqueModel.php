<?php

namespace App\Models;

use CodeIgniter\Model;

class SaqueModel extends Model
{
    protected $table         = 'saques';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'usuario_id',
        'valor',
        'status',
        'chave_pix',
        'observacao',
        'solicitado_em',
        'processado_em',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function doUsuario(int $usuarioId): array
    {
        return $this->where('usuario_id', $usuarioId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Saques ainda não concluídos (recusados/cancelados não bloqueiam saldo,
     * pois o estorno devolve o valor à carteira).
     */
    public function pendentes(int $usuarioId): float
    {
        $row = $this->db->table('saques')
            ->selectSum('valor', 'total')
            ->where('usuario_id', $usuarioId)
            ->whereIn('status', ['solicitado', 'processando'])
            ->get()
            ->getRow();

        return (float) ($row->total ?? 0);
    }
}
