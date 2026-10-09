<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\DemoListaModel;

/**
 * Sitemap XML para os buscadores: páginas fixas + exemplos + hotsites publicados.
 * Rota: /sitemap.xml (registrada antes do catch-all de hotsite).
 */
class Sitemap extends BaseController
{
    public function index()
    {
        $urls = [
            ['loc' => site_url('/'), 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['loc' => site_url('criar-lista-de-presente'), 'changefreq' => 'monthly', 'priority' => '0.9'],
        ];

        // Listas de exemplo (conteúdo público, bom para SEO).
        foreach ((new DemoListaModel())->publicadas() as $demo) {
            $urls[] = [
                'loc'        => site_url('demo/' . $demo['slug']),
                'changefreq' => 'monthly',
                'priority'   => '0.5',
                'lastmod'    => $demo['atualizado_em'] ?? null,
            ];
        }

        // Hotsites de eventos publicados.
        $eventos = db_connect()->table('eventos')
            ->select('slug, atualizado_em')
            ->where('status', 'publicado')
            ->orderBy('atualizado_em', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($eventos as $evento) {
            $urls[] = [
                'loc'        => site_url($evento['slug']),
                'changefreq' => 'weekly',
                'priority'   => '0.7',
                'lastmod'    => $evento['atualizado_em'] ?? null,
            ];
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars((string) $url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";

            if (! empty($url['lastmod'])) {
                $timestamp = strtotime((string) $url['lastmod']);
                if ($timestamp !== false) {
                    $xml .= '    <lastmod>' . date('Y-m-d', $timestamp) . "</lastmod>\n";
                }
            }
            if (! empty($url['changefreq'])) {
                $xml .= '    <changefreq>' . $url['changefreq'] . "</changefreq>\n";
            }
            if (! empty($url['priority'])) {
                $xml .= '    <priority>' . $url['priority'] . "</priority>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $this->response
            ->setContentType('application/xml')
            ->setBody($xml);
    }
}
