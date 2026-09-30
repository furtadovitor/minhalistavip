<?php

namespace App\Controllers;

use App\Entities\Usuario;
use App\Models\UsuarioModel;
use App\Services\AuthService;

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
            return redirect()->back()->withInput()
                ->with('erro', 'Informe um e-mail válido.');
        }

        $email = (string) $this->request->getPost('email');
        $senha = (string) $this->request->getPost('senha');

        if (! $this->auth->tentarLogin($email, $senha)) {
            return redirect()->back()->withInput()
                ->with('erro', 'Não encontramos um usuário ativo com esse e-mail.');
        }

        $destino = session()->get('redirect_url') ?: site_url($this->auth->rotaInicial());
        session()->remove('redirect_url');

        return redirect()->to($destino)->with('sucesso', 'Bem-vindo(a) de volta!');
    }

    public function logout()
    {
        $this->auth->logout();

        return redirect()->to(site_url('login'))->with('sucesso', 'Sessão encerrada com sucesso.');
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
}
