<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Assinaturas contratadas pelos organizadores.
 */
class CreateAssinaturas extends Migration
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
            'plano_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pendente', 'ativa', 'cancelada', 'expirada'],
                'default'    => 'pendente',
            ],
            'inicio_em' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'fim_em' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'renovacao_automatica' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'gateway' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
            ],
            'gateway_transacao_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
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
        $this->forge->addKey(['usuario_id', 'status']);
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('plano_id', 'planos', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('assinaturas', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('assinaturas', true);
    }
}
