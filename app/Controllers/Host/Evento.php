<?php

namespace App\Controllers\Host;

use App\Controllers\BaseController;
use App\Models\GaleriaModel;
use App\Models\PedidoModel;
use App\Services\ConvidadoService;
use App\Services\EventoService;
use App\Services\ModeloService;
use App\Services\PresenteEventoService;
use App\Services\TipoEventoService;
use App\Services\UploadService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Workspace da lista (evento): painel de gerenciamento dividido em
 * Lista / Dinheiro / Personalização. Todo acesso passa por
 * EventoService::doOrganizador() (isolamento de tenant).
 */
class Evento extends BaseController
{
    protected EventoService $eventos;

    protected UploadService $upload;

    protected PedidoModel $pedidos;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->eventos = new EventoService();
        $this->upload  = new UploadService();
        $this->pedidos = new PedidoModel();
    }

    /**
     * Visão geral da lista.
     */
    public function index($eventoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        return $this->render('host/eventos/overview', [
            'titulo'          => $evento->titulo,
            'evento'          => $evento,
            'presentesTotal'  => count((new PresenteEventoService())->doEvento((int) $evento->id)),
            'convidados'      => (new ConvidadoService())->resumo($evento),
            'financeiro'      => $this->pedidos->resumoEvento((int) $evento->id),
            'galeriaTotal'    => (new GaleriaModel())->where('evento_id', (int) $evento->id)->countAllResults(),
        ]);
    }

    // -----------------------------------------------------------------
    // Informações do evento
    // -----------------------------------------------------------------

    public function informacoes($eventoId = null)
    {
        return $this->render('host/eventos/informacoes', [
            'titulo' => 'Informações do evento',
            'evento' => $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId()),
            'tipos'  => array_map(
                static fn (array $tipo): string => $tipo['rotulo'],
                TipoEventoService::todos()
            ),
        ]);
    }

    public function salvarInformacoes($eventoId = null)
    {
        $id = (int) $eventoId;

        $dados = [
            'titulo'           => $this->texto('titulo'),
            'subtitulo'        => $this->texto('subtitulo') ?: null,
            'tipo_evento'      => (string) $this->request->getPost('tipo_evento'),
            'data_evento'      => $this->request->getPost('data_evento') ?: null,
            'horario'          => $this->request->getPost('horario') ?: null,
            'local_nome'       => $this->texto('local_nome') ?: null,
            'local_endereco'   => $this->texto('local_endereco') ?: null,
            'mensagem_convite' => $this->texto('mensagem_convite') ?: null,
            'descricao'        => $this->texto('descricao') ?: null,
        ];

        return $this->salvar($id, $dados, 'informacoes', 'Informações atualizadas.');
    }

    // -----------------------------------------------------------------
    // Aparência
    // -----------------------------------------------------------------

    public function aparencia($eventoId = null)
    {
        return $this->render('host/eventos/aparencia', [
            'titulo' => 'Aparência',
            'evento' => $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId()),
            'temas'  => ModeloService::rotulos(),
        ]);
    }

    public function salvarAparencia($eventoId = null)
    {
        $id     = (int) $eventoId;
        $evento = $this->eventos->doOrganizador($id, $this->usuarioId());

        $dados = [
            'tema'           => $this->texto('tema') ?: 'classico',
            'cor_primaria'   => $this->texto('cor_primaria') ?: '#8e44ad',
            'cor_secundaria' => $this->texto('cor_secundaria') ?: '#f39c12',
        ];

        if ($this->request->getPost('remover_capa')) {
            $this->upload->apagar($evento->imagem_capa, 'eventos');
            $dados['imagem_capa'] = null;
            $erroCapa             = null;
        } else {
            [$dados['imagem_capa'], $erroCapa] = $this->upload->imagem(
                $this->request->getFile('imagem_capa'),
                'eventos',
                $evento->imagem_capa
            );
        }

        $redirect = $this->salvar($id, $dados, 'aparencia', 'Aparência atualizada.');

        return $erroCapa !== null ? $redirect->with('erro', $erroCapa) : $redirect;
    }

    // -----------------------------------------------------------------
    // Funcionalidades
    // -----------------------------------------------------------------

    public function funcionalidades($eventoId = null)
    {
        return $this->render('host/eventos/funcionalidades', [
            'titulo' => 'Funcionalidades',
            'evento' => $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId()),
        ]);
    }

    public function salvarFuncionalidades($eventoId = null)
    {
        $id = (int) $eventoId;

        $limite = $this->request->getPost('limite_convidados');

        $dados = [
            'permite_rsvp'      => $this->request->getPost('permite_rsvp') ? 1 : 0,
            'permite_recados'   => $this->request->getPost('permite_recados') ? 1 : 0,
            'exibir_valores'    => $this->request->getPost('exibir_valores') ? 1 : 0,
            'limite_convidados' => ($limite === null || $limite === '') ? null : (int) $limite,
        ];

        return $this->salvar($id, $dados, 'funcionalidades', 'Funcionalidades atualizadas.');
    }

    // -----------------------------------------------------------------
    // Configurações (endereço, publicação, zona de risco)
    // -----------------------------------------------------------------

    public function configuracoes($eventoId = null)
    {
        return $this->render('host/eventos/configuracoes', [
            'titulo' => 'Configurações',
            'evento' => $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId()),
        ]);
    }

    public function salvarConfiguracoes($eventoId = null)
    {
        $id   = (int) $eventoId;
        $slug = $this->texto('slug');

        if ($slug !== '' && $this->eventos->slugReservado($slug)) {
            return redirect()->back()->withInput()
                ->with('erro', 'O endereço "' . $slug . '" é reservado pelo sistema. Escolha outro.');
        }

        return $this->salvar($id, ['slug' => $slug], 'configuracoes', 'Configurações salvas.');
    }

    // -----------------------------------------------------------------
    // Dinheiro — forma de pagamento
    // -----------------------------------------------------------------

    public function formaPagamento($eventoId = null)
    {
        return $this->render('host/eventos/forma_pagamento', [
            'titulo' => 'Forma de pagamento',
            'evento' => $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId()),
        ]);
    }

    public function salvarFormaPagamento($eventoId = null)
    {
        $id = (int) $eventoId;

        $decimal = static function ($valor): ?float {
            return ($valor === null || $valor === '') ? null : (float) $valor;
        };

        $dados = [
            'quem_paga_taxa'  => (string) $this->request->getPost('quem_paga_taxa'),
            'percentual_taxa' => $decimal($this->request->getPost('percentual_taxa')),
            'meta_valor'      => $decimal($this->request->getPost('meta_valor')),
            'pix_tipo'        => $this->request->getPost('pix_tipo') ?: null,
            'pix_chave'       => $this->texto('pix_chave') ?: null,
            'pix_nome'        => $this->texto('pix_nome') ?: null,
        ];

        return $this->salvar($id, $dados, 'forma-pagamento', 'Forma de pagamento atualizada.');
    }

    // -----------------------------------------------------------------
    // Dinheiro — pagamentos recebidos
    // -----------------------------------------------------------------

    public function pagamentos($eventoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        return $this->render('host/eventos/pagamentos', [
            'titulo'   => 'Pagamentos',
            'evento'   => $evento,
            'pedidos'  => $this->pedidos->doEvento((int) $evento->id),
            'resumo'   => $this->pedidos->resumoEvento((int) $evento->id),
        ]);
    }

    // -----------------------------------------------------------------
    // Compartilhar
    // -----------------------------------------------------------------

    public function compartilhar($eventoId = null)
    {
        return $this->render('host/eventos/compartilhar', [
            'titulo'     => 'Compartilhar',
            'evento'     => $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId()),
            'urlPublica' => site_url('{slug}'),
        ]);
    }

    // -----------------------------------------------------------------
    // Apoio
    // -----------------------------------------------------------------

    private function texto(string $campo): string
    {
        return trim((string) $this->request->getPost($campo));
    }

    /**
     * @param array<string, mixed> $dados
     */
    private function salvar(int $eventoId, array $dados, string $aba, string $mensagem)
    {
        $this->eventos->doOrganizador($eventoId, $this->usuarioId());

        if (! $this->eventos->atualizar($eventoId, $this->usuarioId(), $dados)) {
            return redirect()->back()->withInput()->with('erros', $this->eventos->erros());
        }

        return redirect()->to(site_url('painel/eventos/' . $eventoId . '/' . $aba))
            ->with('sucesso', $mensagem);
    }
}
