<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Galeria de fotos do evento.
 */
class GaleriaModel extends Model
{
    protected $table         = 'evento_galeria';
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
        'imagem',
        'legenda',
        'ordem',
        'ativo',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function doEvento(int $eventoId): array
    {
        return $this->where('evento_id', $eventoId)
            ->orderBy('ordem', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function ativosDoEvento(int $eventoId): array
    {
        return $this->where('evento_id', $eventoId)
            ->where('ativo', 1)
            ->orderBy('ordem', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function um(int $eventoId, int $id): ?array
    {
        return $this->where('evento_id', $eventoId)->where('id', $id)->first();
    }
}
