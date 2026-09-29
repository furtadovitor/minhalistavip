<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Evento (tenant). Cada evento pertence a um organizador.
 */
class Evento extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'              => 'integer',
        'usuario_id'      => 'integer',
        'quem_paga_taxa'  => 'string',
        'percentual_taxa' => '?float',
        'meta_valor'      => '?float',
        'permite_rsvp'    => 'boolean',
        'permite_recados' => 'boolean',
        'exibir_valores'  => 'boolean',
        'status'          => 'string',
    ];

    /**
     * @var list<string>
     */
    protected $dates = ['criado_em', 'atualizado_em', 'deletado_em', 'publicado_em', 'data_evento'];

    public function estaPublicado(): bool
    {
        return ($this->attributes['status'] ?? null) === 'publicado';
    }

    /**
     * Taxa é paga pelo convidado (acrescida no total)?
     */
    public function taxaPagaPeloConvidado(): bool
    {
        return ($this->attributes['quem_paga_taxa'] ?? 'convidado') === 'convidado';
    }
}
