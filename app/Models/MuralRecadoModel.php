<?php

namespace App\Models;

use CodeIgniter\Model;

class MuralRecadoModel extends Model
{
    protected $table         = 'mural_recados';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'evento_id',
        'pedido_id',
        'nome_autor',
        'mensagem',
        'status',
        'publicado_em',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function publicadosDoEvento(int $eventoId): array
    {
        return $this->where('evento_id', $eventoId)
            ->where('status', 'publicado')
            ->orderBy('publicado_em', 'DESC')
            ->findAll();
    }
}
