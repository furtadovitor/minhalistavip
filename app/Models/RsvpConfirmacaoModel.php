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
}
