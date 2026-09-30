<?php

namespace App\Models;

use CodeIgniter\Model;

class RsvpConfirmacaoModel extends Model
{
    protected $table         = 'rsvp_confirmacoes';
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
        'nome',
        'email',
        'telefone',
        'quantidade_acompanhantes',
        'status',
        'observacao',
    ];

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'evento_id' => 'required|integer',
        'nome'      => 'required|min_length[3]|max_length[150]',
        'email'     => 'permit_empty|valid_email|max_length[180]',
        'status'    => 'required|in_list[pendente,confirmado,recusado]',
    ];

    /**
     * Convidados do evento, com filtros.
     *
     * @param array{status?: string|null, busca?: string|null} $filtros
     * @return list<array<string, mixed>>
     */
    public function doEvento(int $eventoId, array $filtros = []): array
    {
        $builder = $this->where('evento_id', $eventoId);

        if (! empty($filtros['status'])) {
            $builder->where('status', $filtros['status']);
        }

        if (! empty($filtros['busca'])) {
            $busca = (string) $filtros['busca'];
            $builder->groupStart()
                ->like('nome', $busca)
                ->orLike('email', $busca)
                ->orLike('telefone', $busca)
                ->groupEnd();
        }

        return $builder->orderBy('status', 'ASC')
            ->orderBy('criado_em', 'DESC')
            ->findAll();
    }

    /**
     * Contagem de registros e de pessoas (registro + acompanhantes) por status.
     *
     * @return array{pendentes:int,confirmados:int,recusados:int,pessoas_pendentes:int,pessoas_confirmadas:int}
     */
    public function contagem(int $eventoId): array
    {
        $linha = $this->db->table('rsvp_confirmacoes')
            ->select("
                SUM(status = 'pendente') AS pendentes,
                SUM(status = 'confirmado') AS confirmados,
                SUM(status = 'recusado') AS recusados,
                SUM(CASE WHEN status = 'pendente' THEN quantidade_acompanhantes + 1 ELSE 0 END) AS pessoas_pendentes,
                SUM(CASE WHEN status = 'confirmado' THEN quantidade_acompanhantes + 1 ELSE 0 END) AS pessoas_confirmadas
            ", false)
            ->where('evento_id', $eventoId)
            ->get()->getRow();

        return [
            'pendentes'          => (int) ($linha->pendentes ?? 0),
            'confirmados'        => (int) ($linha->confirmados ?? 0),
            'recusados'          => (int) ($linha->recusados ?? 0),
            'pessoas_pendentes'  => (int) ($linha->pessoas_pendentes ?? 0),
            'pessoas_confirmadas' => (int) ($linha->pessoas_confirmadas ?? 0),
        ];
    }
}
