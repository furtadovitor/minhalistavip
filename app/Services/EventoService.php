<?php

namespace App\Services;

use App\Entities\Evento;
use App\Models\EventoModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Regras de negócio do evento, incluindo o ISOLAMENTO DE TENANTS (Regra 2.4):
 * todo acesso a um evento passa por doOrganizador(), que valida a posse.
 */
class EventoService
{
    protected EventoModel $eventos;

    protected BaseConnection $db;

    public function __construct(?EventoModel $eventos = null, ?BaseConnection $db = null)
    {
        $this->eventos = $eventos ?? new EventoModel();
        $this->db      = $db ?? db_connect();
    }

    /**
     * @return list<Evento>
     */
    public function listar(int $usuarioId): array
    {
        return $this->eventos
            ->where('usuario_id', $usuarioId)
            ->orderBy('criado_em', 'DESC')
            ->findAll();
    }

    /**
     * Listas ativas (não arquivadas) do organizador.
     *
     * @return list<Evento>
     */
    public function listarAtivos(int $usuarioId): array
    {
        return $this->eventos
            ->where('usuario_id', $usuarioId)
            ->where('arquivado', 0)
            ->orderBy('criado_em', 'DESC')
            ->findAll();
    }

    /**
     * Listas arquivadas do organizador.
     *
     * @return list<Evento>
     */
    public function listarArquivados(int $usuarioId): array
    {
        return $this->eventos
            ->where('usuario_id', $usuarioId)
            ->where('arquivado', 1)
            ->orderBy('criado_em', 'DESC')
            ->findAll();
    }

    /**
     * Arquiva/desarquiva uma lista.
     */
    public function arquivar(int $eventoId, int $usuarioId, bool $arquivado): bool
    {
        $this->doOrganizador($eventoId, $usuarioId);

        return (bool) $this->eventos->update($eventoId, ['arquivado' => $arquivado ? 1 : 0]);
    }

    /**
     * Devolve o evento somente se pertencer ao organizador informado.
     *
     * @throws PageNotFoundException quando não existe ou é de outro tenant
     */
    public function doOrganizador(int $eventoId, int $usuarioId): Evento
    {
        $evento = $this->eventos->find($eventoId);

        if ($evento === null || (int) $evento->usuario_id !== $usuarioId) {
            throw PageNotFoundException::forPageNotFound('Evento não encontrado.');
        }

        return $evento;
    }

    /**
     * Evento acessível publicamente pelo slug. Rascunhos não são visíveis
     * (nem encerrados para checkout — cabe ao chamador filtrar).
     *
     * @throws PageNotFoundException
     */
    public function publicadoPorSlug(string $slug): Evento
    {
        $evento = $this->eventos->buscarPorSlug($slug);

        if ($evento === null || $evento->status === 'rascunho') {
            throw PageNotFoundException::forPageNotFound('Evento não encontrado.');
        }

        return $evento;
    }

    /**
     * @param array<string, mixed> $dados
     */
    public function criar(array $dados, int $usuarioId): ?Evento
    {
        $dados['usuario_id'] = $usuarioId;

        $base          = trim((string) ($dados['slug'] ?? '')) !== '' ? (string) $dados['slug'] : (string) $dados['titulo'];
        $dados['slug'] = $this->gerarSlugUnico($base);

        // No insert o CI4 valida TODAS as regras (cleanValidationRules = false),
        // então campos obrigatórios precisam estar presentes.
        $dados['status'] = $dados['status'] ?? 'rascunho';

        $evento = new Evento($dados);

        if ($this->eventos->insert($evento) === false) {
            return null;
        }

        $evento->id = $this->eventos->getInsertID();

        return $evento;
    }

    /**
     * @param array<string, mixed> $dados
     */
    public function atualizar(int $eventoId, int $usuarioId, array $dados): bool
    {
        $this->doOrganizador($eventoId, $usuarioId);

        if (trim((string) ($dados['slug'] ?? '')) !== '') {
            $dados['slug'] = $this->gerarSlugUnico((string) $dados['slug'], $eventoId);
        } else {
            unset($dados['slug']);
        }

        return (bool) $this->eventos->update($eventoId, $dados);
    }

