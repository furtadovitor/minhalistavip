<?php

namespace App\Controllers\Host;

use App\Controllers\BaseController;
use App\Services\ConvidadoService;
use App\Services\EventoService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Check-in presencial dos convidados confirmados (uso no dia do evento).
 * Todo acesso passa por EventoService::doOrganizador() (isolamento de tenant).
 */
class Checkin extends BaseController
{
    protected EventoService $eventos;

    protected ConvidadoService $convidados;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->eventos    = new EventoService();
        $this->convidados = new ConvidadoService();
    }

    public function index($eventoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        $busca           = trim((string) $this->request->getGet('busca'));
        $somenteAusentes = $this->request->getGet('ausentes') === '1';

        return $this->render('host/checkin/index', [
            'titulo'          => 'Check-in',
            'evento'          => $evento,
            'resumo'          => $this->convidados->resumo($evento),
            'confirmados'     => $this->convidados->listarParaCheckin((int) $evento->id, $busca, $somenteAusentes),
            'busca'           => $busca,
            'somenteAusentes' => $somenteAusentes,
            'acompanhantes'   => $this->convidados->resumoAcompanhantes((int) $evento->id),
        ]);
    }

    public function marcar($eventoId = null, $id = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        $resultado = $this->convidados->checkIn((int) $evento->id, (int) $id, $this->usuarioId());

        return $this->responder((int) $evento->id, $resultado);
    }

    public function desfazer($eventoId = null, $id = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        $resultado = $this->convidados->desfazerCheckIn((int) $evento->id, (int) $id);

        return $this->responder((int) $evento->id, $resultado);
    }

    /**
     * @param array{ok: bool, mensagem: string} $resultado
     */
    private function responder(int $eventoId, array $resultado)
    {
        $retorno = $this->request->getPost('retorno');
        $query   = is_string($retorno) ? trim($retorno) : '';

        $destino = site_url('painel/eventos/' . $eventoId . '/checkin');

        if ($query !== '') {
            $destino .= '?' . ltrim($query, '?');
        }

        return redirect()->to($destino)
            ->with($resultado['ok'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }
}
