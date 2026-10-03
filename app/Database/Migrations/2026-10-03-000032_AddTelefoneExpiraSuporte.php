<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Telefone do visitante e expiração do código de atendimento no chat de suporte.
 */
class AddTelefoneExpiraSuporte extends Migration
{
    public function up()
    {
        $this->forge->addColumn('suporte_conversas', [
            'telefone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'email',
            ],
            'expira_em' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'atendente_visto_em',
                'comment' => 'NULL = sem evento; expiração calculada pela última mensagem',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('suporte_conversas', ['telefone', 'expira_em']);
    }
}
