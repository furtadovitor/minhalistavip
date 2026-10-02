<?php

namespace App\Commands;

use App\Services\ConciliacaoService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Conciliação e expiração de pedidos pendentes.
 *
 * Uso recomendado no cron (a cada 5 minutos):
 *   php spark pedidos:conciliar
 */
class ConciliarPedidos extends BaseCommand
{
    protected $group       = 'Pagamentos';
    protected $name        = 'pedidos:conciliar';
    protected $description = 'Consulta o gateway, confirma pagamentos pendentes e expira pedidos vencidos.';
    protected $usage       = 'pedidos:conciliar';
    protected $arguments   = [];
    protected $options     = [];

    public function run(array $params)
    {
        CLI::write('Conciliando pedidos pendentes...', 'yellow');

        $resultado = (new ConciliacaoService())->executar();

        CLI::write('Pedidos consultados no gateway: ' . $resultado['consultados'], 'white');
        CLI::write('Pagamentos confirmados: ' . $resultado['confirmados'], 'green');
        CLI::write('Pedidos expirados: ' . $resultado['expirados'], 'yellow');
    }
}
