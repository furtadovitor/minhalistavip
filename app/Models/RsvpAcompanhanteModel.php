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
        'categoria',
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
     * Totais de acompanhantes de um evento, por categoria e por menor/maior.
     *
     * @return array{menores:int,maiores:int,total:int,adultos:int,criancas:int,bebes:int,sem_categoria:int}
     */
    public function resumoEvento(int $eventoId): array
    {
        $linha = $this->db->table('rsvp_acompanhantes a')
            ->select('
                SUM(a.menor = 1) AS menores,
                SUM(a.menor = 0) AS maiores,
                SUM(a.categoria = "adulto") AS adultos,
                SUM(a.categoria = "crianca") AS criancas,
                SUM(a.categoria = "bebe") AS bebes,
                SUM(a.categoria IS NULL) AS sem_categoria,
                COUNT(*) AS total
            ', false)
            ->join('rsvp_confirmacoes r', 'r.id = a.rsvp_confirmacao_id')
            ->where('r.evento_id', $eventoId)
            ->get()->getRow();

        return [
            'menores'       => (int) ($linha->menores ?? 0),
            'maiores'       => (int) ($linha->maiores ?? 0),
            'total'         => (int) ($linha->total ?? 0),
            'adultos'       => (int) ($linha->adultos ?? 0),
            'criancas'      => (int) ($linha->criancas ?? 0),
            'bebes'         => (int) ($linha->bebes ?? 0),
            'sem_categoria' => (int) ($linha->sem_categoria ?? 0),
        ];
    }
}
