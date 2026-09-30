<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * Upload de imagens (capa do evento, imagem do presente, item do catálogo).
 * Centraliza validação, nome aleatório e remoção do arquivo anterior.
 */
class UploadService
{
    /** Tamanho máximo em bytes (2 MB). */
    public const TAMANHO_MAXIMO = 2097152;

    /**
     * @var array<string, string>
     */
    private const MIMES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Processa um upload de imagem.
     *
     * @param string $subpasta Subpasta dentro de uploads/ (ex.: "eventos", "presentes", "catalogo").
     * @param string|null $atual Caminho relativo atual (será removido ao substituir).
     * @return array{0: string|null, 1: string|null} [novo caminho, mensagem de erro]
     */
    public function imagem(?UploadedFile $arquivo, string $subpasta, ?string $atual = null): array
    {
        $subpasta = trim($subpasta, '/');

        if ($arquivo === null || $arquivo->getError() === UPLOAD_ERR_NO_FILE) {
            return [$atual, null];
        }

        if (! $arquivo->isValid()) {
            return [$atual, 'Não foi possível receber a imagem enviada.'];
        }

        $mime = (string) $arquivo->getMimeType();

        if (! isset(self::MIMES[$mime])) {
            return [$atual, 'A imagem deve ser JPG, PNG ou WEBP.'];
        }

        if ($arquivo->getSize() > self::TAMANHO_MAXIMO) {
            return [$atual, 'A imagem deve ter no máximo 2 MB.'];
        }

        $destino = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $subpasta;

        if (! is_dir($destino) && ! mkdir($destino, 0o775, true) && ! is_dir($destino)) {
            return [$atual, 'Não foi possível preparar o diretório de uploads.'];
        }

        $nome = $arquivo->getRandomName();

        if (! $arquivo->move($destino, $nome)) {
            return [$atual, 'Não foi possível salvar a imagem enviada.'];
        }

        $this->apagar($atual, $subpasta);

        return ['uploads/' . $subpasta . '/' . $nome, null];
    }

    /**
     * Remove um arquivo de imagem previamente salvo por este serviço.
     */
    public function apagar(?string $caminho, string $subpasta): void
    {
        $subpasta = trim($subpasta, '/');
        $caminho   = (string) $caminho;

        if ($caminho === '' || ! str_starts_with($caminho, 'uploads/' . $subpasta . '/')) {
            return;
        }

        $absoluto = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $caminho);

        if (is_file($absoluto)) {
            unlink($absoluto);
        }
    }
}
