<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Catálogo GERAL de presentes (mantido pelo SuperAdmin) e usado pelos
 * organizadores para clonar itens para os seus eventos.
 *
 * Executar: php spark db:seed CatalogoPresentesSeeder
 */
class CatalogoPresentesSeeder extends Seeder
{
    public function run()
    {
        $categorias = $this->db->table('categorias')->get()->getResultArray();

        if ($categorias === []) {
            return;
        }

        $idPorSlug = array_column($categorias, 'id', 'slug');
        $agora     = date('Y-m-d H:i:s');

        $itens = [
            ['nome' => 'Cota Lua de Mel', 'slug' => 'lua-de-mel', 'descricao' => 'Ajude o casal a realizar a viagem dos sonhos.', 'valor' => 300.00, 'tipo' => 'ficticio'],
            ['nome' => 'Cota do Aluguel', 'slug' => 'cotas-em-dinheiro', 'descricao' => 'Contribua para o primeiro mês do novo lar.', 'valor' => 200.00, 'tipo' => 'ficticio'],
            ['nome' => 'Cota Livre', 'slug' => 'cotas-em-dinheiro', 'descricao' => 'Contribua com o valor que desejar.', 'valor' => 50.00, 'tipo' => 'ficticio'],
            ['nome' => 'Cota do Churrasco', 'slug' => 'cotas-em-dinheiro', 'descricao' => 'Ajude a bancar a festa.', 'valor' => 100.00, 'tipo' => 'ficticio'],
            ['nome' => 'Jogo de Panelas Antiaderente', 'slug' => 'cozinha', 'descricao' => 'Conjunto com 5 peças para a cozinha nova.', 'valor' => 450.00, 'tipo' => 'ficticio'],
            ['nome' => 'Jogo de Talheres Inox', 'slug' => 'cozinha', 'descricao' => 'Conjunto para 6 pessoas.', 'valor' => 220.00, 'tipo' => 'ficticio'],
            ['nome' => 'Air Fryer', 'slug' => 'eletrodomesticos', 'descricao' => 'Praticidade no dia a dia.', 'valor' => 600.00, 'tipo' => 'ficticio'],
            ['nome' => 'Liquidificador', 'slug' => 'eletrodomesticos', 'descricao' => 'Potência para o café da manhã.', 'valor' => 280.00, 'tipo' => 'ficticio'],
            ['nome' => 'Jogo de Toalhas', 'slug' => 'banheiro', 'descricao' => 'Toalhas de banho e rosto.', 'valor' => 150.00, 'tipo' => 'ficticio'],
            ['nome' => 'Jogo de Lençóis', 'slug' => 'quarto', 'descricao' => 'Lençóis de algodão, cama queen.', 'valor' => 320.00, 'tipo' => 'ficticio'],
            ['nome' => 'Edredom', 'slug' => 'quarto', 'descricao' => 'Edredom queen para o inverno.', 'valor' => 380.00, 'tipo' => 'ficticio'],
            ['nome' => 'Manta para Sofá', 'slug' => 'sala-de-estar', 'descricao' => 'Conforto extra para a sala.', 'valor' => 180.00, 'tipo' => 'ficticio'],
            ['nome' => 'Kit de Organizadores', 'slug' => 'sala-de-estar', 'descricao' => 'Caixas organizadoras para o lar.', 'valor' => 130.00, 'tipo' => 'ficticio'],
            ['nome' => 'Body de Bebê (kit 3 peças)', 'slug' => 'bebe', 'descricao' => 'Roupinhas tamanho RN e P.', 'valor' => 90.00, 'tipo' => 'ficticio'],
            ['nome' => 'Kit de Mamadeiras', 'slug' => 'bebe', 'descricao' => 'Mamadeiras anticólica.', 'valor' => 160.00, 'tipo' => 'ficticio'],
            ['nome' => 'Fraldas Descartáveis (pacote)', 'slug' => 'bebe', 'descricao' => 'Pacote tamanho M.', 'valor' => 70.00, 'tipo' => 'ficticio'],
            ['nome' => 'Cafeteira Italiana (presente real)', 'slug' => 'cozinha', 'descricao' => 'Compra via loja parceira.', 'valor' => 210.00, 'tipo' => 'real', 'link' => 'https://example.com/parceiro/cafeteira'],
            ['nome' => 'Aspirador Robô (presente real)', 'slug' => 'eletrodomesticos', 'descricao' => 'Compra via loja parceira.', 'valor' => 1200.00, 'tipo' => 'real', 'link' => 'https://example.com/parceiro/aspirador'],
        ];

        $ordem = 0;

        foreach ($itens as $item) {
            $nome  = $item['nome'];
            $slug  = $item['slug'];
            $valor = $item['valor'];

            $existe = $this->db->table('catalogo_presentes')->where('nome', $nome)->countAllResults();

            if ($existe > 0) {
                continue;
            }

            $ordem++;

            $this->db->table('catalogo_presentes')->insert([
                'categoria_id'   => $idPorSlug[$slug] ?? null,
                'nome'           => $nome,
                'descricao'      => $item['descricao'],
                'tipo'           => $item['tipo'],
                'valor_sugerido' => $valor,
                'link_afiliado'  => $item['link'] ?? null,
                'ativo'          => 1,
                'destaque'       => 0,
                'ordem'          => $ordem,
                'criado_em'      => $agora,
                'atualizado_em'  => $agora,
            ]);
        }
    }
}
