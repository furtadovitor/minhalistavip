<?php

namespace App\Services;

use App\Entities\Evento;
use App\Entities\Pedido;
use App\Models\PedidoModel;
use App\Models\PresenteEventoModel;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/**
 * Checkout do convidado: cria o pedido a partir de um presente do evento,
 * aplicando o snapshot da regra de taxa (Regra 2.1) e gerando a cobrança PIX.
 */
class CheckoutService
{
    protected TaxaService $taxa;

    protected PedidoModel $pedidos;

    protected PresenteEventoModel $presentes;

    protected PixService $pix;

    protected PagamentoService $pagamentos;

    protected BaseConnection $db;

    public function __construct(
        ?TaxaService $taxa = null,
        ?PedidoModel $pedidos = null,
        ?PresenteEventoModel $presentes = null,
        ?PixService $pix = null,
        ?PagamentoService $pagamentos = null,
        ?BaseConnection $db = null
    ) {
        $this->taxa       = $taxa ?? new TaxaService();
        $this->pedidos    = $pedidos ?? new PedidoModel();
        $this->presentes  = $presentes ?? new PresenteEventoModel();
        $this->pix        = $pix ?? new PixService();
        $this->pagamentos = $pagamentos ?? new PagamentoService();
        $this->db         = $db ?? db_connect();
    }

    /**
     * Cálculo da taxa para uma quantidade do presente.
     *
     * @param array<string, mixed> $presente
     * @return array{
     *     valor_presentes: float,
     *     percentual_taxa: float,
     *     valor_taxa: float,
     *     valor_total: float,
     *     quem_paga_taxa: string,
     *     liquido_organizador: float
     * }
     */
    public function resumo(Evento $evento, array $presente, int $quantidade): array
    {
        $valorPresentes = round(((float) $presente['valor']) * max(1, $quantidade), 2);

        // A comissão é sempre o percentual fixo da plataforma (não há taxa por evento).
        return $this->taxa->calcular($valorPresentes, (string) $evento->quem_paga_taxa);
    }

    /**
     * Cotas ainda disponíveis (meta - vendidas).
     *
     * @param array<string, mixed> $presente
     */
    public function cotasDisponiveis(array $presente): int
    {
        return max(0, (int) $presente['quantidade_meta'] - (int) $presente['quantidade_vendida']);
    }

