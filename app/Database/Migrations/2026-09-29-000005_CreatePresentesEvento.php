<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Presentes de um evento específico. Podem ser clonados do catálogo
 * global (catalogo_id preenchido) ou totalmente customizados.
 */
class CreatePresentesEvento extends Migration
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
            'catalogo_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Origem no catálogo global, quando clonado',
            ],
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'imagem' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['ficticio', 'real'],
                'default'    => 'ficticio',
            ],
            'valor' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
                'comment'    => 'Valor de cada cota',
            ],
            'quantidade_meta' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'quantidade_vendida' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'link_afiliado' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'ativo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'ordem' => [
                'type'       => 'INT',
                'constraint' => 10,
                'default'    => 0,
            ],
            'criado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'atualizado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deletado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('evento_id');
        $this->forge->addKey(['evento_id', 'ativo']);
        $this->forge->addForeignKey('evento_id', 'eventos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('catalogo_id', 'catalogo_presentes', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('presentes_evento', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('presentes_evento', true);
    }
}
