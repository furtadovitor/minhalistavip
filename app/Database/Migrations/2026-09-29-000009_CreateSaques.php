<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Solicitações de saque do saldo da carteira do organizador.
 */
class CreateSaques extends Migration
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
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'valor' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['solicitado', 'processando', 'pago', 'recusado', 'cancelado'],
                'default'    => 'solicitado',
            ],
            'chave_pix' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'observacao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'solicitado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'processado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'criado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'atualizado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('usuario_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('saques', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('saques', true);
    }
}
