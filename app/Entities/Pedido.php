<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Pedido do convidado (compra de cota de presente).
 *
 * Guarda um snapshot das regras de taxa do evento no momento da compra,
 * para que alterações futuras não distorçam o financeiro (Regra 2.1).
 */
class Pedido extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'                 => 'integer',
        'evento_id'          => 'integer',
        'presente_evento_id' => '?integer',
        'quantidade'         => 'integer',
        'valor_presentes'    => 'float',
        'valor_taxa'         => 'float',
        'valor_total'        => 'float',
        'percentual_taxa'    => 'float',
        'quem_paga_taxa'     => 'string',
        'status'             => 'string',
        'metodo_pagamento'   => '?string',
        'gateway'            => '?string',
        'gateway_transacao_id' => '?string',
    ];

    /**
     * @var list<string>
     */
    protected $dates = ['criado_em', 'atualizado_em', 'pago_em', 'expira_em'];

    public function estaPago(): bool
    {
        return ($this->attributes['status'] ?? null) === 'pago';
    }

    public function estaPendente(): bool
    {
        return ($this->attributes['status'] ?? null) === 'pendente';
    }

    public function foiCancelado(): bool
    {
        return in_array($this->attributes['status'] ?? null, ['cancelado', 'expirado'], true);
    }

    /**
     * Taxa é paga pelo convidado (acrescida no total)?
     */
    public function taxaPagaPeloConvidado(): bool
    {
        return ($this->attributes['quem_paga_taxa'] ?? 'convidado') === 'convidado';
    }

    /**
     * O PIX já passou da data de expiração?
     */
    public function expirado(): bool
    {
        $expira = $this->attributes['expira_em'] ?? null;

        return $expira !== null && strtotime((string) $expira) < time();
    }
}
