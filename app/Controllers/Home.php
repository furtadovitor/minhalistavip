<?php

namespace App\Controllers;

use App\Controllers\BaseController;

/**
 * Página inicial pública da plataforma.
 */
class Home extends BaseController
{
    public function index()
    {
        if ($this->auth->estaLogado()) {
            return redirect()->to(site_url($this->auth->rotaInicial()));
        }

        return $this->render('home', ['titulo' => 'Minha Lista VIP']);
    }
}
