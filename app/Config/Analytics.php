<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * IDs de rastreamento (GTM / GA4 / Meta Pixel).
 *
 * Configure no `.env` (produção), ex.:
 *   analytics.gtmId       = GTM-XXXXXXX
 *   analytics.ga4Id       = G-XXXXXXXXXX
 *   analytics.metaPixelId = 123456789012345
 *
 * Recomendado: usar só o GTM (os demais entram por dentro dele). O GA4/Pixel
 * diretos ficam como alternativa quando não houver contêiner do GTM.
 * Sem nenhum ID preenchido, nada é carregado (o site não muda).
 */
class Analytics extends BaseConfig
{
    public string $gtmId       = '';
    public string $ga4Id       = '';
    public string $metaPixelId = '';

    public function __construct()
    {
        parent::__construct();

        $this->gtmId       = trim((string) env('analytics.gtmId', ''));
        $this->ga4Id       = trim((string) env('analytics.ga4Id', ''));
        $this->metaPixelId = trim((string) env('analytics.metaPixelId', ''));
    }
}
