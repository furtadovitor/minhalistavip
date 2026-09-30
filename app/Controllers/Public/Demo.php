<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

/**
 * Listas de exemplo (demonstração) exibidas na Home.
 *
 * São dados ESTÁTICOS: nenhum evento real de cliente é exposto publicamente
 * (diretriz de privacidade). Servem para o visitante experimentar a interface.
 */
class Demo extends BaseController
{
    /**
     * Metadados + conteúdo de cada lista de exemplo.
     *
     * @var array<string, array<string, mixed>>
     */
    public const EXEMPLOS = [
        'casamento' => [
            'slug'            => 'casamento',
            'titulo'          => 'Marina & Gabriel',
            'tipo'            => 'Casamento',
            'icone'           => '💍',
            'badge'           => 'warning',
            'data'            => '12 de Outubro',
            'local'           => 'Ilhabela, SP',
            'resumo'          => 'Lista com cotas para lua de mel na Tailândia e itens para a nova casa convertidos em PIX.',
            'presentes'       => 142,
            'capa'            => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=600&q=80',
            'cor_primaria'    => '#D97706',
            'cor_secundaria'  => '#B45309',
            'escuro'          => false,
            'descricao'       => 'Obrigado por fazer parte desse momento tão especial!',
            'mensagem_convite' => 'Sua presença é o nosso maior presente. Se quiser nos mimar, escolha uma cota da nossa lista!',
            'recados'         => [
                ['autor' => 'Tia Cláudia', 'mensagem' => 'Que felicidade! Desejo toda a sorte do mundo para vocês. 💛'],
                ['autor' => 'Rafael e Bia', 'mensagem' => 'Nos vemos em Ilhabela! Parabéns aos noivos. 🥂'],
            ],
            'itens'           => [
                ['nome' => 'Cota de Lua de Mel', 'descricao' => '7 noites na Tailândia', 'valor' => 300.00, 'meta' => 10, 'vendida' => 3],
                ['nome' => 'Jogo de Panelas', 'descricao' => 'Panela antiaderente para a cozinha nova', 'valor' => 250.00, 'meta' => 1, 'vendida' => 0],
                ['nome' => 'Cota do Aluguel', 'descricao' => 'Nos ajuda no primeiro mês do nosso lar', 'valor' => 200.00, 'meta' => 5, 'vendida' => 2],
                ['nome' => 'Kit de Toalhas', 'descricao' => 'Toalhas de banho e rosto', 'valor' => 150.00, 'meta' => 2, 'vendida' => 1],
                ['nome' => 'Cota do Churrasco', 'descricao' => 'Para o churrasco com a família e amigos', 'valor' => 100.00, 'meta' => 20, 'vendida' => 8],
                ['nome' => 'Cota Livre em Dinheiro', 'descricao' => 'Contribua com qualquer valor', 'valor' => 50.00, 'meta' => 50, 'vendida' => 14],
            ],
        ],

        'aniversario' => [
            'slug'            => 'aniversario',
            'titulo'          => '30 Anos do Lucas',
            'tipo'            => 'Aniversário',
            'icone'           => '🎂',
            'badge'           => 'danger',
            'data'            => '25 de Novembro',
            'local'           => 'Rio de Janeiro, RJ',
            'resumo'          => 'Vaquinha e cotas comemorativas para a festa de aniversário e viagem com amigos.',
            'presentes'       => 35,
            'capa'            => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=600&q=80',
            'cor_primaria'    => '#EC4899',
            'cor_secundaria'  => '#8B5CF6',
            'escuro'          => true,
            'descricao'       => 'Trinta anos merecem uma comemoração inesquecível — e vocês fazem parte dela!',
            'mensagem_convite' => 'Em vez de presente físico, me ajude a realizar a festa e a viagem dos sonhos. 🎉',
            'recados'         => [
                ['autor' => 'Turma da faculdade', 'mensagem' => 'Bora pra cima! Já garantimos a cota do bar. 🍻'],
                ['autor' => 'Camila', 'mensagem' => '30 anos, hein! Que venham muitos mais. ❤️'],
            ],
            'itens'           => [
                ['nome' => 'Cota da Festa', 'descricao' => 'Aluguel do espaço e decoração', 'valor' => 100.00, 'meta' => 100, 'vendida' => 35],
                ['nome' => 'Cota da Viagem', 'descricao' => 'Roteiro com os amigos em Floripa', 'valor' => 150.00, 'meta' => 20, 'vendida' => 6],
                ['nome' => 'Cota do Bar', 'descricao' => 'Bebidas e drinks da noite', 'valor' => 80.00, 'meta' => 30, 'vendida' => 12],
                ['nome' => 'Cota Livre', 'descricao' => 'Contribua com qualquer valor', 'valor' => 50.00, 'meta' => 100, 'vendida' => 40],
            ],
        ],

        'cha-de-bebe' => [
            'slug'            => 'cha-de-bebe',
            'titulo'          => 'Chá da Sofia',
            'tipo'            => 'Chá de Bebê',
            'icone'           => '👶',
            'badge'           => 'info',
            'data'            => '05 de Dezembro',
            'local'           => 'São Paulo, SP',
            'resumo'          => 'Lista virtual de fraldas de diversos tamanhos e mimos para o enxoval do bebê.',
            'presentes'       => 88,
            'capa'            => 'https://images.unsplash.com/photo-1519689680058-324335c77eba?auto=format&fit=crop&w=600&q=80',
            'cor_primaria'    => '#06B6D4',
            'cor_secundaria'  => '#0891B2',
            'escuro'          => false,
            'descricao'       => 'A Sofia está chegando! Sua ajuda faz toda a diferença nesse momento.',
            'mensagem_convite' => 'Ao invés de trazer fraldas na mão, você pode presentear online com todo o carinho. 💙',
            'recados'         => [
                ['autor' => 'Vovó Marlene', 'mensagem' => 'Ansiosa para conhecer a Sofia! Já presenteei o enxoval. 🍼'],
                ['autor' => 'Amanda', 'mensagem' => 'Que fase linda! Muitas bençãos para essa família. ✨'],
            ],
            'itens'           => [
                ['nome' => 'Fraldas Tamanho P', 'descricao' => 'Pacote com 40 unidades', 'valor' => 60.00, 'meta' => 100, 'vendida' => 42],
                ['nome' => 'Fraldas Tamanho M', 'descricao' => 'Pacote com 40 unidades', 'valor' => 60.00, 'meta' => 100, 'vendida' => 30],
                ['nome' => 'Kit Enxoval Completo', 'descricao' => 'Roupinhas, mantas e acessórios', 'valor' => 200.00, 'meta' => 5, 'vendida' => 2],
                ['nome' => 'Cota de Mimos', 'descricao' => 'Para os itens do dia a dia', 'valor' => 50.00, 'meta' => 50, 'vendida' => 16],
            ],
        ],
    ];

