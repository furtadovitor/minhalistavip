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

    /**
     * Login com Google (OpenID Connect).
     *
     * Regras:
     *  - reconhece pela conta Google (google_id);
     *  - senão, vincula por e-mail a uma conta existente (e-mail verificado);
     *  - senão, cria uma conta de organizador já ativa.
     *
     * @return array{ok: bool, mensagem: string}
     */
    public function entrarComGoogle(string $googleId, string $email, string $nome, bool $emailVerificado = true): array
    {
        $email = mb_strtolower(trim($email));

        if ($email === '') {
            return ['ok' => false, 'mensagem' => 'Não foi possível obter o e-mail da sua conta Google.'];
        }

        $usuario = $googleId !== '' ? $this->usuarios->buscarPorGoogleId($googleId) : null;

        if ($usuario === null) {
            $usuario = $this->usuarios->buscarPorEmail($email);
        }

        if ($usuario !== null) {
            if (! $usuario->isAtivo()) {
                return ['ok' => false, 'mensagem' => 'Sua conta está inativa. Fale com o suporte.'];
            }

            if ($googleId !== '' && (string) ($usuario->google_id ?? '') !== $googleId) {
                if (! $emailVerificado) {
                    return ['ok' => false, 'mensagem' => 'Confirme o e-mail da sua conta Google para vincular.'];
                }
                $this->usuarios->update($usuario->id, ['google_id' => $googleId]);
            }

            $this->registrarSessao($usuario);

            return ['ok' => true, 'mensagem' => 'Bem-vindo(a) de volta!'];
        }

        $novo = new Usuario([
            'nome'      => trim($nome) !== '' ? trim($nome) : $email,
            'email'     => $email,
            'nivel'     => 'organizador',
            'status'    => 'ativo',
            'google_id' => $googleId !== '' ? $googleId : null,
        ]);

        // Senha aleatória: o acesso é pelo Google (a senha local não é usada).
        $novo->senha = bin2hex(random_bytes(16));

        if ($this->usuarios->insert($novo) === false) {
            return ['ok' => false, 'mensagem' => 'Não foi possível criar sua conta. Tente novamente.'];
        }

        $novo->id = $this->usuarios->getInsertID();
        $this->registrarSessao($novo);

        return ['ok' => true, 'mensagem' => 'Conta criada e conectada com o Google!'];
    }
}
