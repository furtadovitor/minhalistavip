<?php

namespace App\Services;

/**
 * Conteúdo das landing pages de SEO por tipo de evento.
 *
 * Cada página tem título/descrição próprios, texto introdutório, benefícios,
 * passos, FAQ (com FAQPage JSON-LD) e CTA para o fluxo de criação — tudo
 * mantido aqui para ficar em um único lugar.
 */
class LandingService
{
    /**
     * Conteúdo "rico" dos tipos com maior volume de busca. Os demais usam
     * um modelo padrão gerado a partir do rótulo do tipo.
     *
     * @var array<string, array{h1:string, descricao:string, intro:string, beneficios:list<array{icone:string,titulo:string,texto:string}>, passos:list<string>, faq:list<array{p:string,r:string}>}>
     */
    private const CONTEUDO = [
        'casamento' => [
            'h1'         => 'Lista de casamento online grátis',
            'descricao'  => 'Crie sua lista de casamento online grátis e receba presentes em dinheiro via PIX. Site com convite, galeria e RSVP para os convidados.',
            'intro'      => 'Monte a lista de presentes do seu casamento em poucos minutos, compartilhe o site com os convidados e receba as cotas direto na sua conta via PIX — sem taxas escondidas.',
            'beneficios' => [
                ['icone' => 'bi-cash-coin', 'titulo' => 'Receba em dinheiro via PIX', 'texto' => 'Cotas de lua de mel, casa nova ou qualquer valor — o dinheiro cai na sua conta, não em produto.'],
                ['icone' => 'bi-palette', 'titulo' => 'Site com a cara do casal', 'texto' => 'Escolha o tema, as cores e a capa. Seu site de casamento fica pronto em minutos.'],
                ['icone' => 'bi-check2-circle', 'titulo' => 'RSVP dos convidados', 'texto' => 'Confirmação de presença com acompanhantes, limite de convidados e exportação da lista.'],
                ['icone' => 'bi-images', 'titulo' => 'Galeria e mural', 'texto' => 'Compartilhe fotos e receba recadinhos carinhosos dos convidados.'],
            ],
            'passos' => [
                'Crie sua conta grátis e informe nome, data e local do casamento.',
                'Monte a lista de cotas (lua de mel, aluguel, casa nova...) ou use um modelo pronto.',
                'Compartilhe o link do site com os convidados e receba pelo PIX.',
            ],
            'faq' => [
                ['p' => 'A lista de casamento é grátis?', 'r' => 'Sim. Criar, personalizar e compartilhar o site é 100% grátis, sem mensalidade. Só há uma taxa quando você recebe um presente, sempre com valores transparentes na sua carteira.'],
                ['p' => 'Os convidados precisam de conta para presentear?', 'r' => 'Não. O convidado escolhe a cota, informa os dados e paga por PIX sem criar conta.'],
                ['p' => 'Posso receber um valor livre em dinheiro?', 'r' => 'Sim. Além das cotas, você pode criar uma "cota livre" para quem quiser contribuir com qualquer valor.'],
                ['p' => 'Como funciona o RSVP do casamento?', 'r' => 'Você ativa a confirmação de presença no site. O convidado responde com nome e acompanhantes, e você aprova pela lista de convidados no painel.'],
                ['p' => 'O valor cai direto na minha conta?', 'r' => 'O pagamento entra no seu saldo na plataforma e você solicita o saque via PIX, direto para a chave que cadastrar.'],
            ],
        ],

        'cha_bebe' => [
            'h1'         => 'Lista de chá de bebê online grátis',
            'descricao'  => 'Monte sua lista de chá de bebê online grátis: fraldas, enxoval e cotas em dinheiro via PIX. Site com RSVP e recados para os convidados.',
            'intro'      => 'Organize o chá de bebê sem estresse: crie a lista online, inclua fraldas, itens do enxoval ou cotas em dinheiro e compartilhe com a família e os amigos por WhatsApp.',
            'beneficios' => [
                ['icone' => 'bi-gift', 'titulo' => 'Fraldas, enxoval ou dinheiro', 'texto' => 'Cadastre itens com quantidade e meta, ou cotas em PIX para o que o bebê mais precisar.'],
                ['icone' => 'bi-people', 'titulo' => 'Confirmar presença', 'texto' => 'Saiba quem vem e quantas pessoas, para acertar o buffet e a decoração.'],
                ['icone' => 'bi-chat-heart', 'titulo' => 'Mural de recados', 'texto' => 'Receba mensagens carinhosas dos convidados no site do chá.'],
                ['icone' => 'bi-phone', 'titulo' => 'Pronto para o WhatsApp', 'texto' => 'Link bonito para compartilhar no grupo da família em segundos.'],
            ],
            'passos' => [
                'Crie sua conta grátis e informe a data e o local.',
                'Adicione fraldas, itens do enxoval ou cotas em dinheiro.',
                'Compartilhe o link e acompanhe as confirmações pelo painel.',
            ],
            'faq' => [
                ['p' => 'Fazer lista de chá de bebê online é grátis?', 'r' => 'Sim, é grátis para criar e compartilhar. A plataforma só cobra uma taxa quando você recebe um presente.'],
                ['p' => 'Posso pedir fraldas de vários tamanhos?', 'r' => 'Pode. Cada item tem meta e quantidade, então você controla quantos pacotes de cada tamanho quer receber.'],
                ['p' => 'E se eu preferir dinheiro em vez de produto?', 'r' => 'Basta criar uma "cota livre em dinheiro": o convidado presenteia com o valor que quiser, direto via PIX.'],
                ['p' => 'Os convidados veem quem já presenteou?', 'r' => 'Você controla a exibição. Os indicadores ficam no seu painel; no site público não mostramos os números.'],
            ],
        ],

        'aniversario' => [
            'h1'         => 'Lista de aniversário online grátis',
            'descricao'  => 'Crie uma lista de aniversário online grátis com cotas em PIX, vaquinha e RSVP. Ideal para festas de aniversário de todas as idades.',
            'intro'      => 'Em vez de presentes repetidos, crie uma lista com cotas para a festa, uma viagem ou o que você quiser — e receba os valores por PIX de forma simples.',
            'beneficios' => [
                ['icone' => 'bi-cash-stack', 'titulo' => 'Vaquinha e cotas', 'texto' => 'Junte dinheiro para a festa, uma viagem ou um sonho. Cada convidado contribui como quiser.'],
                ['icone' => 'bi-emoji-smile', 'titulo' => 'Para qualquer idade', 'texto' => 'Funciona para aniversários infantis, 15 anos, 30 anos ou comemorações em família.'],
                ['icone' => 'bi-check2-circle', 'titulo' => 'Confirmação de presença', 'texto' => 'Saiba quantos convidados virão e organize o espaço e o buffet.'],
                ['icone' => 'bi-share', 'titulo' => 'Fácil de compartilhar', 'texto' => 'Envie o link no WhatsApp e acompanhe os presentes em tempo real.'],
            ],
            'passos' => [
                'Crie sua conta grátis e informe os dados da festa.',
                'Escolha as cotas (festa, viagem, presente...) e defina os valores.',
                'Compartilhe com os convidados e receba pelo PIX.',
            ],
            'faq' => [
                ['p' => 'A lista de aniversário é gratuita?', 'r' => 'Sim: criar e compartilhar é grátis. A taxa só existe sobre os valores que você efetivamente recebe.'],
                ['p' => 'Dá para fazer uma vaquinha para a festa?', 'r' => 'Dá. Use a "cota livre" ou cotas específicas — os convidados escolhem quanto contribuir.'],
                ['p' => 'Serve para aniversário infantil?', 'r' => 'Serve. Você pode misturar presentes físicos (brinquedos) e cotas em dinheiro no mesmo site.'],
                ['p' => 'Como meus convidados pagam?', 'r' => 'Por PIX, direto no site, sem precisar criar conta.'],
            ],
        ],

        'cha_panela' => [
            'h1'         => 'Lista de chá de panela online grátis',
            'descricao'  => 'Monte sua lista de chá de panela online grátis com utensílios, eletrodomésticos e cotas em PIX. Compartilhe no WhatsApp e organize tudo.',
            'intro'      => 'Crie a lista do chá de panela com os itens que você realmente precisa para a casa nova, marque metas e deixe os convidados escolherem sem repetição.',
            'beneficios' => [
                ['icone' => 'bi-basket', 'titulo' => 'Sem presentes repetidos', 'texto' => 'Cada item tem meta e quantidade — quando esgota, some da lista.'],
                ['icone' => 'bi-cash-coin', 'titulo' => 'Cotas em dinheiro e PIX', 'texto' => 'Prefere o valor em dinheiro? Crie cotas livres para os convidados contribuírem.'],
                ['icone' => 'bi-list-check', 'titulo' => 'Organização fácil', 'texto' => 'Veja quem presenteou o quê e acompanhe o que ainda falta.'],
                ['icone' => 'bi-phone', 'titulo' => 'Compartilhe no WhatsApp', 'texto' => 'Um link bonito para enviar para família e amigos.'],
            ],
            'passos' => [
                'Crie sua conta grátis e informe os dados do chá.',
                'Adicione os utensílios e eletrodomésticos com quantidade e meta.',
                'Compartilhe o link e acompanhe os presentes no painel.',
            ],
            'faq' => [
                ['p' => 'A lista de chá de panela é grátis?', 'r' => 'Sim, criar e compartilhar é grátis. Há apenas uma taxa quando você recebe um presente.'],
                ['p' => 'Posso cadastrar eletrodomésticos e utensílios?', 'r' => 'Posso cadastrar quantos itens quiser, cada um com foto, descrição, valor e meta.'],
                ['p' => 'Como evito presentes repetidos?', 'r' => 'Cada item controla a quantidade. Ao atingir a meta, ele fica marcado como esgotado.'],
                ['p' => 'Dá para receber em dinheiro também?', 'r' => 'Sim, crie cotas livres ou específicas em PIX para quem preferir contribuir com valor.'],
            ],
        ],

        'cha_revelacao' => [
            'h1'         => 'Lista de chá revelação online grátis',
            'descricao'  => 'Organize seu chá revelação com lista online grátis, confirmação de presença e cotas em PIX. Site personalizado para compartilhar com a família.',
            'intro'      => 'Personalize o site do chá revelação, receba as confirmações de presença e organize os presentes do bebê com cotas em dinheiro via PIX.',
            'beneficios' => [
                ['icone' => 'bi-gender-ambiguous', 'titulo' => 'Tema personalizado', 'texto' => 'Escolha cores e um site bonito para o convite e os presentes do bebê.'],
                ['icone' => 'bi-people', 'titulo' => 'RSVP dos convidados', 'texto' => 'Saiba quem vem e quantas pessoas para planejar a festa.'],
                ['icone' => 'bi-gift', 'titulo' => 'Fraldas e enxoval', 'texto' => 'Liste os itens do enxoval ou cotas em dinheiro para o que o bebê precisar.'],
                ['icone' => 'bi-chat-heart', 'titulo' => 'Recados dos convidados', 'texto' => 'Receba mensagens especiais no mural do site.'],
            ],
            'passos' => [
                'Crie sua conta grátis e informe a data do chá revelação.',
                'Adicione itens ou cotas de presente para o bebê.',
                'Compartilhe o link e acompanhe as confirmações.',
            ],
            'faq' => [
                ['p' => 'É grátis criar a lista do chá revelação?', 'r' => 'Sim, é grátis para criar e compartilhar. A taxa só incide sobre os presentes recebidos.'],
                ['p' => 'Dá para fazer RSVP e lista de presentes juntos?', 'r' => 'Sim — o mesmo site tem confirmação de presença, mural de recados e a lista de presentes.'],
                ['p' => 'Posso escolher a cor do sexo do bebê?', 'r' => 'Sim, você personaliza o tema e as cores do site como quiser.'],
                ['p' => 'Como recebo os presentes?', 'r' => 'O convidado presenteia pelo site via PIX e o valor entra no seu saldo para saque.'],
            ],
        ],

        'cha_fraldas' => [
            'h1'         => 'Lista de chá de fraldas online grátis',
            'descricao'  => 'Crie sua lista de chá de fraldas online grátis, com fraldas de vários tamanhos e cotas em PIX. Site com RSVP e recados para os convidados.',
            'intro'      => 'Organize o chá de fraldas sem bagunça: cadastre os tamanhos de fralda e outras necessidades, defina metas e deixe os convidados escolherem online.',
            'beneficios' => [
                ['icone' => 'bi-gift', 'titulo' => 'Fraldas por tamanho', 'texto' => 'Controle quantos pacotes de cada tamanho você quer receber.'],
                ['icone' => 'bi-cash-coin', 'titulo' => 'Ou cotas em dinheiro', 'texto' => 'Quem preferir pode contribuir com uma cota em PIX para o enxoval.'],
                ['icone' => 'bi-check2-circle', 'titulo' => 'Confirmação de presença', 'texto' => 'Saiba quantos convidados vêm e organize tudo com antecedência.'],
                ['icone' => 'bi-phone', 'titulo' => 'Link para o WhatsApp', 'texto' => 'Compartilhe num clique com a família e os amigos.'],
            ],
            'passos' => [
                'Crie sua conta grátis e informe a data do chá.',
                'Adicione as fraldas (por tamanho) e as cotas de dinheiro.',
                'Compartilhe o link e acompanhe os presentes.',
            ],
            'faq' => [
                ['p' => 'A lista de chá de fraldas é grátis?', 'r' => 'Sim. Criar e compartilhar a lista não custa nada; a taxa só existe sobre os presentes recebidos.'],
                ['p' => 'Posso pedir fraldas de vários tamanhos?', 'r' => 'Pode — cada tamanho é um item com a quantidade/meta que você definir.'],
                ['p' => 'O convidado precisa criar conta?', 'r' => 'Não. Ele escolhe a fralda, informa os dados e paga por PIX.'],
                ['p' => 'Dá para receber dinheiro em vez de fralda?', 'r' => 'Sim, crie uma "cota livre" para contribuições em qualquer valor.'],
            ],
        ],

        'quinze_anos' => [
            'h1'         => 'Lista de 15 anos online grátis',
            'descricao'  => 'Crie a lista de presentes da festa de 15 anos online, grátis, com cotas em PIX, RSVP e convite digital. Compartilhe com os convidados.',
            'intro'      => 'Para uma festa de 15 anos inesquecível: convite digital, confirmação de presença e uma lista de presentes com cotas em dinheiro via PIX.',
            'beneficios' => [
                ['icone' => 'bi-cash-coin', 'titulo' => 'Cotas para a festa', 'texto' => 'Ajude a realizar a festa ou uma viagem com cotas e vaquinha via PIX.'],
                ['icone' => 'bi-star', 'titulo' => 'Convite e site personalizados', 'texto' => 'Um site bonito para compartilhar a data, o local e os detalhes da festa.'],
                ['icone' => 'bi-check2-circle', 'titulo' => 'Confirmação de presença', 'texto' => 'Controle quem vem (com acompanhantes) e organize os lugares.'],
                ['icone' => 'bi-images', 'titulo' => 'Galeria e recados', 'texto' => 'Reúna fotos e mensagens dos convidados no site.'],
            ],
            'passos' => [
                'Crie sua conta grátis e informe os dados da festa.',
                'Monte a lista de cotas e personalize o convite.',
                'Compartilhe o link e acompanhe as confirmações.',
            ],
            'faq' => [
                ['p' => 'A lista de 15 anos é gratuita?', 'r' => 'Criar e compartilhar é grátis. A taxa só é aplicada sobre os valores recebidos.'],
                ['p' => 'Tem convite digital?', 'r' => 'Sim — o site do evento funciona como convite, com data, local e mapa.'],
                ['p' => 'Posso receber em dinheiro?', 'r' => 'Sim, com cotas em PIX (festa, viagem, presente) e uma cota livre.'],
                ['p' => 'Como funciona o RSVP?', 'r' => 'O convidado confirma presença no site e você acompanha e aprova no painel.'],
            ],
        ],

        'formatura' => [
            'h1'         => 'Lista de formatura online grátis',
            'descricao'  => 'Crie uma lista de formatura online grátis com vaquinha e cotas em PIX para a festa, viagem ou colação. Confirmação de presença incluída.',
            'intro'      => 'Reunir a turma é mais fácil com uma lista de formatura online: vaquinha para a festa e a viagem, cotas em PIX e confirmação de presença.',
            'beneficios' => [
                ['icone' => 'bi-cash-stack', 'titulo' => 'Vaquinha da turma', 'texto' => 'Cada formando contribui com uma cota; acompanhe o total arrecadado.'],
                ['icone' => 'bi-people', 'titulo' => 'Confirmação de presença', 'texto' => 'Saiba quem vai à festa e organize convites e lugares.'],
                ['icone' => 'bi-camera', 'titulo' => 'Galeria da turma', 'texto' => 'Compartilhe fotos dos melhores momentos no site.'],
                ['icone' => 'bi-share', 'titulo' => 'Compartilhamento fácil', 'texto' => 'Mande o link no grupo da turma em segundos.'],
            ],
            'passos' => [
                'Crie sua conta grátis e informe a data da formatura.',
                'Crie as cotas (festa, viagem, presente da turma).',
                'Compartilhe com a turma e acompanhe as contribuições.',
            ],
            'faq' => [
                ['p' => 'A lista de formatura é grátis?', 'r' => 'Sim, criar e compartilhar é grátis; a taxa só incide sobre o que você recebe.'],
                ['p' => 'Dá para fazer vaquinha com a turma?', 'r' => 'Dá. Use cotas em PIX e acompanhe o valor total arrecadado no painel.'],
                ['p' => 'Serve para colação e festa?', 'r' => 'Serve para organizar presentes, custos da festa e a viagem de formatura.'],
                ['p' => 'Como os convidados pagam?', 'r' => 'Por PIX, direto no site, sem precisar de conta.'],
            ],
        ],

        'noivado' => [
            'h1'         => 'Lista de noivado online grátis',
            'descricao'  => 'Crie sua lista de noivado online grátis com cotas em PIX para a casa nova e o casamento. Site personalizado e confirmação de presença.',
            'intro'      => 'Celebre o noivado e já comece a construir o lar: uma lista online com cotas em dinheiro para a casa nova, a lua de mel ou o casamento.',
            'beneficios' => [
                ['icone' => 'bi-heart', 'titulo' => 'Comece o lar junto', 'texto' => 'Cotas para móveis, viagens ou qualquer sonho do casal.'],
                ['icone' => 'bi-cash-coin', 'titulo' => 'Receba em PIX', 'texto' => 'O valor dos presentes cai direto no seu saldo para saque.'],
                ['icone' => 'bi-check2-circle', 'titulo' => 'Confirmação de presença', 'texto' => 'Saiba quem vem à festa de noivado e organize tudo.'],
                ['icone' => 'bi-palette', 'titulo' => 'Site do casal', 'texto' => 'Personalize o tema, as cores e a capa do site.'],
            ],
            'passos' => [
                'Crie sua conta grátis e informe os dados do noivado.',
                'Monte a lista de cotas para o casal.',
                'Compartilhe o link com convidados e família.',
            ],
            'faq' => [
                ['p' => 'A lista de noivado é gratuita?', 'r' => 'Sim, criar e compartilhar é grátis; há taxa apenas sobre os presentes recebidos.'],
                ['p' => 'Posso usar a mesma lista para o casamento?', 'r' => 'Você pode manter o site e atualizar as cotas conforme a nova fase se aproxima.'],
                ['p' => 'Dá para receber dinheiro?', 'r' => 'Sim, com cotas em PIX e uma cota livre para qualquer valor.'],
                ['p' => 'Os convidados precisam de conta?', 'r' => 'Não. O convidado presenteia pelo site sem criar conta.'],
            ],
        ],
    ];

