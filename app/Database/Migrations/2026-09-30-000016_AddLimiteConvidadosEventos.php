<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Limite de convidados (pessoas) por evento. NULL = sem limite.
 */
class AddLimiteConvidadosEventos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('eventos', [
            'limite_convidados' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'meta_valor',
                'comment'    => 'Máximo de pessoas confirmadas; NULL = ilimitado',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('eventos', 'limite_convidados');
    }
}
