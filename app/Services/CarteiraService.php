<?php

namespace App\Services;

use App\Entities\Pedido;
use App\Models\CarteiraMovimentacaoModel;
use App\Models\SaqueModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Carteira do organizador: saldo, extrato, retenção de taxa e saques.
 *
 * Convenção de sinal (ver migration): créditos positivos, débitos negativos.
 * Só existe crédito de pedido quando o pedido é confirmado como 'pago'
 * (Regra 2.2), responsabilidade do PagamentoService.
 */
class CarteiraService
{
    protected CarteiraMovimentacaoModel $movimentacoes;

    protected SaqueModel $saques;

    protected ConfiguracaoService $config;

    protected BaseConnection $db;

    public function __construct(
        ?CarteiraMovimentacaoModel $movimentacoes = null,
        ?SaqueModel $saques = null,
        ?ConfiguracaoService $config = null,
        ?BaseConnection $db = null
    ) {
        helper('formato');

        $this->movimentacoes = $movimentacoes ?? new CarteiraMovimentacaoModel();
        $this->saques        = $saques ?? new SaqueModel();
        $this->config        = $config ?? new ConfiguracaoService();
        $this->db            = $db ?? db_connect();
    }

    /**
     * Credita o pedido pago na carteira do dono do evento.
     *
     * - 'convidado': credita o valor dos presentes (a taxa já foi embutida no total).
     * - 'organizador': credita o valor dos presentes e debita a comissão.
     */
    public function creditarPedido(Pedido $pedido): void
    {
        $usuarioId = $this->donoDoEvento((int) $pedido->evento_id);

        if ($usuarioId === null) {
            return;
        }

        $this->lancar(
            $usuarioId,
            (float) $pedido->valor_presentes,
            'credito',
            $pedido,
            'Presente pago no pedido ' . $pedido->protocolo
        );

        if (! $pedido->taxaPagaPeloConvidado() && (float) $pedido->valor_taxa > 0) {
            $this->lancar(
                $usuarioId,
                -1 * (float) $pedido->valor_taxa,
                'taxa',
                $pedido,
                'Comissão da plataforma (' . number_format((float) $pedido->percentual_taxa, 2, ',', '.') . '%)'
            );
        }
    }

    public function saldo(int $usuarioId): float
    {
        return round($this->movimentacoes->soma($usuarioId, ['credito', 'taxa', 'saque', 'estorno', 'ajuste']), 2);
    }

    public function totalArrecadado(int $usuarioId): float
    {
        return round($this->movimentacoes->soma($usuarioId, ['credito']), 2);
    }

    public function totalTaxas(int $usuarioId): float
    {
        return round(abs($this->movimentacoes->soma($usuarioId, ['taxa'])), 2);
    }

