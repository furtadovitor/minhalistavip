<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventoModel;
use App\Models\UsuarioModel;
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
        $filtros = [
            'busca'       => $this->request->getGet('busca') ?: null,
            'status'      => $this->request->getGet('status') ?: null,
            'arquivado'   => $this->request->getGet('arquivado'),
            'tipo_evento' => $this->request->getGet('tipo_evento') ?: null,
            'organizador' => $this->request->getGet('organizador') ?: null,
        ];

        $listas = $this->eventos->paginarAdmin($filtros, 20);

        // Mantém os filtros ao navegar entre as páginas.
        $this->eventos->pager->only(['busca', 'status', 'arquivado', 'tipo_evento', 'organizador']);

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
