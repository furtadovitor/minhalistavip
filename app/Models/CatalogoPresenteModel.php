<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Catálogo GERAL de presentes, mantido pelo SuperAdmin.
 */
class CatalogoPresenteModel extends Model
{
    protected $table          = 'catalogo_presentes';
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
        'categoria_id',
        'nome',
        'descricao',
        'imagem',
        'tipo',
        'valor_sugerido',
        'link_afiliado',
        'ativo',
        'destaque',
        'ordem',
    ];

    /**
     * Catálogo ativo, com o nome da categoria, opcionalmente filtrado.
     *
     * @param array{categoria_id?: int|null, busca?: string|null} $filtros
     * @return list<array<string, mixed>>
     */
    public function ativosComCategoria(array $filtros = []): array
    {
        $builder = $this->select('catalogo_presentes.*, categorias.nome AS categoria_nome')
            ->join('categorias', 'categorias.id = catalogo_presentes.categoria_id', 'left')
            ->where('catalogo_presentes.ativo', 1);

        if (! empty($filtros['categoria_id'])) {
            $builder->where('catalogo_presentes.categoria_id', (int) $filtros['categoria_id']);
        }

        if (! empty($filtros['busca'])) {
            $builder->like('catalogo_presentes.nome', (string) $filtros['busca']);
        }

        return $builder
            ->orderBy('catalogo_presentes.ordem', 'ASC')
            ->orderBy('catalogo_presentes.nome', 'ASC')
            ->findAll();
    }

    /**
     * Listagem do SuperAdmin (inclui itens inativos).
     *
     * @param array{categoria_id?: int|null, busca?: string|null, ativo?: int|string|null} $filtros
     * @return list<array<string, mixed>>
     */
    public function listarAdmin(array $filtros = []): array
    {
        $builder = $this->select('catalogo_presentes.*, categorias.nome AS categoria_nome')
            ->join('categorias', 'categorias.id = catalogo_presentes.categoria_id', 'left');

        if (! empty($filtros['categoria_id'])) {
            $builder->where('catalogo_presentes.categoria_id', (int) $filtros['categoria_id']);
        }

        if (! empty($filtros['busca'])) {
            $builder->like('catalogo_presentes.nome', (string) $filtros['busca']);
        }

        if (isset($filtros['ativo']) && $filtros['ativo'] !== '' && $filtros['ativo'] !== null) {
            $builder->where('catalogo_presentes.ativo', (int) $filtros['ativo']);
        }

        return $builder
            ->orderBy('catalogo_presentes.ordem', 'ASC')
            ->orderBy('catalogo_presentes.nome', 'ASC')
            ->findAll();
    }
}
