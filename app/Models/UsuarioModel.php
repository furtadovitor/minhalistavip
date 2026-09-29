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
        'telefone',
        'cpf_cnpj',
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

    public function buscarPorEmail(string $email): ?Usuario
    {
        return $this->where('email', mb_strtolower(trim($email)))->first();
    }
}