    /**
     * Dados completos da landing page (tipo + conteúdo), ou null se o slug não existir.
     *
     * @return array<string, mixed>|null
     */
    public static function porSlug(string $slug): ?array
    {
        $tipo = TipoEventoService::porSlug($slug);

        if ($tipo === null) {
            return null;
        }

        $conteudo = self::CONTEUDO[$tipo['chave']] ?? self::conteudoPadrao($tipo);

        return $tipo + $conteudo;
    }

    /**
     * Todas as landing pages (para o hub e o sitemap).
     *
     * @return list<array<string, mixed>>
     */
    public static function paginas(): array
    {
        $paginas = [];

        foreach (TipoEventoService::todos() as $chave => $tipo) {
            $conteudo = self::CONTEUDO[$chave] ?? self::conteudoPadrao($tipo);
            $paginas[] = ['chave' => $chave] + $tipo + $conteudo;
        }

        return $paginas;
    }

    /**
     * Modelo de conteúdo para tipos sem texto dedicado.
     *
     * @param array{slug:string, rotulo:string, icone:string} $tipo
     * @return array{h1:string, descricao:string, intro:string, beneficios:list<array{icone:string,titulo:string,texto:string}>, passos:list<string>, faq:list<array{p:string,r:string}>}
     */
    private static function conteudoPadrao(array $tipo): array
    {
        $rotulo = $tipo['rotulo'];

        return [
            'h1'         => 'Lista de presentes para ' . $rotulo . ' online e grátis',
            'descricao'  => 'Crie uma lista de presentes para ' . $rotulo . ' online e grátis, com cotas em dinheiro via PIX, convite e confirmação de presença.',
            'intro'      => 'Crie a lista de ' . $rotulo . ' online em poucos minutos, compartilhe o site com os convidados e receba os presentes em dinheiro via PIX.',
            'beneficios' => [
                ['icone' => 'bi-cash-coin', 'titulo' => 'Receba em PIX', 'texto' => 'Cotas em dinheiro caem direto no seu saldo para saque, sem produto no meio.'],
                ['icone' => 'bi-palette', 'titulo' => 'Site personalizado', 'texto' => 'Escolha tema, cores e capa para combinar com o seu evento.'],
                ['icone' => 'bi-check2-circle', 'titulo' => 'Confirmação de presença', 'texto' => 'Saiba quem vem e organize tudo com a lista de convidados.'],
                ['icone' => 'bi-chat-heart', 'titulo' => 'Mural de recados', 'texto' => 'Receba mensagens carinhosas dos convidados no site.'],
            ],
            'passos' => [
                'Crie sua conta grátis e informe os dados do evento.',
                'Monte a lista de cotas e personalize o site.',
                'Compartilhe o link e receba pelo PIX.',
            ],
            'faq' => [
                ['p' => 'Fazer a lista de ' . $rotulo . ' é grátis?', 'r' => 'Sim, criar e compartilhar é 100% grátis. A plataforma só cobra uma taxa quando você recebe um presente.'],
                ['p' => 'Os convidados precisam de conta para presentear?', 'r' => 'Não. O convidado escolhe a cota e paga por PIX, sem criar conta.'],
                ['p' => 'Posso receber um valor livre em dinheiro?', 'r' => 'Sim, crie uma cota livre para quem quiser contribuir com qualquer valor.'],
                ['p' => 'O valor cai na minha conta?', 'r' => 'O pagamento entra no seu saldo na plataforma e você solicita o saque via PIX.'],
            ],
        ];
    }
}
