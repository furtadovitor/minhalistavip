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
            'acompanhantesResumo' => $this->convidados->resumoAcompanhantes((int) $evento->id),
            'convidados' => $this->convidados->listar((int) $evento->id, $filtros),
            'filtros'    => $filtros,
        ]);
    }

    /**
     * Detalhe do convidado: acompanhantes com nome e categoria.
     */
    public function ver($eventoId = null, $id = null)
    {
        $evento    = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());
        $convidado = $this->convidados->um((int) $evento->id, (int) $id);

        return $this->render('host/convidados/ver', [
            'titulo'       => 'Convidado: ' . $convidado['nome'],
            'evento'       => $evento,
            'convidado'    => $convidado,
            'acompanhantes' => $this->convidados->acompanhantes((int) $convidado['id']),
        ]);
    }

    public function adicionarAcompanhante($eventoId = null, $id = null)
    {
        $evento    = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());
        $resultado = $this->convidados->adicionarAcompanhante((int) $evento->id, (int) $id, [
            'nome'      => $this->request->getPost('nome'),
            'categoria' => $this->request->getPost('categoria'),
        ]);

        return $this->responderConvidado((int) $evento->id, (int) $id, $resultado);
    }

    public function removerAcompanhante($eventoId = null, $id = null, $acompanhanteId = null)
    {
        $evento    = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());
        $resultado = $this->convidados->removerAcompanhante((int) $evento->id, (int) $id, (int) $acompanhanteId);

        return $this->responderConvidado((int) $evento->id, (int) $id, $resultado);
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

        $linhas = ['Nome;E-mail;Telefone;Acompanhantes;Pessoas;Menores;Maiores;Nome dos acompanhantes (categoria);Status;Observacao;Check-in em;Enviado em'];

        foreach ($lista as $c) {
            $acompanhantes = $this->convidados->acompanhantes((int) $c['id']);
            $nomes         = [];
            $menores       = 0;
            $maiores       = 0;

            foreach ($acompanhantes as $a) {
                $categoria = $a['categoria'] ?? null;

                $nomes[] = $a['nome'] . ' (' . rotulo_categoria_acompanhante($categoria) . ')';

                $ehMenor = $categoria !== null
                    ? $categoria !== 'adulto'
                    : (int) ($a['menor'] ?? 0) === 1;

                if ($ehMenor) {
                    $menores++;
                } else {
                    $maiores++;
                }
            }

            $linhas[] = implode(';', [
                $this->csv((string) $c['nome']),
                $this->csv((string) $c['email']),
                $this->csv((string) $c['telefone']),
                (int) $c['quantidade_acompanhantes'],
                (int) $c['quantidade_acompanhantes'] + 1,
                $menores,
                $maiores,
                $this->csv(implode(', ', $nomes)),
                $c['status'],
                $this->csv((string) $c['observacao']),
                $c['check_in_em'] !== null ? date('d/m/Y H:i', strtotime((string) $c['check_in_em'])) : '',
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

    /**
     * @param array{ok: bool, mensagem: string} $resultado
     */
    private function responderConvidado(int $eventoId, int $convidadoId, array $resultado)
    {
        return redirect()->to(site_url('painel/eventos/' . $eventoId . '/convidados/' . $convidadoId))
            ->with($resultado['ok'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }

    private function csv(string $valor): string
    {
        return str_replace(["\r", "\n", ';'], [' ', ' ', ','], $valor);
    }
}
