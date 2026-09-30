<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ConfiguracaoService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Configurações globais da plataforma (taxas, PIX, parâmetros de saque).
 */
class Configuracoes extends BaseController
{
    protected ConfiguracaoService $config;

    /**
     * Chaves editáveis, por grupo.
     *
     * @var array<string, list<string>>
     */
    private const CHAVES = [
        'geral'      => ['nome_plataforma', 'email_suporte', 'moeda'],
        'financeiro' => ['percentual_taxa_padrao', 'saque_valor_minimo'],
        'pix'        => [
            'pix_gateway',
            'pix_chave_plataforma',
            'pix_nome_plataforma',
            'pix_cidade_plataforma',
            'pix_expiracao_minutos',
            'pix_webhook_token',
            'mercadopago_access_token',
            'mercadopago_webhook_secret',
            'mercadopago_notification_url',
        ],
    ];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->config = new ConfiguracaoService();
    }

    public function index()
    {
        return $this->render('admin/configuracoes/index', [
            'titulo' => 'Configurações',
            'grupos' => $this->config->todas(),
        ]);
    }

    public function salvar()
    {
        foreach (self::CHAVES as $grupo => $chaves) {
            foreach ($chaves as $chave) {
                $valor = $this->request->getPost($chave);

                if ($valor === null) {
                    continue;
                }

                $this->config->definir($chave, trim((string) $valor), $grupo);
            }
        }

        return redirect()->to(site_url('admin/configuracoes'))
            ->with('sucesso', 'Configurações salvas com sucesso.');
    }
}
