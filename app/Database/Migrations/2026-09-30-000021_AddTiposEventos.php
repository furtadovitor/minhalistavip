<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Amplia o ENUM `eventos.tipo_evento` com as novas ocasiões suportadas
 * pelos atalhos da Home (chá de casa nova, lingerie, amigo secreto, natal,
 * evento da igreja, pet, entre outras).
 */
class AddTiposEventos extends Migration
{
    /**
     * @var list<string>
     */
    private array $tipos = [
        'casamento',
        'cha_bebe',
        'cha_fraldas',
        'cha_panela',
        'cha_casa_nova',
        'cha_cozinha',
        'noivado',
        'cha_revelacao',
        'quinze_anos',
        'festa_infantil',
        'festa_junina',
        'aniversario',
        'formatura',
        'cha_lingerie',
        'amigo_secreto',
        'bodas',
        'evento_pet',
        'evento_igreja',
        'dia_namorados',
        'natal',
        'compras',
        'material_escolar',
        'corporativo',
        'outro',
    ];

    public function up()
    {
        $this->forge->modifyColumn('eventos', [
            'tipo_evento' => [
                'type'       => 'ENUM',
                'constraint' => $this->tipos,
                'default'    => 'outro',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('eventos', [
            'tipo_evento' => [
                'type'       => 'ENUM',
                'constraint' => ['casamento', 'cha_bebe', 'cha_fraldas', 'cha_panela', 'aniversario', 'formatura', 'corporativo', 'outro'],
                'default'    => 'outro',
            ],
        ]);
    }
}
