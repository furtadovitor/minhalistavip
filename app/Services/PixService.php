<?php

namespace App\Services;

use App\Entities\Pedido;

/**
 * Gateway PIX em modo SANDBOX (interno).
 *
 * Gera uma cobrança com um BR Code (PIX Copia e Cola) válido no formato EMV,
 * apontando para a chave da plataforma. Não conversa com nenhum provedor
 * externo: a confirmação é feita pelo endpoint de webhook `webhooks/pix`
 * ou pelo botão de simulação disponível apenas em ambiente de desenvolvimento.
 *
 * Para produção, basta substituir a geração da cobrança por uma chamada à API
 * do provedor (Mercado Pago, Asasas, Gerencianet, etc.) mantendo o mesmo
 * contrato de retorno e o fluxo de PagamentoService::confirmar().
 */
class PixService
{
    public const GATEWAY = 'sandbox';
    public const TIPO    = 'pix';

    protected ConfiguracaoService $config;

    public function __construct(?ConfiguracaoService $config = null)
    {
        $this->config = $config ?? new ConfiguracaoService();
    }

    /**
     * Monta a cobrança PIX do pedido.
     *
     * @return array{
     *     gateway: string,
     *     gateway_transacao_id: string,
     *     tipo: string,
     *     status: string,
     *     valor: float,
     *     expira_em: string,
     *     copia_e_cola: string,
     *     chave: string,
     *     recebedor: string
     * }
     */
    public function gerarCobranca(Pedido $pedido): array
    {
        $transacaoId = $this->transacaoId($pedido);
        $expiracao   = $this->config->inteiro('pix_expiracao_minutos', 30);
        $chave       = $this->config->texto('pix_chave_plataforma', 'pagamentos@minhalistavip.com.br');

        return [
            'gateway'              => self::GATEWAY,
            'gateway_transacao_id' => $transacaoId,
            'tipo'                 => self::TIPO,
            'status'               => 'pendente',
            'valor'                => (float) $pedido->valor_total,
            'expira_em'            => date('Y-m-d H:i:s', time() + ($expiracao * 60)),
            'copia_e_cola'         => $this->montarBrCode((float) $pedido->valor_total, $transacaoId),
            'chave'                => $chave,
            'recebedor'            => $this->config->texto('pix_nome_plataforma', 'Minha Lista VIP'),
        ];
    }

    /**
     * Identificador da transação no gateway (também usado como txid do EMV).
     */
    public function transacaoId(Pedido $pedido): string
    {
        $protocolo = preg_replace('/[^A-Za-z0-9]/', '', (string) $pedido->protocolo);

        return 'SBX' . strtoupper(substr($protocolo, 0, 24));
    }

    /**
     * Verifica se a confirmação veio de uma origem autorizada.
     */
    public function tokenValido(?string $token): bool
    {
        $esperado = $this->webhookToken();

        return $esperado !== '' && is_string($token) && hash_equals($esperado, $token);
    }

    public function webhookToken(): string
    {
        return $this->config->texto('pix_webhook_token', 'sandbox-token');
    }

    /**
     * Constrói o payload "Copia e Cola" (BR Code) no padrão EMV do Bacen.
     */
    public function montarBrCode(float $valor, string $txid): string
    {
        $txid = $this->apenasAlfanumerico($txid, 25);

        $payload = ''
            . $this->campo('00', '01')
            . $this->campo('01', '12')
            . $this->campo('26', $this->campo('00', 'br.gov.bcb.pix') . $this->campo('01', $this->config->texto('pix_chave_plataforma', 'pagamentos@minhalistavip.com.br')))
            . $this->campo('52', '0000')
            . $this->campo('53', '986')
            . $this->campo('54', number_format($valor, 2, '.', ''))
            . $this->campo('58', 'BR')
            . $this->campo('59', $this->semAcentos($this->config->texto('pix_nome_plataforma', 'Minha Lista VIP'), 25))
            . $this->campo('60', $this->semAcentos($this->config->texto('pix_cidade_plataforma', 'SAO PAULO'), 15))
            . $this->campo('62', $this->campo('05', $txid))
            . '6304';

        return $payload . $this->crc16($payload);
    }

    /**
     * Campo EMV: ID (2) + tamanho (2) + valor.
     */
    private function campo(string $id, string $valor): string
    {
        return $id . str_pad((string) strlen($valor), 2, '0', STR_PAD_LEFT) . $valor;
    }

    private function apenasAlfanumerico(string $valor, int $limite): string
    {
        $valor = preg_replace('/[^A-Za-z0-9]/', '', $valor) ?? '';

        return substr($valor, 0, $limite);
    }

    private function semAcentos(string $valor, int $limite): string
    {
        $mapa = [
            'á' => 'A', 'à' => 'A', 'ã' => 'A', 'â' => 'A', 'ä' => 'A',
            'é' => 'E', 'è' => 'E', 'ê' => 'E', 'ë' => 'E',
            'í' => 'I', 'ì' => 'I', 'î' => 'I', 'ï' => 'I',
            'ó' => 'O', 'ò' => 'O', 'õ' => 'O', 'ô' => 'O', 'ö' => 'O',
            'ú' => 'U', 'ù' => 'U', 'û' => 'U', 'ü' => 'U',
            'ç' => 'C', 'ñ' => 'N',
        ];

        $valor = strtr(mb_strtolower($valor), $mapa);

        return substr(mb_strtoupper($valor), 0, $limite);
    }

    /**
     * CRC16/CCITT-FALSE exigido pelo padrão do BR Code.
     */
    public function crc16(string $payload): string
    {
        $crc = 0xFFFF;

        for ($i = 0, $len = strlen($payload); $i < $len; $i++) {
            $crc ^= ord($payload[$i]) << 8;

            for ($bit = 0; $bit < 8; $bit++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
