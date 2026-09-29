<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controle de acesso por nível (ACL).
 *
 * Uso nas rotas: 'filter' => 'role:superadmin'
 *                  'filter' => 'role:superadmin,organizador'
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('url');

        $nivel = session()->get('usuario_nivel');

        if ($nivel === null) {
            return redirect()->to(site_url('login'))->with('erro', 'Faça login para continuar.');
        }

        $permitidos = array_filter((array) ($arguments ?? []));

        if ($permitidos !== [] && ! in_array($nivel, $permitidos, true)) {
            $destino = $nivel === 'superadmin' ? 'admin' : 'painel';

            return redirect()->to(site_url($destino))
                ->with('erro', 'Você não tem permissão para acessar essa área.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
