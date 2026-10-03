<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Listas de exemplo (demonstração) exibidas na Home e em /demo/{slug}.
 *
 * O conteúdo interno (itens/cotas, recados e galeria) é guardado em colunas
 * TEXT no formato JSON para que o SuperAdmin edite tudo em um único formulário.
 */
class DemoListaModel extends Model
{
    protected $table         = 'demo_listas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'slug',
        'titulo',
        'tipo',
        'icone',
        'badge',
        'data_texto',
        'local',
        'resumo',
        'descricao',
        'mensagem_convite',
        'capa',
        'cor_primaria',
        'cor_secundaria',
        'tema',
        'escuro',
        'presentes',
        'itens',
        'recados',
        'galeria',
        'ativo',
        'ordem',
    ];

    /**
     * Exemplos que acompanham a plataforma. Fonte única usada para popular a
     * tabela (migration) e para a ação "Restaurar exemplos padrão" no admin.
     *
     * @var array<string, array<string, mixed>>
     */
    public const EXEMPLOS_PADRAO = [
        'casamento' => [
            'slug'             => 'casamento',
            'titulo'           => 'Marina & Gabriel',
            'tipo'             => 'Casamento',
            'icone'            => '💍',
            'badge'            => 'warning',
            'data_texto'       => '12 de Outubro',
            'local'            => 'Ilhabela, SP',
            'resumo'           => 'Lista com cotas para lua de mel na Tailândia e itens para a nova casa convertidos em PIX.',
            'descricao'        => 'Obrigado por fazer parte desse momento tão especial!',
            'mensagem_convite' => 'Sua presença é o nosso maior presente. Se quiser nos mimar, escolha uma cota da nossa lista!',
            'capa'             => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=600&q=80',
            'cor_primaria'     => '#D97706',
            'cor_secundaria'   => '#B45309',
            'tema'             => 'casamento',
            'escuro'           => 0,
            'presentes'        => 142,
            'ativo'            => 1,
            'ordem'            => 1,
            'recados'          => [
                ['autor' => 'Tia Cláudia', 'mensagem' => 'Que felicidade! Desejo toda a sorte do mundo para vocês. 💛'],
                ['autor' => 'Rafael e Bia', 'mensagem' => 'Nos vemos em Ilhabela! Parabéns aos noivos. 🥂'],
            ],
            'galeria'          => [
                ['imagem' => 'https://picsum.photos/seed/marina-gabriel-1/800/800', 'legenda' => 'O pedido, em Ilhabela'],
                ['imagem' => 'https://picsum.photos/seed/marina-gabriel-2/800/800', 'legenda' => 'Ensaio pré-wedding'],
                ['imagem' => 'https://picsum.photos/seed/marina-gabriel-3/800/800', 'legenda' => 'Nosso primeiro apartamento'],
                ['imagem' => 'https://picsum.photos/seed/marina-gabriel-4/800/800', 'legenda' => 'Viagem de noivado'],
                ['imagem' => 'https://picsum.photos/seed/marina-gabriel-5/800/800', 'legenda' => 'Com as famílias reunidas'],
                ['imagem' => 'https://picsum.photos/seed/marina-gabriel-6/800/800', 'legenda' => 'Contagem regressiva!'],
            ],
            'itens'            => [
                ['nome' => 'Cota de Lua de Mel', 'descricao' => '7 noites na Tailândia', 'valor' => 300.00, 'meta' => 10, 'vendida' => 3],
                ['nome' => 'Jogo de Panelas', 'descricao' => 'Panela antiaderente para a cozinha nova', 'valor' => 250.00, 'meta' => 1, 'vendida' => 0],
                ['nome' => 'Cota do Aluguel', 'descricao' => 'Nos ajuda no primeiro mês do nosso lar', 'valor' => 200.00, 'meta' => 5, 'vendida' => 2],
                ['nome' => 'Kit de Toalhas', 'descricao' => 'Toalhas de banho e rosto', 'valor' => 150.00, 'meta' => 2, 'vendida' => 1],
                ['nome' => 'Cota do Churrasco', 'descricao' => 'Para o churrasco com a família e amigos', 'valor' => 100.00, 'meta' => 20, 'vendida' => 8],
                ['nome' => 'Cota Livre em Dinheiro', 'descricao' => 'Contribua com qualquer valor', 'valor' => 50.00, 'meta' => 50, 'vendida' => 14],
            ],
        ],

        'aniversario' => [
            'slug'             => 'aniversario',
            'titulo'           => '30 Anos do Lucas',
            'tipo'             => 'Aniversário',
            'icone'            => '🎂',
            'badge'            => 'danger',
            'data_texto'       => '25 de Novembro',
            'local'            => 'Rio de Janeiro, RJ',
            'resumo'           => 'Vaquinha e cotas comemorativas para a festa de aniversário e viagem com amigos.',
            'descricao'        => 'Trinta anos merecem uma comemoração inesquecível — e vocês fazem parte dela!',
            'mensagem_convite' => 'Em vez de presente físico, me ajude a realizar a festa e a viagem dos sonhos. 🎉',
            'capa'             => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=600&q=80',
            'cor_primaria'     => '#EC4899',
            'cor_secundaria'   => '#8B5CF6',
            'tema'             => 'moderno',
            'escuro'           => 1,
            'presentes'        => 35,
            'ativo'            => 1,
            'ordem'            => 2,
            'recados'          => [
                ['autor' => 'Turma da faculdade', 'mensagem' => 'Bora pra cima! Já garantimos a cota do bar. 🍻'],
                ['autor' => 'Camila', 'mensagem' => '30 anos, hein! Que venham muitos mais. ❤️'],
            ],
            'galeria'          => [
                ['imagem' => 'https://picsum.photos/seed/lucas-30-1/800/800', 'legenda' => 'A turma reunida'],
                ['imagem' => 'https://picsum.photos/seed/lucas-30-2/800/800', 'legenda' => 'Pré-festa'],
                ['imagem' => 'https://picsum.photos/seed/lucas-30-3/800/800', 'legenda' => 'Viagem com os amigos'],
                ['imagem' => 'https://picsum.photos/seed/lucas-30-4/800/800', 'legenda' => 'Momentos que ficam'],
            ],
            'itens'            => [
                ['nome' => 'Cota da Festa', 'descricao' => 'Aluguel do espaço e decoração', 'valor' => 100.00, 'meta' => 100, 'vendida' => 35],
                ['nome' => 'Cota da Viagem', 'descricao' => 'Roteiro com os amigos em Floripa', 'valor' => 150.00, 'meta' => 20, 'vendida' => 6],
                ['nome' => 'Cota do Bar', 'descricao' => 'Bebidas e drinks da noite', 'valor' => 80.00, 'meta' => 30, 'vendida' => 12],
                ['nome' => 'Cota Livre', 'descricao' => 'Contribua com qualquer valor', 'valor' => 50.00, 'meta' => 100, 'vendida' => 40],
            ],
        ],

        'cha-de-bebe' => [
            'slug'             => 'cha-de-bebe',
            'titulo'           => 'Chá da Sofia',
            'tipo'             => 'Chá de Bebê',
            'icone'            => '👶',
            'badge'            => 'info',
            'data_texto'       => '05 de Dezembro',
            'local'            => 'São Paulo, SP',
            'resumo'           => 'Lista virtual de fraldas de diversos tamanhos e mimos para o enxoval do bebê.',
            'descricao'        => 'A Sofia está chegando! Sua ajuda faz toda a diferença nesse momento.',
            'mensagem_convite' => 'Ao invés de trazer fraldas na mão, você pode presentear online com todo o carinho. 💙',
            'capa'             => 'https://images.unsplash.com/photo-1519689680058-324335c77eba?auto=format&fit=crop&w=600&q=80',
            'cor_primaria'     => '#06B6D4',
            'cor_secundaria'   => '#0891B2',
            'tema'             => 'cha_bebe',
            'escuro'           => 0,
            'presentes'        => 88,
            'ativo'            => 1,
            'ordem'            => 3,
            'recados'          => [
                ['autor' => 'Vovó Marlene', 'mensagem' => 'Ansiosa para conhecer a Sofia! Já presenteei o enxoval. 🍼'],
                ['autor' => 'Amanda', 'mensagem' => 'Que fase linda! Muitas bençãos para essa família. ✨'],
            ],
            'galeria'          => [
                ['imagem' => 'https://picsum.photos/seed/sofia-1/800/800', 'legenda' => 'O quartinho ficando pronto'],
                ['imagem' => 'https://picsum.photos/seed/sofia-2/800/800', 'legenda' => 'Chá revelação'],
                ['imagem' => 'https://picsum.photos/seed/sofia-3/800/800', 'legenda' => 'Enxoval chegando'],
                ['imagem' => 'https://picsum.photos/seed/sofia-4/800/800', 'legenda' => 'Esperando a Sofia'],
            ],
            'itens'            => [
                ['nome' => 'Fraldas Tamanho P', 'descricao' => 'Pacote com 40 unidades', 'valor' => 60.00, 'meta' => 100, 'vendida' => 42],
                ['nome' => 'Fraldas Tamanho M', 'descricao' => 'Pacote com 40 unidades', 'valor' => 60.00, 'meta' => 100, 'vendida' => 30],
                ['nome' => 'Kit Enxoval Completo', 'descricao' => 'Roupinhas, mantas e acessórios', 'valor' => 200.00, 'meta' => 5, 'vendida' => 2],
                ['nome' => 'Cota de Mimos', 'descricao' => 'Para os itens do dia a dia', 'valor' => 50.00, 'meta' => 50, 'vendida' => 16],
            ],
        ],
    ];

    /**
     * Exemplos padrão prontos para inserir no banco (JSON já codificado).
     *
     * @return list<array<string, mixed>>
     */
    public static function padroesParaBanco(): array
    {
        $linhas = [];

        foreach (self::EXEMPLOS_PADRAO as $exemplo) {
            $linhas[] = [
                'slug'             => $exemplo['slug'],
                'titulo'           => $exemplo['titulo'],
                'tipo'             => $exemplo['tipo'],
                'icone'            => $exemplo['icone'],
                'badge'            => $exemplo['badge'],
                'data_texto'       => $exemplo['data_texto'],
                'local'            => $exemplo['local'],
                'resumo'           => $exemplo['resumo'],
                'descricao'        => $exemplo['descricao'],
                'mensagem_convite' => $exemplo['mensagem_convite'],
                'capa'             => $exemplo['capa'],
                'cor_primaria'     => $exemplo['cor_primaria'],
                'cor_secundaria'   => $exemplo['cor_secundaria'],
                'tema'             => $exemplo['tema'],
                'escuro'           => $exemplo['escuro'],
                'presentes'        => $exemplo['presentes'],
                'itens'            => self::codificar($exemplo['itens'] ?? []),
                'recados'          => self::codificar($exemplo['recados'] ?? []),
                'galeria'          => self::codificar($exemplo['galeria'] ?? []),
                'ativo'            => $exemplo['ativo'],
                'ordem'            => $exemplo['ordem'],
            ];
        }

        return $linhas;
    }

    /**
     * Codifica uma lista para JSON (sem escapar acentos).
     *
     * @param list<array<string, mixed>> $lista
     */
    public static function codificar(array $lista): string
    {
        return json_encode(array_values($lista), JSON_UNESCAPED_UNICODE) ?: '[]';
    }

    /**
     * Decodifica os campos JSON de uma linha.
     *
     * @param array<string, mixed> $linha
     * @return array<string, mixed>
     */
    public static function decodificar(array $linha): array
    {
        foreach (['itens', 'recados', 'galeria'] as $campo) {
            $valor = $linha[$campo] ?? null;

            if (is_array($valor)) {
                continue;
            }

            $linha[$campo] = json_decode((string) $valor, true) ?: [];
        }

        return $linha;
    }

    /**
     * Demos publicadas, na ordem de exibição (Home e /demo).
     *
     * @return list<array<string, mixed>>
     */
    public function publicadas(): array
    {
        return $this->db->table($this->table)
            ->where('ativo', 1)
            ->orderBy('ordem', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Todas as demos (para o admin), na ordem de exibição.
     *
     * @return list<array<string, mixed>>
     */
    public function todas(): array
    {
        return $this->db->table($this->table)
            ->orderBy('ordem', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Busca uma demo publicada pelo slug (com os JSONs já decodificados).
     *
     * @return array<string, mixed>|null
     */
    public function publicadaPorSlug(string $slug): ?array
    {
        $linha = $this->db->table($this->table)
            ->where('slug', $slug)
            ->where('ativo', 1)
            ->get()
            ->getRowArray();

        return $linha === null ? null : self::decodificar($linha);
    }

    /**
     * Busca uma demo pelo ID (com os JSONs já decodificados), para edição.
     *
     * @return array<string, mixed>|null
     */
    public function buscarPorId(int $id): ?array
    {
        $linha = $this->db->table($this->table)->where('id', $id)->get()->getRowArray();

        return $linha === null ? null : self::decodificar($linha);
    }

    /**
     * Ordem livre para uma nova demo.
     */
    public function proximaOrdem(): int
    {
        $linha = $this->db->table($this->table)->selectMax('ordem')->get()->getRowArray();

        return (int) ($linha['ordem'] ?? 0) + 1;
    }

    /**
     * Insere (ou atualiza) os exemplos padrão pelo slug.
     *
     * @return int Quantidade de exemplos importados/atualizados.
     */
    public function restaurarPadroes(): int
    {
        $total = 0;

        foreach (self::padroesParaBanco() as $linha) {
            $existente = $this->db->table($this->table)
                ->where('slug', $linha['slug'])
                ->get()
                ->getRowArray();

            if ($existente === null) {
                $this->insert($linha);
            } else {
                $this->update((int) $existente['id'], $linha);
            }

            $total++;
        }

        return $total;
    }
}
