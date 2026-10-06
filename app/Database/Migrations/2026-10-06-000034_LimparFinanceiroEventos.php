<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * A comissão passou a ser fixa da plataforma (10%).
 *
 * Limpa os valores por evento que sobrescreviam a taxa (inclusive 0%, que
 * zerava a cobrança) e os campos de PIX/meta por evento, que não são usados.
 */
class LimparFinanceiroEventos extends Migration
{
    public function up()
    {
        $this->db->table('eventos')
            ->where('id >', 0)
            ->update([
                'percentual_taxa' => null,
                'meta_valor'      => null,
                'pix_tipo'        => null,
                'pix_chave'       => null,
                'pix_nome'        => null,
            ]);
    }

    public function down()
    {
        // Sem reversão: os valores antigos não são recuperáveis.
    }
}
