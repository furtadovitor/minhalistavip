<?php

namespace App\Services;

use App\Entities\Usuario;
use App\Models\UsuarioModel;

/**
 * Redefinição de senha por e-mail: token aleatório de uso único, guardado
 * apenas como hash (SHA-256) e com validade curta.
 */
class SenhaService
{
    /** Validade do link, em minutos. */
    public const VALIDADE_MINUTOS = 60;

    protected UsuarioModel $usuarios;

    protected MailService $mail;

    public function __construct(?UsuarioModel $usuarios = null, ?MailService $mail = null)
    {
        $this->usuarios = $usuarios ?? new UsuarioModel();
        $this->mail     = $mail ?? new MailService();
    }

    /**
     * Gera o token e envia o e-mail. A resposta ao visitante é sempre genérica,
     * para não revelar se o e-mail está cadastrado.
     */
    public function solicitar(string $email): void
    {
        $usuario = $this->usuarios->buscarPorEmail($email);

        if ($usuario === null || ! $usuario->isAtivo()) {
            return;
        }

        $token = bin2hex(random_bytes(32));

        $this->usuarios->update($usuario->id, [
            'reset_senha_token'  => hash('sha256', $token),
            'reset_senha_expira' => date('Y-m-d H:i:s', time() + self::VALIDADE_MINUTOS * 60),
        ]);

        $link = site_url('redefinir-senha/' . $token);

        $this->mail->enviar(
            $usuario->email,
            'Redefinir sua senha · Minha Lista VIP',
            $this->corpoEmail((string) $usuario->nome, $link)
        );

        // Em desenvolvimento, registra o link no log para facilitar os testes
        // quando o e-mail ainda não está configurado.
        if (ENVIRONMENT !== 'production') {
            log_message('debug', '[reset-senha] ' . $usuario->email . ' -> ' . $link);
        }
    }

    /**
     * Retorna o usuário dono do token, se existir, não estiver expirado e a
     * conta estiver ativa.
     */
    public function usuarioPorToken(string $token): ?Usuario
    {
        if ($token === '') {
            return null;
        }

        $usuario = $this->usuarios->where('reset_senha_token', hash('sha256', $token))->first();

        if ($usuario === null || ! $usuario->isAtivo()) {
            return null;
        }

        $expira = (string) ($usuario->reset_senha_expira ?? '');

        if ($expira === '' || strtotime($expira) < time()) {
            return null;
        }

        return $usuario;
    }

    /**
     * Troca a senha e invalida o token (uso único).
     */
    public function redefinir(string $token, string $novaSenha): bool
    {
        $usuario = $this->usuarioPorToken($token);

        if ($usuario === null) {
            return false;
        }

        $usuario->senha = $novaSenha; // mutator gera o hash

        return (bool) $this->usuarios->update($usuario->id, [
            'senha'              => $usuario->senha,
            'reset_senha_token'  => null,
            'reset_senha_expira' => null,
        ]);
    }

    private function corpoEmail(string $nome, string $link): string
    {
        $nome = esc($nome);
        $url  = esc($link, 'attr');

        return <<<HTML
        <div style="font-family:Arial,Helvetica,sans-serif;max-width:520px;margin:0 auto;color:#111827">
            <h2 style="color:#4F46E5;margin-bottom:.5rem">Redefinir sua senha</h2>
            <p>Olá, {$nome}!</p>
            <p>Recebemos um pedido para redefinir a senha da sua conta na Minha Lista VIP.
               O link é válido por {self::VALIDADE_MINUTOS} minutos e só pode ser usado uma vez.</p>
            <p style="text-align:center;margin:1.5rem 0">
                <a href="{$url}"
                   style="background:#4F46E5;color:#fff;text-decoration:none;padding:.7rem 1.4rem;border-radius:.6rem;display:inline-block">
                    Criar nova senha
                </a>
            </p>
            <p style="font-size:.85rem;color:#6B7280">Se você não pediu isso, ignore este e-mail —
               sua senha continua a mesma.</p>
        </div>
        HTML;
    }
}
