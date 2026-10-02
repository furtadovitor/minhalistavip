<?php

namespace App\Models;

use App\Entities\Pedido;
use CodeIgniter\Model;

class PedidoModel extends Model
{
    protected $table         = 'pedidos';
    protected $primaryKey    = 'id';
    protected $returnType    = Pedido::class;
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'evento_id',
        'presente_evento_id',
        'protocolo',
        'nome_convidado',
        'email_convidado',
        'telefone_convidado',
        'mensagem',
        'quantidade',
        'valor_presentes',
        'valor_taxa',
        'valor_total',
        'percentual_taxa',
        'quem_paga_taxa',
        'status',
        'metodo_pagamento',
        'gateway',
        'gateway_transacao_id',
        'pago_em',
        'expira_em',
    ];

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'evento_id'      => 'required|integer',
        'protocolo'      => 'required|max_length[40]',
        'nome_convidado' => 'required|min_length[3]|max_length[150]',
        'valor_total'    => 'required|numeric|greater_than_equal_to[0]',
        'status'         => 'required|in_list[pendente,pago,cancelado,expirado,reembolsado]',
    ];

    /**
     * Resumo financeiro de um evento (para o painel da lista).
     *
     * @return array{pagos:int,pendentes:int,arrecadado:float,taxas:float}
     */
    public function resumoEvento(int $eventoId): array
    {
        $linhas = $this->db->table('pedidos')
            ->select("SUM(CASE WHEN status = 'pago' THEN 1 ELSE 0 END) AS pagos", false)
            ->select("SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) AS pendentes", false)
            ->select("COALESCE(SUM(CASE WHEN status = 'pago' THEN valor_total ELSE 0 END), 0) AS arrecadado", false)
            ->select("COALESCE(SUM(CASE WHEN status = 'pago' THEN valor_taxa ELSE 0 END), 0) AS taxas", false)
            ->where('evento_id', $eventoId)
            ->get()
            ->getRowArray();

        return [
            'pagos'      => (int) ($linhas['pagos'] ?? 0),
            'pendentes'  => (int) ($linhas['pendentes'] ?? 0),
            'arrecadado' => (float) ($linhas['arrecadado'] ?? 0),
            'taxas'      => (float) ($linhas['taxas'] ?? 0),
        ];
    }

    public function porProtocolo(string $protocolo): ?Pedido
    {
        return $this->where('protocolo', $protocolo)->first();
    }

    public function porTransacao(string $transacaoId): ?Pedido
    {
        return $this->where('gateway_transacao_id', $transacaoId)->first();
    }

    /**
     * Pedidos pendentes que já têm transação criada no gateway (candidatos à
     * conciliação, pois permitem consultar o status real).
     *
     * @return list<Pedido>
     */
    public function pendentesComTransacao(): array
    {
        return $this->where('status', 'pendente')
            ->where('gateway_transacao_id !=', null)
            ->findAll();
    }

    /**
     * Marca como 'expirado' os pedidos pendentes cuja validade já passou.
     *
     * @return int quantidade de pedidos expirados
     */
    public function expirarVencidos(string $agora): int
    {
        $this->db->table('pedidos')
            ->where('status', 'pendente')
            ->where('expira_em !=', null)
            ->where('expira_em <', $agora)
            ->update([
                'status'        => 'expirado',
                'atualizado_em' => $agora,
            ]);

        return (int) $this->db->affectedRows();
    }

    /**
     * @return list<Pedido>
     */
    public function doEvento(int $eventoId): array
    {
        return $this->where('evento_id', $eventoId)
            ->orderBy('criado_em', 'DESC')
            ->findAll();
    }

    /**
     * Pedidos de todos os eventos do organizador (isolamento de tenant).
     *
     * @param array{status?: string|null, evento_id?: int|null} $filtros
     * @return list<Pedido>
     */
    public function doOrganizador(int $usuarioId, array $filtros = []): array
    {
        $builder = $this->db->table('pedidos')
            ->select('pedidos.*')
            ->join('eventos', 'eventos.id = pedidos.evento_id')
            ->where('eventos.usuario_id', $usuarioId)
            ->orderBy('pedidos.criado_em', 'DESC');

        if (! empty($filtros['status'])) {
            $builder->where('pedidos.status', $filtros['status']);
        }

        if (! empty($filtros['evento_id'])) {
            $builder->where('pedidos.evento_id', (int) $filtros['evento_id']);
        }

        return $this->entidades($builder->get()->getResultArray());
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<Pedido>
     */
    private function entidades(array $rows): array
    {
        return array_map(fn (array $row): Pedido => new Pedido($row), $rows);
    }
}