    public function totalSacado(int $usuarioId): float
    {
        return round(abs($this->movimentacoes->soma($usuarioId, ['saque'])), 2);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function extrato(int $usuarioId, int $limite = 100): array
    {
        return $this->movimentacoes->doUsuario($usuarioId, $limite);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function saques(int $usuarioId): array
    {
        return $this->saques->doUsuario($usuarioId);
    }

    public function valorMinimoSaque(): float
    {
        return $this->config->decimal('saque_valor_minimo', 50.00);
    }

    /**
     * Solicita um saque: valida o mínimo e o saldo e reserva o valor
     * com um débito imediato na carteira.
     *
     * @return array{ok: bool, mensagem: string, saque_id?: int}
     */
    public function solicitarSaque(int $usuarioId, float $valor, ?string $chavePix = null, ?string $observacao = null): array
    {
        $valor = round($valor, 2);

        if ($valor <= 0) {
            return ['ok' => false, 'mensagem' => 'Informe um valor válido para saque.'];
        }

        $minimo = $this->valorMinimoSaque();

        if ($valor < $minimo) {
            return ['ok' => false, 'mensagem' => 'O valor mínimo para saque é ' . moeda_brl($minimo) . '.'];
        }

        if (trim((string) $chavePix) === '') {
            return ['ok' => false, 'mensagem' => 'Informe a chave PIX que receberá o saque.'];
        }

        $saldo = $this->saldo($usuarioId);

        if ($valor > $saldo) {
            return ['ok' => false, 'mensagem' => 'Saldo insuficiente. Disponível: ' . moeda_brl($saldo) . '.'];
        }

        $agora = date('Y-m-d H:i:s');
        $this->db->transStart();

        $this->saques->insert([
            'usuario_id'    => $usuarioId,
            'valor'         => $valor,
            'status'        => 'solicitado',
            'chave_pix'     => trim((string) $chavePix),
            'observacao'    => $observacao !== null && trim($observacao) !== '' ? trim($observacao) : null,
            'solicitado_em' => $agora,
        ]);

        $saqueId = (int) $this->saques->getInsertID();

        $this->lancar(
            $usuarioId,
            -1 * $valor,
            'saque',
            null,
            'Saque #' . $saqueId . ' solicitado'
        );

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return ['ok' => false, 'mensagem' => 'Não foi possível registrar o saque. Tente novamente.'];
        }

        return [
            'ok'       => true,
            'mensagem' => 'Saque solicitado! Nossa equipe processará em até 2 dias úteis.',
            'saque_id' => $saqueId,
        ];
    }

    /**
     * Lista de saques para o SuperAdmin, com os dados do organizador.
     *
     * @param array{status?: string|null} $filtros
     * @return list<array<string, mixed>>
     */
    public function saquesAdmin(array $filtros = []): array
    {
        $builder = $this->db->table('saques s')
            ->select('s.*, u.nome AS organizador_nome, u.email AS organizador_email')
            ->join('usuarios u', 'u.id = s.usuario_id')
            ->orderBy('s.id', 'DESC');

        if (! empty($filtros['status'])) {
            $builder->where('s.status', $filtros['status']);
        }

        return $builder->get()->getResultArray();
    }

    public function saque(int $saqueId): ?array
    {
        return $this->saques->find($saqueId);
    }

    /**
     * Totais usados no painel do SuperAdmin.
     *
     * @return array{pendentes_qtd: int, pendentes_valor: float, pagos_qtd: int, pagos_valor: float}
     */
    public function resumoSaques(): array
    {
        $pendentes = $this->db->table('saques')
            ->select('COUNT(*) AS q, COALESCE(SUM(valor), 0) AS total')
            ->whereIn('status', ['solicitado', 'processando'])
            ->get()->getRow();

        $pagos = $this->db->table('saques')
            ->select('COUNT(*) AS q, COALESCE(SUM(valor), 0) AS total')
            ->where('status', 'pago')
            ->get()->getRow();

        return [
            'pendentes_qtd'   => (int) ($pendentes->q ?? 0),
            'pendentes_valor' => (float) ($pendentes->total ?? 0),
            'pagos_qtd'       => (int) ($pagos->q ?? 0),
            'pagos_valor'     => (float) ($pagos->total ?? 0),
        ];
    }

    /**
     * Fluxo de aprovação do saque pelo SuperAdmin.
     *
     * Recusar/cancelar devolve o valor reservado à carteira (estorno).
     *
     * @return array{ok: bool, mensagem: string}
     */
    public function alterarStatusSaque(int $saqueId, string $novoStatus, ?string $motivo = null): array
    {
        $saque = $this->saques->find($saqueId);

        if ($saque === null) {
            return ['ok' => false, 'mensagem' => 'Saque não encontrado.'];
        }

        $atual = (string) $saque['status'];

        if ($atual === $novoStatus) {
            return ['ok' => true, 'mensagem' => 'O saque já está como ' . rotulo_status_saque($novoStatus) . '.'];
        }

        $permitidas = [
            'solicitado'  => ['processando', 'pago', 'recusado', 'cancelado'],
            'processando' => ['pago', 'recusado', 'cancelado'],
            'pago'        => [],
            'recusado'    => [],
            'cancelado'   => [],
        ];

        if (! in_array($novoStatus, $permitidas[$atual] ?? [], true)) {
            return ['ok' => false, 'mensagem' => 'Transição inválida: ' . rotulo_status_saque($atual) . ' → ' . rotulo_status_saque($novoStatus) . '.'];
        }

        $agora    = date('Y-m-d H:i:s');
        $conclui  = in_array($novoStatus, ['pago', 'recusado', 'cancelado'], true);

        $this->db->transBegin();

        $dados = ['status' => $novoStatus];

        if ($conclui) {
            $dados['processado_em'] = $agora;
        }

        $this->saques->update($saqueId, $dados);

        if (in_array($novoStatus, ['recusado', 'cancelado'], true)) {
            $descricao = 'Estorno do saque #' . $saqueId
                . ($motivo !== null && trim($motivo) !== '' ? ' — ' . trim($motivo) : '');

            $this->lancar((int) $saque['usuario_id'], (float) $saque['valor'], 'estorno', null, $descricao);
        }

        $this->db->transCommit();

        if (! $this->db->transStatus()) {
            return ['ok' => false, 'mensagem' => 'Não foi possível atualizar o saque. Tente novamente.'];
        }

        return [
            'ok'       => true,
            'mensagem' => 'Saque #' . $saqueId . ' atualizado para ' . rotulo_status_saque($novoStatus)
                . ($novoStatus === 'recusado' ? '. O valor foi devolvido à carteira do organizador.' : '.'),
        ];
    }

    private function lancar(int $usuarioId, float $valor, string $tipo, ?Pedido $pedido, string $descricao): void
    {
        $novoSaldo = round($this->saldo($usuarioId) + $valor, 2);

        $this->movimentacoes->insert([
            'usuario_id' => $usuarioId,
            'evento_id'  => $pedido !== null ? (int) $pedido->evento_id : null,
            'pedido_id'  => $pedido !== null ? (int) $pedido->id : null,
            'tipo'       => $tipo,
            'valor'      => round($valor, 2),
            'saldo_apos' => $novoSaldo,
            'descricao'  => $descricao,
            'criado_em'  => date('Y-m-d H:i:s'),
        ]);
    }

    private function donoDoEvento(int $eventoId): ?int
    {
        $row = $this->db->table('eventos')
            ->select('usuario_id')
            ->where('id', $eventoId)
            ->get()
            ->getRow();

        return $row === null ? null : (int) $row->usuario_id;
    }
}
