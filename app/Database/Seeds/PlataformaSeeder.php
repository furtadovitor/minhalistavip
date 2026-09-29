<?php

namespace App\Database\Seeds;

use App\Entities\Usuario;
use App\Models\UsuarioModel;
use CodeIgniter\Database\Seeder;

/**
 * Dados iniciais da plataforma: configurações, categorias, planos e
 * usuários de teste (SuperAdmin e Organizador demo).
 *
 * Executar: php spark db:seed PlataformaSeeder
 */
class PlataformaSeeder extends Seeder
{
    public function run()
    {
        $agora = date('Y-m-d H:i:s');

        $this->seedConfiguracoes($agora);
        $this->seedCategorias($agora);
        $this->seedPlanos($agora);
        $this->seedUsuarios();
    }

    private function seedConfiguracoes(string $agora): void
    {
        $itens = [
            ['chave' => 'percentual_taxa_padrao', 'valor' => '10.00', 'grupo' => 'financeiro', 'descricao' => 'Comissão padrão da plataforma sobre cada presente (%)'],
            ['chave' => 'nome_plataforma', 'valor' => 'Minha Lista VIP', 'grupo' => 'geral', 'descricao' => 'Nome exibido na plataforma'],
            ['chave' => 'moeda', 'valor' => 'BRL', 'grupo' => 'financeiro', 'descricao' => 'Moeda base'],
            ['chave' => 'email_suporte', 'valor' => 'suporte@minhalistavip.com.br', 'grupo' => 'geral', 'descricao' => 'E-mail de suporte'],
            ['chave' => 'saque_valor_minimo', 'valor' => '50.00', 'grupo' => 'financeiro', 'descricao' => 'Valor mínimo para solicitar saque'],
            ['chave' => 'pix_chave_plataforma', 'valor' => 'pagamentos@minhalistavip.com.br', 'grupo' => 'pix', 'descricao' => 'Chave PIX que recebe os pagamentos da plataforma'],
            ['chave' => 'pix_nome_plataforma', 'valor' => 'Minha Lista VIP', 'grupo' => 'pix', 'descricao' => 'Nome do recebedor exibido no PIX'],
            ['chave' => 'pix_cidade_plataforma', 'valor' => 'SAO PAULO', 'grupo' => 'pix', 'descricao' => 'Cidade do recebedor exibida no PIX'],
            ['chave' => 'pix_expiracao_minutos', 'valor' => '30', 'grupo' => 'pix', 'descricao' => 'Validade da cobrança PIX em minutos'],
            ['chave' => 'pix_webhook_token', 'valor' => 'sandbox-token', 'grupo' => 'pix', 'descricao' => 'Token compartilhado para autenticar o webhook PIX'],
        ];

        foreach ($itens as $item) {
            $existe = $this->db->table('configuracoes')->where('chave', $item['chave'])->countAllResults();

            if ($existe === 0) {
                $this->db->table('configuracoes')->insert($item + [
                    'criado_em'     => $agora,
                    'atualizado_em' => $agora,
                ]);
            }
        }
    }

    private function seedCategorias(string $agora): void
    {
        $categorias = [
            ['nome' => 'Cozinha', 'slug' => 'cozinha', 'icone' => 'bi-cup-hot', 'ordem' => 1],
            ['nome' => 'Quarto', 'slug' => 'quarto', 'icone' => 'bi-bed', 'ordem' => 2],
            ['nome' => 'Sala de Estar', 'slug' => 'sala-de-estar', 'icone' => 'bi-lamp', 'ordem' => 3],
            ['nome' => 'Banheiro', 'slug' => 'banheiro', 'icone' => 'bi-droplet', 'ordem' => 4],
            ['nome' => 'Eletrodomésticos', 'slug' => 'eletrodomesticos', 'icone' => 'bi-plugin', 'ordem' => 5],
            ['nome' => 'Cotas em Dinheiro', 'slug' => 'cotas-em-dinheiro', 'icone' => 'bi-cash-coin', 'ordem' => 6],
            ['nome' => 'Lua de Mel', 'slug' => 'lua-de-mel', 'icone' => 'bi-airplane', 'ordem' => 7],
            ['nome' => 'Bebê', 'slug' => 'bebe', 'icone' => 'bi-balloon', 'ordem' => 8],
        ];

        foreach ($categorias as $categoria) {
            $existe = $this->db->table('categorias')->where('slug', $categoria['slug'])->countAllResults();

            if ($existe === 0) {
                $this->db->table('categorias')->insert($categoria + [
                    'ativo'         => 1,
                    'criado_em'     => $agora,
                    'atualizado_em' => $agora,
                ]);
            }
        }
    }

    private function seedPlanos(string $agora): void
    {
        $planos = [
            [
                'nome'            => 'Gratuito',
                'slug'            => 'gratuito',
                'descricao'       => 'Para começar: um evento e taxa padrão da plataforma.',
                'preco'           => 0.00,
                'periodo'         => 'mensal',
                'percentual_taxa' => null,
                'limite_eventos'  => 1,
                'recursos'        => json_encode(['rsvp' => true, 'recados' => true]),
                'ordem'           => 1,
            ],
            [
                'nome'            => 'Premium',
                'slug'            => 'premium',
                'descricao'       => 'Eventos ilimitados e taxa reduzida.',
                'preco'           => 29.90,
                'periodo'         => 'mensal',
                'percentual_taxa' => 7.00,
                'limite_eventos'  => null,
                'recursos'        => json_encode(['rsvp' => true, 'recados' => true, 'destaque' => true]),
                'ordem'           => 2,
            ],
        ];

        foreach ($planos as $plano) {
            $existe = $this->db->table('planos')->where('slug', $plano['slug'])->countAllResults();

            if ($existe === 0) {
                $this->db->table('planos')->insert($plano + [
                    'ativo'         => 1,
                    'criado_em'     => $agora,
                    'atualizado_em' => $agora,
                ]);
            }
        }
    }

    private function seedUsuarios(): void
    {
        $model = new UsuarioModel();

        $usuarios = [
            ['nome' => 'Super Admin', 'email' => 'admin@minhalistavip.com.br', 'senha' => 'Admin@123', 'nivel' => 'superadmin'],
            ['nome' => 'Organizador Demo', 'email' => 'organizador@minhalistavip.com.br', 'senha' => 'Demo@123', 'nivel' => 'organizador'],
        ];

        foreach ($usuarios as $dados) {
            if ($model->buscarPorEmail($dados['email']) !== null) {
                continue;
            }

            /** @var Usuario $usuario */
            $usuario = new Usuario([
                'nome'   => $dados['nome'],
                'email'  => $dados['email'],
                'nivel'  => $dados['nivel'],
                'status' => 'ativo',
            ]);
            $usuario->senha = $dados['senha'];

            $model->insert($usuario);
        }
    }
}
