<?php

namespace App\Services;

use Config\Database;

/**
 * Centraliza o cálculo financeiro dos pedidos.
 *
 * Regra de negócio (SCRIPT.md § 2.1):
 *  - quem_paga_taxa = 'convidado'   => valor_total = valor_presentes + valor_taxa
 *  - quem_paga_taxa = 'organizador' => valor_total = valor_presentes
 *                                       (o líquido do organizador é reduzido pela taxa)
 */
class TaxaService
{
    public const CHAVE_CONFIG_PADRAO = 'percentual_taxa_padrao';

    private ?float $percentualPadrao = null;

    /**
     * @return array{
     *     valor_presentes: float,
     *     percentual_taxa: float,
     *     valor_taxa: float,
     *     valor_total: float,
     *     quem_paga_taxa: string,
     *     liquido_organizador: float
     * }
     */
    public function calcular(float $valorPresentes, string $quemPagaTaxa = 'convidado', ?float $percentualTaxa = null): array
    {
        $valorPresentes = round($valorPresentes, 2);
        $percentual     = $percentualTaxa ?? $this->percentualPadrao();
        $valorTaxa      = round($valorPresentes * ($percentual / 100), 2);

        if ($quemPagaTaxa === 'convidado') {
            $valorTotal = round($valorPresentes + $valorTaxa, 2);
            $liquido    = $valorPresentes;
        } else {
            $valorTotal = $valorPresentes;
            $liquido    = round($valorPresentes - $valorTaxa, 2);
        }

        return [
            'valor_presentes'     => $valorPresentes,
            'percentual_taxa'     => $percentual,
            'valor_taxa'          => $valorTaxa,
            'valor_total'         => $valorTotal,
            'quem_paga_taxa'      => $quemPagaTaxa,
            'liquido_organizador' => $liquido,
        ];
    }

    /**
     * Percentual global configurado pelo SuperAdmin, com fallback seguro.
     */
    public function percentualPadrao(): float
    {
        if ($this->percentualPadrao !== null) {
            return $this->percentualPadrao;
        }

        $row = Database::connect()
            ->table('configuracoes')
            ->select('valor')
            ->where('chave', self::CHAVE_CONFIG_PADRAO)
            ->get()
            ->getRow();

        $this->percentualPadrao = $row !== null && is_numeric($row->valor)
            ? (float) $row->valor
            : 10.00;

        return $this->percentualPadrao;
    }
}
