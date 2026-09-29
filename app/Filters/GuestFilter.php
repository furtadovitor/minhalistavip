<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Impede que usuários já autenticados acessem login/registro.
 */
class GuestFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('url');

        $nivel = session()->get('usuario_nivel');

        if ($nivel === null) {
            return null;
        }

        $destino = $nivel === 'superadmin' ? 'admin' : 'painel';

        return redirect()->to(site_url($destino));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
