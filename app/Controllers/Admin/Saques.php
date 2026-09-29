<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\CarteiraService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Gestão dos saques solicitados pelos organizadores (SuperAdmin).
 *
 * Fluxo: solicitado → processando → pago, ou recusado (com estorno à carteira).
 */
class Saques extends BaseController
{
    protected CarteiraService $carteira;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->carteira = new CarteiraService();
    }

    public function index()
    {
        $filtros = ['status' => $this->request->getGet('status') ?: null];

        return $this->render('admin/saques/index', [
            'titulo'  => 'Saques',
            'saques'  => $this->carteira->saquesAdmin($filtros),
            'resumo'  => $this->carteira->resumoSaques(),
            'filtros' => $filtros,
        ]);
    }

    public function processar($id = null)
    {
        return $this->responder($this->carteira->alterarStatusSaque((int) $id, 'processando'));
    }

    public function pagar($id = null)
    {
        return $this->responder($this->carteira->alterarStatusSaque((int) $id, 'pago'));
    }

    public function recusar($id = null)
    {
        $motivo = trim((string) $this->request->getPost('motivo'));

        return $this->responder($this->carteira->alterarStatusSaque((int) $id, 'recusado', $motivo !== '' ? $motivo : null));
    }

    /**
     * @param array{ok: bool, mensagem: string} $resultado
     */
    private function responder(array $resultado)
    {
        return redirect()->to(site_url('admin/saques'))
            ->with($resultado['ok'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }
}
