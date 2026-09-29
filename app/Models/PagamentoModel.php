<?php

namespace App\Models;

use CodeIgniter\Model;

class PagamentoModel extends Model
{
    protected $table         = 'pagamentos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'pedido_id',
        'gateway',
        'gateway_transacao_id',
        'tipo',
        'status',
        'valor',
        'payload',
    ];

    /**
     * Última transação registrada para o pedido.
     *
     * @return array<string, mixed>|null
     */
    public function ultimoDoPedido(int $pedidoId): ?array
    {
        return $this->where('pedido_id', $pedidoId)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function marcarPago(int $pedidoId): bool
    {
        return (bool) $this->where('pedido_id', $pedidoId)
            ->set('status', 'pago')
            ->update();
    }
}
