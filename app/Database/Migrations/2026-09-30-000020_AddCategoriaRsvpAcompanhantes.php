<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Categoria do acompanhante, escolhida no hotsite por radio:
 *  - adulto  → "Adulto ou adolescente"
 *  - crianca → "Criança (5 a 12 anos)"
 *  - bebe    → "Bebê (menos de 5 anos)"
 *
 * A coluna `idade` é mantida (nullable) por compatibilidade e os registros
 * antigos continuam sendo exibidos. O campo `menor` passa a ser derivado
 * também da categoria (criança e bebê ⇒ menor).
 */
class AddCategoriaRsvpAcompanhantes extends Migration
{
    public function up()
    {
        $this->forge->addColumn('rsvp_acompanhantes', [
            'categoria' => [
                'type'       => 'ENUM',
                'constraint' => ['adulto', 'crianca', 'bebe'],
                'null'       => true,
                'after'      => 'idade',
                'comment'    => 'adulto | crianca | bebe',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('rsvp_acompanhantes', 'categoria');
    }
}
