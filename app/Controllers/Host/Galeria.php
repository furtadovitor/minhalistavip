<?php

namespace App\Controllers\Host;

use App\Controllers\BaseController;
use App\Models\GaleriaModel;
use App\Services\EventoService;
use App\Services\UploadService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Galeria de fotos do evento (isolamento de tenant via EventoService).
 */
class Galeria extends BaseController
{
    protected EventoService $eventos;

    protected GaleriaModel $galeria;

    protected UploadService $upload;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->eventos = new EventoService();
        $this->galeria = new GaleriaModel();
        $this->upload  = new UploadService();
    }

    public function index($eventoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        return $this->render('host/galeria/index', [
            'titulo'  => 'Galeria',
            'evento'  => $evento,
            'fotos'   => $this->galeria->doEvento((int) $evento->id),
        ]);
    }

    public function upload($eventoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());
        $legenda = $this->texto('legenda') ?: null;

        $arquivos = $this->request->getFileMultiple('imagens') ?? [];
        $enviadas = 0;
        $erros    = [];

        foreach ($arquivos as $arquivo) {
            if ($arquivo === null || $arquivo->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            [$caminho, $erro] = $this->upload->imagem($arquivo, 'galeria');

            if ($erro !== null || $caminho === null) {
                $erros[] = $erro ?? 'Falha ao enviar a imagem.';

                continue;
            }

            $this->galeria->insert([
                'evento_id' => (int) $evento->id,
                'imagem'    => $caminho,
                'legenda'   => $legenda,
                'ativo'     => 1,
                'ordem'     => 0,
            ]);

            $enviadas++;
        }

        $redirect = redirect()->to($this->urlGaleria((int) $evento->id));

        if ($enviadas > 0) {
            $redirect->with('sucesso', $enviadas . ' foto(s) adicionada(s) à galeria.');
        }

        if ($erros !== []) {
            $redirect->with('erro', implode(' ', array_unique($erros)));
        }

        return $redirect;
    }

    public function atualizar($eventoId = null, $fotoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());

        if ($this->galeria->um((int) $evento->id, (int) $fotoId) === null) {
            return redirect()->to($this->urlGaleria((int) $evento->id))->with('erro', 'Foto não encontrada.');
        }

        $this->galeria->update((int) $fotoId, [
            'legenda' => $this->texto('legenda') ?: null,
            'ordem'   => (int) $this->request->getPost('ordem'),
        ]);

        return redirect()->to($this->urlGaleria((int) $evento->id))->with('sucesso', 'Foto atualizada.');
    }

    public function alternar($eventoId = null, $fotoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());
        $foto   = $this->galeria->um((int) $evento->id, (int) $fotoId);

        if ($foto === null) {
            return redirect()->to($this->urlGaleria((int) $evento->id))->with('erro', 'Foto não encontrada.');
        }

        $this->galeria->update((int) $fotoId, ['ativo' => (int) $foto['ativo'] === 1 ? 0 : 1]);

        return redirect()->to($this->urlGaleria((int) $evento->id))->with('sucesso', 'Visibilidade atualizada.');
    }

    public function excluir($eventoId = null, $fotoId = null)
    {
        $evento = $this->eventos->doOrganizador((int) $eventoId, $this->usuarioId());
        $foto   = $this->galeria->um((int) $evento->id, (int) $fotoId);

        if ($foto === null) {
            return redirect()->to($this->urlGaleria((int) $evento->id))->with('erro', 'Foto não encontrada.');
        }

        $this->galeria->delete((int) $fotoId);
        $this->upload->apagar($foto['imagem'] ?? null, 'galeria');

        return redirect()->to($this->urlGaleria((int) $evento->id))->with('sucesso', 'Foto removida.');
    }

    private function texto(string $campo): string
    {
        return trim((string) $this->request->getPost($campo));
    }

    private function urlGaleria(int $eventoId): string
    {
        return site_url('painel/eventos/' . $eventoId . '/galeria');
    }
}
