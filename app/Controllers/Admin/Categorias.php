<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoriaModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Categorias do catálogo global.
 */
class Categorias extends BaseController
{
    protected CategoriaModel $categorias;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->categorias = new CategoriaModel();
    }

    public function index()
    {
        return $this->render('admin/categorias/index', [
            'titulo'     => 'Categorias',
            'categorias' => $this->categorias->todasComContagem(),
        ]);
    }

    public function criar()
    {
        $dados = $this->dadosDoFormulario();

        if ($dados['nome'] === '') {
            return redirect()->to(site_url('admin/categorias'))->with('erro', 'Informe o nome da categoria.');
        }

        $dados['slug'] = $this->gerarSlug($dados['slug'] !== '' ? $dados['slug'] : $dados['nome']);
        $this->categorias->insert($dados);

        return redirect()->to(site_url('admin/categorias'))->with('sucesso', 'Categoria criada.');
    }

    public function atualizar($id = null)
    {
        $id = (int) $id;
        $this->buscar($id);

        $dados = $this->dadosDoFormulario();

        if ($dados['nome'] === '') {
            return redirect()->to(site_url('admin/categorias'))->with('erro', 'Informe o nome da categoria.');
        }

        $dados['slug'] = $this->gerarSlug($dados['slug'] !== '' ? $dados['slug'] : $dados['nome'], $id);
        $this->categorias->update($id, $dados);

        return redirect()->to(site_url('admin/categorias'))->with('sucesso', 'Categoria atualizada.');
    }

    public function alternar($id = null)
    {
        $categoria = $this->buscar((int) $id);

        $this->categorias->update((int) $id, ['ativo' => empty($categoria['ativo']) ? 1 : 0]);

        return redirect()->to(site_url('admin/categorias'))->with('sucesso', 'Disponibilidade da categoria atualizada.');
    }

    public function excluir($id = null)
    {
        $this->buscar((int) $id);
        $this->categorias->delete((int) $id);

        return redirect()->to(site_url('admin/categorias'))->with('sucesso', 'Categoria removida. Os itens ficaram sem categoria.');
    }

    /**
     * @return array<string, mixed>
     */
    private function buscar(int $id): array
    {
        $categoria = $this->categorias->find($id);

        if ($categoria === null) {
            throw PageNotFoundException::forPageNotFound('Categoria não encontrada.');
        }

        return $categoria;
    }

    /**
     * @return array<string, mixed>
     */
    private function dadosDoFormulario(): array
    {
        $texto = fn (string $campo): string => trim((string) $this->request->getPost($campo));

        return [
            'nome'  => $texto('nome'),
            'slug'  => $texto('slug'),
            'icone' => $texto('icone') ?: null,
            'ordem' => (int) $this->request->getPost('ordem'),
            'ativo' => $this->request->getPost('ativo') ? 1 : 0,
        ];
    }

    private function gerarSlug(string $base, ?int $ignorarId = null): string
    {
        helper('url');
        $slug = url_title($base, '-', true) ?: 'categoria';
        $original = $slug;
        $sufixo = 2;

        while ($this->slugEmUso($slug, $ignorarId)) {
            $slug = $original . '-' . $sufixo;
            $sufixo++;
        }

        return $slug;
    }

    private function slugEmUso(string $slug, ?int $ignorarId): bool
    {
        $builder = $this->categorias->where('slug', $slug);

        if ($ignorarId !== null) {
            $builder->where('id !=', $ignorarId);
        }

        return $builder->countAllResults() > 0;
    }
}