    /**
     * Cria o pedido e a cobrança PIX.
     *
     * @param array<string, mixed> $presente
     * @param array{nome?: string, email?: string, telefone?: string, mensagem?: string} $convidado
     * @return array{pedido: Pedido, pagamento_id: int|null, cobranca: array<string, mixed>|null}
     *
     * @throws RuntimeException quando os dados não permitem concluir o checkout
     */
    public function criar(Evento $evento, array $presente, int $quantidade, array $convidado): array
    {
        $this->validar($evento, $presente, $quantidade, $convidado);

        $quantidade   = max(1, $quantidade);
        $resumo       = $this->resumo($evento, $presente, $quantidade);
        $nome         = trim((string) $convidado['nome']);
        $email        = trim((string) ($convidado['email'] ?? ''));
        $telefone     = trim((string) ($convidado['telefone'] ?? ''));
        $mensagem     = $evento->permite_recados ? trim((string) ($convidado['mensagem'] ?? '')) : '';

        $this->db->transBegin();

        try {
            // Revalida a disponibilidade dentro da transação (evita venda concorrente).
            $atual = $this->presentes->find((int) $presente['id']);

            if ($atual === null || empty($atual['ativo']) || (int) $atual['quantidade_vendida'] + $quantidade > (int) $atual['quantidade_meta']) {
                throw new RuntimeException('As cotas deste presente esgotaram. Escolha outro item da lista.');
            }

            /** @var Pedido $pedido */
            $pedido = new Pedido([
                'evento_id'          => (int) $evento->id,
                'presente_evento_id' => (int) $presente['id'],
                'protocolo'          => $this->gerarProtocolo(),
                'nome_convidado'     => $nome,
                'email_convidado'    => $email !== '' ? $email : null,
                'telefone_convidado' => $telefone !== '' ? $telefone : null,
                'mensagem'           => $mensagem !== '' ? $mensagem : null,
                'quantidade'         => $quantidade,
                'valor_presentes'    => $resumo['valor_presentes'],
                'valor_taxa'         => $resumo['valor_taxa'],
                'valor_total'        => $resumo['valor_total'],
                'percentual_taxa'    => $resumo['percentual_taxa'],
                'quem_paga_taxa'     => $resumo['quem_paga_taxa'],
                'status'             => 'pendente',
            ]);

            if ($this->pedidos->insert($pedido) === false) {
                throw new RuntimeException('Não foi possível registrar o pedido. Verifique os dados e tente novamente.');
            }

            $pedido->id = (int) $this->pedidos->getInsertID();

            $cobranca    = null;
            $pagamentoId = null;

            if ($this->pix->cobrarNaCriacao()) {
                $cobranca    = $this->pix->gerarCobranca($pedido);
                $pagamentoId = $this->pagamentos->registrar($pedido, $cobranca);

                $this->pedidos->update($pedido->id, [
                    'gateway'              => $cobranca['gateway'],
                    'gateway_transacao_id' => $cobranca['gateway_transacao_id'],
                    'metodo_pagamento'     => 'pix',
                    'expira_em'            => $cobranca['expira_em'],
                ]);

                $pedido->gateway              = $cobranca['gateway'];
                $pedido->gateway_transacao_id = $cobranca['gateway_transacao_id'];
                $pedido->expira_em            = $cobranca['expira_em'];
            } else {
                // Checkout Bricks: o pagamento (PIX ou cartão) é criado depois,
                // quando o convidado envia o formulário na página do pedido.
                $expiracao = $this->pix->expiracaoPedido();

                $this->pedidos->update($pedido->id, [
                    'gateway'   => $this->pix->nomeGateway(),
                    'expira_em' => $expiracao,
                ]);

                $pedido->gateway   = $this->pix->nomeGateway();
                $pedido->expira_em = $expiracao;
            }

            $this->db->transCommit();
        } catch (RuntimeException $e) {
            $this->db->transRollback();

            throw $e;
        }

        return ['pedido' => $pedido, 'pagamento_id' => $pagamentoId, 'cobranca' => $cobranca];
    }

    /**
     * @param array<string, mixed> $presente
     * @param array<string, mixed> $convidado
     *
     * @throws RuntimeException
     */
    private function validar(Evento $evento, array $presente, int $quantidade, array $convidado): void
    {
        if ($evento->status !== 'publicado') {
            throw new RuntimeException('Este evento não está recebendo presentes no momento.');
        }

        if (($presente['tipo'] ?? 'ficticio') !== 'ficticio') {
            throw new RuntimeException('Este item é um presente real e não passa pelo checkout.');
        }

        if (empty($presente['ativo'])) {
            throw new RuntimeException('Este presente não está mais disponível.');
        }

        if ($quantidade < 1) {
            throw new RuntimeException('Selecione ao menos uma cota.');
        }

        if ($quantidade > $this->cotasDisponiveis($presente)) {
            throw new RuntimeException('Quantidade maior que as cotas disponíveis (' . $this->cotasDisponiveis($presente) . ').');
        }

        $nome = trim((string) ($convidado['nome'] ?? ''));

        if (mb_strlen($nome) < 3) {
            throw new RuntimeException('Informe seu nome completo.');
        }

        $email = trim((string) ($convidado['email'] ?? ''));

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('Informe um e-mail válido.');
        }
    }

    private function gerarProtocolo(): string
    {
        do {
            $protocolo = 'MLV' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        } while ($this->pedidos->where('protocolo', $protocolo)->countAllResults() > 0);

        return $protocolo;
    }
}
