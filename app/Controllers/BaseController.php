<?php

namespace App\Controllers;

use App\Entities\Usuario;
use App\Services\AuthService;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController: componentes compartilhados por todos os controllers.
 *
 * Disponibiliza a sessão, o serviço de autenticação e o usuário atual
 * para os controllers de Admin, Host e Public.
 */
abstract class BaseController extends Controller
{
    /**
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    protected AuthService $auth;

    protected ?Usuario $usuario = null;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        helper(['url', 'form', 'formato']);

        $this->session = service('session');
        $this->auth    = new AuthService();
        $this->usuario = $this->auth->usuarioAtual();
    }

    /**
     * ID do usuário autenticado (fallback na sessão, à prova de nulos).
     */
    protected function usuarioId(): int
    {
        return (int) ($this->usuario->id ?? session()->get('usuario_id'));
    }

    /**
     * Renderiza uma view injetando dados comuns (usuário autenticado).
     *
     * @param array<string, mixed> $data
     */
    protected function render(string $view, array $data = []): string
    {
        $data['usuario'] = $data['usuario'] ?? $this->usuario;

        return view($view, $data);
    }
}
