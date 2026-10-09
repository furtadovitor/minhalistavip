<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Services\LandingService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Landing pages de SEO por tipo de evento: /lista-de-presentes e
 * /lista-de-presentes/{tipo}. Conteúdo em LandingService.
 */
class Landing extends BaseController
{
    /**
     * Hub com todas as listas por tipo (bom para links internos).
     */
    public function index()
    {
        return $this->render('public/landing_hub', [
            'titulo'  => 'Listas de presentes por tipo de evento',
            'paginas' => LandingService::paginas(),
            'seo'     => [
                'descricao' => 'Listas de presentes por tipo de evento: casamento, chá de bebê, aniversário, formatura e muito mais. Crie grátis, compartilhe e receba por PIX.',
                'tipo'      => 'website',
                'url'       => site_url('lista-de-presentes'),
                'jsonld'    => [[
                    '@context' => 'https://schema.org',
                    '@type'    => 'CollectionPage',
                    'name'     => 'Listas de presentes por tipo de evento',
                    'url'      => site_url('lista-de-presentes'),
                ]],
            ],
        ]);
    }

    /**
     * Página de um tipo de evento.
     */
    public function show($slug = null)
    {
        $pagina = LandingService::porSlug((string) $slug);

        if ($pagina === null) {
            throw PageNotFoundException::forPageNotFound('Página não encontrada.');
        }

        $url = site_url('lista-de-presentes/' . $pagina['slug']);

        $faq = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(static function (array $item): array {
                return [
                    '@type'          => 'Question',
                    'name'           => $item['p'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['r']],
                ];
            }, $pagina['faq']),
        ];

        $breadcrumb = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => site_url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Listas por tipo', 'item' => site_url('lista-de-presentes')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $pagina['h1'], 'item' => $url],
            ],
        ];

        return $this->render('public/landing', [
            'titulo'  => $pagina['h1'],
            'pagina'  => $pagina,
            'outras'  => $this->outras($pagina['chave']),
            'seo'     => [
                'descricao' => $pagina['descricao'],
                'tipo'      => 'website',
                'url'       => $url,
                'jsonld'    => [$faq, $breadcrumb],
            ],
        ]);
    }

    /**
     * Outros tipos para links internos (máx. 12).
     *
     * @return list<array<string, mixed>>
     */
    private function outras(string $chaveAtual): array
    {
        $outras = [];

        foreach (LandingService::paginas() as $pagina) {
            if ($pagina['chave'] !== $chaveAtual) {
                $outras[] = $pagina;
            }
        }

        return array_slice($outras, 0, 12);
    }
}
