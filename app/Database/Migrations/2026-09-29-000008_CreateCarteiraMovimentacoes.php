<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Extrato da carteira do organizador.
 * Só recebe crédito quando o pedido é confirmado como 'pago' (webhook).
 *
 * Convenção de sinal: valores positivos = crédito; negativos = débito
 * (taxa retida, saque, estorno).
 */
class CreateCarteiraMovimentacoes extends Migration
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
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'evento_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'pedido_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['credito', 'taxa', 'saque', 'estorno', 'ajuste'],
                'comment'    => 'credito = valor do presente; taxa = comissão da plataforma',
            ],
            'valor' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'saldo_apos' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
            ],
            'descricao' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'criado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('usuario_id');
        $this->forge->addKey('evento_id');
        $this->forge->addKey('pedido_id');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('evento_id', 'eventos', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('pedido_id', 'pedidos', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('carteira_movimentacoes', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('carteira_movimentacoes', true);
    }
}