    /**
     * Alterna entre rascunho e publicado. Para publicar, o evento precisa passar
     * por pendenciasPublicacao() (modelo, tipo, data, local e ao menos um presente).
     *
     * @return array{ok: bool, publicado: bool, pendencias: list<string>}
     */
    public function alternarPublicacao(int $eventoId, int $usuarioId): array
    {
        $evento     = $this->doOrganizador($eventoId, $usuarioId);
        $publicando = $evento->status !== 'publicado';

        if ($publicando) {
            $pendencias = $this->pendenciasPublicacao($evento);

            if ($pendencias !== []) {
                return ['ok' => false, 'publicado' => false, 'pendencias' => $pendencias];
            }
        }

        $this->eventos->update($eventoId, [
            'status'       => $publicando ? 'publicado' : 'rascunho',
            'publicado_em' => $publicando ? date('Y-m-d H:i:s') : null,
        ]);

        return ['ok' => true, 'publicado' => $publicando, 'pendencias' => []];
    }

    /**
     * Itens que impedem a publicação do site da lista. Vazio = pode publicar.
     *
     * Regras mínimas: modelo visual, tipo de evento, data, local e ao menos um
     * presente ativo (sem presentes a lista não tem o que receber).
     *
     * @return list<string>
     */
    public function pendenciasPublicacao(Evento $evento): array
    {
        $pendencias = [];

        if (! ModeloService::existe((string) $evento->tema)) {
            $pendencias[] = 'Escolha um modelo visual.';
        }

        if (! TipoEventoService::existe((string) $evento->tipo_evento)) {
            $pendencias[] = 'Informe o tipo de evento.';
        }

        if ($evento->data_evento === null) {
            $pendencias[] = 'Informe a data do evento.';
        }

        if (trim((string) $evento->local_nome) === '') {
            $pendencias[] = 'Informe o local do evento.';
        }

        $presentes = $this->db->table('presentes_evento')
            ->where('evento_id', (int) $evento->id)
            ->where('ativo', 1)
            ->where('deletado_em IS NULL')
            ->countAllResults();

        if ($presentes < 1) {
            $pendencias[] = 'Adicione pelo menos um presente à lista.';
        }

        return $pendencias;
    }

    public function podePublicar(Evento $evento): bool
    {
        return $this->pendenciasPublicacao($evento) === [];
    }

    public function excluir(int $eventoId, int $usuarioId): bool
    {
        $this->doOrganizador($eventoId, $usuarioId);

        return (bool) $this->eventos->delete($eventoId);
    }

    /**
     * Palavras reservadas pelas rotas fixas do sistema. Como o hotsite fica na
     * raiz (minhalistavip.com.br/{slug}), estes nomes NÃO podem ser usados.
     *
     * @var list<string>
     */
    public const SLUGS_RESERVADOS = [
        'login', 'registro', 'logout', 'painel', 'admin',
        'api', 'webhooks', 'uploads', 'assets', 'vendor',
        'home', 'index', 'www', 'e', 'sitemap', 'robots', 'favicon',
        'demo', 'exemplos', 'buscar', 'criar-lista-de-presente',
    ];

    public function slugReservado(string $slug): bool
    {
        helper('url');

        return in_array(url_title($slug, '-', true), self::SLUGS_RESERVADOS, true)
            || in_array(mb_strtolower(trim($slug)), self::SLUGS_RESERVADOS, true);
    }

    /**
     * Gera um slug único e não-reservado, ignorando o próprio evento na edição.
     */
    public function gerarSlugUnico(string $base, ?int $ignorarId = null): string
    {
        helper('url');

        $slug = url_title($base, '-', true);

        if ($slug === '') {
            $slug = 'evento';
        }

        // Reserva espaço para o sufixo numérico dentro do limite da coluna (160).
        $slug = mb_substr($slug, 0, 140);

        $original = $slug;
        $sufixo   = 2;

        while ($this->slugEmUso($slug, $ignorarId)) {
            $slug = $original . '-' . $sufixo;
            $sufixo++;
        }

        return $slug;
    }

    /**
     * Considera os slugs reservados e TODOS os registros (inclusive
     * soft-deleted), pois o índice UNIQUE continua ocupado por eles.
     */
    protected function slugEmUso(string $slug, ?int $ignorarId): bool
    {
        if ($this->slugReservado($slug)) {
            return true;
        }

        $builder = $this->db->table('eventos')->where('slug', $slug);

        if ($ignorarId !== null) {
            $builder->where('id !=', $ignorarId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * @return array<string, string>
     */
    public function erros(): array
    {
        return $this->eventos->errors();
    }
}
