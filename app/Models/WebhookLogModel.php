<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Log bruto dos webhooks recebidos, usado para idempotência e auditoria.
 * Tabela sem updated_em: o timestamp de criação é setado manualmente.
 */
class WebhookLogModel extends Model
{
    protected $table         = 'webhooks_log';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'gateway',
        'evento_tipo',
        'referencia_id',
        'payload',
        'processado',
        'mensagem',
        'criado_em',
    ];

    public function jaProcessado(string $gateway, string $referenciaId): bool
    {
        return $this->where('gateway', $gateway)
            ->where('referencia_id', $referenciaId)
            ->where('processado', 1)
            ->countAllResults() > 0;
    }
}
