<?php

use App\Entities\Evento;
use App\Models\RsvpAcompanhanteModel;
use App\Models\RsvpConfirmacaoModel;
use App\Services\ConvidadoService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ConvidadoServiceTest extends CIUnitTestCase
{
    /**
     * @param array<string, mixed> $dados
     */
    private function convidado(array $dados = []): array
    {
        return array_merge([
            'id'                       => 1,
            'evento_id'                => 10,
            'nome'                     => 'Ana Souza',
            'email'                    => null,
            'telefone'                 => null,
            'quantidade_acompanhantes' => 0,
            'status'                   => 'pendente',
            'observacao'               => null,
            'check_in_em'              => null,
        ], $dados);
    }

    private function evento(?int $limite = null): Evento
    {
        return new Evento([
            'id'                => 10,
            'status'            => 'publicado',
            'limite_convidados' => $limite,
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function contagem(int $pessoasConfirmadas = 0): array
    {
        return [
            'pendentes'           => 0,
            'confirmados'         => 0,
            'recusados'           => 0,
            'pessoas_pendentes'   => 0,
            'pessoas_confirmadas' => $pessoasConfirmadas,
            'presentes'           => 0,
            'pessoas_presentes'   => 0,
        ];
    }

    public function testAtualizarRejeitaNomeCurto(): void
    {
        $convidados    = $this->createMock(RsvpConfirmacaoModel::class);
        $acompanhantes = $this->createMock(RsvpAcompanhanteModel::class);

        $convidados->method('find')->willReturn($this->convidado());

        $resultado = (new ConvidadoService($convidados, $acompanhantes))
            ->atualizar(10, 1, ['nome' => 'Ab'], $this->evento());

        $this->assertFalse($resultado['ok']);
    }

    public function testAtualizarRejeitaEmailInvalido(): void
    {
        $convidados    = $this->createMock(RsvpConfirmacaoModel::class);
        $acompanhantes = $this->createMock(RsvpAcompanhanteModel::class);

        $convidados->method('find')->willReturn($this->convidado());

        $resultado = (new ConvidadoService($convidados, $acompanhantes))
            ->atualizar(10, 1, ['nome' => 'Ana Souza', 'email' => 'email-invalido'], $this->evento());

        $this->assertFalse($resultado['ok']);
    }

    public function testAtualizarRejeitaQuantidadeMenorQueAcompanhantesDetalhados(): void
    {
        $convidados    = $this->createMock(RsvpConfirmacaoModel::class);
        $acompanhantes = $this->createMock(RsvpAcompanhanteModel::class);

        $convidados->method('find')->willReturn($this->convidado());
        $acompanhantes->method('daConfirmacao')->willReturn([['id' => 1], ['id' => 2]]);

        $resultado = (new ConvidadoService($convidados, $acompanhantes))
            ->atualizar(10, 1, ['nome' => 'Ana Souza', 'quantidade_acompanhantes' => 1], $this->evento());

        $this->assertFalse($resultado['ok']);
        $this->assertStringContainsString('detalhou 2', $resultado['mensagem']);
    }

    public function testAtualizarBloqueiaAumentoAcimaDoLimite(): void
    {
        $convidados    = $this->createMock(RsvpConfirmacaoModel::class);
        $acompanhantes = $this->createMock(RsvpAcompanhanteModel::class);

        $convidados->method('find')->willReturn($this->convidado([
            'status'                   => 'confirmado',
            'quantidade_acompanhantes' => 1,
        ]));
        $convidados->method('contagem')->willReturn($this->contagem(2));
        $acompanhantes->method('daConfirmacao')->willReturn([]);

        $convidados->expects($this->never())->method('update');

        $resultado = (new ConvidadoService($convidados, $acompanhantes))
            ->atualizar(10, 1, ['nome' => 'Ana Souza', 'quantidade_acompanhantes' => 3], $this->evento(3));

        $this->assertFalse($resultado['ok']);
        $this->assertStringContainsString('limite de 3', $resultado['mensagem']);
    }

    public function testAtualizarSalvaCamposNormalizados(): void
    {
        $convidados    = $this->createMock(RsvpConfirmacaoModel::class);
        $acompanhantes = $this->createMock(RsvpAcompanhanteModel::class);

        $convidados->method('find')->willReturn($this->convidado());
        $acompanhantes->method('daConfirmacao')->willReturn([]);

        $convidados->expects($this->once())
            ->method('update')
            ->with(1, [
                'nome'                     => 'Ana Souza',
                'email'                    => 'ana@example.com',
                'telefone'                 => null,
                'quantidade_acompanhantes' => 2,
                'observacao'               => null,
            ]);

        $resultado = (new ConvidadoService($convidados, $acompanhantes))->atualizar(10, 1, [
            'nome'                     => 'Ana Souza',
            'email'                    => 'ana@example.com',
            'telefone'                 => '',
            'quantidade_acompanhantes' => 2,
            'observacao'               => '',
        ], $this->evento());

        $this->assertTrue($resultado['ok']);
    }

    public function testAtualizarAcompanhanteValidaCategoria(): void
    {
        $convidados    = $this->createMock(RsvpConfirmacaoModel::class);
        $acompanhantes = $this->createMock(RsvpAcompanhanteModel::class);

        $convidados->method('find')->willReturn($this->convidado());
        $acompanhantes->method('find')->willReturn(['id' => 5, 'rsvp_confirmacao_id' => 1, 'nome' => 'Maria']);

        $resultado = (new ConvidadoService($convidados, $acompanhantes))
            ->atualizarAcompanhante(10, 1, 5, ['nome' => 'Maria Silva', 'categoria' => 'pet']);

        $this->assertFalse($resultado['ok']);
    }

    public function testAtualizarAcompanhanteRecalculaMenor(): void
    {
        $convidados    = $this->createMock(RsvpConfirmacaoModel::class);
        $acompanhantes = $this->createMock(RsvpAcompanhanteModel::class);

        $convidados->method('find')->willReturn($this->convidado());
        $acompanhantes->method('find')->willReturn(['id' => 5, 'rsvp_confirmacao_id' => 1, 'nome' => 'Maria']);

        $acompanhantes->expects($this->once())
            ->method('update')
            ->with(5, [
                'nome'      => 'Maria Silva',
                'categoria' => 'crianca',
                'idade'     => null,
                'menor'     => 1,
            ]);

        $resultado = (new ConvidadoService($convidados, $acompanhantes))
            ->atualizarAcompanhante(10, 1, 5, ['nome' => 'Maria Silva', 'categoria' => 'crianca']);

        $this->assertTrue($resultado['ok']);
    }

    public function testAdicionarAcompanhanteSincronizaQuantidadeDeclarada(): void
    {
        $convidados    = $this->createMock(RsvpConfirmacaoModel::class);
        $acompanhantes = $this->createMock(RsvpAcompanhanteModel::class);

        $convidados->method('find')->willReturn($this->convidado());
        $acompanhantes->method('insert')->willReturn(true);
        $acompanhantes->method('daConfirmacao')->willReturn([['id' => 7, 'nome' => 'Joana']]);

        $convidados->expects($this->once())
            ->method('update')
            ->with(1, ['quantidade_acompanhantes' => 1]);

        $resultado = (new ConvidadoService($convidados, $acompanhantes))
            ->adicionarAcompanhante(10, 1, ['nome' => 'Joana Silva', 'categoria' => 'adulto']);

        $this->assertTrue($resultado['ok']);
    }

    public function testAdicionarAcompanhanteBloqueiaLimiteEDesfaz(): void
    {
        $convidados    = $this->createMock(RsvpConfirmacaoModel::class);
        $acompanhantes = $this->createMock(RsvpAcompanhanteModel::class);

        $convidados->method('find')->willReturn($this->convidado(['status' => 'confirmado']));
        $convidados->method('contagem')->willReturn($this->contagem(1));
        $acompanhantes->method('insert')->willReturn(true);
        $acompanhantes->method('daConfirmacao')->willReturn([['id' => 7, 'nome' => 'Joana']]);
        $acompanhantes->expects($this->once())->method('delete');

        $resultado = (new ConvidadoService($convidados, $acompanhantes))
            ->adicionarAcompanhante(10, 1, ['nome' => 'Joana Silva', 'categoria' => 'adulto'], $this->evento(1));

        $this->assertFalse($resultado['ok']);
    }

    public function testResumoCalculaVagasEPercentual(): void
    {
        $convidados    = $this->createMock(RsvpConfirmacaoModel::class);
        $acompanhantes = $this->createMock(RsvpAcompanhanteModel::class);

        $convidados->method('contagem')->willReturn($this->contagem(3));

        $resumo = (new ConvidadoService($convidados, $acompanhantes))->resumo($this->evento(5));

        $this->assertSame(5, $resumo['limite']);
        $this->assertSame(2, $resumo['vagas']);
        $this->assertSame(60, $resumo['percentual']);
    }
}
