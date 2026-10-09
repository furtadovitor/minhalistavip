<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\DemoListaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Listas de exemplo (demonstração) exibidas na Home e em /demo/{slug}.
 *
 * Os dados são gerenciados pelo SuperAdmin em Admin\Demos e guardados na tabela
 * `demo_listas`. Nenhum evento real de cliente é exposto publicamente.
 */
class Demo extends BaseController
{
    /**
     * Cards de exemplo usados na Home (apenas demos publicadas).
     *
     * @return list<array<string, mixed>>
     */
    public static function cards(): array
    {
        $cards = [];

        foreach ((new DemoListaModel())->publicadas() as $linha) {
            $itens     = json_decode((string) ($linha['itens'] ?? ''), true) ?: [];
            $presentes = (int) ($linha['presentes'] ?? 0);

            // Sem número informado, usa a soma das cotas como contagem exibida.
            if ($presentes <= 0) {
                foreach ($itens as $item) {
                    $presentes += (int) ($item['meta'] ?? 0);
                }
            }

            $cards[] = [
                'slug'      => $linha['slug'],
                'titulo'    => $linha['titulo'],
                'tipo'      => $linha['tipo'],
                'icone'     => $linha['icone'] ?: '🎁',
                'badge'     => $linha['badge'] ?: 'primary',
                'data'      => $linha['data_texto'] ?? '',
                'local'     => $linha['local'] ?? '',
                'resumo'    => $linha['resumo'] ?? '',
                'presentes' => $presentes,
                'capa'      => $linha['capa'] ?? '',
            ];
        }

        return $cards;
    }

    public function index()
    {
        return redirect()->to(site_url('/') . '#exemplos');
    }

    public function show($slug = null)
    {
        $slug = (string) $slug;
        $demo = (new DemoListaModel())->publicadaPorSlug($slug);

        if ($demo === null) {
            throw PageNotFoundException::forPageNotFound('Exemplo não encontrado.');
        }

        $presentes = [];
        foreach (($demo['itens'] ?? []) as $indice => $item) {
            $presentes[] = [
                'id'                 => $indice + 1,
                'nome'               => $item['nome'] ?? 'Presente',
                'descricao'          => $item['descricao'] ?? null,
                'imagem'             => null,
                'tipo'               => 'ficticio',
                'valor'              => (float) ($item['valor'] ?? 0),
                'quantidade_meta'    => (int) ($item['meta'] ?? 0),
                'quantidade_vendida' => (int) ($item['vendida'] ?? 0),
                'link_afiliado'      => null,
            ];
        }

        $recados = [];
        foreach (($demo['recados'] ?? []) as $recado) {
            $recados[] = [
                'nome_autor' => $recado['autor'] ?? 'Convidado',
                'mensagem'   => $recado['mensagem'] ?? '',
            ];
        }

        $galeria = [];
        foreach (($demo['galeria'] ?? []) as $foto) {
            if (empty($foto['imagem'])) {
                continue;
            }

            $galeria[] = [
                'imagem'  => $foto['imagem'],
                'legenda' => $foto['legenda'] ?? null,
            ];
        }

        $cotasTotal    = 0;
        $cotasVendidas = 0;
        foreach ($presentes as $presente) {
            $cotasTotal    += (int) $presente['quantidade_meta'];
            $cotasVendidas += (int) $presente['quantidade_vendida'];
        }

        $evento = (object) [
            'titulo'           => $demo['titulo'],
            'subtitulo'        => $demo['resumo'] ?? '',
            'tipo_evento'      => $demo['tipo'],
            'slug'             => 'demo/' . $demo['slug'],
            'descricao'        => $demo['descricao'] ?? null,
            'mensagem_convite' => $demo['mensagem_convite'] ?? null,
            'local_nome'       => $demo['local'] ?? null,
            'imagem_capa'      => $demo['capa'] ?? null,
            'cor_primaria'     => $demo['cor_primaria'] ?: '#722ED4',
            'cor_secundaria'   => $demo['cor_secundaria'] ?: '#7C3AED',
            'tema'             => $demo['tema'] ?? 'classico',
            'permite_rsvp'     => true,
            'permite_recados'  => true,
            'exibir_valores'   => true,
            'meta_valor'       => null,
            'status'           => 'publicado',
        ];

        $capaDemo = trim((string) ($demo['capa'] ?? ''));
        $seoDemo  = [
            'descricao' => trim((string) ($demo['resumo'] ?? '')) ?: ('Exemplo de lista de presentes: ' . $demo['titulo'] . '.'),
            'imagem'    => $capaDemo !== '' ? $capaDemo : null,
            'tipo'      => 'website',
            'url'       => site_url('demo/' . $demo['slug']),
        ];

        return view('hotsite/lista', [
            'titulo'    => $demo['titulo'] . ' (exemplo)',
            'evento'    => $evento,
            'presentes' => $presentes,
            'recados'   => $recados,
            'galeria'   => $galeria,
            'modo'      => 'demo',
            'escuro'    => ! empty($demo['escuro']),
            'dataTexto' => $demo['data_texto'] ?: null,
            'dataIso'   => null,
            'stats'     => [
                'cotas_total'    => $cotasTotal,
                'cotas_vendidas' => $cotasVendidas,
                'arrecadado'     => 0.0,
                'confirmados'    => count($recados),
            ],
            'seo'       => $seoDemo,
        ]);
    }
}
