<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Usuário da plataforma (SuperAdmin ou Organizador).
 */
class Usuario extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'     => 'integer',
        'nivel'  => 'string',
        'status' => 'string',
    ];

    /**
     * @var list<string>
     */
    protected $dates = ['criado_em', 'atualizado_em', 'deletado_em', 'ultimo_login_em'];

    /**
     * Nunca expor o hash da senha em serializações.
     *
     * @var list<string>
     */
    protected $hidden = ['senha'];

    public function isSuperAdmin(): bool
    {
        return ($this->attributes['nivel'] ?? null) === 'superadmin';
    }

    public function isOrganizador(): bool
    {
        return ($this->attributes['nivel'] ?? null) === 'organizador';
    }

    public function isAtivo(): bool
    {
        return ($this->attributes['status'] ?? null) === 'ativo';
    }

    /**
     * Sempre armazena a senha como hash.
     */
    public function setSenha(string $senha): static
    {
        $this->attributes['senha'] = password_hash($senha, PASSWORD_DEFAULT);

        return $this;
    }

    /**
     * Rota inicial do usuário conforme o nível de acesso.
     */
    public function rotaInicial(): string
    {
        return $this->isSuperAdmin() ? 'admin' : 'painel';
    }
}
