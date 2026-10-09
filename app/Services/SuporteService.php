<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Chat de suporte ao vivo.
 *
 * Conversas podem ser abertas pelo organizador (canal "painel") ou por um
 * visitante do site (canal "site", identificado por token de sessão). O
 * atendimento no admin é por fila, com "assumir" atômico para nunca duplicar.
 *
 * Regra de tempo: sem presença do cliente por TIMEOUT_MINUTOS, a conversa é
 * encerrada automaticamente pelo sistema. Se o cliente voltar antes disso, o
 * widget retoma a mesma conversa.
 */
class SuporteService
{
    /** Minutos de ausência do cliente até encerrar automaticamente. */
    public const TIMEOUT_MINUTOS = 5;

    /** Tamanho máximo de cada mensagem. */
    public const TAMANHO_MENSAGEM = 4000;

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    // ------------------------------------------------------------------
    // Lado cliente
    // ------------------------------------------------------------------

    /**
     * Conversa aberta (não encerrada) do cliente, se existir.
     *
     * @param array{usuario_id?: int|null, token?: string|null} $identidade
     * @return array<string, mixed>|null
     */
    public function conversaAberta(array $identidade): ?array
    {
        $builder = $this->db->table('suporte_conversas')->where('status !=', 'encerrada');
        $this->aplicarIdentidade($builder, $identidade);
        $linha = $builder->orderBy('id', 'DESC')->get()->getRowArray();

        if ($linha === null) {
            return null;
        }

        // Expiração "preguiçosa": sem presença do cliente por TIMEOUT_MINUTOS,
        // encerra na hora — sem depender do cron. O cron só faz a limpeza de
        // conversas em que o cliente nunca mais volta.
        if ($this->inativa($linha)) {
            $this->encerrar((int) $linha['id'], 'sistema');

            return null;
        }

        return $this->comNaoLidas($linha, 'cliente');
    }

    /**
     * Retoma a conversa aberta ou abre uma nova. Atualiza nome/e-mail/contexto.
     *
     * @param array{usuario_id?: int|null, token?: string|null, conversa_id?: int|null} $identidade
     * @param array{nome?: string|null, email?: string|null, telefone?: string|null, evento_id?: int|null, assunto?: string|null} $dados
     * @return array<string, mixed>
     */
    public function abrir(array $identidade, string $canal, array $dados = []): array
    {
        $existente = $this->conversaAberta($identidade);

        if ($existente !== null) {
            $upd = [];
            foreach (['nome' => 120, 'email' => 150, 'telefone' => 30, 'assunto' => 180] as $campo => $limite) {
                $valor = trim((string) ($dados[$campo] ?? ''));
                if ($valor !== '') {
                    $upd[$campo] = mb_substr($valor, 0, $limite);
                }
            }
            if (! empty($dados['evento_id'])) {
                $upd['evento_id'] = (int) $dados['evento_id'];
                $upd['expira_em'] = $this->expiracaoParaEvento((int) $dados['evento_id']);
            }
            if ($upd !== []) {
                $upd['atualizado_em'] = date('Y-m-d H:i:s');
                $this->db->table('suporte_conversas')->where('id', $existente['id'])->update($upd);
            }

            return $this->conversa((int) $existente['id']);
        }

        $agora = date('Y-m-d H:i:s');
        $this->db->table('suporte_conversas')->insert([
            'protocolo'       => $this->gerarProtocolo(),
            'canal'           => in_array($canal, ['painel', 'site'], true) ? $canal : 'site',
            'usuario_id'      => $identidade['usuario_id'] ?? null,
            'visitante_token' => $identidade['token'] ?? null,
            'nome'            => $this->limitar($dados['nome'] ?? null, 120),
            'email'           => $this->limitar($dados['email'] ?? null, 150),
            'telefone'        => $this->limitar($dados['telefone'] ?? null, 30),
            'evento_id'       => ! empty($dados['evento_id']) ? (int) $dados['evento_id'] : null,
            'assunto'         => $this->limitar($dados['assunto'] ?? null, 180),
            'status'          => 'aguardando',
            'expira_em'       => $this->expiracaoParaEvento(! empty($dados['evento_id']) ? (int) $dados['evento_id'] : null),
            'cliente_visto_em' => $agora,
            'criado_em'       => $agora,
            'atualizado_em'   => $agora,
        ]);

        $id = (int) $this->db->insertID();

        $this->inserirMensagem($id, 'sistema', null, 'Conversa iniciada. Guarde seu código de atendimento para voltar a falar com a gente.');

        return $this->conversa($id);
    }

