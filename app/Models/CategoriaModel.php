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

    /**
     * Todas as categorias (para o SuperAdmin), com a contagem de itens do catálogo.
     *
     * @return list<array<string, mixed>>
     */
    public function todasComContagem(): array
    {
        $linhas = $this->orderBy('ordem', 'ASC')->orderBy('nome', 'ASC')->findAll();

        foreach ($linhas as &$linha) {
            $linha['total_itens'] = $this->db->table('catalogo_presentes')
                ->where('categoria_id', (int) $linha['id'])
                ->where('deletado_em IS NULL')
                ->countAllResults();
        }

        return $linhas;
    }
}
