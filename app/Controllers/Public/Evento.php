<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Entities\Evento as EventoEntity;
use App\Models\GaleriaModel;
use App\Models\MuralRecadoModel;
use App\Models\PresenteEventoModel;
use App\Models\RsvpConfirmacaoModel;
use App\Services\ConvidadoService;
use App\Services\EventoService;

/**
 * Interface pública do evento, acessada pelo slug (/e/{slug}).
 * Não exige autenticação — é o canal do convidado.
 */
class Evento extends BaseController
{
    public function show($slug = null)
    {
        $evento    = $this->buscarPublicado($slug);
        $presentes = (new PresenteEventoModel())->ativosDoEvento((int) $evento->id);
        $recados   = (new MuralRecadoModel())->publicadosDoEvento((int) $evento->id);
        $galeria   = (new GaleriaModel())->ativosDoEvento((int) $evento->id);

        $db = db_connect();

        $arrecadado = (float) $db->table('pedidos')
            ->selectSum('valor_total', 'total')
            ->where('evento_id', $evento->id)
            ->where('status', 'pago')
            ->get()->getRow()->total;

        $confirmados = (int) $db->table('rsvp_confirmacoes')
            ->where('evento_id', $evento->id)
            ->where('status', 'confirmado')
            ->countAllResults();

        $cotasTotal = 0;
        $cotasVendidas = 0;

        foreach ($presentes as $presente) {
            $cotasTotal    += (int) $presente['quantidade_meta'];
            $cotasVendidas += (int) $presente['quantidade_vendida'];
        }

        $horario = ! empty($evento->horario) ? substr((string) $evento->horario, 0, 5) : null;

        $convidados    = new ConvidadoService();
        $rsvpEncerrado = $evento->permite_rsvp && $convidados->limiteAtingido($evento);
        $maxAcompanhantes = 20;

        if ($evento->limite_convidados !== null) {
            $resumo = $convidados->resumo($evento);
            $maxAcompanhantes = min(20, max(0, (int) $resumo['vagas'] - 1));
        }

        // SEO: descrição, imagem de compartilhamento (capa) e dados estruturados.
        $capaUrl = ! empty($evento->imagem_capa)
            ? (str_starts_with((string) $evento->imagem_capa, 'http')
                ? (string) $evento->imagem_capa
                : base_url((string) $evento->imagem_capa))
            : null;

        $horaEvento    = ! empty($evento->horario) ? substr((string) $evento->horario, 0, 5) : '00:00';
        $dataIsoSchema = $evento->data_evento !== null
            ? $evento->data_evento->format('Y-m-d') . 'T' . $horaEvento . ':00-03:00'
            : null;

        $descricaoEvento = trim(
            (! empty($evento->subtitulo) ? $evento->subtitulo . '. ' : '')
            . 'Lista de presentes de ' . $evento->titulo . ' — presenteie pelo site com PIX.'
        );

        $schemaEvento = array_filter([
            '@context'            => 'https://schema.org',
            '@type'               => 'Event',
            'name'                => $evento->titulo,
            'startDate'           => $dataIsoSchema,
            'description'         => $evento->subtitulo ?: null,
            'image'               => $capaUrl,
            'url'                 => site_url($evento->slug),
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'eventStatus'         => 'https://schema.org/EventScheduled',
            'location'            => ! empty($evento->local_nome) ? ['@type' => 'Place', 'name' => $evento->local_nome] : null,
            'organizer'           => ['@type' => 'Organization', 'name' => 'Minha Lista VIP', 'url' => base_url()],
        ], static fn ($valor) => $valor !== null);

        return view('hotsite/lista', [
            'titulo'    => $evento->titulo,
            'evento'    => $evento,
            'presentes' => $presentes,
            'recados'   => $recados,
            'galeria'   => $galeria,
            'modo'      => 'real',
            'escuro'    => false,
            'dataTexto' => $evento->data_evento !== null
                ? $evento->data_evento->format('d/m/Y') . ($horario !== null ? ' às ' . $horario : '')
                : null,
            'dataIso'   => $evento->data_evento !== null
                ? $evento->data_evento->format('Y-m-d') . ' ' . (! empty($evento->horario) ? (string) $evento->horario : '00:00:00')
                : null,
            'stats'     => [
                'cotas_total'    => $cotasTotal,
                'cotas_vendidas' => $cotasVendidas,
                'arrecadado'     => $arrecadado,
                'confirmados'    => $confirmados,
            ],
            'rsvpEncerrado'     => $rsvpEncerrado,
            'maxAcompanhantes'  => $maxAcompanhantes,
            'seo'               => [
                'descricao' => $descricaoEvento,
                'imagem'    => $capaUrl,
                'tipo'      => 'website',
                'url'       => site_url($evento->slug),
                'jsonld'    => [$schemaEvento],
            ],
        ]);
    }

