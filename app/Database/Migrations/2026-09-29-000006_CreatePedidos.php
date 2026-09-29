<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Pedidos dos convidados. Guarda um snapshot das regras de taxa no momento
 * da compra para que alterações futuras no evento não distorçam o financeiro.
 *
 * Regra de negócio:
 *  - quem_paga_taxa = 'convidado'   => valor_total = valor_presentes + valor_taxa
 *  - quem_paga_taxa = 'organizador' => valor_total = valor_presentes
 *    (a taxa é abatida do líquido do organizador na carteira)
 */
class CreatePedidos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'evento_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'presente_evento_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'protocolo' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'comment'    => 'Código público de acompanhamento do pedido',
            ],
            'nome_convidado' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'email_convidado' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
            ],
            'telefone_convidado' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'mensagem' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'quantidade' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'valor_presentes' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'valor_taxa' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'valor_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'percentual_taxa' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0,
            ],
            'quem_paga_taxa' => [
                'type'       => 'ENUM',
                'constraint' => ['convidado', 'organizador'],
                'default'    => 'convidado',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pendente', 'pago', 'cancelado', 'expirado', 'reembolsado'],
                'default'    => 'pendente',
            ],
            'metodo_pagamento' => [
                'type'       => 'ENUM',
                'constraint' => ['pix', 'cartao', 'boleto'],
                'null'       => true,
            ],
            'gateway' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
            ],
            'gateway_transacao_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
            ],
            'pago_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'expira_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'criado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'atualizado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('protocolo');
        $this->forge->addKey('evento_id');
        $this->forge->addKey('status');
        $this->forge->addKey('gateway_transacao_id');
        $this->forge->addForeignKey('evento_id', 'eventos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('presente_evento_id', 'presentes_evento', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('pedidos', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('pedidos', true);
    }
}
