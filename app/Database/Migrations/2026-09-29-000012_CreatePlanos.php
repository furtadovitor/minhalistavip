<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Planos de assinatura (fonte de receita recorrente da plataforma).
 */
class CreatePlanos extends Migration
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
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 140,
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'preco' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'periodo' => [
                'type'       => 'ENUM',
                'constraint' => ['mensal', 'anual', 'vitalicio'],
                'default'    => 'mensal',
            ],
            'percentual_taxa' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Sobrescreve o percentual padrão da plataforma',
            ],
            'limite_eventos' => [
                'type'       => 'INT',
                'constraint' => 10,
                'null'       => true,
                'comment'    => 'NULL = ilimitado',
            ],
            'recursos' => [
                'type' => 'JSON',
                'null' => true,
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
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('planos', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('planos', true);
    }
}
