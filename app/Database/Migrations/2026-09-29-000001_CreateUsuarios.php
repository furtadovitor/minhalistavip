<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Usuários da plataforma: SuperAdmin global e Organizadores (clientes).
 * Convidados NÃO são usuários (acessam via slug público do evento).
 */
class CreateUsuarios extends Migration
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
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
            ],
            'senha' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'Hash gerado por password_hash()',
            ],
            'telefone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'cpf_cnpj' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'nivel' => [
                'type'       => 'ENUM',
                'constraint' => ['superadmin', 'organizador'],
                'default'    => 'organizador',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['ativo', 'inativo', 'suspenso'],
                'default'    => 'ativo',
            ],
            'ultimo_login_em' => [
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
            'deletado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('usuarios', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('usuarios', true);
    }
}
