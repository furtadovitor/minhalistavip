<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Conversas do chat de suporte ao vivo.
 *
 * Pode ser aberta pelo organizador (painel), por um visitante do site público
 * ou por um convidado dentro de um evento. O atendimento é feito no admin, com
 * fila e "assumir" (atendente_id).
 */
class CreateSuporteConversas extends Migration
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
            'protocolo' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'comment'    => 'Código curto exibido ao cliente (ex.: SUP-8FK2ZP)',
            ],
            'canal' => [
                'type'       => 'ENUM',
                'constraint' => ['painel', 'site'],
                'default'    => 'site',
                'comment'    => 'Origem: painel do organizador ou site público',
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Preenchido quando o cliente está autenticado',
            ],
            'visitante_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'comment'    => 'Identifica visitante anônimo (sessão/cookie)',
            ],
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'evento_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Contexto, quando aberto dentro de um evento',
            ],
            'assunto' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aguardando', 'em_atendimento', 'encerrada'],
                'default'    => 'aguardando',
            ],
            'atendente_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'ultima_mensagem_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'ultima_mensagem_preview' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
            ],
            'cliente_visto_em' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Última presença do cliente (heartbeat do widget aberto)',
            ],
            'atendente_visto_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'encerrada_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'encerrada_por' => [
                'type'       => 'ENUM',
                'constraint' => ['cliente', 'atendente', 'sistema'],
                'null'       => true,
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
        $this->forge->addKey('usuario_id');
        $this->forge->addKey('visitante_token');
        $this->forge->addKey('status');
        $this->forge->addKey('atendente_id');
        $this->forge->addKey('ultima_mensagem_em');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('atendente_id', 'usuarios', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('evento_id', 'eventos', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('suporte_conversas', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('suporte_conversas', true);
    }
}
