<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Configurações globais da plataforma em formato chave/valor
 * (ex.: percentual_taxa_padrao, chave PIX da plataforma, etc.).
 */
class CreateConfiguracoes extends Migration
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
            'chave' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'valor' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'grupo' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'default'    => 'geral',
            ],
            'descricao' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addUniqueKey('chave');
        $this->forge->createTable('configuracoes', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('configuracoes', true);
    }
}
