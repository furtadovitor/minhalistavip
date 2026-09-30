<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Acompanhantes de cada confirmação (RSVP).
 *
 * O organizador registra o nome completo e a idade; o sistema marca
 * automaticamente se é menor ou maior de idade (menor = idade < 18).
 */
class CreateRsvpAcompanhantes extends Migration
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
            'rsvp_confirmacao_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'idade' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'menor' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'comment'    => '1 = menor de idade, 0 = maior; derivado da idade',
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
        $this->forge->addKey('rsvp_confirmacao_id');
        $this->forge->addForeignKey('rsvp_confirmacao_id', 'rsvp_confirmacoes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rsvp_acompanhantes', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('rsvp_acompanhantes', true);
    }
}
