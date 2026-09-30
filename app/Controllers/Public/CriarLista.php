<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Services\EventoService;
use App\Services\TipoEventoService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Criação rápida de lista a partir dos atalhos de tipo de evento da Home.
 *
 * Fluxo (espelha o "criar lista" da listaideal.com.br):
 *  - Visitante escolhe o tipo em /criar-lista-de-presente/{slug}.
 *  - Informa nome e descrição da lista e confirma.
 *  - Já autenticado: a lista é criada na hora.
 *  - Sem login: guarda a intenção na sessão, pede login/cadastro e, ao voltar,
 *    conclui a criação automaticamente (/criar-lista-de-presente/continuar).
 */
class CriarLista extends BaseController
{
    private const SESSAO_PENDENTE = 'lista_pendente';

    /**
     * Vitrine de tipos (mesmos atalhos da Home).
     */
    public function index()
    {
        return $this->render('public/criar_lista_tipos', [
            'titulo' => 'Crie sua lista de presentes grátis',
            'tipos'  => TipoEventoService::todos(),
        ]);
    }

    /**
     * Formulário de criação para um tipo específico.
     */
    public function form($slug = null)
    {
        $tipo = $this->resolverTipo((string) $slug);

        $pendente = $this->session->get(self::SESSAO_PENDENTE);

        return $this->render('public/criar_lista', [
            'titulo'   => 'Criar lista de ' . $tipo['rotulo'],
            'tipo'     => $tipo,
            'logado'   => $this->auth->estaLogado(),
            'valores'  => $this->request->getPost() ?: ($pendente['dados'] ?? []),
        ]);
    }

    /**
     * Recebe o formulário. Cria a lista ou encaminha para o login.
     */
    public function criar($slug = null)
    {
        $tipo = $this->resolverTipo((string) $slug);

        $regras = [
            'titulo'    => 'required|min_length[3]|max_length[180]',
            'descricao' => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($regras)) {
            return redirect()->back()->withInput()->with('erros', $this->validator->getErrors());
        }

        $dados = $this->montarDados($tipo, $this->request->getPost());

        if (! $this->auth->estaLogado()) {
            $this->session->set(self::SESSAO_PENDENTE, ['slug' => $tipo['slug'], 'dados' => $dados]);
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
                ->with('erro', 'Escolha o tipo de evento para criar sua lista.');
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
     * @param array{chave: string, slug: string, rotulo: string, icone: string, tema: string, cor_primaria: string, cor_secundaria: string} $tipo
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function montarDados(array $tipo, array $post): array
    {
        $descricao = trim((string) ($post['descricao'] ?? ''));

        return [
            'titulo'           => trim((string) ($post['titulo'] ?? '')),
            'descricao'        => $descricao !== '' ? $descricao : null,
            'mensagem_convite' => $descricao !== '' ? $descricao : null,
            'tipo_evento'      => $tipo['chave'],
            'tema'             => $tipo['tema'],
            'cor_primaria'     => $tipo['cor_primaria'],
            'cor_secundaria'   => $tipo['cor_secundaria'],
            'quem_paga_taxa'   => 'convidado',
            'permite_rsvp'     => 1,
            'permite_recados'  => 1,
            'exibir_valores'   => 1,
            'status'           => 'rascunho',
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
