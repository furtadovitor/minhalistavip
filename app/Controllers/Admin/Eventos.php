<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventoModel;
use App\Models\UsuarioModel;
use App\Services\EventoService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Gestão das listas (eventos) de TODOS os organizadores, para o SuperAdmin.
 *
 * Diferente do painel do organizador (que é isolado por tenant), aqui o admin
 * enxerga a plataforma inteira, com filtros por status/situação e ações de
 * publicar/despublicar e arquivar/reativar.
 */
class Eventos extends BaseController
{
    protected EventoModel $eventos;

    protected UsuarioModel $usuarios;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->eventos  = new EventoModel();
        $this->usuarios = new UsuarioModel();
    }

    public function index()
    {
        $filtros = $this->filtrosDaRequisicao();

        $listas = $this->eventos->paginarAdmin($filtros, 20);

        // Mantém os filtros ao navegar entre as páginas.
        $this->eventos->pager->only(['busca', 'status', 'arquivado', 'tipo_evento', 'organizador', 'periodo']);

        return $this->render('admin/eventos/index', [
            'titulo'  => 'Listas da plataforma',
            'listas'  => $listas,
            'pager'   => $this->eventos->pager,
            'filtros' => $filtros,
            'resumo'  => $this->resumo(),
            'tipos'   => tipos_evento(),
            'orgs'    => $this->usuarios->where('nivel', 'organizador')->orderBy('nome', 'ASC')->findAll(),
        ]);
    }

    public function ver($id = null)
    {
        $evento = $this->buscar((int) $id);
        $db     = db_connect();
        $eventoId = (int) $evento->id;

        $dono = $this->usuarios->find((int) $evento->usuario_id);

        $pagos = $db->table('pedidos')
            ->selectSum('valor_presentes', 'presentes')
            ->selectSum('valor_taxa', 'taxas')
            ->selectSum('valor_total', 'total')
            ->where('evento_id', $eventoId)
            ->where('status', 'pago')
            ->get()
            ->getRowArray() ?? [];

        return $this->render('admin/eventos/ver', [
            'titulo' => 'Lista: ' . $evento->titulo,
            'evento' => $evento,
            'dono'   => $dono,
            'numeros' => [
                'presentes'     => $db->table('presentes_evento')->where('evento_id', $eventoId)->where('deletado_em', null)->countAllResults(),
                'convidados'    => $db->table('rsvp_confirmacoes')->where('evento_id', $eventoId)->countAllResults(),
                'confirmados'   => $db->table('rsvp_confirmacoes')->where('evento_id', $eventoId)->where('status', 'confirmado')->countAllResults(),
                'pedidos_pagos' => $db->table('pedidos')->where('evento_id', $eventoId)->where('status', 'pago')->countAllResults(),
                'arrecadado'    => (float) ($pagos['presentes'] ?? 0),
                'taxas'         => (float) ($pagos['taxas'] ?? 0),
            ],
        ]);
    }

    public function alternarPublicacao($id = null)
    {
        $evento     = $this->buscar((int) $id);
        $publicando = $evento->status !== 'publicado';

        if ($publicando) {
            $pendencias = (new EventoService())->pendenciasPublicacao($evento);

            if ($pendencias !== []) {
                return redirect()->back()->with('erros', array_merge(
                    ['Não é possível publicar esta lista. Itens pendentes:'],
                    $pendencias
                ));
            }
        }

        $this->eventos->update($evento->id, [
            'status'       => $publicando ? 'publicado' : 'rascunho',
            'publicado_em' => $publicando ? date('Y-m-d H:i:s') : null,
        ]);

        return redirect()->back()->with(
            'sucesso',
            $publicando ? 'Lista publicada.' : 'Lista despublicada e enviada para rascunhos.'
        );
    }

    public function alternarArquivado($id = null)
    {
        $evento = $this->buscar((int) $id);
        $novo   = ! ((bool) $evento->arquivado);

        $this->eventos->update($evento->id, ['arquivado' => $novo ? 1 : 0]);

        return redirect()->back()->with(
            'sucesso',
            $novo ? 'Lista arquivada.' : 'Lista reativada.'
        );
    }

    /**
     * Filtros vindos da querystring (compartilhados entre listagem e exportação).
     *
     * @return array<string, string|null>
     */
    private function filtrosDaRequisicao(): array
    {
        return [
            'busca'       => $this->request->getGet('busca') ?: null,
            'status'      => $this->request->getGet('status') ?: null,
            'arquivado'   => $this->request->getGet('arquivado'),
            'tipo_evento' => $this->request->getGet('tipo_evento') ?: null,
            'organizador' => $this->request->getGet('organizador') ?: null,
            'periodo'     => $this->request->getGet('periodo') ?: null,
        ];
    }

    /**
     * Exporta as listas filtradas em CSV (UTF-8 com BOM e ';' — abre certinho no Excel pt-BR).
     */
    public function exportar(): ResponseInterface
    {
        $filtros = $this->filtrosDaRequisicao();
        $linhas  = (new EventoModel())->listarAdmin($filtros);

        $csv  = "\xEF\xBB\xBF"; // BOM para o Excel reconhecer o UTF-8
        $csv .= "ID;Titulo;Slug;Organizador;Email;Tipo;Status;Situacao;Publicada em;Criada em\r\n";

        foreach ($linhas as $l) {
            $csv .= implode(';', [
                (int) $l->id,
                $this->csv($l->titulo),
                $this->csv('/' . $l->slug),
                $this->csv((string) ($l->organizador_nome ?? '')),
                $this->csv((string) ($l->organizador_email ?? '')),
                $this->csv(rotulo_tipo_evento((string) $l->tipo_evento)),
                rotulo_status_evento((string) $l->status),
                $l->arquivado ? 'Arquivada' : 'Ativa',
                $l->publicado_em !== null ? $l->publicado_em->format('d/m/Y H:i') : '',
                $l->criado_em !== null ? $l->criado_em->format('d/m/Y H:i') : '',
            ]) . "\r\n";
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="listas-' . date('Ymd-His') . '.csv"')
            ->setBody($csv);
    }

    /**
     * Aplica uma ação em massa (arquivar / reativar / publicar / despublicar).
     */
    public function acaoEmLote()
    {
        $ids  = array_values(array_filter(array_map('intval', (array) $this->request->getPost('ids'))));
        $acao = (string) $this->request->getPost('acao');

        if ($ids === []) {
            return redirect()->back()->with('erro', 'Selecione ao menos uma lista.');
        }

        $ids = array_slice($ids, 0, 500);

        // Publicação em lote respeita as mesmas regras do painel do organizador.
        if ($acao === 'publicar') {
            return $this->publicarEmLote($ids);
        }

        $acoes = [
            'arquivar'    => ['arquivado' => 1],
            'reativar'    => ['arquivado' => 0],
            'despublicar' => ['status' => 'rascunho', 'publicado_em' => null],
        ];

        if (! isset($acoes[$acao])) {
            return redirect()->back()->with('erro', 'Ação em lote inválida.');
        }

        $dados = $acoes[$acao] + ['atualizado_em' => date('Y-m-d H:i:s')];

        db_connect()->table('eventos')->whereIn('id', $ids)->update($dados);

        return redirect()->back()->with('sucesso', count($ids) . ' lista(s) atualizada(s).');
    }

    /**
     * Publica em lote apenas as listas que atendem às regras de publicação.
     *
     * @param list<int> $ids
     */
    private function publicarEmLote(array $ids)
    {
        $servico    = new EventoService();
        $eventos    = $this->eventos->whereIn('id', $ids)->findAll();
        $liberadas  = [];
        $bloqueadas = 0;

        foreach ($eventos as $evento) {
            if ($servico->podePublicar($evento)) {
                $liberadas[] = (int) $evento->id;
            } else {
                $bloqueadas++;
            }
        }

        if ($liberadas !== []) {
            $agora = date('Y-m-d H:i:s');

            db_connect()->table('eventos')->whereIn('id', $liberadas)->update([
                'status'        => 'publicado',
                'publicado_em'  => $agora,
                'atualizado_em' => $agora,
            ]);
        }

        if ($liberadas === []) {
            return redirect()->back()->with(
                'erro',
                'Nenhuma lista foi publicada: as selecionadas têm dados incompletos (modelo, tipo, data, local ou presentes).'
            );
        }

        $mensagem = count($liberadas) . ' lista(s) publicada(s).';

        if ($bloqueadas > 0) {
            $mensagem .= ' ' . $bloqueadas . ' não publicada(s) por dados incompletos.';
        }

        return redirect()->back()->with('sucesso', $mensagem);
    }

    private function csv(?string $valor): string
    {
        $valor = (string) $valor;

        if (preg_match('/[";\r\n]/', $valor) === 1) {
            return '"' . str_replace('"', '""', $valor) . '"';
        }

        return $valor;
    }

    /**
     * Resumo da plataforma para os cartões do topo.
     *
     * @return array<string, int>
     */
    private function resumo(): array
    {
        $db = db_connect();

        $contar = static function (array $where = []) use ($db): int {
            $builder = $db->table('eventos')->where('deletado_em', null);

            if ($where !== []) {
                $builder->where($where);
            }

            return $builder->countAllResults();
        };

        return [
            'total'      => $contar(),
            'publicadas' => $contar(['status' => 'publicado']),
            'rascunhos'  => $contar(['status' => 'rascunho']),
            'encerradas' => $contar(['status' => 'encerrado']),
            'ativas'     => $contar(['arquivado' => 0]),
            'arquivadas' => $contar(['arquivado' => 1]),
        ];
    }

    private function buscar(int $id): \App\Entities\Evento
    {
        $evento = $this->eventos->find($id);

        if ($evento === null) {
            throw PageNotFoundException::forPageNotFound('Lista não encontrada.');
        }

        return $evento;
    }
}