    /**
     * Busca uma conversa pelo código de atendimento (protocolo).
     *
     * @return array<string, mixed>|null
     */
    public function porCodigo(string $codigo): ?array
    {
        $codigo = strtoupper(trim($codigo));

        if ($codigo === '') {
            return null;
        }

        $linha = $this->db->table('suporte_conversas')->where('protocolo', $codigo)->get()->getRowArray();

        return $linha === null ? null : $linha;
    }

    /**
     * Retoma uma conversa pelo código: reassocia a identidade atual e reabre
     * se estava encerrada (desde que não tenha expirado).
     *
     * @param array{usuario_id?: int|null, token?: string|null} $identidade
     * @return array<string, mixed>
     */
    public function retomar(int $conversaId, array $identidade): array
    {
        $conv = $this->conversa($conversaId);

        if ($conv === null) {
            return [];
        }

        $agora = date('Y-m-d H:i:s');
        $upd   = ['cliente_visto_em' => $agora, 'atualizado_em' => $agora];

        if (! empty($identidade['usuario_id'])) {
            $upd['usuario_id'] = (int) $identidade['usuario_id'];
        }
        if (! empty($identidade['token'])) {
            $upd['visitante_token'] = (string) $identidade['token'];
        }

        if ($conv['status'] === 'encerrada') {
            $upd['status']        = 'aguardando';
            $upd['encerrada_em']  = null;
            $upd['encerrada_por'] = null;
            $this->inserirMensagem($conversaId, 'sistema', null, 'Conversa retomada pelo cliente.');
        }

        $this->db->table('suporte_conversas')->where('id', $conversaId)->update($upd);

        return $this->conversa($conversaId);
    }

    /**
     * Data efetiva de expiração do código:
     *  - conversas de evento: campo expira_em (data do evento + 10 dias);
     *  - demais: última mensagem (ou criação) + 10 dias.
     *
     * @param array<string, mixed> $conv
     */
    public function expiracao(array $conv): ?string
    {
        if (! empty($conv['expira_em'])) {
            return (string) $conv['expira_em'];
        }

        $ref = $conv['ultima_mensagem_em'] ?: ($conv['criado_em'] ?? null);

        if (empty($ref)) {
            return null;
        }

        return date('Y-m-d H:i:s', strtotime((string) $ref) + 10 * 86400);
    }

    /**
     * @param array<string, mixed> $conv
     */
    public function expirou(array $conv): bool
    {
        $expiraEm = $this->expiracao($conv);

        return $expiraEm !== null && strtotime($expiraEm) < time();
    }

    // ------------------------------------------------------------------
    // Mensagens
    // ------------------------------------------------------------------

