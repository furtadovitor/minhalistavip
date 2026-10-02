<?php

namespace App\Controllers\Host;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use App\Services\CarteiraService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Financeiro do organizador: dados de repasse (identificação, dados bancários
 * e endereço), saldo, valores aguardando liberação, resgate e extrato.
 */
class Carteira extends BaseController
{
    protected CarteiraService $carteira;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->carteira = new CarteiraService();
    }

    public function index()
    {
        $usuarioId = $this->usuarioId();

        return $this->render('host/carteira/index', [
            'titulo'              => 'Financeiro',
            'saldo'               => $this->carteira->saldo($usuarioId),
            'arrecadado'          => $this->carteira->totalArrecadado($usuarioId),
            'taxas'               => $this->carteira->totalTaxas($usuarioId),
            'sacado'              => $this->carteira->totalSacado($usuarioId),
            'aguardandoLiberacao' => $this->carteira->aguardandoLiberacao($usuarioId),
            'prazoLiberacao'      => CarteiraService::PRAZO_LIBERACAO_CARTAO_DIAS,
            'extrato'             => $this->carteira->extrato($usuarioId),
            'saques'              => $this->carteira->saques($usuarioId),
            'valorMinimo'         => $this->carteira->valorMinimoSaque(),
            'usuario'             => $this->usuario,
            'repasseCompleto'     => $this->usuario !== null && $this->usuario->repasseCompleto(),
        ]);
    }

    /**
     * Salva os dados de repasse (responsável, bancários e endereço).
     */
    public function salvarRepasse()
    {
        $id = $this->usuarioId();

        $digitos = static fn (?string $valor): ?string => preg_replace('/\D/', '', (string) $valor) ?: null;

        $dados = [
            'nome'            => trim((string) $this->request->getPost('nome')),
            'email'           => mb_strtolower(trim((string) $this->request->getPost('email'))),
            'telefone'        => $digitos($this->request->getPost('telefone')),
            'cpf_cnpj'        => $digitos($this->request->getPost('cpf_cnpj')),
            'data_nascimento' => $this->request->getPost('data_nascimento') ?: null,
            'destinatario'    => trim((string) $this->request->getPost('destinatario')) ?: null,
            'cep'             => $digitos($this->request->getPost('cep')),
            'endereco'        => trim((string) $this->request->getPost('endereco')) ?: null,
            'numero'          => trim((string) $this->request->getPost('numero')) ?: null,
            'complemento'     => trim((string) $this->request->getPost('complemento')) ?: null,
            'bairro'          => trim((string) $this->request->getPost('bairro')) ?: null,
            'cidade'          => trim((string) $this->request->getPost('cidade')) ?: null,
            'estado'          => strtoupper(trim((string) $this->request->getPost('estado'))) ?: null,
            'tipo_pagamento'  => $this->request->getPost('tipo_pagamento') ?: 'pix',
            'tipo_chave_pix'  => $this->request->getPost('tipo_chave_pix') ?: null,
            'chave_pix'       => trim((string) $this->request->getPost('chave_pix')) ?: null,
        ];

        $regras = [
            'nome'            => 'required|min_length[3]|max_length[150]',
            'email'           => 'required|valid_email|max_length[180]|is_unique[usuarios.email,id,' . $id . ']',
            'telefone'        => 'required|min_length[10]|max_length[20]',
            'cpf_cnpj'        => 'required|min_length[11]|max_length[20]',
            'data_nascimento' => 'required|valid_date[Y-m-d]',
            'destinatario'    => 'required|max_length[150]',
            'cep'             => 'required|min_length[8]|max_length[9]',
            'endereco'        => 'required|max_length[180]',
            'bairro'          => 'required|max_length[120]',
            'cidade'          => 'required|max_length[120]',
            'estado'          => 'required|exact_length[2]|alpha',
            'tipo_pagamento'  => 'required|in_list[pix,ted]',
            'tipo_chave_pix'  => 'required|in_list[cpf,cnpj,email,telefone,aleatoria]',
            'chave_pix'       => 'required|max_length[255]',
        ];

        if (! $this->validate($regras)) {
            return redirect()->back()->withInput()->with('erros', $this->validator->getErrors());
        }

        // Já validamos acima (inclusive a unicidade do e-mail, com o id explícito),
        // então dispensamos a revalidação do model.
        $model = new UsuarioModel();
        $model->skipValidation(true);

        if ($model->update($id, $dados + ['dados_repasse_ok_em' => date('Y-m-d H:i:s')]) === false) {
            return redirect()->back()->withInput()
                ->with('erros', $model->errors() ?: ['Não foi possível salvar os dados de repasse.']);
        }

        return redirect()->to(site_url('painel/carteira'))
            ->with('sucesso', 'Dados de repasse salvos com sucesso.');
    }

    public function saque()
    {
        return $this->render('host/carteira/saque', [
            'titulo'          => 'Solicitar resgate',
            'saldo'           => $this->carteira->saldo($this->usuarioId()),
            'valorMinimo'     => $this->carteira->valorMinimoSaque(),
            'usuario'         => $this->usuario,
            'repasseCompleto' => $this->usuario !== null && $this->usuario->repasseCompleto(),
        ]);
    }

    public function solicitarSaque()
    {
        if ($this->usuario === null || ! $this->usuario->repasseCompleto()) {
            return redirect()->to(site_url('painel/carteira'))
                ->with('erro', 'Preencha seus dados de repasse antes de solicitar o resgate.');
        }

        $chave = trim((string) $this->request->getPost('chave_pix')) ?: (string) $this->usuario->chave_pix;

        $resultado = $this->carteira->solicitarSaque(
            $this->usuarioId(),
            (float) str_replace(',', '.', (string) $this->request->getPost('valor')),
            $chave,
            (string) $this->request->getPost('observacao')
        );

        if (! $resultado['ok']) {
            return redirect()->back()->withInput()->with('erro', $resultado['mensagem']);
        }

        return redirect()->to(site_url('painel/carteira'))->with('sucesso', $resultado['mensagem']);
    }
}
