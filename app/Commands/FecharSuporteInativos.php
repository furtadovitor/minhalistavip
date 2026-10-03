<?php

namespace App\Commands;

use App\Services\SuporteService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Encerra conversas de suporte sem presença recente do cliente.
 *
 * Uso recomendado no cron (a cada poucos minutos):
 *   php spark suporte:fechar-inativos
 */
class FecharSuporteInativos extends BaseCommand
{
    protected $group       = 'Suporte';
    protected $name        = 'suporte:fechar-inativos';
    protected $description = 'Encerra conversas do chat de suporte inativas (sem presença do cliente).';
    protected $usage       = 'suporte:fechar-inativos [minutos]';
    protected $arguments   = [
        'minutos' => 'Minutos de inatividade (padrão: 5).',
    ];
    protected $options     = [];

    public function run(array $params)
    {
        $minutos = max(1, (int) ($params[0] ?? SuporteService::TIMEOUT_MINUTOS));

        CLI::write('Encerrando conversas de suporte inativas há mais de ' . $minutos . ' minuto(s)...', 'yellow');

        $total = (new SuporteService())->fecharInativos($minutos);

        CLI::write('Conversas encerradas: ' . $total, $total > 0 ? 'green' : 'white');
    }
}
