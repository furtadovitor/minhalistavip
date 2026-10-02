<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Guarda o identificador do Google (claim `sub` do OpenID Connect) para o
 * login com Google. Permite vincular uma conta local a uma conta Google e
 * reconhecer o usuário nos próximos logins.
 */
class AddGoogleIdUsuarios extends Migration
{
    public function up()
    {
        $this->forge->addColumn('usuarios', [
            'google_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'chave_pix',
                'comment'    => 'sub do Google (OpenID Connect), quando loga com Google',
            ],
        ]);

        $this->db->query('ALTER TABLE `usuarios` ADD INDEX `usuarios_google_id` (`google_id`)');
    }

    public function down()
    {
        $this->forge->dropColumn('usuarios', 'google_id');
    }
}
