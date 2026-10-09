<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Controllers\Public\Demo;

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

        return $this->render('home', [
            'titulo' => 'Lista de presentes em dinheiro para o seu evento',
            'demos'  => Demo::cards(),
            'seo'    => [
                'descricao' => 'Crie a lista de presentes do seu casamento, chá de bebê ou aniversário em 1 minuto e receba por PIX. Site, convite e RSVP inclusos — é grátis.',
                'tipo'      => 'website',
                'jsonld'    => [
                    [
                        '@context' => 'https://schema.org',
                        '@type'    => 'Organization',
                        'name'     => 'Minha Lista VIP',
                        'url'      => base_url(),
                        'logo'     => base_url('assets/logo_mlvp_real.png'),
                    ],
                    [
                        '@context'   => 'https://schema.org',
                        '@type'      => 'WebSite',
                        'name'       => 'Minha Lista VIP',
                        'url'        => base_url(),
                        'inLanguage' => 'pt-BR',
                    ],
                ],
            ],
        ]);
    }
}
