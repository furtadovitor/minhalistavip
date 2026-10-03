<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Mensagens das conversas do chat de suporte.
 */
class CreateSuporteMensagens extends Migration
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
            'conversa_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'autor_tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['cliente', 'atendente', 'sistema'],
                'default'    => 'cliente',
            ],
            'autor_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'texto' => [
                'type' => 'TEXT',
            ],
            'criado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('conversa_id');
        $this->forge->addKey('criado_em');
        $this->forge->addForeignKey('conversa_id', 'suporte_conversas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('suporte_mensagens', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('suporte_mensagens', true);
    }
}
