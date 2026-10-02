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

    /**
     * Todos os recados do evento (para o painel, com filtro opcional).
     *
     * @param array{status?: string|null, busca?: string|null} $filtros
     * @return list<array<string, mixed>>
     */
    public function doEvento(int $eventoId, array $filtros = []): array
    {
        $builder = $this->where('evento_id', $eventoId)->orderBy('criado_em', 'DESC');

        if (! empty($filtros['status'])) {
            $builder->where('status', $filtros['status']);
        }

        if (! empty($filtros['busca'])) {
            $builder->groupStart()
                ->like('nome_autor', $filtros['busca'])
                ->orLike('mensagem', $filtros['busca'])
                ->groupEnd();
        }

        return $builder->findAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function um(int $eventoId, int $id): ?array
    {
        return $this->where('evento_id', $eventoId)->where('id', $id)->first();
    }
}
