<?php

namespace App\Models;

use App\Entities\Evento;
use CodeIgniter\Model;

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
}
