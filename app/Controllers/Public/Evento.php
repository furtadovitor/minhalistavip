<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Entities\Evento as EventoEntity;
use App\Models\MuralRecadoModel;
use App\Models\PresenteEventoModel;
use App\Models\RsvpConfirmacaoModel;
use App\Services\EventoService;

/**
 * Interface pública do evento, acessada pelo slug (/e/{slug}).
 * Não exige autenticação — é o canal do convidado.
 */
class Evento extends BaseController
{
    public function show($slug = null)
    {
        $evento = $this->buscarPublicado($slug);

        return $this->render('public/evento', [
            'titulo'    => $evento->titulo,
            'evento'    => $evento,
            'presentes' => (new PresenteEventoModel())->ativosDoEvento((int) $evento->id),
            'recados'   => (new MuralRecadoModel())->publicadosDoEvento((int) $evento->id),
        ]);
    }

    public function rsvp($slug = null)
    {
        $evento = $this->buscarPublicado($slug);

        if (! $evento->permite_rsvp) {
            return redirect()->to(site_url($evento->slug))
                ->with('erro', 'Este evento não está recebendo confirmações de presença.');
        }

        $model = new RsvpConfirmacaoModel();

        $dados = [
            'evento_id'                 => $evento->id,
            'nome'                      => (string) $this->request->getPost('nome'),
            'email'                     => $this->request->getPost('email') ?: null,
            'telefone'                  => $this->request->getPost('telefone') ?: null,
            'quantidade_acompanhantes'  => (int) $this->request->getPost('quantidade_acompanhantes'),
            'status'                    => $this->request->getPost('status') === 'recusado' ? 'recusado' : 'confirmado',
            'observacao'                => $this->request->getPost('observacao') ?: null,
        ];

        if (! $model->insert($dados)) {
            return redirect()->to(site_url($evento->slug))
                ->with('erros', $model->errors());
        }

        return redirect()->to(site_url($evento->slug))
            ->with('sucesso', 'Presença registrada. Obrigado!');
    }

    public function recado($slug = null)
    {
        $evento = $this->buscarPublicado($slug);

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
