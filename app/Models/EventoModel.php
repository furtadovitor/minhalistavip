<?php

namespace App\Models;

use App\Entities\Evento;
use App\Services\TipoEventoService;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;

class EventoModel extends Model
{
    protected $table          = 'eventos';
    protected $primaryKey     = 'id';
    protected $returnType     = Evento::class;
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $createdField   = 'criado_em';
    protected $updatedField   = 'atualizado_em';
    protected $deletedField   = 'deletado_em';

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'usuario_id',
        'slug',
        'titulo',
        'subtitulo',
        'tipo_evento',
        'descricao',
        'mensagem_convite',
        'data_evento',
        'horario',
        'local_nome',
        'local_endereco',
        'imagem_capa',
        'tema',
        'cor_primaria',
        'cor_secundaria',
        'quem_paga_taxa',
        'percentual_taxa',
        'meta_valor',
        'limite_convidados',
        'pix_chave',
        'pix_tipo',
        'pix_nome',
        'permite_rsvp',
        'permite_recados',
        'exibir_valores',
        'arquivado',
        'status',
        'publicado_em',
    ];

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'usuario_id'     => 'required|integer',
        'titulo'         => 'required|min_length[3]|max_length[180]',
        // A unicidade do slug é garantida por EventoService::gerarSlugUnico()
        // + índice UNIQUE da tabela (o placeholder {id} não é preenchido no update).
        'slug'           => 'required|alpha_dash|max_length[160]',
        'tipo_evento'    => 'required|in_list[casamento,cha_bebe,cha_fraldas,cha_panela,aniversario,formatura,corporativo,outro]',
        'quem_paga_taxa' => 'required|in_list[convidado,organizador]',
        'status'         => 'required|in_list[rascunho,publicado,encerrado]',
    ];

    /**
     * Mantém a lista de tipos válidos sincronizada com o catálogo central.
     */
    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        $this->validationRules['tipo_evento'] =
            'required|in_list[' . implode(',', TipoEventoService::chaves()) . ']';
    }

    public function buscarPorSlug(string $slug): ?Evento
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Isolamento de tenant: sempre filtra pelo organizador dono.
     *
     * @return list<Evento>
     */
    public function doOrganizador(int $usuarioId): array
    {
        return $this->where('usuario_id', $usuarioId)
            ->orderBy('criado_em', 'DESC')
            ->findAll();
    }

    /**
     * Listagem administrativa (SuperAdmin) de TODAS as listas da plataforma,
     * com filtros e paginação. Traz o nome/e-mail do organizador como campos
     * extras (organizador_nome / organizador_email) na própria entidade.
     *
     * @param array{busca?: string|null, status?: string|null, arquivado?: string|null, tipo_evento?: string|null, organizador?: string|int|null} $filtros
     * @return list<Evento>
     */
    public function paginarAdmin(array $filtros = [], int $porPagina = 20): array
    {
        $this->select('eventos.*, u.nome AS organizador_nome, u.email AS organizador_email')
            ->join('usuarios u', 'u.id = eventos.usuario_id', 'left');

        if (! empty($filtros['busca'])) {
            $busca = (string) $filtros['busca'];
            $this->groupStart()
                ->like('eventos.titulo', $busca)
                ->orLike('eventos.slug', $busca)
                ->orLike('u.nome', $busca)
                ->orLike('u.email', $busca)
                ->groupEnd();
        }

        if (! empty($filtros['status'])) {
            $this->where('eventos.status', $filtros['status']);
        }

        if (isset($filtros['arquivado']) && $filtros['arquivado'] !== '' && $filtros['arquivado'] !== null) {
            $this->where('eventos.arquivado', (int) $filtros['arquivado']);
        }

        if (! empty($filtros['tipo_evento'])) {
            $this->where('eventos.tipo_evento', $filtros['tipo_evento']);
        }

        if (! empty($filtros['organizador'])) {
            $this->where('eventos.usuario_id', (int) $filtros['organizador']);
        }

        return $this->orderBy('eventos.criado_em', 'DESC')->paginate($porPagina, 'listas');
    }
}
