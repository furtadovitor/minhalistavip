<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Corrige cores gravadas com o "#" URL-encoded ("%23").
 *
 * Um valor como "%2372b3d8" vira CSS inválido (--cor-primaria) e deixava o
 * herói e os botões do hotsite brancos. Aqui normalizamos os dados existentes;
 * o helper cor_hex() evita que volte a acontecer.
 */
class FixCoresEventos extends Migration
{
    public function up()
    {
        foreach (['eventos', 'demo_listas'] as $tabela) {
            if (! $this->db->tableExists($tabela)) {
                continue;
            }

            foreach (['cor_primaria', 'cor_secundaria'] as $campo) {
                $this->db->query(
                    "UPDATE `{$tabela}` SET `{$campo}` = REPLACE(`{$campo}`, '%23', '#')"
                );
            }
        }
    }

    public function down()
    {
        // Sem reversão: os valores corrigidos são os corretos.
    }
}
