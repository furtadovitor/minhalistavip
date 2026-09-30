<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Check-in presencial dos convidados confirmados.
 *
 * Guarda o momento do check-in e qual usuário (organizador) o registrou.
 * A presença é considerada quando `check_in_em` está preenchido.
 */
class AddCheckinRsvp extends Migration
{
    public function up()
    {
        $this->forge->addColumn('rsvp_confirmacoes', [
            'check_in_em' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'observacao',
            ],
            'check_in_por' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'check_in_em',
            ],
        ]);

        $this->db->query('CREATE INDEX rsvp_confirmacoes_check_in_idx ON rsvp_confirmacoes (evento_id, check_in_em)');
    }

    public function down()
    {
        $this->db->query('DROP INDEX rsvp_confirmacoes_check_in_idx ON rsvp_confirmacoes');
        $this->forge->dropColumn('rsvp_confirmacoes', ['check_in_em', 'check_in_por']);
    }
}
