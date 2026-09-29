<?php

namespace App\Controllers\Host;

use App\Controllers\BaseController;
use App\Models\CatalogoPresenteModel;
use App\Models\CategoriaModel;
use App\Services\EventoService;
use App\Services\PresenteEventoService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Presentes do evento: cadastro customizado e clonagem do catálogo global.
 */
class Presentes extends BaseController
{
    protected EventoService $eventos;

    protected PresenteEventoService $presentes;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->eventos   = new EventoService();
        $this->presentes = new PresenteEventoService();
    }

    public function index($eventoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        return $this->render('host/presentes/index', [
            'titulo'    => 'Presentes',
            'evento'    => $evento,
            'presentes' => $this->presentes->doEvento((int) $evento->id),
        ]);
    }

    public function novo($eventoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        return $this->render('host/presentes/form', [
            'titulo'   => 'Novo presente',
            'evento'   => $evento,
            'presente' => null,
        ]);
    }

    public function criar($eventoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        if ($this->presentes->criar((int) $evento->id, $this->dadosDoFormulario()) === null) {
            return redirect()->back()->withInput()->with('erros', $this->presentes->erros());
        }

        return redirect()->to($this->urlPresentes((int) $evento->id))
            ->with('sucesso', 'Presente adicionado à lista.');
    }

    public function editar($eventoId = null, $presenteId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        return $this->render('host/presentes/form', [
            'titulo'   => 'Editar presente',
            'evento'   => $evento,
            'presente' => $this->presentes->um((int) $evento->id, (int) $presenteId),
        ]);
    }

    public function atualizar($eventoId = null, $presenteId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        if (! $this->presentes->atualizar((int) $evento->id, (int) $presenteId, $this->dadosDoFormulario())) {
            return redirect()->back()->withInput()->with('erros', $this->presentes->erros());
        }

        return redirect()->to($this->urlPresentes((int) $evento->id))
            ->with('sucesso', 'Presente atualizado.');
    }

    public function alternar($eventoId = null, $presenteId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        $this->presentes->alternarAtivo((int) $evento->id, (int) $presenteId);

        return redirect()->to($this->urlPresentes((int) $evento->id))
            ->with('sucesso', 'Disponibilidade do presente atualizada.');
    }

    public function excluir($eventoId = null, $presenteId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        $this->presentes->excluir((int) $evento->id, (int) $presenteId);

        return redirect()->to($this->urlPresentes((int) $evento->id))
            ->with('sucesso', 'Presente removido da lista.');
    }

    /**
     * Catálogo global para clonagem (Regra 2.3).
     */
    public function catalogo($eventoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        $filtros = [
            'categoria_id' => $this->request->getGet('categoria') ?: null,
            'busca'        => $this->request->getGet('busca') ?: null,
        ];

        return $this->render('host/presentes/catalogo', [
            'titulo'     => 'Catálogo de presentes',
            'evento'     => $evento,
            'categorias' => (new CategoriaModel())->ativas(),
            'itens'      => (new CatalogoPresenteModel())->ativosComCategoria($filtros),
            'jaUsados'   => $this->presentes->idsCatalogoJaUsados((int) $evento->id),
            'filtros'    => $filtros,
        ]);
    }

    public function clonar($eventoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        $resultado = $this->presentes->clonarDoCatalogo(
            (int) $evento->id,
            (array) $this->request->getPost('catalogo_ids')
        );

        $mensagem = $resultado['criados'] . ' item(ns) adicionado(s) à lista.';

        if ($resultado['ignorados'] > 0) {
            $mensagem .= ' ' . $resultado['ignorados'] . ' já estavam no evento.';
        }

        return redirect()->to($this->urlPresentes((int) $evento->id))
            ->with($resultado['criados'] > 0 ? 'sucesso' : 'erro', $mensagem);
    }

    /**
     * @return array<string, mixed>
     */
    private function dadosDoFormulario(): array
    {
        $texto = fn (string $campo): string => trim((string) $this->request->getPost($campo));

        return [
            'nome'            => $texto('nome'),
            'descricao'       => $texto('descricao') ?: null,
            'tipo'            => (string) $this->request->getPost('tipo'),
            'valor'           => (float) $this->request->getPost('valor'),
            'quantidade_meta' => max(1, (int) $this->request->getPost('quantidade_meta')),
            'link_afiliado'   => $texto('link_afiliado') ?: null,
            'ativo'           => $this->request->getPost('ativo') ? 1 : 0,
            'ordem'           => (int) $this->request->getPost('ordem'),
        ];
    }

    private function urlPresentes(int $eventoId): string
    {
        return site_url('painel/eventos/' . $eventoId . '/presentes');
    }
}
