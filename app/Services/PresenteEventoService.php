<?php

namespace App\Services;

use App\Models\CatalogoPresenteModel;
use App\Models\PresenteEventoModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Presentes do evento: cadastro customizado e clonagem do catálogo global
 * (Regra 2.3). Todo método valida o vínculo evento → organizador.
 */
class PresenteEventoService
{
    protected PresenteEventoModel $presentes;

    protected BaseConnection $db;

    public function __construct(?PresenteEventoModel $presentes = null, ?BaseConnection $db = null)
    {
        $this->presentes = $presentes ?? new PresenteEventoModel();
        $this->db        = $db ?? db_connect();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function doEvento(int $eventoId): array
    {
        return $this->presentes
            ->where('evento_id', $eventoId)
            ->orderBy('ordem', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PageNotFoundException
     */
    public function um(int $eventoId, int $presenteId): array
    {
        $presente = $this->presentes->find($presenteId);

        if ($presente === null || (int) $presente['evento_id'] !== $eventoId) {
            throw PageNotFoundException::forPageNotFound('Presente não encontrado.');
        }

        return $presente;
    }

    /**
     * @param array<string, mixed> $dados
     */
    public function criar(int $eventoId, array $dados): ?int
    {
        $dados['evento_id']          = $eventoId;
        $dados['quantidade_vendida'] = 0;
        $dados['ordem']              = $dados['ordem'] ?? $this->proximaOrdem($eventoId);

        if ($this->presentes->insert($dados) === false) {
            return null;
        }

        return (int) $this->presentes->getInsertID();
    }

    /**
     * @param array<string, mixed> $dados
     */
    public function atualizar(int $eventoId, int $presenteId, array $dados): bool
    {
        $this->um($eventoId, $presenteId);

        unset($dados['evento_id'], $dados['quantidade_vendida']);

        return (bool) $this->presentes->update($presenteId, $dados);
    }

    public function excluir(int $eventoId, int $presenteId): bool
    {
        $this->um($eventoId, $presenteId);

        return (bool) $this->presentes->delete($presenteId);
    }

    public function alternarAtivo(int $eventoId, int $presenteId): bool
    {
        $presente = $this->um($eventoId, $presenteId);

        return (bool) $this->presentes->update($presenteId, [
            'ativo' => ! empty($presente['ativo']) ? 0 : 1,
        ]);
    }

    /**
     * Clona itens do catálogo global para o evento, ignorando os já existentes.
     *
     * @param list<int> $catalogoIds
     * @return array{criados: int, ignorados: int}
     */
    public function clonarDoCatalogo(int $eventoId, array $catalogoIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $catalogoIds))));

        if ($ids === []) {
            return ['criados' => 0, 'ignorados' => 0];
        }

        $itens = (new CatalogoPresenteModel())->whereIn('id', $ids)->findAll();

        $ordem    = $this->proximaOrdem($eventoId);
        $criados  = 0;
        $ignorados = 0;

        foreach ($itens as $item) {
            $jaExiste = $this->presentes
                ->where('evento_id', $eventoId)
                ->where('catalogo_id', $item['id'])
                ->countAllResults();

            if ($jaExiste > 0) {
                $ignorados++;
                continue;
            }

            $this->presentes->insert([
                'evento_id'          => $eventoId,
                'catalogo_id'        => (int) $item['id'],
                'nome'               => $item['nome'],
                'descricao'          => $item['descricao'],
                'imagem'             => $item['imagem'],
                'tipo'               => $item['tipo'],
                'valor'              => $item['valor_sugerido'] ?? 0,
                'quantidade_meta'    => 1,
                'quantidade_vendida' => 0,
                'link_afiliado'      => $item['link_afiliado'],
                'ativo'              => 1,
                'ordem'              => $ordem++,
            ]);

            $criados++;
        }

        return ['criados' => $criados, 'ignorados' => $ignorados];
    }

    /**
     * IDs do catálogo já presentes no evento (para marcar na tela de clonagem).
     *
     * @return list<int>
     */
    public function idsCatalogoJaUsados(int $eventoId): array
    {
        $rows = $this->db->table('presentes_evento')
            ->select('catalogo_id')
            ->where('evento_id', $eventoId)
            ->where('catalogo_id IS NOT NULL')
            ->where('deletado_em IS NULL')
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row): int => (int) $row['catalogo_id'], $rows);
    }

    protected function proximaOrdem(int $eventoId): int
    {
        $row = $this->db->table('presentes_evento')
            ->selectMax('ordem', 'maior')
            ->where('evento_id', $eventoId)
            ->get()
            ->getRow();

        return ((int) ($row->maior ?? 0)) + 1;
    }

    /**
     * @return array<string, string>
     */
    public function erros(): array
    {
        return $this->presentes->errors();
    }
}
