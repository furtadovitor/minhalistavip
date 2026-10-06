<?php

namespace App\Models;

use App\Entities\Usuario;
use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id';
    protected $returnType       = Usuario::class;
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;
    protected $createdField     = 'criado_em';
    protected $updatedField     = 'atualizado_em';
    protected $deletedField     = 'deletado_em';

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'nome',
        'email',
        'senha',
        'reset_senha_token',
        'reset_senha_expira',
        'telefone',
        'cpf_cnpj',
        'data_nascimento',
        'destinatario',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'tipo_pagamento',
        'tipo_chave_pix',
        'chave_pix',
        'dados_repasse_ok_em',
        'google_id',
        'nivel',
        'status',
        'ultimo_login_em',
    ];

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'nome'  => 'required|min_length[3]|max_length[150]',
        'email' => 'required|valid_email|max_length[180]|is_unique[usuarios.email,id,{id}]',
        'nivel' => 'required|in_list[superadmin,organizador]',
        'status' => 'permit_empty|in_list[ativo,inativo,suspenso]',
    ];

    /**
     * @var array<string, array<string, string>>
     */
    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Este e-mail já está cadastrado.',
        ],
    ];

    public function buscarPorGoogleId(string $googleId): ?Usuario
    {
        return $this->where('google_id', $googleId)->first();
    }

    public function buscarPorEmail(string $email): ?Usuario
    {
        return $this->where('email', mb_strtolower(trim($email)))->first();
    }

    /**
     * Listagem para o SuperAdmin, com filtros.
     *
     * @param array{nivel?: string|null, status?: string|null, busca?: string|null} $filtros
     * @return list<Usuario>
     */
    public function listar(array $filtros = []): array
    {
        $builder = $this->orderBy('nome', 'ASC');

        if (! empty($filtros['nivel'])) {
            $builder->where('nivel', $filtros['nivel']);
        }

        if (! empty($filtros['status'])) {
            $builder->where('status', $filtros['status']);
        }

        if (! empty($filtros['busca'])) {
            $busca = (string) $filtros['busca'];
            $builder->groupStart()
                ->like('nome', $busca)
                ->orLike('email', $busca)
                ->groupEnd();
        }

        return $builder->findAll();
    }
}
