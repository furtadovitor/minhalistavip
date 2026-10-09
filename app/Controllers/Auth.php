<?php

namespace App\Controllers;

use App\Entities\Usuario;
use App\Models\UsuarioModel;
use App\Services\AuthService;
use App\Services\SenhaService;

/**
 * Autenticação: login, logout e auto-cadastro de organizadores.
 */
class Auth extends BaseController
{
    public function login()
    {
        return view('auth/login', ['titulo' => 'Entrar']);
    }

    public function autenticar()
    {
        $regras = [
            'email' => 'required|valid_email',
        ];

        if (AuthService::EXIGIR_SENHA) {
            $regras['senha'] = 'required|min_length[6]';
        }

        if (! $this->validate($regras)) {
            $mensagem = AuthService::EXIGIR_SENHA
                ? 'Informe um e-mail válido e uma senha com pelo menos 6 caracteres.'
                : 'Informe um e-mail válido.';

            return redirect()->back()->withInput()->with('erro', $mensagem);
        }

        // Proteção contra força bruta: limita tentativas por IP (5 a cada 5 min).
        if (! service('throttler')->check('login_' . md5($this->request->getIPAddress()), 5, 300)) {
            return redirect()->back()->withInput()
                ->with('erro', 'Muitas tentativas de login. Aguarde alguns minutos e tente novamente.');
        }

        $email = (string) $this->request->getPost('email');
        $senha = (string) $this->request->getPost('senha');

        if (! $this->auth->tentarLogin($email, $senha)) {
            // Mensagem genérica: não revela se o e-mail existe.
            return redirect()->back()->withInput()
                ->with('erro', 'E-mail ou senha inválidos.');
        }

        $destino = $this->auth->destinoSeguro(session()->get('redirect_url'));
        session()->remove('redirect_url');

        return redirect()->to($destino)->with('sucesso', 'Bem-vindo(a) de volta!');
    }

    public function logout()
    {
        $this->auth->logout();

        return redirect()->to(site_url('/'))->with('sucesso', 'Você saiu da sua conta.');
    }

    /**
     * Formulário "Esqueci minha senha".
     */
    public function esqueciSenha()
    {
        return view('auth/esqueci_senha', ['titulo' => 'Esqueci minha senha']);
    }

    /**
     * Envia o link de redefinição. A resposta é sempre genérica.
     */
    public function enviarRecuperacao()
    {
        if (! service('throttler')->check('recuperar_' . md5($this->request->getIPAddress()), 5, 900)) {
            return redirect()->back()
                ->with('erro', 'Muitas solicitações. Aguarde alguns minutos e tente novamente.');
        }

        if (! $this->validate(['email' => 'required|valid_email'])) {
            return redirect()->back()->withInput()->with('erro', 'Informe um e-mail válido.');
        }

        (new SenhaService())->solicitar((string) $this->request->getPost('email'));

        return redirect()->to(site_url('login'))->with(
            'sucesso',
            'Se este e-mail estiver cadastrado, enviamos um link para redefinir a senha. Verifique também a caixa de spam.'
        );
    }

    /**
     * Formulário de nova senha (valida o token do link).
     */
    public function redefinirSenha($token = null)
    {
        $usuario = (new SenhaService())->usuarioPorToken((string) $token);

        if ($usuario === null) {
            return redirect()->to(site_url('login'))
                ->with('erro', 'Link inválido ou expirado. Solicite um novo.');
        }

        return view('auth/redefinir_senha', [
            'titulo' => 'Definir nova senha',
            'token'  => (string) $token,
        ]);
    }

    /**
     * Salva a nova senha e invalida o token.
     */
    public function salvarNovaSenha()
    {
        $token = (string) $this->request->getPost('token');

        $regras = [
            'senha'             => 'required|min_length[6]|max_length[72]',
            'senha_confirmacao' => 'required|matches[senha]',
        ];

        if (! $this->validate($regras)) {
            return redirect()->back()->withInput()->with('erros', $this->validator->getErrors());
        }

        if (! (new SenhaService())->redefinir($token, (string) $this->request->getPost('senha'))) {
            return redirect()->to(site_url('login'))
                ->with('erro', 'Link inválido ou expirado. Solicite um novo.');
        }

        return redirect()->to(site_url('login'))->with('sucesso', 'Senha alterada com sucesso! Faça login.');
    }

    public function registro()
    {
        return view('auth/registro', ['titulo' => 'Criar conta']);
    }

