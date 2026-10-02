<?php

namespace App\Controllers\Host;

use App\Controllers\BaseController;
use App\Services\EventoService;
use App\Services\UploadService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * CRUD de eventos do organizador.
 * Todo acesso individual passa por EventoService::doOrganizador() (tenant).
 */
class Eventos extends BaseController
{
    protected EventoService $eventos;

    protected UploadService $upload;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->eventos = new EventoService();
        $this->upload  = new UploadService();
    }

    public function index()
    {
        $eventos = $this->eventos->listar($this->usuarioId());

        return $this->render('host/eventos/index', [
            'titulo'     => 'Meus eventos',
            'eventos'    => $eventos,
            'publicados' => count(array_filter(
                $eventos,
                static fn ($evento): bool => $evento->status === 'publicado'
            )),
        ]);
    }

    public function novo()
    {
        return $this->render('host/eventos/form', [
            'titulo' => 'Novo evento',
            'evento' => null,
        ]);
    }

    public function criar()
    {
        $dados = $this->dadosDoFormulario();
        [$dados['imagem_capa'], $erroCapa] = $this->tratarCapa(null);

        if (($erro = $this->erroSlugReservado($dados['slug'])) !== null) {
            return redirect()->back()->withInput()->with('erros', ['slug' => $erro]);
        }

        $evento = $this->eventos->criar($dados, $this->usuarioId());

        if ($evento === null) {
            return redirect()->back()->withInput()->with('erros', $this->eventos->erros());
        }

        if ($erroCapa !== null) {
            return redirect()->to($this->urlPresentes((int) $evento->id))->with('erro', $erroCapa);
        }

        return redirect()->to($this->urlPresentes((int) $evento->id))
            ->with('sucesso', 'Evento criado! Agora monte a lista de presentes.');
    }

    public function editar($id = null)
    {
        $evento = $this->eventos->doOrganizador((int) $id, $this->usuarioId());

        return $this->render('host/eventos/form', [
            'titulo' => 'Editar evento',
            'evento' => $evento,
        ]);
    }

    public function atualizar($id = null)
    {
        $eventoId = (int) $id;
        $evento   = $this->eventos->doOrganizador($eventoId, $this->usuarioId());

        $dados = $this->dadosDoFormulario();

        if (($erro = $this->erroSlugReservado($dados['slug'])) !== null) {
            return redirect()->back()->withInput()->with('erros', ['slug' => $erro]);
        }

        if ($this->request->getPost('remover_capa')) {
            $this->upload->apagar($evento->imagem_capa, 'eventos');
            $dados['imagem_capa'] = null;
            $erroCapa             = null;
        } else {
            [$dados['imagem_capa'], $erroCapa] = $this->tratarCapa($evento->imagem_capa);
        }

        if (! $this->eventos->atualizar($eventoId, $this->usuarioId(), $dados)) {
            return redirect()->back()->withInput()->with('erros', $this->eventos->erros());
        }

        $redirect = redirect()->to(site_url('painel/eventos'))->with('sucesso', 'Evento atualizado com sucesso.');

        return $erroCapa !== null ? $redirect->with('erro', $erroCapa) : $redirect;
    }

    public function publicar($id = null)
    {
        $this->eventos->alternarPublicacao((int) $id, $this->usuarioId());

        return redirect()->back()->with('sucesso', 'Disponibilidade do evento atualizada.');
    }

    public function arquivar($id = null)
    {
        $arquivar = (int) $this->request->getPost('arquivar') === 1;

        $this->eventos->arquivar((int) $id, $this->usuarioId(), $arquivar);

        return redirect()->to(site_url('painel'))
            ->with('sucesso', $arquivar ? 'Lista arquivada.' : 'Lista reativada.');
    }

    public function excluir($id = null)
    {
        $this->eventos->excluir((int) $id, $this->usuarioId());

        return redirect()->to(site_url('painel/eventos'))->with('sucesso', 'Evento removido.');
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

        $inteiro = function (string $campo): ?int {
            $valor = $this->request->getPost($campo);

            return ($valor === null || $valor === '') ? null : (int) $valor;
        };

        return [
            'titulo'           => $texto('titulo'),
            'slug'             => $texto('slug'),
            'subtitulo'        => $texto('subtitulo') ?: null,
            'tipo_evento'      => (string) $this->request->getPost('tipo_evento'),
            'descricao'        => $texto('descricao') ?: null,
            'mensagem_convite' => $texto('mensagem_convite') ?: null,
            'data_evento'      => $this->request->getPost('data_evento') ?: null,
            'horario'          => $this->request->getPost('horario') ?: null,
            'local_nome'       => $texto('local_nome') ?: null,
            'local_endereco'   => $texto('local_endereco') ?: null,
            'tema'             => $texto('tema') ?: 'classico',
            'cor_primaria'     => $texto('cor_primaria') ?: '#8e44ad',
            'cor_secundaria'   => $texto('cor_secundaria') ?: '#f39c12',
            'quem_paga_taxa'   => (string) $this->request->getPost('quem_paga_taxa'),
            'percentual_taxa'  => $decimal('percentual_taxa'),
            'meta_valor'       => $decimal('meta_valor'),
            'limite_convidados' => $inteiro('limite_convidados'),
            'pix_chave'        => $texto('pix_chave') ?: null,
            'pix_tipo'         => $this->request->getPost('pix_tipo') ?: null,
            'pix_nome'         => $texto('pix_nome') ?: null,
            'permite_rsvp'     => $this->request->getPost('permite_rsvp') ? 1 : 0,
            'permite_recados'  => $this->request->getPost('permite_recados') ? 1 : 0,
            'exibir_valores'   => $this->request->getPost('exibir_valores') ? 1 : 0,
        ];
    }

    /**
     * Processa o upload da capa. Retorna [caminho relativo|null, erro].
     *
     * @return array{0: string|null, 1: string|null}
     */
    private function tratarCapa(?string $atual): array
    {
        return $this->upload->imagem($this->request->getFile('imagem_capa'), 'eventos', $atual);
    }

    private function urlPresentes(int $eventoId): string
    {
        return site_url('painel/eventos/' . $eventoId . '/presentes');
    }

    /**
     * Bloqueia slugs que colidem com rotas fixas (hotsite fica na raiz).
     */
    private function erroSlugReservado(string $slug): ?string
    {
        if ($slug === '' || ! $this->eventos->slugReservado($slug)) {
            return null;
        }

        return 'O endereço "' . $slug . '" é reservado pelo sistema. Escolha outro.';
    }
}
