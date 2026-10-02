<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ConfiguracaoService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Psr\Log\LoggerInterface;
use Throwable;

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
            'mercadopago_public_key',
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
        $aviso = $this->validarCredenciaisMercadoPago();

        if ($aviso !== null) {
            return redirect()->to(site_url('admin/configuracoes'))
                ->withInput()
                ->with('erro', $aviso);
        }

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

    /**
     * Confere se o Access Token e a Public Key do Mercado Pago são coerentes
     * (mesmo ambiente). Evita o erro `401 Unauthorized use of live credentials`
     * causado por misturar um token de usuário de teste com uma Public Key de
     * produção (ou vice-versa).
     */
    private function validarCredenciaisMercadoPago(): ?string
    {
        $token = trim((string) $this->request->getPost('mercadopago_access_token'));
        $publicKey = trim((string) $this->request->getPost('mercadopago_public_key'));

        if ($token === '' || $publicKey === '') {
            return null;
        }

        try {
            $resposta = Services::curlrequest()->request('GET', 'https://api.mercadopago.com/users/me', [
                'headers'     => ['Authorization' => 'Bearer ' . $token],
                'http_errors' => false,
                'timeout'     => 10,
            ]);
        } catch (Throwable $e) {
            log_message('warning', 'Não foi possível validar as credenciais do Mercado Pago: ' . $e->getMessage());

            return null;
        }

        $status = $resposta->getStatusCode();

        if ($status !== 200) {
            return 'O Access Token do Mercado Pago parece inválido (HTTP ' . $status . '). Confira em Suas integrações.';
        }

        $usuario    = json_decode((string) $resposta->getBody(), true);
        $ehTeste    = is_array($usuario) && (bool) ($usuario['test_data']['test_user'] ?? false);
        $tokenTeste = str_starts_with($token, 'TEST-');
        $pkTeste    = str_starts_with($publicKey, 'TEST-');

        if (($ehTeste || $tokenTeste) && ! $pkTeste) {
            return 'Credenciais inconsistentes: o Access Token é de teste, mas a Public Key não é (deve começar com TEST-). Use o par de teste do mesmo aplicativo.';
        }

        if (! $ehTeste && ! $tokenTeste && $pkTeste) {
            return 'Credenciais inconsistentes: o Access Token é de produção, mas a Public Key é de teste (TEST-). Use credenciais do mesmo ambiente.';
        }

        return null;
    }
}
