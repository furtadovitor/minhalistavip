<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Services\EventoService;
use App\Services\ModeloService;
use App\Services\TipoEventoService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Pré-registro e criação rápida de lista (nova entrada de "Criar minha lista").
 *
 * Fluxo:
 *  - Visitante informa nome, modelo visual, tipo de evento, data, hora e local.
 *  - Clica em "Avançar".
 *      - Já autenticado: a lista (rascunho) é criada na hora.
 *      - Sem login: guarda a intenção na sessão, pede login/cadastro e, ao
 *        voltar, conclui a criação automaticamente
 *        (/criar-lista-de-presente/continuar).
 */
class CriarLista extends BaseController
{
    private const SESSAO_PENDENTE = 'lista_pendente';

    /**
     * Tela de pré-registro (sem tipo pré-selecionado).
     */
    public function index()
    {
        return $this->form(null);
    }

    /**
     * Tela de pré-registro, opcionalmente com um tipo de evento já escolhido
     * (atalhos da Home: /criar-lista-de-presente/{slug}).
     */
    public function form($slug = null)
    {
        $tipo     = ((string) $slug !== '') ? $this->resolverTipo((string) $slug) : null;
        $pendente = $this->session->get(self::SESSAO_PENDENTE);

        return $this->render('public/criar_lista', [
            'titulo'  => 'Crie sua lista de presentes grátis',
            'tipo'    => $tipo,
            'modelos' => ModeloService::todos(),
            'tipos'   => TipoEventoService::todos(),
            'logado'  => $this->auth->estaLogado(),
            'valores' => $this->request->getPost() ?: ($pendente['dados'] ?? []),
            'seo'     => [
                'descricao' => 'Crie sua lista de presentes online e grátis em 1 minuto: escolha o tipo de evento, personalize o site e convide. Os convidados presenteiam via PIX.',
                'tipo'      => 'website',
            ],
        ]);
    }

    /**
     * Recebe o formulário. Cria a lista ou encaminha para o login.
     */
    public function criar($slug = null)
    {
        $tipoPre = ((string) $slug !== '') ? $this->resolverTipo((string) $slug) : null;

        $regras = [
            'titulo'      => 'required|min_length[3]|max_length[180]',
            'tema'        => 'required',
            'data_evento' => 'permit_empty|valid_date[Y-m-d]',
            'horario'     => 'permit_empty',
            'local_nome'  => 'permit_empty|max_length[180]',
        ];

        $temaPost = (string) $this->request->getPost('tema');

        if (! $this->validate($regras) || ! ModeloService::existe($temaPost)) {
            return redirect()->back()->withInput()
                ->with('erros', $this->validator->getErrors() ?: ['Escolha um modelo para a sua lista.']);
        }

        $dados = $this->montarDados($tipoPre, $this->request->getPost());

        if (! $this->auth->estaLogado()) {
            $this->session->set(self::SESSAO_PENDENTE, ['dados' => $dados]);
            $this->session->set('redirect_url', site_url('criar-lista-de-presente/continuar'));

            return redirect()->to(site_url('login'))
                ->with('sucesso', 'Falta só um passo: entre ou crie sua conta para publicar a lista.');
        }

        return $this->finalizar($dados);
    }

    /**
     * Conclui a criação após o login/cadastro.
     */
    public function continuar()
    {
        if (! $this->auth->estaLogado()) {
            $this->session->set('redirect_url', site_url('criar-lista-de-presente/continuar'));

            return redirect()->to(site_url('login'))->with('erro', 'Faça login para concluir a criação da lista.');
        }

        $pendente = $this->session->get(self::SESSAO_PENDENTE);
        $this->session->remove(self::SESSAO_PENDENTE);

        if (! is_array($pendente) || empty($pendente['dados'])) {
            return redirect()->to(site_url('criar-lista-de-presente'))
                ->with('erro', 'Preencha os dados da lista para continuar.');
        }

        return $this->finalizar($pendente['dados']);
    }

    /**
     * @param array<string, mixed> $dados
     */
    private function finalizar(array $dados): \CodeIgniter\HTTP\RedirectResponse
    {
        $servico = new EventoService();
        $evento  = $servico->criar($dados, $this->usuarioId());

        if ($evento === null) {
            return redirect()->back()->withInput()->with('erros', $servico->erros());
        }

        return redirect()->to(site_url('painel/eventos/' . $evento->id . '/presentes'))
            ->with('sucesso', 'Lista criada! Agora adicione os presentes.');
    }

    /**
     * @param array{chave: string, slug: string, rotulo: string, icone: string, tema: string, cor_primaria: string, cor_secundaria: string}|null $tipoPre
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function montarDados(?array $tipoPre, array $post): array
    {
        $modelo = ModeloService::um((string) ($post['tema'] ?? '')) ?? ModeloService::um('classico');

        $tipoEvento = (string) ($post['tipo_evento'] ?? '');
        if (! TipoEventoService::existe($tipoEvento)) {
            $tipoEvento = $tipoPre['chave'] ?? 'outro';
        }

        return [
            'titulo'         => trim((string) ($post['titulo'] ?? '')),
            'tipo_evento'    => $tipoEvento,
            'tema'           => $modelo['chave'],
            'cor_primaria'   => $modelo['cor_primaria'],
            'cor_secundaria' => $modelo['cor_secundaria'],
            'data_evento'    => trim((string) ($post['data_evento'] ?? '')) ?: null,
            'horario'        => trim((string) ($post['horario'] ?? '')) ?: null,
            'local_nome'     => trim((string) ($post['local_nome'] ?? '')) ?: null,
            'quem_paga_taxa' => 'convidado',
            'permite_rsvp'   => 1,
            'permite_recados' => 1,
            'exibir_valores' => 1,
            'status'         => 'rascunho',
        ];
    }

    /**
     * @return array{chave: string, slug: string, rotulo: string, icone: string, tema: string, cor_primaria: string, cor_secundaria: string}
     */
    private function resolverTipo(string $slug): array
    {
        $tipo = TipoEventoService::porSlug($slug);

        if ($tipo === null) {
            throw PageNotFoundException::forPageNotFound('Tipo de evento não encontrado.');
        }

        return $tipo;
    }
}
