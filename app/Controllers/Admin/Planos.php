<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PlanoModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Planos de assinatura da plataforma.
 */
class Planos extends BaseController
{
    protected PlanoModel $planos;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->planos = new PlanoModel();
    }

    public function index()
    {
        return $this->render('admin/planos/index', [
            'titulo' => 'Planos',
            'planos' => $this->planos->listar(),
        ]);
    }

    public function novo()
    {
        return $this->render('admin/planos/form', [
            'titulo' => 'Novo plano',
            'plano'  => null,
        ]);
    }

    public function criar()
    {
        $dados = $this->dadosDoFormulario();
        $dados['slug'] = $this->gerarSlug($dados['slug'] !== '' ? $dados['slug'] : $dados['nome']);

        if ($this->planos->insert($dados) === false) {
            return redirect()->back()->withInput()->with('erros', $this->planos->errors());
        }

        return redirect()->to(site_url('admin/planos'))->with('sucesso', 'Plano criado.');
    }

    public function editar($id = null)
    {
        return $this->render('admin/planos/form', [
            'titulo' => 'Editar plano',
            'plano'  => $this->buscar((int) $id),
        ]);
    }

    public function atualizar($id = null)
    {
        $id = (int) $id;
        $this->buscar($id);

        $dados = $this->dadosDoFormulario();
        $dados['slug'] = $this->gerarSlug($dados['slug'] !== '' ? $dados['slug'] : $dados['nome'], $id);

        if ($this->planos->update($id, $dados) === false) {
            return redirect()->back()->withInput()->with('erros', $this->planos->errors());
        }

        return redirect()->to(site_url('admin/planos'))->with('sucesso', 'Plano atualizado.');
    }

    public function alternar($id = null)
    {
        $plano = $this->buscar((int) $id);

        $this->planos->update((int) $id, ['ativo' => empty($plano['ativo']) ? 1 : 0]);

        return redirect()->to(site_url('admin/planos'))->with('sucesso', 'Disponibilidade do plano atualizada.');
    }

    public function excluir($id = null)
    {
        $this->buscar((int) $id);
        $this->planos->delete((int) $id);

        return redirect()->to(site_url('admin/planos'))->with('sucesso', 'Plano removido.');
    }

    /**
     * @return array<string, mixed>
     */
    private function buscar(int $id): array
    {
        $plano = $this->planos->find($id);

        if ($plano === null) {
            throw PageNotFoundException::forPageNotFound('Plano não encontrado.');
        }

        return $plano;
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

        $recursos = [];
        foreach (['rsvp', 'recados', 'destaque'] as $recurso) {
            if ($this->request->getPost('recurso_' . $recurso)) {
                $recursos[$recurso] = true;
            }
        }

        $limite = $this->request->getPost('limite_eventos');

        return [
            'nome'            => $texto('nome'),
            'slug'            => $texto('slug'),
            'descricao'       => $texto('descricao') ?: null,
            'preco'           => (float) $this->request->getPost('preco'),
            'periodo'         => (string) $this->request->getPost('periodo'),
            'percentual_taxa' => $decimal('percentual_taxa'),
            'limite_eventos'  => ($limite === null || $limite === '') ? null : (int) $limite,
            'recursos'        => $recursos === [] ? null : json_encode($recursos),
            'ativo'           => $this->request->getPost('ativo') ? 1 : 0,
            'ordem'           => (int) $this->request->getPost('ordem'),
        ];
    }

    private function gerarSlug(string $base, ?int $ignorarId = null): string
    {
        helper('url');
        $slug = url_title($base, '-', true) ?: 'plano';
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
        $builder = $this->planos->where('slug', $slug);

        if ($ignorarId !== null) {
            $builder->where('id !=', $ignorarId);
        }

        return $builder->countAllResults() > 0;
    }
}
