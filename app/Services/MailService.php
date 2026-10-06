<?php

namespace App\Services;

use Config\Email;

/**
 * Envio de e-mails transacionais (redefinição de senha etc.).
 *
 * Configure no .env:
 *   email.protocol = smtp
 *   email.SMTPHost = smtp.hostinger.com
 *   email.SMTPUser = no-reply@seudominio.com.br
 *   email.SMTPPass = SUA-SENHA
 *   email.SMTPPort = 465
 *   email.SMTPCrypto = ssl
 *   email.fromEmail = no-reply@seudominio.com.br
 *   email.fromName = Minha Lista VIP
 */
class MailService
{
    protected Email $config;

    public function __construct(?Email $config = null)
    {
        $this->config = $config ?? config('Email');
    }

    /**
     * O envio está configurado? (SMTP com host, ou mail/sendmail nativo)
     */
    public function configurado(): bool
    {
        if ($this->config->protocol === 'smtp') {
            return trim($this->config->SMTPHost) !== '';
        }

        return true;
    }

    public function enviar(string $para, string $assunto, string $html): bool
    {
        $email = service('email');
        $email->clear(true);

        $host   = (string) (parse_url(base_url(), PHP_URL_HOST) ?: 'minhalistavip.com.br');
        $remetente = trim($this->config->fromEmail) !== '' ? $this->config->fromEmail : 'nao-responda@' . $host;
        $nome      = trim($this->config->fromName) !== '' ? $this->config->fromName : 'Minha Lista VIP';

        $email->setFrom($remetente, $nome);
        $email->setTo($para);
        $email->setSubject($assunto);
        $email->setMessage($html);
        $email->setMailType('html');

        if (! $email->send()) {
            log_message('error', 'Falha ao enviar e-mail para ' . $para . ': ' . $email->printDebugger(['subject']));

            return false;
        }

        return true;
    }
}
