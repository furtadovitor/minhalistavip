<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\EventoModel;
use App\Models\PedidoModel;

/**
 * Busca do convidado: localiza a lista pelo link/slug do evento ou pelo
 * código do pedido (protocolo), redirecionando para a página correspondente.
 */
class Busca extends BaseController
{
    public function buscar()
    {
        $termo = trim((string) (
            $this->request->getPost('termo')
            ?? $this->request->getGet('termo')
            ?? ''
        ));

        if ($termo === '') {
            return redirect()->to(site_url('/') . '#buscar')
                ->with('erro', 'Informe o link da lista ou o código do seu pedido.');
        }

        // 1) Tenta como endereço (slug) de evento.
        $slug = $this->extrairSlug($termo);

        if ($slug !== null) {
            $evento = (new EventoModel())->buscarPorSlug($slug);

            if ($evento !== null && $evento->status !== 'rascunho') {
                return redirect()->to(site_url($evento->slug));
            }
        }

        // 2) Tenta como código de pedido (ex.: MLV260929ABCD12).
        $protocolo = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $termo));

        if ($protocolo !== '') {
            $pedido = (new PedidoModel())->porProtocolo($protocolo);

            if ($pedido !== null) {
                $evento = (new EventoModel())->find((int) $pedido->evento_id);

                if ($evento !== null) {
                    return redirect()->to(site_url($evento->slug . '/pedido/' . $pedido->protocolo));
                }
            }
        }

        return redirect()->to(site_url('/') . '#buscar')
            ->withInput()
            ->with('erro', 'Não encontramos uma lista com esse link/código. Confira e tente novamente.');
    }

    /**
     * Extrai o último segmento do link informado (o slug do evento).
     */
    private function extrairSlug(string $termo): ?string
    {
        $caminho = $termo;

        if (str_contains($termo, '://')) {
            $caminho = (string) parse_url($termo, PHP_URL_PATH);
        }

        $partes = array_values(array_filter(explode('/', trim($caminho, '/ '))));

        if ($partes === []) {
            return null;
        }

        $slug = (string) end($partes);

        return $slug !== '' ? $slug : null;
    }
}
