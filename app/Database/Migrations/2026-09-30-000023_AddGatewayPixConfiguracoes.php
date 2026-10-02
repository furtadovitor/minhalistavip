<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Configurações do gateway PIX (seleção e credenciais do Mercado Pago).
 */
class AddGatewayPixConfiguracoes extends Migration
{
    public function up()
    {
        $agora = date('Y-m-d H:i:s');

        $itens = [
            ['chave' => 'pix_gateway', 'grupo' => 'pix', 'descricao' => 'Gateway PIX ativo (sandbox ou mercadopago)'],
            ['chave' => 'mercadopago_access_token', 'grupo' => 'pix', 'descricao' => 'Mercado Pago - Access Token (produção)'],
            ['chave' => 'mercadopago_webhook_secret', 'grupo' => 'pix', 'descricao' => 'Mercado Pago - segredo de assinatura do webhook'],
        ];

        foreach ($itens as $indice => $item) {
            $existe = $this->db->table('configuracoes')->where('chave', $item['chave'])->countAllResults();

            if ($existe === 0) {
                $this->db->table('configuracoes')->insert([
                    'chave'         => $item['chave'],
                    'valor'         => $indice === 0 ? 'sandbox' : null,
                    'grupo'         => $item['grupo'],
                    'descricao'     => $item['descricao'],
                    'criado_em'     => $agora,
                    'atualizado_em' => $agora,
                ]);
            }
        }
    }

    public function down()
    {
        $this->db->table('configuracoes')
            ->whereIn('chave', ['pix_gateway', 'mercadopago_access_token', 'mercadopago_webhook_secret'])
            ->delete();
    }
}
