<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Conciliação financeira da plataforma (visão global do SuperAdmin).
 */
class Financeiro extends BaseController
{
    protected BaseConnection $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->db = db_connect();
    }

    public function index()
    {
        $arrecadado = (float) $this->db->table('carteira_movimentacoes')
            ->selectSum('valor', 'total')->where('tipo', 'credito')->get()->getRow()->total;

        $taxas = abs((float) $this->db->table('carteira_movimentacoes')
            ->selectSum('valor', 'total')->where('tipo', 'taxa')->get()->getRow()->total);

        $saquesPagos = (float) $this->db->table('saques')
            ->selectSum('valor', 'total')->where('status', 'pago')->get()->getRow()->total;

        $saquesPendentes = (float) $this->db->table('saques')
            ->selectSum('valor', 'total')->whereIn('status', ['solicitado', 'processando'])->get()->getRow()->total;

        $pedidosPagos = (int) $this->db->table('pedidos')->where('status', 'pago')->countAllResults();

        $ultimos = $this->db->table('pedidos p')
            ->select('p.protocolo, p.nome_convidado, p.valor_total, p.valor_taxa, p.pago_em, p.quem_paga_taxa,
                      e.titulo AS evento_titulo, e.slug AS evento_slug, u.nome AS organizador_nome')
            ->join('eventos e', 'e.id = p.evento_id')
            ->join('usuarios u', 'u.id = e.usuario_id')
            ->where('p.status', 'pago')
            ->orderBy('p.pago_em', 'DESC')
            ->limit(20)
            ->get()->getResultArray();

        return $this->render('admin/financeiro/index', [
            'titulo'           => 'Financeiro',
            'arrecadado'       => $arrecadado,
            'taxas'            => $taxas,
            'saquesPagos'      => $saquesPagos,
            'saquesPendentes'  => $saquesPendentes,
            'pedidosPagos'     => $pedidosPagos,
            'ticketMedio'      => $pedidosPagos > 0 ? $arrecadado / $pedidosPagos : 0.0,
            'saldoPlataforma'  => $taxas - $saquesPagos,
            'ultimos'          => $ultimos,
        ]);
    }
}
