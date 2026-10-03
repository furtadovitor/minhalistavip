<?php

namespace App\Database\Migrations;

use App\Models\DemoListaModel;
use CodeIgniter\Database\Migration;

/**
 * Listas de exemplo (demonstração) gerenciáveis pelo SuperAdmin.
 *
 * Antes eram fixas no código (Public\Demo::EXEMPLOS). A migration cria a tabela
 * e importa os 3 exemplos padrão para que possam ser editados no admin.
 */
class CreateDemoListas extends Migration
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
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'titulo' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'icone' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
            ],
            'badge' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'primary',
            ],
            'data_texto' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'local' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'resumo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'mensagem_convite' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'capa' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'cor_primaria' => [
                'type'       => 'VARCHAR',
                'constraint' => 9,
                'default'    => '#4F46E5',
            ],
            'cor_secundaria' => [
                'type'       => 'VARCHAR',
                'constraint' => 9,
                'default'    => '#7C3AED',
            ],
            'tema' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'classico',
            ],
            'escuro' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'presentes' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => null,
            ],
            'itens' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'recados' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'galeria' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ativo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'ordem' => [
                'type'    => 'INT',
                'default' => 0,
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
        $this->forge->createTable('demo_listas', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);

        // Importa os 3 exemplos que antes eram fixos no código.
        $agora = date('Y-m-d H:i:s');

        foreach (DemoListaModel::padroesParaBanco() as $linha) {
            $linha['criado_em']     = $agora;
            $linha['atualizado_em'] = $agora;
            $this->db->table('demo_listas')->insert($linha);
        }
    }

    public function down()
    {
        $this->forge->dropTable('demo_listas', true);
    }
}
