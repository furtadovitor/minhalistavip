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
     * Login com senha.
     *
     * Quando `true`, o login exige e-mail + senha (padrão de produção).
     * Quando `false`, o login aceita apenas o e-mail (a senha não é conferida) —
     * use somente em ambiente controlado, pois qualquer pessoa que saiba um
     * e-mail cadastrado consegue entrar, inclusive o SuperAdmin.
     */
    public const EXIGIR_SENHA = true;

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
        // Evita fixação de sessão: gera um novo ID logo após autenticar.
        session()->regenerate(true);

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
        session()->remove(['usuario_id', 'usuario_nome', 'usuario_email', 'usuario_nivel', 'redirect_url']);
        session()->regenerate(true);
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
     * Valida o destino pós-login: precisa ser do próprio site e acessível pelo
     * nível do usuário. Caso contrário, devolve a rota inicial.
     *
     * Evita open redirect e o caso de um organizador ser enviado para /admin
     * (onde veria "você não tem permissão") por causa de um redirect_url antigo.
     */
    public function destinoSeguro(?string $destino): string
    {
        $padrao = site_url($this->rotaInicial());

        if ($destino === null || trim($destino) === '') {
            return $padrao;
        }

        $partes = parse_url($destino);

        if ($partes === false) {
            return $padrao;
        }

        // Só aceita o próprio domínio (ou caminho relativo).
        if (! empty($partes['host'])) {
            $hostBase = (string) parse_url(base_url(), PHP_URL_HOST);

            if ($hostBase === '' || strcasecmp((string) $partes['host'], $hostBase) !== 0) {
                return $padrao;
            }
        }

        // Descobre o primeiro segmento do caminho (sem o baseURL).
        $basePath = (string) (parse_url(base_url(), PHP_URL_PATH) ?? '');
        $caminho  = (string) ($partes['path'] ?? '');

        if ($basePath !== '' && str_starts_with($caminho, $basePath)) {
            $caminho = substr($caminho, strlen($basePath));
        }

        $caminho = ltrim($caminho, '/');

        if (str_starts_with($caminho, 'admin') && $this->nivel() !== 'superadmin') {
            return $padrao;
        }

        return $destino;
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
