<?php

namespace App\Controllers\Host;

use App\Controllers\BaseController;
use App\Services\ConvidadoService;
use App\Services\EventoService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Lista de convidados (RSVP) do evento, com homologação e limite.
 * Todo acesso passa por EventoService::doOrganizador() (isolamento de tenant).
 */
class Convidados extends BaseController
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
        $evento  = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());
        $filtros = [
            'status' => $this->request->getGet('status') ?: null,
            'busca'  => $this->request->getGet('busca') ?: null,
        ];

        return $this->render('host/convidados/index', [
            'titulo'     => 'Lista de convidados',
            'evento'     => $evento,
            'resumo'     => $this->convidados->resumo($evento),
            'convidados' => $this->convidados->listar((int) $evento->id, $filtros),
            'filtros'    => $filtros,
        ]);
    }

    public function adicionar($eventoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        $resultado = $this->convidados->adicionar((int) $evento->id, [
            'nome'                     => $this->request->getPost('nome'),
            'email'                    => $this->request->getPost('email'),
            'telefone'                 => $this->request->getPost('telefone'),
            'quantidade_acompanhantes' => $this->request->getPost('quantidade_acompanhantes'),
            'observacao'               => $this->request->getPost('observacao'),
        ], $evento);

        return $this->responder((int) $evento->id, $resultado);
    }

    public function aprovar($eventoId = null, $id = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        return $this->responder((int) $evento->id, $this->convidados->aprovar((int) $evento->id, (int) $id, $evento));
    }

    public function recusar($eventoId = null, $id = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        return $this->responder((int) $evento->id, $this->convidados->recusar((int) $evento->id, (int) $id));
    }

    public function remover($eventoId = null, $id = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        return $this->responder((int) $evento->id, $this->convidados->remover((int) $evento->id, (int) $id));
    }

    /**
     * Exporta a lista em CSV (para planilha).
     */
    public function exportar($eventoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());
        $lista  = $this->convidados->listar((int) $evento->id);

        $linhas = ['Nome;E-mail;Telefone;Acompanhantes;Pessoas;Status;Observacao;Enviado em'];

        foreach ($lista as $c) {
            $linhas[] = implode(';', [
                $this->csv((string) $c['nome']),
                $this->csv((string) $c['email']),
                $this->csv((string) $c['telefone']),
                (int) $c['quantidade_acompanhantes'],
                (int) $c['quantidade_acompanhantes'] + 1,
                $c['status'],
                $this->csv((string) $c['observacao']),
                (string) $c['criado_em'],
            ]);
        }

        $csv = "\xEF\xBB\xBF" . implode("\r\n", $linhas);

        return $this->response->download('convidados-' . $evento->slug . '.csv', $csv);
    }

    /**
     * @param array{ok: bool, mensagem: string} $resultado
     */
    private function responder(int $eventoId, array $resultado)
    {
        return redirect()->to(site_url('painel/eventos/' . $eventoId . '/convidados'))
            ->with($resultado['ok'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }

    private function csv(string $valor): string
    {
        return str_replace(["\r", "\n", ';'], [' ', ' ', ','], $valor);
    }
}
