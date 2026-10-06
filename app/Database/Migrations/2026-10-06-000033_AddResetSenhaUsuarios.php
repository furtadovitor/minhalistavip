<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Redefinição de senha: guarda o hash do token e a validade.
 */
class AddResetSenhaUsuarios extends Migration
{
    public function up()
    {
        $this->forge->addColumn('usuarios', [
            'reset_senha_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'senha',
            ],
            'reset_senha_expira' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'reset_senha_token',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('usuarios', ['reset_senha_token', 'reset_senha_expira']);
    }
}
