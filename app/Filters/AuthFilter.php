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

        if (session()->get('usuario_id')) {
            return null;
        }

        session()->set('redirect_url', (string) $request->getUri());

        return redirect()->to(site_url('login'))->with('erro', 'Faça login para continuar.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
