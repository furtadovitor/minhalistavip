<?php

namespace App\Services;

use App\Entities\Usuario;
use App\Models\UsuarioModel;

/**
 * Autenticação via sessão para SuperAdmin e Organizadores.
 * Convidados não possuem conta — acessam a página pública pelo slug.
 */
class AuthService
{
    /**
     * ⚠️ LOGIN SEM SENHA.
     *
     * Quando `false`, o login aceita apenas o e-mail (a senha não é conferida).
     * Para voltar a exigir senha, mude para `true`.
     *
     * ATENÇÃO: com `false`, qualquer pessoa que saiba um e-mail cadastrado
     * consegue entrar (inclusive o SuperAdmin). Use com cautela.
     */
    public const EXIGIR_SENHA = false;

    protected UsuarioModel $usuarios;

    public function __construct(?UsuarioModel $usuarios = null)
    {
        $this->usuarios = $usuarios ?? new UsuarioModel();
    }

    public function tentarLogin(string $email, ?string $senha = null): bool
    {
        $usuario = $this->usuarios->buscarPorEmail($email);

        if ($usuario === null || ! $usuario->isAtivo()) {
            return false;
        }

        if (self::EXIGIR_SENHA && ! password_verify((string) $senha, (string) $usuario->senha)) {
            return false;
        }

        $this->registrarSessao($usuario);

        return true;
    }

    public function registrarSessao(Usuario $usuario): void
    {
        $this->usuarios->update($usuario->id, ['ultimo_login_em' => date('Y-m-d H:i:s')]);

        session()->set([
            'usuario_id'    => $usuario->id,
            'usuario_nome'  => $usuario->nome,
            'usuario_email' => $usuario->email,
            'usuario_nivel' => $usuario->nivel,
        ]);
    }

    public function logout(): void
    {
        session()->remove(['usuario_id', 'usuario_nome', 'usuario_email', 'usuario_nivel']);
    }

    public function estaLogado(): bool
    {
        return (bool) session()->get('usuario_id');
    }

    public function nivel(): ?string
    {
        return session()->get('usuario_nivel');
    }

    public function usuarioAtual(): ?Usuario
    {
        $id = session()->get('usuario_id');

        return $id ? $this->usuarios->find($id) : null;
    }

    /**
     * Rota inicial conforme o nível do usuário autenticado.
     */
    public function rotaInicial(): string
    {
        return $this->nivel() === 'superadmin' ? 'admin' : 'painel';
    }
}
