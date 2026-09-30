<?php

namespace App\Controllers\Host;

use App\Controllers\BaseController;
use App\Models\MuralRecadoModel;
use App\Services\EventoService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Mural de recados do evento: moderação (publicar, ocultar, excluir).
 */
class Recados extends BaseController
{
    protected EventoService $eventos;

    protected MuralRecadoModel $recados;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->eventos = new EventoService();
        $this->recados = new MuralRecadoModel();
    }

    public function index($eventoId = null)
    {
        $evento  = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());
        $filtros = [
            'status' => $this->request->getGet('status') ?: null,
            'busca'  => $this->request->getGet('busca') ?: null,
        ];

        return $this->render('host/recados/index', [
            'titulo'  => 'Recadinhos',
            'evento'  => $evento,
            'recados' => $this->recados->doEvento((int) $evento->id, $filtros),
            'filtros' => $filtros,
        ]);
    }

    public function publicar($eventoId = null, $id = null)
    {
        return $this->alterarStatus((int) $eventoId, (int) $id, 'publicado', 'Recado publicado no mural.');
    }

    public function ocultar($eventoId = null, $id = null)
    {
        return $this->alterarStatus((int) $eventoId, (int) $id, 'oculto', 'Recado ocultado do mural.');
    }

    public function excluir($eventoId = null, $id = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        if ($this->recados->um((int) $evento->id, (int) $id) === null) {
            return $this->voltar((int) $evento->id, 'erro', 'Recado não encontrado.');
        }

        $this->recados->delete((int) $id);

        return $this->voltar((int) $evento->id, 'sucesso', 'Recado removido.');
    }

    private function alterarStatus(int $eventoId, int $id, string $status, string $mensagem)
    {
        $evento = $this->eventos->doOrganizador($eventoId, $this->usuarioId());

        if ($this->recados->um((int) $evento->id, $id) === null) {
            return $this->voltar((int) $evento->id, 'erro', 'Recado não encontrado.');
        }

        $this->recados->update($id, [
            'status'       => $status,
            'publicado_em' => $status === 'publicado' ? date('Y-m-d H:i:s') : null,
        ]);

        return $this->voltar((int) $evento->id, 'sucesso', $mensagem);
    }

    private function voltar(int $eventoId, string $tipo, string $mensagem)
    {
        return redirect()->to(site_url('painel/eventos/' . $eventoId . '/recadinhos'))->with($tipo, $mensagem);
    }
}