    /**
     * @return list<array<string, mixed>>
     */
    public function mensagens(int $conversaId, int $desdeId = 0): array
    {
        return $this->db->table('suporte_mensagens')
            ->where('conversa_id', $conversaId)
            ->where('id >', $desdeId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @param 'cliente'|'atendente' $autorTipo
     */
    public function enviar(int $conversaId, string $autorTipo, ?int $autorId, string $texto): void
    {
        $texto = trim($texto);

        if ($texto === '') {
            return;
        }

        $texto = mb_substr($texto, 0, self::TAMANHO_MENSAGEM);
        $agora = date('Y-m-d H:i:s');

        $this->inserirMensagem($conversaId, $autorTipo, $autorId, $texto);

        $upd = [
            'ultima_mensagem_em'      => $agora,
            'ultima_mensagem_preview' => mb_substr(preg_replace('/\s+/', ' ', $texto) ?? $texto, 0, 180),
            'atualizado_em'           => $agora,
        ];

        if ($autorTipo === 'cliente') {
            $upd['cliente_visto_em'] = $agora;
        } else {
            $upd['atendente_visto_em'] = $agora;

            $conv = $this->conversa($conversaId);
            if ($conv !== null && $conv['status'] !== 'encerrada') {
                if (empty($conv['atendente_id']) && $autorId !== null) {
                    $upd['atendente_id'] = $autorId;
                }
                if ($conv['status'] === 'aguardando') {
                    $upd['status'] = 'em_atendimento';
                }
            }
        }

        $this->db->table('suporte_conversas')->where('id', $conversaId)->update($upd);
    }

    /**
     * Marca que um dos lados leu as mensagens (zera o contador de não lidas).
     *
     * @param 'cliente'|'atendente' $lado
     */
    public function visto(int $conversaId, string $lado): void
    {
        $campo = $lado === 'cliente' ? 'cliente_visto_em' : 'atendente_visto_em';

        $this->db->table('suporte_conversas')->where('id', $conversaId)->update([
            $campo         => date('Y-m-d H:i:s'),
            'atualizado_em' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Resposta automática opcional (fora de horário etc.). Mantida simples.
     */
    public function inserirMensagem(int $conversaId, string $autorTipo, ?int $autorId, string $texto): void
    {
        $this->db->table('suporte_mensagens')->insert([
            'conversa_id' => $conversaId,
            'autor_tipo'  => $autorTipo,
            'autor_id'    => $autorId,
            'texto'       => $texto,
            'criado_em'   => date('Y-m-d H:i:s'),
        ]);
    }

    // ------------------------------------------------------------------
    // Lado admin (fila e atendimento)
    // ------------------------------------------------------------------

    /**
     * Fila: aguardando (mais antigas primeiro) e em atendimento (mais recentes).
     *
     * @return array{aguardando: list<array<string, mixed>>, em_atendimento: list<array<string, mixed>>}
     */
    public function fila(): array
    {
        $aguardando = $this->db->table('suporte_conversas')
            ->where('status', 'aguardando')
            ->orderBy('criado_em', 'ASC')
            ->get()
            ->getResultArray();

        $emAtendimento = $this->db->table('suporte_conversas')
            ->where('status', 'em_atendimento')
            ->orderBy('ultima_mensagem_em', 'DESC')
            ->get()
            ->getResultArray();

        $aguardando    = array_map(fn ($c) => $this->comNaoLidas($c, 'atendente'), $aguardando);
        $emAtendimento = array_map(fn ($c) => $this->comNaoLidas($c, 'atendente'), $emAtendimento);

        return ['aguardando' => $aguardando, 'em_atendimento' => $emAtendimento];
    }

    /**
     * Tenta assumir a conversa. Atômico: se outro atendente pegou antes, falha.
     *
     * @return array{ok: bool, conversa: array<string, mixed>|null}
     */
    public function assumir(int $conversaId, int $atendenteId): array
    {
        $agora = date('Y-m-d H:i:s');

        $this->db->table('suporte_conversas')
            ->where('id', $conversaId)
            ->where('status', 'aguardando')
            ->where('atendente_id IS NULL')
            ->update([
                'atendente_id'       => $atendenteId,
                'status'             => 'em_atendimento',
                'atendente_visto_em' => $agora,
                'atualizado_em'      => $agora,
            ]);

        if ($this->db->affectedRows() > 0) {
            $this->inserirMensagem($conversaId, 'sistema', null, 'Um atendente entrou na conversa.');

            return ['ok' => true, 'conversa' => $this->conversa($conversaId)];
        }

        $conv = $this->conversa($conversaId);

        return ['ok' => $conv !== null && (int) $conv['atendente_id'] === $atendenteId, 'conversa' => $conv];
    }

    /**
     * @param 'cliente'|'atendente'|'sistema' $por
     */
    public function encerrar(int $conversaId, string $por = 'sistema', ?int $autorId = null): void
    {
        $conv = $this->conversa($conversaId);

        if ($conv === null || $conv['status'] === 'encerrada') {
            return;
        }

        $agora = date('Y-m-d H:i:s');

        $this->inserirMensagem($conversaId, 'sistema', $autorId, 'Conversa encerrada.');

        $this->db->table('suporte_conversas')->where('id', $conversaId)->update([
            'status'                  => 'encerrada',
            'encerrada_em'            => $agora,
            'encerrada_por'           => $por,
            'ultima_mensagem_em'      => $agora,
            'ultima_mensagem_preview' => 'Conversa encerrada.',
            'atualizado_em'           => $agora,
        ]);
    }

    /**
     * A conversa está sem presença do cliente há mais de TIMEOUT_MINUTOS?
     *
     * @param array<string, mixed> $conv
     */
    private function inativa(array $conv): bool
    {
        $ref = $conv['cliente_visto_em'] ?: ($conv['criado_em'] ?? null);

        if (empty($ref)) {
            return false;
        }

        return strtotime((string) $ref) < time() - self::TIMEOUT_MINUTOS * 60;
    }

    /**
     * Encerra conversas sem presença recente do cliente. Retorna quantas fechou.
     */
    public function fecharInativos(int $minutos = self::TIMEOUT_MINUTOS): int
    {
        $limite = date('Y-m-d H:i:s', time() - $minutos * 60);

        $ids = $this->db
            ->query(
                "SELECT id FROM suporte_conversas
                 WHERE status != 'encerrada'
                   AND COALESCE(cliente_visto_em, criado_em) < ?",
                [$limite]
            )
            ->getResultArray();

        foreach ($ids as $linha) {
            $this->encerrar((int) $linha['id'], 'sistema');
        }

        return count($ids);
    }

    // ------------------------------------------------------------------
    // Consultas auxiliares
    // ------------------------------------------------------------------

    /**
     * @return array<string, mixed>|null
     */
    public function conversa(int $id): ?array
    {
        $linha = $this->db->table('suporte_conversas')->where('id', $id)->get()->getRowArray();

        return $linha === null ? null : $linha;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function porProtocolo(string $protocolo): ?array
    {
        $linha = $this->db->table('suporte_conversas')->where('protocolo', $protocolo)->get()->getRowArray();

        return $linha === null ? null : $linha;
    }

    /** Total de conversas aguardando (para o badge do admin). */
    public function totalAguardando(): int
    {
        return $this->db->table('suporte_conversas')->where('status', 'aguardando')->countAllResults();
    }

    /**
     * Quantidade de mensagens não lidas por um dos lados.
     *
     * @param array<string, mixed> $conv
     * @param 'cliente'|'atendente' $lado
     */
    public function comNaoLidas(array $conv, string $lado): array
    {
        $visto    = $lado === 'cliente' ? ($conv['cliente_visto_em'] ?? null) : ($conv['atendente_visto_em'] ?? null);
        $remetente = $lado === 'cliente' ? 'atendente' : 'cliente';

        $builder = $this->db->table('suporte_mensagens')
            ->where('conversa_id', (int) $conv['id'])
            ->where('autor_tipo', $remetente);

        if (! empty($visto)) {
            $builder->where('criado_em >', $visto);
        }

        $conv['nao_lidas'] = $builder->countAllResults();
        $conv['nome_exibicao'] = $this->nomeExibicao($conv);

        return $conv;
    }

    /**
     * @param array<string, mixed> $conv
     */
    public function nomeExibicao(array $conv): string
    {
        $nome = trim((string) ($conv['nome'] ?? ''));

        return $nome !== '' ? $nome : 'Visitante';
    }

    /**
     * @param array{usuario_id?: int|null, token?: string|null} $identidade
     */
    private function aplicarIdentidade($builder, array $identidade): void
    {
        if (! empty($identidade['conversa_id'])) {
            $builder->where('id', (int) $identidade['conversa_id']);

            return;
        }

        if (! empty($identidade['usuario_id'])) {
            $builder->where('usuario_id', (int) $identidade['usuario_id']);

            return;
        }

        if (! empty($identidade['token'])) {
            $builder->where('visitante_token', (string) $identidade['token']);

            return;
        }

        // Sem identidade: não casa com nenhuma conversa.
        $builder->where('id', -1);
    }

    /**
     * Expiração para conversas abertas dentro de um evento: data do evento + 10 dias.
     */
    private function expiracaoParaEvento(?int $eventoId): ?string
    {
        if (empty($eventoId)) {
            return null;
        }

        $evento = $this->db->table('eventos')->select('data_evento')->where('id', $eventoId)->get()->getRowArray();
        $data   = $evento['data_evento'] ?? null;

        if (empty($data)) {
            return null;
        }

        return date('Y-m-d H:i:s', strtotime((string) $data . ' 23:59:59') + 10 * 86400);
    }

    private function gerarProtocolo(): string
    {
        $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $codigo = 'SUP-';
            for ($i = 0; $i < 6; $i++) {
                $codigo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
            }
        } while ($this->db->table('suporte_conversas')->where('protocolo', $codigo)->countAllResults() > 0);

        return $codigo;
    }

    private function limitar($valor, int $limite): ?string
    {
        $valor = trim((string) $valor);

        return $valor === '' ? null : mb_substr($valor, 0, $limite);
    }
}
