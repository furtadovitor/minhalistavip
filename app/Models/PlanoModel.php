<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Planos de assinatura da plataforma (mantidos pelo SuperAdmin).
 */
class PlanoModel extends Model
{
    protected $table         = 'planos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'nome',
        'slug',
        'descricao',
        'preco',
        'periodo',
        'percentual_taxa',
        'limite_eventos',
        'recursos',
        'ativo',
        'ordem',
    ];

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'nome'    => 'required|min_length[2]|max_length[120]',
        'slug'    => 'required|alpha_dash|max_length[140]',
        'periodo' => 'required|in_list[mensal,anual,vitalicio]',
        'preco'   => 'required|numeric|greater_than_equal_to[0]',
    ];

    /**
     * @var array<string, string>
     */
    protected $validationMessages = [
        'slug' => [
            'is_unique' => 'Já existe um plano com este identificador (slug).',
        ],
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listar(): array
    {
        return $this->orderBy('ordem', 'ASC')->orderBy('preco', 'ASC')->findAll();
    }
}
