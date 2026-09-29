<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoriaModel extends Model
{
    protected $table         = 'categorias';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';

    /**
     * @var list<string>
     */
    protected $allowedFields = ['nome', 'slug', 'icone', 'ativo', 'ordem'];

    /**
     * @return list<array<string, mixed>>
     */
    public function ativas(): array
    {
        return $this->where('ativo', 1)
            ->orderBy('ordem', 'ASC')
            ->orderBy('nome', 'ASC')
            ->findAll();
    }
}
