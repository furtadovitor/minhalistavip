<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Catálogo GERAL de presentes (global, mantido pelo SuperAdmin).
 * Os organizadores clonam itens daqui para presentes_evento.
 */
class CreateCatalogoPresentes extends Migration
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
            'categoria_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
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
                'comment'    => 'ficticio = cota em dinheiro/PIX; real = redireciona para link de afiliado',
            ],
            'valor_sugerido' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
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
            'destaque' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
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
        $this->forge->addKey('categoria_id');
        $this->forge->addKey('ativo');
        $this->forge->addForeignKey('categoria_id', 'categorias', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('catalogo_presentes', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('catalogo_presentes', true);
    }
}