    public function rsvp($slug = null)
    {
        $evento = $this->buscarPublicado($slug);

        if (! service('throttler')->check('rsvp_' . md5($this->request->getIPAddress()), 20, 600)) {
            return redirect()->to(site_url($evento->slug) . '#presenca')
                ->with('erro', 'Muitas confirmações em pouco tempo. Aguarde alguns minutos e tente novamente.');
        }

        if (! $evento->permite_rsvp) {
            return redirect()->to(site_url($evento->slug))
                ->with('erro', 'Este evento não está recebendo confirmações de presença.');
        }

        $querIr     = $this->request->getPost('status') !== 'recusado';
        $convidados = new ConvidadoService();

        if ($querIr && $convidados->limiteAtingido($evento)) {
            return redirect()->to(site_url($evento->slug) . '#presenca')
                ->with('erro', 'As confirmações estão encerradas: o limite de convidados foi atingido.');
        }

        // Acompanhantes: nome completo e categoria (adulto/criança/bebê) —
        // obrigatórios apenas para quem vai comparecer.
        $validacao = $querIr
            ? $convidados->validarAcompanhantes(
                (array) $this->request->getPost('acompanhantes_nome'),
                (array) $this->request->getPost('acompanhantes_categoria')
            )
            : ['linhas' => [], 'erros' => []];

        if ($validacao['erros'] !== []) {
            return redirect()->to(site_url($evento->slug) . '#presenca')
                ->withInput()
                ->with('erros', $validacao['erros']);
        }

        $linhas = $validacao['linhas'];

        $model = new RsvpConfirmacaoModel();

        $dados = [
            'evento_id'                 => $evento->id,
            'nome'                      => (string) $this->request->getPost('nome'),
            'email'                     => $this->request->getPost('email') ?: null,
            'telefone'                  => $this->request->getPost('telefone') ?: null,
            'quantidade_acompanhantes'  => count($linhas),
            // Quem vai entra como PENDENTE (aguardando homologação do organizador).
            'status'                    => $querIr ? 'pendente' : 'recusado',
            'observacao'                => $this->request->getPost('observacao') ?: null,
        ];

        if (! $model->insert($dados)) {
            return redirect()->to(site_url($evento->slug) . '#presenca')
                ->withInput()
                ->with('erros', $model->errors());
        }

        if ($linhas !== []) {
            $convidados->gravarAcompanhantes((int) $model->getInsertID(), $linhas);
        }

        return redirect()->to(site_url($evento->slug) . '#presenca')
            ->with('sucesso', $querIr
                ? 'Confirmação enviada! Ela será exibida após a aprovação do organizador.'
                : 'Resposta registrada. Sentiremos sua falta!');
    }

    public function recado($slug = null)
    {
        $evento = $this->buscarPublicado($slug);

        if (! service('throttler')->check('recado_' . md5($this->request->getIPAddress()), 20, 600)) {
            return redirect()->to(site_url($evento->slug))
                ->with('erro', 'Muitos recados em pouco tempo. Aguarde alguns minutos e tente novamente.');
        }

        if (! $evento->permite_recados) {
            return redirect()->to(site_url($evento->slug))
                ->with('erro', 'Este evento não está recebendo recados.');
        }

        $nome     = trim((string) $this->request->getPost('nome_autor'));
        $mensagem = trim((string) $this->request->getPost('mensagem'));

        if ($nome === '' || $mensagem === '') {
            return redirect()->to(site_url($evento->slug))
                ->with('erro', 'Informe seu nome e escreva um recado.');
        }

        // Regra: recado só vai ao ar após aprovação/publicação.
        (new MuralRecadoModel())->insert([
            'evento_id'  => $evento->id,
            'nome_autor' => $nome,
            'mensagem'   => $mensagem,
            'status'     => 'pendente',
        ]);

        return redirect()->to(site_url($evento->slug))
            ->with('sucesso', 'Recado enviado! Ele será exibido após aprovação do organizador.');
    }

    /**
     * Retorna o evento público ou lança 404 (rascunhos não são visíveis).
     */
    protected function buscarPublicado($slug): EventoEntity
    {
        return (new EventoService())->publicadoPorSlug((string) $slug);
    }
}
