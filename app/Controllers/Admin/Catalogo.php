<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CatalogoPresenteModel;
use App\Models\CategoriaModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Catálogo global de presentes (mantido pelo SuperAdmin).
 * Os organizadores clonam estes itens para o próprio evento.
 */
class Catalogo extends BaseController
{
    protected CatalogoPresenteModel $itens;

    protected CategoriaModel $categorias;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->itens      = new CatalogoPresenteModel();
        $this->categorias = new CategoriaModel();
    }

    public function index()
    {
        $filtros = [
            'categoria_id' => $this->request->getGet('categoria') ?: null,
            'busca'        => $this->request->getGet('busca') ?: null,
            'ativo'        => $this->request->getGet('ativo'),
        ];

        return $this->render('admin/catalogo/index', [
            'titulo'     => 'Catálogo global',
            'itens'      => $this->itens->listarAdmin($filtros),
            'categorias' => $this->categorias->ativas(),
            'filtros'    => $filtros,
        ]);
    }

    public function novo()
    {
        return $this->render('admin/catalogo/form', [
            'titulo'     => 'Novo item do catálogo',
            'item'       => null,
            'categorias' => $this->categorias->ativas(),
        ]);
    }

    public function criar()
    {
        if ($this->itens->insert($this->dadosDoFormulario()) === false) {
            return redirect()->back()->withInput()->with('erros', $this->itens->errors());
        }

        return redirect()->to(site_url('admin/catalogo'))->with('sucesso', 'Item adicionado ao catálogo.');
    }

    public function editar($id = null)
    {
        return $this->render('admin/catalogo/form', [
            'titulo'     => 'Editar item do catálogo',
            'item'       => $this->buscarItem((int) $id),
            'categorias' => $this->categorias->ativas(),
        ]);
    }

    public function atualizar($id = null)
    {
        $id = (int) $id;
        $this->buscarItem($id);

        if ($this->itens->update($id, $this->dadosDoFormulario()) === false) {
            return redirect()->back()->withInput()->with('erros', $this->itens->errors());
        }

        return redirect()->to(site_url('admin/catalogo'))->with('sucesso', 'Item atualizado.');
    }

    public function alternar($id = null)
    {
        $item = $this->buscarItem((int) $id);

        $this->itens->update((int) $id, ['ativo' => empty($item['ativo']) ? 1 : 0]);

        return redirect()->to(site_url('admin/catalogo'))->with('sucesso', 'Disponibilidade do item atualizada.');
    }

    public function excluir($id = null)
    {
        $this->buscarItem((int) $id);
        $this->itens->delete((int) $id);

        return redirect()->to(site_url('admin/catalogo'))->with('sucesso', 'Item removido do catálogo.');
    }

    /**
     * @return array<string, mixed>
     */
    private function buscarItem(int $id): array
    {
        $item = $this->itens->find($id);

        if ($item === null) {
            throw PageNotFoundException::forPageNotFound('Item do catálogo não encontrado.');
        }

        return $item;
    }

    /**
     * @return array<string, mixed>
     */
    private function dadosDoFormulario(): array
    {
        $texto = fn (string $campo): string => trim((string) $this->request->getPost($campo));
        $decimal = function (string $campo): ?float {
            $valor = $this->request->getPost($campo);

            return ($valor === null || $valor === '') ? null : (float) $valor;
        };

        return [
            'categoria_id'   => $this->request->getPost('categoria_id') ?: null,
            'nome'           => $texto('nome'),
            'descricao'      => $texto('descricao') ?: null,
            'tipo'           => (string) $this->request->getPost('tipo'),
            'valor_sugerido' => $decimal('valor_sugerido'),
            'link_afiliado'  => $texto('link_afiliado') ?: null,
            'ativo'          => $this->request->getPost('ativo') ? 1 : 0,
            'destaque'       => $this->request->getPost('destaque') ? 1 : 0,
            'ordem'          => (int) $this->request->getPost('ordem'),
        ];
    }
}
