<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Mural de recados do evento. Só é publicado após confirmação do
 * pagamento do pedido (status 'pago'), conforme regra de negócio.
 */
class CreateMuralRecados extends Migration
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
            'evento_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'pedido_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'nome_autor' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'mensagem' => [
                'type' => 'TEXT',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pendente', 'publicado', 'oculto'],
                'default'    => 'pendente',
            ],
            'publicado_em' => [
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
        $this->forge->addKey('evento_id');
        $this->forge->addKey(['evento_id', 'status']);
        $this->forge->addForeignKey('evento_id', 'eventos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('pedido_id', 'pedidos', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('mural_recados', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('mural_recados', true);
    }
}
