<?php

namespace App\Services;

use App\Entities\Evento;
use App\Models\RsvpAcompanhanteModel;
use App\Models\RsvpConfirmacaoModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Lista de convidados (RSVP) do evento.
 *
 * Regras:
 *  - a confirmação pública entra como 'pendente' e precisa ser homologada;
 *  - a contagem considera as PESSOAS (registro + acompanhantes);
 *  - o limite do evento (limite_convidados) é respeitado na aprovação e no público.
 */
class ConvidadoService
{
    /** Categorias válidas de acompanhante (radio no hotsite). */
    public const CATEGORIAS = ['adulto', 'crianca', 'bebe'];

    protected RsvpConfirmacaoModel $convidados;

    protected RsvpAcompanhanteModel $acompanhantes;

    public function __construct(?RsvpConfirmacaoModel $convidados = null, ?RsvpAcompanhanteModel $acompanhantes = null)
    {
        $this->convidados    = $convidados ?? new RsvpConfirmacaoModel();
        $this->acompanhantes = $acompanhantes ?? new RsvpAcompanhanteModel();
    }

    /**
     * @param array{status?: string|null, busca?: string|null, presenca?: string|null, categoria?: string|null, ordem?: string|null} $filtros
     * @return list<array<string, mixed>>
     */
    public function listar(int $eventoId, array $filtros = []): array
    {
        return $this->convidados->doEvento($eventoId, $filtros);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PageNotFoundException
     */
    public function um(int $eventoId, int $id): array
    {
        $convidado = $this->convidados->find($id);

        if ($convidado === null || (int) $convidado['evento_id'] !== $eventoId) {
            throw PageNotFoundException::forPageNotFound('Convidado não encontrado.');
        }

        return $convidado;
    }

    /**
     * Acompanhantes detalhados de um convidado (nome, idade e menor/maior).
     *
     * @return list<array<string, mixed>>
     */
    public function acompanhantes(int $convidadoId): array
    {
        return $this->acompanhantes->daConfirmacao($convidadoId);
    }

    /**
     * Totais de menores e maiores do evento.
     *
     * @return array{menores:int,maiores:int,total:int}
     */
    public function resumoAcompanhantes(int $eventoId): array
    {
        return $this->acompanhantes->resumoEvento($eventoId);
    }

    /**
     * Acompanhantes de vários convidados, agrupados por id do convidado.
     *
     * @param list<int> $convidadoIds
     * @return array<int, list<array<string, mixed>>>
     */
    public function acompanhantesPorConvidados(array $convidadoIds): array
    {
        return $this->acompanhantes->porConfirmacoes($convidadoIds);
    }

    /**
     * Registra um acompanhante. A categoria define menor/maior.
     *
     * A quantidade declarada do convidado é sincronizada para cima, para que o
     * novo acompanhante passe a contar no total de pessoas e no limite.
     *
     * @param array{nome?: string, categoria?: string} $dados
     * @return array{ok: bool, mensagem: string}
     */
    public function adicionarAcompanhante(int $eventoId, int $convidadoId, array $dados, ?Evento $evento = null): array
    {
        helper('formato');

        $convidado = $this->um($eventoId, $convidadoId);

        $nome = trim((string) ($dados['nome'] ?? ''));

        if (mb_strlen($nome) < 3) {
            return ['ok' => false, 'mensagem' => 'Informe o nome completo do acompanhante.'];
        }

        $categoria = trim((string) ($dados['categoria'] ?? ''));

        if (! in_array($categoria, self::CATEGORIAS, true)) {
            return ['ok' => false, 'mensagem' => 'Escolha a categoria do acompanhante.'];
        }

        $ok = $this->acompanhantes->insert([
            'rsvp_confirmacao_id' => $convidadoId,
            'nome'                => $nome,
            'categoria'           => $categoria,
            'idade'               => null,
            'menor'               => $this->classificarMenorCategoria($categoria),
        ]);

        if ($ok === false) {
            return ['ok' => false, 'mensagem' => 'Não foi possível salvar o acompanhante.'];
        }

        $detalhados = count($this->acompanhantes->daConfirmacao($convidadoId));

        if ($detalhados > (int) $convidado['quantidade_acompanhantes']) {
            if ($evento !== null && $convidado['status'] === 'confirmado') {
                $resumo = $this->resumo($evento);

                if ($resumo['limite'] !== null && $resumo['pessoas_confirmadas'] + 1 > $resumo['limite']) {
                    $this->acompanhantes->delete((int) $this->acompanhantes->getInsertID());

                    return [
                        'ok'       => false,
                        'mensagem' => 'Isso ultrapassaria o limite de ' . $resumo['limite'] . ' convidados'
                            . ' (restam ' . $resumo['vagas'] . ' vaga(s)).',
                    ];
                }
            }

            $this->convidados->update($convidadoId, ['quantidade_acompanhantes' => $detalhados]);
        }

        return ['ok' => true, 'mensagem' => $nome . ' (' . rotulo_categoria_acompanhante($categoria) . ') registrado(a).'];
    }

    /**
     * Atualiza nome e categoria de um acompanhante.
     *
     * @param array{nome?: string, categoria?: string} $dados
     * @return array{ok: bool, mensagem: string}
     */
    public function atualizarAcompanhante(int $eventoId, int $convidadoId, int $acompanhanteId, array $dados): array
    {
        helper('formato');

        $this->um($eventoId, $convidadoId);

        $acompanhante = $this->acompanhantes->find($acompanhanteId);

        if ($acompanhante === null || (int) $acompanhante['rsvp_confirmacao_id'] !== $convidadoId) {
            throw PageNotFoundException::forPageNotFound('Acompanhante não encontrado.');
        }

        $nome = trim((string) ($dados['nome'] ?? ''));

        if (mb_strlen($nome) < 3) {
            return ['ok' => false, 'mensagem' => 'Informe o nome completo do acompanhante.'];
        }

        $categoria = trim((string) ($dados['categoria'] ?? ''));

        if (! in_array($categoria, self::CATEGORIAS, true)) {
            return ['ok' => false, 'mensagem' => 'Escolha a categoria do acompanhante.'];
        }

        $this->acompanhantes->update($acompanhanteId, [
            'nome'      => $nome,
            'categoria' => $categoria,
            'idade'     => null,
            'menor'     => $this->classificarMenorCategoria($categoria),
        ]);

        return ['ok' => true, 'mensagem' => 'Acompanhante atualizado(a) para ' . $nome . '.'];
    }

    /**
     * @return array{ok: bool, mensagem: string}
     */
    public function removerAcompanhante(int $eventoId, int $convidadoId, int $acompanhanteId): array
    {
        $this->um($eventoId, $convidadoId);

        $acompanhante = $this->acompanhantes->find($acompanhanteId);

        if ($acompanhante === null || (int) $acompanhante['rsvp_confirmacao_id'] !== $convidadoId) {
            throw PageNotFoundException::forPageNotFound('Acompanhante não encontrado.');
        }

        $this->acompanhantes->delete($acompanhanteId);

        return ['ok' => true, 'mensagem' => 'Acompanhante removido.'];
    }

    /**
     * Valida e normaliza as linhas de acompanhantes do formulário público.
     *
     * @param array<int|string, mixed> $nomes
     * @param array<int|string, mixed> $categorias
     * @return array{linhas: list<array{nome: string, categoria: string, menor: int}>, erros: list<string>}
     */
    public function validarAcompanhantes(array $nomes, array $categorias): array
    {
        $linhas = [];
        $erros  = [];

        $nomes      = array_values($nomes);
        $categorias = array_values($categorias);
        $total      = max(count($nomes), count($categorias));

        for ($i = 0; $i < $total; $i++) {
            $nome      = trim((string) ($nomes[$i] ?? ''));
            $categoria = trim((string) ($categorias[$i] ?? ''));

            // Linha totalmente vazia é ignorada.
            if ($nome === '' && $categoria === '') {
                continue;
            }

            $numero = $i + 1;

            if (mb_strlen($nome) < 3) {
                $erros[] = "Informe o nome completo do acompanhante {$numero}.";
                continue;
            }

            if (! in_array($categoria, self::CATEGORIAS, true)) {
                $erros[] = "Escolha a categoria do acompanhante {$numero}.";
                continue;
            }

            $linhas[] = [
                'nome'      => $nome,
                'categoria' => $categoria,
                'menor'     => $this->classificarMenorCategoria($categoria),
            ];
        }

        return ['linhas' => $linhas, 'erros' => $erros];
    }

    /**
     * Grava (já validados) os acompanhantes de uma confirmação.
     *
     * @param list<array{nome: string, categoria: string}> $linhas
     */
    public function gravarAcompanhantes(int $convidadoId, array $linhas): void
    {
        foreach ($linhas as $linha) {
            $categoria = (string) ($linha['categoria'] ?? '');

            $this->acompanhantes->insert([
                'rsvp_confirmacao_id' => $convidadoId,
                'nome'                => $linha['nome'],
                'categoria'           => $categoria,
                'idade'               => null,
                'menor'               => $this->classificarMenorCategoria($categoria),
            ]);
        }
    }

    /**
     * Deriva menor/maior da categoria: criança e bebê ⇒ menor.
     */
    private function classificarMenorCategoria(?string $categoria): ?int
    {
        if ($categoria === null || $categoria === '') {
            return null;
        }

        return $categoria === 'adulto' ? 0 : 1;
    }

    /**
     * Deriva menor/maior a partir da idade: menor = idade < 18.
     */
    private function classificarMenor(?int $idade): ?int
    {
        if ($idade === null) {
            return null;
        }

        return $idade < 18 ? 1 : 0;
    }

    private function rotuloIdade(?int $idade): string
    {
        if ($idade === null) {
            return 'idade não informada';
        }

        return $idade . ' ano(s) — ' . ($idade < 18 ? 'menor' : 'maior') . ' de idade';
    }

    /**
     * @return array{pendentes:int,confirmados:int,recusados:int,pessoas_pendentes:int,pessoas_confirmadas:int,limite:int|null,vagas:int|null,percentual:int|null}
     */
    public function resumo(Evento $evento): array
    {
        $contagem = $this->convidados->contagem((int) $evento->id);
        $limite   = $evento->limite_convidados !== null ? (int) $evento->limite_convidados : null;

        return $contagem + [
            'limite'     => $limite,
            'vagas'      => $limite !== null ? max(0, $limite - $contagem['pessoas_confirmadas']) : null,
            'percentual' => $limite !== null && $limite > 0
                ? min(100, (int) round($contagem['pessoas_confirmadas'] / $limite * 100))
                : null,
        ];
    }

    /**
     * O evento já atingiu o limite de pessoas confirmadas?
     */
    public function limiteAtingido(Evento $evento): bool
    {
        if ($evento->limite_convidados === null) {
            return false;
        }

        return $this->convidados->contagem((int) $evento->id)['pessoas_confirmadas']
            >= (int) $evento->limite_convidados;
    }

    /**
     * Convidados confirmados para a tela de check-in presencial.
     *
     * @return list<array<string, mixed>>
     */
    public function listarParaCheckin(int $eventoId, ?string $busca = null, bool $somenteAusentes = false): array
    {
        return $this->convidados->paraCheckin($eventoId, $busca, $somenteAusentes);
    }

    /**
     * Registra a chegada do convidado (e acompanhantes).
     *
     * @return array{ok: bool, mensagem: string}
     */
    public function checkIn(int $eventoId, int $convidadoId, int $usuarioId): array
    {
        $convidado = $this->um($eventoId, $convidadoId);

        if ($convidado['status'] !== 'confirmado') {
            return ['ok' => false, 'mensagem' => 'Só é possível fazer check-in de convidados confirmados.'];
        }

        if (! empty($convidado['check_in_em'])) {
            return ['ok' => true, 'mensagem' => $convidado['nome'] . ' já havia feito check-in.'];
        }

        $this->convidados->update($convidadoId, [
            'check_in_em'  => date('Y-m-d H:i:s'),
            'check_in_por' => $usuarioId,
        ]);

        return [
            'ok'       => true,
            'mensagem' => 'Check-in de ' . $convidado['nome']
                . ' — ' . $this->pessoasDo($convidado) . ' pessoa(s).',
        ];
    }

    /**
     * Desfaz o check-in (marcação por engano).
     *
     * @return array{ok: bool, mensagem: string}
     */
    public function desfazerCheckIn(int $eventoId, int $convidadoId): array
    {
        $convidado = $this->um($eventoId, $convidadoId);

        $this->convidados->update($convidadoId, ['check_in_em' => null, 'check_in_por' => null]);

        return ['ok' => true, 'mensagem' => 'Check-in de ' . $convidado['nome'] . ' desfeito.'];
    }

    /**
     * Quantas pessoas a confirmação representa (titular + acompanhantes).
     *
     * @param array<string, mixed> $convidado
     */
    private function pessoasDo(array $convidado): int
    {
        return (int) ($convidado['quantidade_acompanhantes'] ?? 0) + 1;
    }

    /**
     * @return array{ok: bool, mensagem: string}
     */
    public function aprovar(int $eventoId, int $convidadoId, Evento $evento): array
    {
        $convidado = $this->um($eventoId, $convidadoId);

        if ($convidado['status'] === 'confirmado') {
            return ['ok' => true, 'mensagem' => 'Este convidado já está confirmado.'];
        }

        $pessoas = (int) $convidado['quantidade_acompanhantes'] + 1;
        $resumo  = $this->resumo($evento);

        if ($resumo['limite'] !== null && $resumo['pessoas_confirmadas'] + $pessoas > $resumo['limite']) {
            return [
                'ok'       => false,
                'mensagem' => 'Isso ultrapassaria o limite de ' . $resumo['limite'] . ' convidados'
                    . ' (restam ' . $resumo['vagas'] . ' vaga(s)). Ajuste o limite do evento ou recuse.',
            ];
        }

        $this->convidados->update($convidadoId, ['status' => 'confirmado']);

        return ['ok' => true, 'mensagem' => $convidado['nome'] . ' confirmado(a) — ' . $pessoas . ' pessoa(s).'];
    }

    /**
     * @return array{ok: bool, mensagem: string}
     */
    public function recusar(int $eventoId, int $convidadoId): array
    {
        $convidado = $this->um($eventoId, $convidadoId);

        $this->convidados->update($convidadoId, [
            'status'       => 'recusado',
            'check_in_em'  => null,
            'check_in_por' => null,
        ]);

        return ['ok' => true, 'mensagem' => $convidado['nome'] . ' marcado(a) como recusado(a).'];
    }

    /**
     * @return array{ok: bool, mensagem: string}
     */
    public function remover(int $eventoId, int $convidadoId): array
    {
        $convidado = $this->um($eventoId, $convidadoId);
        $this->convidados->delete($convidadoId);

        return ['ok' => true, 'mensagem' => 'Convidado "' . $convidado['nome'] . '" removido.'];
    }

    /**
     * Adiciona um convidado já homologado (confirmado).
     *
     * @param array<string, mixed> $dados
     * @return array{ok: bool, mensagem: string}
     */
    public function adicionar(int $eventoId, array $dados, Evento $evento): array
    {
        $nome = trim((string) ($dados['nome'] ?? ''));

        if (mb_strlen($nome) < 3) {
            return ['ok' => false, 'mensagem' => 'Informe o nome do convidado.'];
        }

        $acompanhantes = max(0, (int) ($dados['quantidade_acompanhantes'] ?? 0));
        $pessoas       = $acompanhantes + 1;
        $resumo        = $this->resumo($evento);

        if ($resumo['limite'] !== null && $resumo['pessoas_confirmadas'] + $pessoas > $resumo['limite']) {
            return ['ok' => false, 'mensagem' => 'Limite de convidados atingido (' . $resumo['limite'] . ' pessoas).'];
        }

        $ok = $this->convidados->insert([
            'evento_id'                => $eventoId,
            'nome'                     => $nome,
            'email'                    => trim((string) ($dados['email'] ?? '')) ?: null,
            'telefone'                 => trim((string) ($dados['telefone'] ?? '')) ?: null,
            'quantidade_acompanhantes' => $acompanhantes,
            'status'                   => 'confirmado',
            'observacao'               => trim((string) ($dados['observacao'] ?? '')) ?: null,
        ]);

        return $ok === false
            ? ['ok' => false, 'mensagem' => 'Não foi possível adicionar o convidado.']
            : ['ok' => true, 'mensagem' => $nome . ' adicionado(a) como confirmado(a).'];
    }

    /**
     * Atualiza os dados do convidado (titular).
     *
     * A quantidade declarada não pode ficar menor que os acompanhantes já
     * detalhados, e o aumento em convidado confirmado respeita o limite.
     *
     * @param array<string, mixed> $dados
     * @return array{ok: bool, mensagem: string}
     */
    public function atualizar(int $eventoId, int $convidadoId, array $dados, Evento $evento): array
    {
        $convidado = $this->um($eventoId, $convidadoId);

        $nome = trim((string) ($dados['nome'] ?? ''));

        if (mb_strlen($nome) < 3) {
            return ['ok' => false, 'mensagem' => 'Informe o nome do convidado.'];
        }

        $email = trim((string) ($dados['email'] ?? '')) ?: null;

        if ($email !== null && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'mensagem' => 'Informe um e-mail válido.'];
        }

        $acompanhantes = max(0, (int) ($dados['quantidade_acompanhantes'] ?? 0));
        $detalhados    = count($this->acompanhantes->daConfirmacao($convidadoId));

        if ($acompanhantes < $detalhados) {
            return [
                'ok'       => false,
                'mensagem' => 'Você já detalhou ' . $detalhados . ' acompanhante(s).'
                    . ' Remova os excedentes antes de reduzir a quantidade.',
            ];
        }

        $atual = (int) $convidado['quantidade_acompanhantes'];

        if ($convidado['status'] === 'confirmado' && $acompanhantes > $atual) {
            $resumo = $this->resumo($evento);

            if ($resumo['limite'] !== null && $resumo['pessoas_confirmadas'] + ($acompanhantes - $atual) > $resumo['limite']) {
                return [
                    'ok'       => false,
                    'mensagem' => 'Isso ultrapassaria o limite de ' . $resumo['limite'] . ' convidados'
                        . ' (restam ' . $resumo['vagas'] . ' vaga(s)).',
                ];
            }
        }

        $this->convidados->update($convidadoId, [
            'nome'                     => $nome,
            'email'                    => $email,
            'telefone'                 => trim((string) ($dados['telefone'] ?? '')) ?: null,
            'quantidade_acompanhantes' => $acompanhantes,
            'observacao'               => trim((string) ($dados['observacao'] ?? '')) ?: null,
        ]);

        return ['ok' => true, 'mensagem' => 'Dados de ' . $nome . ' atualizados.'];
    }
}
