<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Permite arquivar uma lista sem excluí-la: sai da aba "Ativas" e vai para
 * "Arquivadas" no painel do organizador.
 */
class AddArquivadoEventos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('eventos', [
            'arquivado' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'status',
                'comment'    => 'Lista arquivada (fora da lista ativa)',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('eventos', 'arquivado');
    }
}
