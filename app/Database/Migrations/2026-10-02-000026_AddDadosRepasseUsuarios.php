<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Dados de repasse do organizador: identificação do responsável, dados
 * bancários (PIX) e endereço de correspondência. Usados para processar o
 * resgate do saldo. NÃO aparecem no site do evento para os convidados.
 */
class AddDadosRepasseUsuarios extends Migration
{
    public function up()
    {
        $this->forge->addColumn('usuarios', [
            'data_nascimento' => [
                'type'  => 'DATE',
                'null'  => true,
                'after' => 'cpf_cnpj',
            ],
            'destinatario' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'data_nascimento',
            ],
            'cep' => [
                'type'       => 'VARCHAR',
                'constraint' => 9,
                'null'       => true,
                'after'      => 'destinatario',
            ],
            'endereco' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
                'after'      => 'cep',
            ],
            'numero' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'endereco',
            ],
            'complemento' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
                'after'      => 'numero',
            ],
            'bairro' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
                'after'      => 'complemento',
            ],
            'cidade' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
                'after'      => 'bairro',
            ],
            'estado' => [
                'type'       => 'CHAR',
                'constraint' => 2,
                'null'       => true,
                'after'      => 'cidade',
            ],
            'tipo_pagamento' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => 'pix',
                'after'      => 'estado',
            ],
            'tipo_chave_pix' => [
                'type'       => 'VARCHAR',
                'constraint' => 15,
                'null'       => true,
                'after'      => 'tipo_pagamento',
                'comment'    => 'cpf, cnpj, email, telefone ou aleatoria',
            ],
            'chave_pix' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'tipo_chave_pix',
            ],
            'dados_repasse_ok_em' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'chave_pix',
            ],
        ]);
    }

    public function down()
    {
        foreach ([
            'data_nascimento', 'destinatario', 'cep', 'endereco', 'numero', 'complemento',
            'bairro', 'cidade', 'estado', 'tipo_pagamento', 'tipo_chave_pix', 'chave_pix',
            'dados_repasse_ok_em',
        ] as $coluna) {
            $this->forge->dropColumn('usuarios', $coluna);
        }
    }
}
