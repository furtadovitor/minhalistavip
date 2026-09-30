<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Acompanhantes de uma confirmação de presença.
 */
class RsvpAcompanhanteModel extends Model
{
    protected $table         = 'rsvp_acompanhantes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'rsvp_confirmacao_id',
        'nome',
        'idade',
        'menor',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function daConfirmacao(int $rsvpId): array
    {
        return $this->where('rsvp_confirmacao_id', $rsvpId)
            ->orderBy('nome', 'ASC')
            ->findAll();
    }

    /**
     * Totais de menores e maiores de um evento.
     *
     * @return array{menores:int,maiores:int,total:int}
     */
    public function resumoEvento(int $eventoId): array
    {
        $linha = $this->db->table('rsvp_acompanhantes a')
            ->select('
                SUM(a.menor = 1) AS menores,
                SUM(a.menor = 0) AS maiores,
                COUNT(*) AS total
            ', false)
            ->join('rsvp_confirmacoes r', 'r.id = a.rsvp_confirmacao_id')
            ->where('r.evento_id', $eventoId)
            ->get()->getRow();

        return [
            'menores' => (int) ($linha->menores ?? 0),
            'maiores' => (int) ($linha->maiores ?? 0),
            'total'   => (int) ($linha->total ?? 0),
        ];
    }
}
