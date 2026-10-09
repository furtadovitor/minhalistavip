<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DemoListaModel;
use App\Services\UploadService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Listas de exemplo (demonstração) exibidas na Home e em /demo/{slug}.
 *
 * O SuperAdmin cria, edita, publica/oculta, reordena e remove as demos, além de
 * poder restaurar os 3 exemplos padrão da plataforma.
 */
class Demos extends BaseController
{
    protected DemoListaModel $demos;

    protected UploadService $upload;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->demos  = new DemoListaModel();
        $this->upload = new UploadService();
    }

    public function index()
    {
        return $this->render('admin/demos/index', [
            'titulo' => 'Listas de exemplo',
            'demos'  => $this->demos->todas(),
        ]);
    }

    public function novo()
    {
        return $this->render('admin/demos/form', [
            'titulo'       => 'Nova lista de exemplo',
            'demo'         => null,
            'proximaOrdem' => $this->demos->proximaOrdem(),
        ]);
    }

    public function criar()
    {
        $dados = $this->dadosDoFormulario();

        if ($dados['titulo'] === '') {
            return redirect()->back()->withInput()->with('erro', 'Informe o título da lista.');
        }

        $dados['slug'] = $this->gerarSlug($dados['slug'] !== '' ? $dados['slug'] : $dados['titulo']);

        [$dados['capa'], $erroCapa] = $this->resolverCapa(null);

        if ($this->demos->insert($dados) === false) {
            return redirect()->back()->withInput()->with('erros', $this->demos->errors());
        }

        return redirect()->to(site_url('admin/demos'))
            ->with($erroCapa === null ? 'sucesso' : 'erro', $erroCapa ?? 'Lista de exemplo criada.');
    }

    public function editar($id = null)
    {
        return $this->render('admin/demos/form', [
            'titulo'       => 'Editar lista de exemplo',
            'demo'         => $this->buscar((int) $id),
            'proximaOrdem' => 0,
        ]);
    }

    public function atualizar($id = null)
    {
        $id   = (int) $id;
        $demo = $this->buscar($id);

        $dados = $this->dadosDoFormulario();

        if ($dados['titulo'] === '') {
            return redirect()->back()->withInput()->with('erro', 'Informe o título da lista.');
        }

        $dados['slug'] = $this->gerarSlug($dados['slug'] !== '' ? $dados['slug'] : $dados['titulo'], $id);

        [$dados['capa'], $erroCapa] = $this->resolverCapa($demo['capa'] ?? null);

        if ($this->demos->update($id, $dados) === false) {
            return redirect()->back()->withInput()->with('erros', $this->demos->errors());
        }

        return redirect()->to(site_url('admin/demos'))
            ->with($erroCapa === null ? 'sucesso' : 'erro', $erroCapa ?? 'Lista de exemplo atualizada.');
    }

    public function alternar($id = null)
    {
        $demo = $this->buscar((int) $id);

        $this->demos->update((int) $id, ['ativo' => empty($demo['ativo']) ? 1 : 0]);

        return redirect()->to(site_url('admin/demos'))->with('sucesso', 'Exibição da lista atualizada.');
    }

    public function excluir($id = null)
    {
        $demo = $this->buscar((int) $id);

        $this->demos->delete((int) $id);
        $this->upload->apagar($demo['capa'] ?? null, 'demos');

        return redirect()->to(site_url('admin/demos'))->with('sucesso', 'Lista de exemplo removida.');
    }

    /**
     * Recria/atualiza os 3 exemplos padrão da plataforma pelo slug.
     */
    public function restaurar()
    {
        $total = $this->demos->restaurarPadroes();

        return redirect()->to(site_url('admin/demos'))
            ->with('sucesso', $total . ' exemplo(s) padrão restaurado(s).');
    }

    /**
     * @return array<string, mixed>
     */
    private function buscar(int $id): array
    {
        $demo = $this->demos->buscarPorId($id);

        if ($demo === null) {
            throw PageNotFoundException::forPageNotFound('Lista de exemplo não encontrada.');
        }

        return $demo;
    }

    /**
     * Lê os campos do formulário (incluindo as linhas dinâmicas).
     *
     * @return array<string, mixed>
     */
    private function dadosDoFormulario(): array
    {
        $texto = fn (string $campo): string => trim((string) $this->request->getPost($campo));
        $cor   = static fn (string $valor, string $padrao): string => preg_match('/^#[0-9A-Fa-f]{6}$/', $valor) ? $valor : $padrao;

        return [
            'slug'             => $texto('slug'),
            'titulo'           => $texto('titulo'),
            'tipo'             => $texto('tipo') ?: 'Evento',
            'icone'            => $texto('icone') ?: null,
            'badge'            => $texto('badge') ?: 'primary',
            'data_texto'       => $texto('data_texto') ?: null,
            'local'            => $texto('local') ?: null,
            'resumo'           => $texto('resumo') ?: null,
            'descricao'        => $texto('descricao') ?: null,
            'mensagem_convite' => $texto('mensagem_convite') ?: null,
            'cor_primaria'     => $cor($texto('cor_primaria'), '#722ED4'),
            'cor_secundaria'   => $cor($texto('cor_secundaria'), '#7C3AED'),
            'tema'             => $texto('tema') ?: 'classico',
            'escuro'           => $this->request->getPost('escuro') ? 1 : 0,
            'presentes'        => $texto('presentes') === '' ? null : max(0, (int) $this->request->getPost('presentes')),
            'itens'            => DemoListaModel::codificar($this->linhasItens()),
            'recados'          => DemoListaModel::codificar($this->linhasRecados()),
            'galeria'          => DemoListaModel::codificar($this->linhasGaleria()),
            'ativo'            => $this->request->getPost('ativo') ? 1 : 0,
            'ordem'            => (int) $this->request->getPost('ordem'),
        ];
    }

    /**
     * Resolve a capa: upload novo, URL digitada ou remoção.
     *
     * @return array{0: string|null, 1: string|null} [capa, erro]
     */
    private function resolverCapa(?string $capaAtual): array
    {
        $capa = $this->normalizarCapa(trim((string) $this->request->getPost('capa')));

        if ($this->request->getPost('remover_capa')) {
            $this->upload->apagar($capaAtual, 'demos');

            return [null, null];
        }

        return $this->upload->imagem($this->request->getFile('capa_arquivo'), 'demos', $capa);
    }

    /**
     * A capa digitada só pode ser uma URL http(s) ou um arquivo já salvo em
     * uploads/demos/. Nunca um caminho arbitrário do sistema (path traversal).
     */
    private function normalizarCapa(string $capa): ?string
    {
        if ($capa === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $capa) === 1) {
            return filter_var($capa, FILTER_VALIDATE_URL) !== false ? $capa : null;
        }

        if (preg_match('#^uploads/demos/[A-Za-z0-9._-]+$#', $capa) === 1) {
            return $capa;
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function linhasItens(): array
    {
        $nomes     = (array) $this->request->getPost('itens_nome');
        $descricoes = (array) $this->request->getPost('itens_descricao');
        $valores   = (array) $this->request->getPost('itens_valor');
        $metas     = (array) $this->request->getPost('itens_meta');
        $vendidas  = (array) $this->request->getPost('itens_vendida');

        $itens = [];

        foreach ($nomes as $i => $nome) {
            $nome = trim((string) $nome);

            if ($nome === '') {
                continue;
            }

            $itens[] = [
                'nome'      => $nome,
                'descricao' => trim((string) ($descricoes[$i] ?? '')) ?: null,
                'valor'     => (float) str_replace(',', '.', (string) ($valores[$i] ?? 0)),
                'meta'      => max(1, (int) ($metas[$i] ?? 1)),
                'vendida'   => max(0, (int) ($vendidas[$i] ?? 0)),
            ];
        }

        return $itens;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function linhasRecados(): array
    {
        $autores   = (array) $this->request->getPost('recados_autor');
        $mensagens = (array) $this->request->getPost('recados_mensagem');

        $recados = [];

        foreach ($mensagens as $i => $mensagem) {
            $mensagem = trim((string) $mensagem);

            if ($mensagem === '') {
                continue;
            }

            $recados[] = [
                'autor'    => trim((string) ($autores[$i] ?? '')) ?: 'Convidado',
                'mensagem' => $mensagem,
            ];
        }

        return $recados;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function linhasGaleria(): array
    {
        $imagens  = (array) $this->request->getPost('galeria_imagem');
        $legendas = (array) $this->request->getPost('galeria_legenda');

        $galeria = [];

        foreach ($imagens as $i => $imagem) {
            $imagem = trim((string) $imagem);

            if ($imagem === '') {
                continue;
            }

            $galeria[] = [
                'imagem'  => $imagem,
                'legenda' => trim((string) ($legendas[$i] ?? '')) ?: null,
            ];
        }

        return $galeria;
    }

    private function gerarSlug(string $base, ?int $ignorarId = null): string
    {
        helper('url');
        $slug     = url_title($base, '-', true) ?: 'exemplo';
        $original = $slug;
        $sufixo   = 2;

        while ($this->slugEmUso($slug, $ignorarId)) {
            $slug = $original . '-' . $sufixo;
            $sufixo++;
        }

        return $slug;
    }

    private function slugEmUso(string $slug, ?int $ignorarId): bool
    {
        $builder = $this->demos->where('slug', $slug);

        if ($ignorarId !== null) {
            $builder->where('id !=', $ignorarId);
        }

        return $builder->countAllResults() > 0;
    }
}
