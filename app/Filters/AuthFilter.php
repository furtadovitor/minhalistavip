<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Garante que exista um usuário autenticado na sessão.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('url');

        $id = session()->get('usuario_id');

        if (! $id) {
            session()->set('redirect_url', (string) $request->getUri());

            return redirect()->to(site_url('login'))->with('erro', 'Faça login para continuar.');
        }

        // Revalida o usuário a cada requisição: se foi suspenso ou excluído
        // depois do login, encerra a sessão imediatamente.
        $usuario = model(\App\Models\UsuarioModel::class)->find($id);

        if ($usuario === null || ! $usuario->isAtivo()) {
            (new \App\Services\AuthService())->logout();

            return redirect()->to(site_url('login'))->with('erro', 'Sua conta está inativa. Entre novamente.');
        }

        // Mantém o nível da sessão em sincronia com o banco (ACL confiável).
        session()->set('usuario_nivel', $usuario->nivel);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
