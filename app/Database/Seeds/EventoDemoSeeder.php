<?php

namespace App\Database\Seeds;

use App\Entities\Evento;
use App\Models\EventoModel;
use App\Models\MuralRecadoModel;
use App\Models\PresenteEventoModel;
use App\Models\UsuarioModel;
use CodeIgniter\Database\Seeder;

/**
 * Evento de demonstração (público) com presentes e recado, vinculado ao
 * organizador demo. Útil para testar a página pública /e/{slug}.
 *
 * Executar: php spark db:seed EventoDemoSeeder
 */
class EventoDemoSeeder extends Seeder
{
    public function run()
    {
        $organizador = (new UsuarioModel())->buscarPorEmail('organizador@minhalistavip.com.br');

        if ($organizador === null) {
            return;
        }

        $eventos = new EventoModel();

        if ($eventos->buscarPorSlug('casamento-ana-e-joao') !== null) {
            return;
        }

        $agora = date('Y-m-d H:i:s');

        /** @var Evento $evento */
        $evento = new Evento([
            'usuario_id'       => $organizador->id,
            'slug'             => 'casamento-ana-e-joao',
            'titulo'           => 'Casamento de Ana & João',
            'subtitulo'        => 'A nossa lista de presentes',
            'tipo_evento'      => 'casamento',
            'descricao'        => 'Obrigado por fazer parte desse momento tão especial!',
            'mensagem_convite' => 'Sua presença é o nosso maior presente. Se quiser nos mimar, escolha uma cota da nossa lista!',
            'data_evento'      => '2026-12-12',
            'horario'          => '19:00:00',
            'local_nome'       => 'Espaço Jardim das Flores',
            'local_endereco'   => 'Av. das Acácias, 1000 - São Paulo/SP',
            'tema'             => 'casamento',
            'cor_primaria'     => '#b8860b',
            'cor_secundaria'   => '#d4af37',
            'quem_paga_taxa'   => 'convidado',
            'percentual_taxa'  => 10.00,
            'meta_valor'       => 5000.00,
            'permite_rsvp'     => 1,
            'permite_recados'  => 1,
            'exibir_valores'   => 1,
            'status'           => 'publicado',
            'publicado_em'     => $agora,
        ]);

        $eventos->insert($evento);
        $eventoId = (int) $eventos->getInsertID();

        $presentes = new PresenteEventoModel();
        $lista = [
            ['nome' => 'Cota de Lua de Mel', 'descricao' => 'Ajude a pagar a nossa primeira viagem a dois.', 'valor' => 300.00, 'quantidade_meta' => 10],
            ['nome' => 'Jogo de Panelas', 'descricao' => 'Um item essencial para a nossa cozinha nova.', 'valor' => 250.00, 'quantidade_meta' => 1],
            ['nome' => 'Cota do Aluguel', 'descricao' => 'Nos ajudará no primeiro mês do nosso lar.', 'valor' => 200.00, 'quantidade_meta' => 5],
            ['nome' => 'Kit de Toalhas', 'descricao' => 'Toalhas de banho e rosto.', 'valor' => 150.00, 'quantidade_meta' => 2],
            ['nome' => 'Cota do Churrasco', 'descricao' => 'Para o churrasco com a família e amigos.', 'valor' => 100.00, 'quantidade_meta' => 20],
            ['nome' => 'Cota Livre em Dinheiro', 'descricao' => 'Contribua com qualquer valor.', 'valor' => 50.00, 'quantidade_meta' => 50],
        ];

        foreach ($lista as $ordem => $item) {
            $presentes->insert([
                'evento_id'        => $eventoId,
                'nome'             => $item['nome'],
                'descricao'        => $item['descricao'],
                'tipo'             => 'ficticio',
                'valor'            => $item['valor'],
                'quantidade_meta'  => $item['quantidade_meta'],
                'quantidade_vendida' => 0,
                'ativo'            => 1,
                'ordem'            => $ordem + 1,
            ]);
        }

        (new MuralRecadoModel())->insert([
            'evento_id'    => $eventoId,
            'nome_autor'   => 'Maria Clara',
            'mensagem'     => 'Que felicidade! Desejo toda a sorte do mundo para vocês. Nos vemos na festa!',
            'status'       => 'publicado',
            'publicado_em' => $agora,
        ]);
    }
}
