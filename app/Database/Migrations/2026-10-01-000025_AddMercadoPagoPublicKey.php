<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Public Key do Mercado Pago (usada pelo Checkout Bricks no frontend).
 */
class AddMercadoPagoPublicKey extends Migration
{
    public function up()
    {
        $chave = 'mercadopago_public_key';

        if ($this->db->table('configuracoes')->where('chave', $chave)->countAllResults() === 0) {
            $agora = date('Y-m-d H:i:s');

            $this->db->table('configuracoes')->insert([
                'chave'         => $chave,
                'valor'         => null,
                'grupo'         => 'pix',
                'descricao'     => 'Mercado Pago - Public Key (Checkout Bricks)',
                'criado_em'     => $agora,
                'atualizado_em' => $agora,
            ]);
        }
    }

    public function down()
    {
        $this->db->table('configuracoes')->where('chave', 'mercadopago_public_key')->delete();
    }
}
