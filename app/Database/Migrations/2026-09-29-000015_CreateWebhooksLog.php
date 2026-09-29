<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Log bruto de webhooks recebidos dos gateways de pagamento, usado para
 * idempotência e depuração da confirmação de pedidos.
 */
class CreateWebhooksLog extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'gateway' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
            ],
            'evento_tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'referencia_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
                'comment'    => 'ID da transação no gateway (idempotência)',
            ],
            'payload' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'processado' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'mensagem' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'criado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('referencia_id');
        $this->forge->addKey('processado');
        $this->forge->createTable('webhooks_log', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('webhooks_log', true);
    }
}
