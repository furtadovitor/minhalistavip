<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * URL pública de notificação do Mercado Pago.
 *
 * Útil quando a URL do site (base_url) não é acessível pelo provedor — por
 * exemplo, ao testar no local com um túnel (ngrok/cloudflared).
 */
class AddMercadoPagoNotificationUrl extends Migration
{
    public function up()
    {
        $chave = 'mercadopago_notification_url';

        if ($this->db->table('configuracoes')->where('chave', $chave)->countAllResults() === 0) {
            $agora = date('Y-m-d H:i:s');

            $this->db->table('configuracoes')->insert([
                'chave'         => $chave,
                'valor'         => null,
                'grupo'         => 'pix',
                'descricao'     => 'Mercado Pago - URL pública do webhook (ex.: https://.../webhooks/pix)',
                'criado_em'     => $agora,
                'atualizado_em' => $agora,
            ]);
        }
    }

    public function down()
    {
        $this->db->table('configuracoes')->where('chave', 'mercadopago_notification_url')->delete();
    }
}
