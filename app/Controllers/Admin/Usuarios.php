<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventoModel;
use App\Models\UsuarioModel;
use App\Services\CarteiraService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Gestão de usuários (organizadores e administradores).
 */
class Usuarios extends BaseController
{
    protected UsuarioModel $usuarios;

    protected CarteiraService $carteira;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->usuarios = new UsuarioModel();
        $this->carteira = new CarteiraService();
    }

    public function index()
    {
        $filtros = [
            'nivel'  => $this->request->getGet('nivel') ?: null,
            'status' => $this->request->getGet('status') ?: null,
            'busca'  => $this->request->getGet('busca') ?: null,
        ];

        return $this->render('admin/usuarios/index', [
            'titulo'         => 'Usuários',
            'usuarios'       => $this->usuarios->listar($filtros),
            'filtros'        => $filtros,
            'usuarioAtualId' => $this->usuarioId(),
        ]);
    }

    public function ver($id = null)
    {
        $id = (int) $id;
        $usuario = $this->buscar($id);

        return $this->render('admin/usuarios/ver', [
            'titulo'     => 'Usuário: ' . $usuario->nome,
            'alvo'       => $usuario,
            'eventos'    => (new EventoModel())->doOrganizador($id),
            'arrecadado' => $this->carteira->totalArrecadado($id),
            'saldo'      => $this->carteira->saldo($id),
            'taxas'      => $this->carteira->totalTaxas($id),
            'saques'     => $this->carteira->saques($id),
        ]);
    }

    public function alternar($id = null)
    {
        $id = (int) $id;
        $usuario = $this->buscar($id);

        if ($id === $this->usuarioId()) {
            return redirect()->back()->with('erro', 'Você não pode suspender a própria conta.');
        }

        $novoStatus = $usuario->status === 'ativo' ? 'suspenso' : 'ativo';
        $this->usuarios->update($id, ['status' => $novoStatus]);

        return redirect()->back()->with('sucesso', 'Status do usuário alterado para "' . $novoStatus . '".');
    }

    private function buscar(int $id): \App\Entities\Usuario
    {
        $usuario = $this->usuarios->find($id);

        if ($usuario === null) {
            throw PageNotFoundException::forPageNotFound('Usuário não encontrado.');
        }

        return $usuario;
    }
}