    public function salvarRegistro()
    {
        $regras = [
            'nome'              => 'required|min_length[3]|max_length[150]',
            'email'             => 'required|valid_email|max_length[180]|is_unique[usuarios.email]',
            'senha'             => 'required|min_length[6]|max_length[72]',
            'senha_confirmacao' => 'required|matches[senha]',
        ];

        if (! $this->validate($regras)) {
            return redirect()->back()->withInput()
                ->with('erros', $this->validator->getErrors());
        }

        /** @var Usuario $usuario */
        $usuario = new Usuario([
            'nome'     => (string) $this->request->getPost('nome'),
            'email'    => mb_strtolower(trim((string) $this->request->getPost('email'))),
            'telefone' => $this->request->getPost('telefone') ?: null,
            'nivel'    => 'organizador',
            'status'   => 'ativo',
        ]);

        // O mutator setSenha() gera o hash no momento da atribuição.
        $usuario->senha = (string) $this->request->getPost('senha');

        $model = new UsuarioModel();
        $model->insert($usuario);

        return redirect()->to(site_url('login'))
            ->with('sucesso', 'Conta criada! Faça login para começar.');
    }

    /**
     * Inicia o fluxo "Entrar com Google" (redireciona para o Google).
     */
    public function google()
    {
        $google = config('Google');

        if (! $google->configurado()) {
            return redirect()->to(site_url('login'))->with('erro', 'Login com Google não está configurado.');
        }

        $state = bin2hex(random_bytes(16));
        session()->set('google_oauth_state', $state);

        $params = [
            'client_id'     => $google->clientId,
            'redirect_uri'  => $this->googleRedirectUri(),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online',
            'prompt'        => 'select_account',
        ];

        return redirect()->to('https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
    }

    /**
     * Retorno do Google: valida o state, troca o code por token e faz o login.
     */
    public function googleCallback()
    {
        $google = config('Google');

        if (! $google->configurado()) {
            return redirect()->to(site_url('login'))->with('erro', 'Login com Google não está configurado.');
        }

        if ($this->request->getGet('error')) {
            return redirect()->to(site_url('login'))->with('erro', 'Login com Google cancelado.');
        }

        $state       = (string) $this->request->getGet('state');
        $stateSessao = (string) session()->get('google_oauth_state');
        session()->remove('google_oauth_state');

        if ($state === '' || $stateSessao === '' || ! hash_equals($stateSessao, $state)) {
            return redirect()->to(site_url('login'))->with('erro', 'Sessão de login expirada. Tente novamente.');
        }

        $code = (string) $this->request->getGet('code');

        if ($code === '') {
            return redirect()->to(site_url('login'))->with('erro', 'Não recebemos a autorização do Google.');
        }

        $client = service('curlrequest');

        try {
            $resposta = $client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'code'          => $code,
                    'client_id'     => $google->clientId,
                    'client_secret' => $google->clientSecret,
                    'redirect_uri'  => $this->googleRedirectUri(),
                    'grant_type'    => 'authorization_code',
                ],
                'http_errors' => false,
                'timeout'     => 15,
            ]);
        } catch (\Throwable $e) {
            return redirect()->to(site_url('login'))->with('erro', 'Falha ao falar com o Google. Tente novamente.');
        }

        $token = json_decode($resposta->getBody(), true);

        if ($resposta->getStatusCode() !== 200 || empty($token['access_token'])) {
            return redirect()->to(site_url('login'))->with('erro', 'Não foi possível concluir o login com o Google.');
        }

        try {
            $perfilResp = $client->get('https://www.googleapis.com/oauth2/v3/userinfo', [
                'headers'     => ['Authorization' => 'Bearer ' . $token['access_token']],
                'http_errors' => false,
                'timeout'     => 15,
            ]);
        } catch (\Throwable $e) {
            return redirect()->to(site_url('login'))->with('erro', 'Falha ao obter seus dados do Google.');
        }

        $perfil = json_decode($perfilResp->getBody(), true);

        if ($perfilResp->getStatusCode() !== 200 || empty($perfil['email'])) {
            return redirect()->to(site_url('login'))->with('erro', 'Não foi possível obter seu e-mail do Google.');
        }

        $resultado = $this->auth->entrarComGoogle(
            (string) ($perfil['sub'] ?? ''),
            (string) $perfil['email'],
            (string) ($perfil['name'] ?? ''),
            (bool) ($perfil['email_verified'] ?? false)
        );

        if (! $resultado['ok']) {
            return redirect()->to(site_url('login'))->with('erro', $resultado['mensagem']);
        }

        $destino = $this->auth->destinoSeguro(session()->get('redirect_url'));
        session()->remove('redirect_url');

        return redirect()->to($destino)->with('sucesso', $resultado['mensagem']);
    }

    private function googleRedirectUri(): string
    {
        $google = config('Google');

        return trim($google->redirectUri) !== '' ? trim($google->redirectUri) : site_url('auth/google/callback');
    }
}
