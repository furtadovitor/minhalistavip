<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Eventos (tenant). Cada evento pertence a um organizador e tem uma
 * página pública acessada pelo slug.
 */
class CreateEventos extends Migration
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
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'comment'    => 'Identificador público do evento',
            ],
            'titulo' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
            ],
            'subtitulo' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
            ],
            'tipo_evento' => [
                'type'       => 'ENUM',
                'constraint' => ['casamento', 'cha_bebe', 'cha_fraldas', 'cha_panela', 'aniversario', 'formatura', 'corporativo', 'outro'],
                'default'    => 'outro',
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'mensagem_convite' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'data_evento' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'horario' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'local_nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
            ],
            'local_endereco' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'imagem_capa' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'tema' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'default'    => 'classico',
            ],
            'cor_primaria' => [
                'type'       => 'VARCHAR',
                'constraint' => 9,
                'default'    => '#8e44ad',
            ],
            'cor_secundaria' => [
                'type'       => 'VARCHAR',
                'constraint' => 9,
                'default'    => '#f39c12',
            ],
            'quem_paga_taxa' => [
                'type'       => 'ENUM',
                'constraint' => ['convidado', 'organizador'],
                'default'    => 'convidado',
                'comment'    => 'Define se a comissão é acrescida ao convidado ou descontada do organizador',
            ],
            'percentual_taxa' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'NULL = usa o padrão da plataforma',
            ],
            'meta_valor' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
            ],
            'pix_chave' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'pix_tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['cpf', 'cnpj', 'email', 'telefone', 'aleatoria'],
                'null'       => true,
            ],
            'pix_nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'permite_rsvp' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'permite_recados' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'exibir_valores' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['rascunho', 'publicado', 'encerrado'],
                'default'    => 'rascunho',
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
            'deletado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('usuario_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('eventos', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('eventos', true);
    }
}
