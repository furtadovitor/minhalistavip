<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Credenciais do "Entrar com Google" (OAuth 2.0 / OpenID Connect).
 *
 * Configure no .env (nunca versione os segredos):
 *
 *   google.clientId     = 1234567890-xxxxxxxx.apps.googleusercontent.com
 *   google.clientSecret = GOCSPX-xxxxxxxxxxxxxxxx
 *   google.redirectUri  = (opcional) padrão: site_url('auth/google/callback')
 *
 * No Google Cloud, cadastre em "URIs de redirecionamento autorizados" a MESMA
 * URL de retorno (localhost e produção).
 */
class Google extends BaseConfig
{
    public string $clientId = '';

    public string $clientSecret = '';

    public string $redirectUri = '';

    /**
     * O login com Google está configurado?
     */
    public function configurado(): bool
    {
        return trim($this->clientId) !== '' && trim($this->clientSecret) !== '';
    }
}
