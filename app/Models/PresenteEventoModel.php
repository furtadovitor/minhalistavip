<?php

namespace App\Models;

use CodeIgniter\Model;

class PresenteEventoModel extends Model
{
    protected $table          = 'presentes_evento';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $createdField   = 'criado_em';
    protected $updatedField   = 'atualizado_em';
    protected $deletedField   = 'deletado_em';

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'evento_id',
        'catalogo_id',
        'nome',
        'descricao',
        'imagem',
        'tipo',
        'valor',
        'quantidade_meta',
        'quantidade_vendida',
        'link_afiliado',
        'ativo',
        'ordem',
    ];

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'evento_id'       => 'required|integer',
        'nome'            => 'required|min_length[2]|max_length[180]',
        'tipo'            => 'required|in_list[ficticio,real]',
        'valor'           => 'required|numeric|greater_than_equal_to[0]',
        'quantidade_meta' => 'permit_empty|integer|greater_than[0]',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function ativosDoEvento(int $eventoId): array
    {
        return $this->where('evento_id', $eventoId)
            ->where('ativo', 1)
            ->orderBy('ordem', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}