    /**
     * Cards de exemplo usados na Home.
     *
     * @return list<array<string, mixed>>
     */
    public static function cards(): array
    {
        return array_values(self::EXEMPLOS);
    }

    public function index()
    {
        return redirect()->to(site_url('/') . '#exemplos');
    }

    public function show($slug = null)
    {
        $slug = (string) $slug;

        if (! isset(self::EXEMPLOS[$slug])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Exemplo não encontrado.');
        }

        $demo = self::EXEMPLOS[$slug];

        $presentes = [];
        foreach ($demo['itens'] as $indice => $item) {
            $presentes[] = [
                'id'                 => $indice + 1,
                'nome'               => $item['nome'],
                'descricao'          => $item['descricao'] ?? null,
                'imagem'             => null,
                'tipo'               => 'ficticio',
                'valor'              => $item['valor'],
                'quantidade_meta'    => $item['meta'],
                'quantidade_vendida' => $item['vendida'],
                'link_afiliado'      => null,
            ];
        }

        $recados = [];
        foreach ($demo['recados'] as $recado) {
            $recados[] = ['nome_autor' => $recado['autor'], 'mensagem' => $recado['mensagem']];
        }

        $cotasTotal = 0;
        $cotasVendidas = 0;
        foreach ($presentes as $presente) {
            $cotasTotal    += (int) $presente['quantidade_meta'];
            $cotasVendidas += (int) $presente['quantidade_vendida'];
        }

        $evento = (object) [
            'titulo'           => $demo['titulo'],
            'subtitulo'        => $demo['resumo'],
            'tipo_evento'      => $demo['tipo'],
            'slug'             => 'demo/' . $demo['slug'],
            'descricao'        => $demo['descricao'] ?? null,
            'mensagem_convite' => $demo['mensagem_convite'] ?? null,
            'local_nome'       => $demo['local'] ?? null,
            'imagem_capa'      => $demo['capa'] ?? null,
            'cor_primaria'     => $demo['cor_primaria'],
            'cor_secundaria'   => $demo['cor_secundaria'],
            'permite_rsvp'     => true,
            'permite_recados'  => true,
            'exibir_valores'   => true,
            'meta_valor'       => null,
            'status'           => 'publicado',
        ];

        return view('hotsite/lista', [
            'titulo'    => $demo['titulo'] . ' (exemplo)',
            'evento'    => $evento,
            'presentes' => $presentes,
            'recados'   => $recados,
            'modo'      => 'demo',
            'escuro'    => ! empty($demo['escuro']),
            'dataTexto' => $demo['data'],
            'dataIso'   => null,
            'stats'     => [
                'cotas_total'    => $cotasTotal,
                'cotas_vendidas' => $cotasVendidas,
                'arrecadado'     => 0.0,
                'confirmados'    => count($recados),
            ],
        ]);
    }
}
