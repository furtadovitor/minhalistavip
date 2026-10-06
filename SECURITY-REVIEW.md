# Revisão de Segurança — Minha Lista VIP

Revisão feita em 06/10/2026, antes do go-live. Cobre autenticação, controle de
acesso, uploads, XSS, pagamentos/webhooks, endpoints públicos, configuração e
infra de produção.

Legenda de severidade: **Crítico** · **Alto** · **Médio** · **Baixo**

---

## 1. Login e senha (reativado)

O login por senha estava desligado por uma constante de desenvolvimento:

- `AuthService::EXIGIR_SENHA` estava `false` → **qualquer pessoa entrava só com
  o e-mail** (inclusive o SuperAdmin). Agora está `true`.

Melhorias aplicadas em `app/Controllers/Auth.php` e `app/Services/AuthService.php`:

- Senha obrigatória na tela de login (`app/Views/auth/login.php`).
- **Mensagem genérica** de erro ("E-mail ou senha inválidos.") — não revela mais
  se o e-mail existe (enumeração de usuários).
- **Throttle de força bruta**: 5 tentativas por IP a cada 5 minutos.
- **Regeneração de sessão** no login e no logout (evita *session fixation*).
- Sessão revalidada a cada request no `AuthFilter` (ver item 2).

> **Redefinição de senha:** o fluxo de **"esqueci minha senha"** foi implementado
> (token de uso único por e-mail). Contas criadas pelo Google ou que usaram o
> modo "só e-mail" agora conseguem definir uma senha. Basta configurar o SMTP no
> `.env` (ver checklist). Usuários de teste do seeder têm senha:
> `admin@minhalistavip.com.br` / `Admin@123` e
> `organizador@minhalistavip.com.br` / `Demo@123`.

---

## 2. Correções aplicadas nesta revisão

| # | Severidade | Arquivo | Correção |
|---|---|---|---|
| 1 | Crítico | `app/Views/admin/suporte/index.php` | XSS armazenado (visitante → SuperAdmin) via `json_encode` sem `JSON_HEX_TAG`. Flag adicionada. |
| 2 | Alto | `app/Views/public/pedido.php` | XSS armazenado via `nome_convidado` no `json_encode` do Mercado Pago. Flags adicionadas. |
| 3 | Médio | `app/Views/host/eventos/aparencia.php`, `compartilhar.php` | Mesmo problema de JSON em `<script>` com o título do evento. Flags adicionadas. |
| 4 | Médio | `app/Services/UploadService.php` | Path traversal em `apagar()`: agora exige `realpath` confinado à subpasta e rejeita `..`. |
| 5 | Médio | `app/Controllers/Admin/Demos.php` | Campo `capa` do POST não é mais aceito como caminho de arquivo (só URL http(s) ou `uploads/demos/...`). |
| 6 | Médio/Alto | `app/Controllers/Host/Presentes.php`, `Admin/Catalogo.php` | `link_afiliado` só aceita URL `http(s)` (bloqueia `javascript:`). |
| 7 | Médio | `app/Controllers/Host/Convidados.php`, `Admin/Eventos.php` | CSV injection: valores iniciados por `= + - @` ganham apóstrofo. |
| 8 | Alto | `app/Filters/AuthFilter.php` | Revalida o usuário a cada request (status/nível); encerra a sessão de quem foi suspenso/excluído. |
| 9 | Crítico | `app/Services/Pix/SandboxGateway.php`, `MercadoPagoGateway.php`, `app/Controllers/Webhook/Pix.php` | Webhook falha **fechado**: sem token/segredo configurado rejeita; não assume mais `status=pago`; token por query string só fora de produção. |
| 10 | Alto | `app/Controllers/Public/Checkout.php` | Botão "simular" agora só existe em `ENVIRONMENT=development` (antes vazava em staging). |
| 11 | Médio | `Auth.php`, `Public/Suporte.php`, `Public/Evento.php`, `Public/Busca.php` | Rate limiting: login (5/5min), retomada de chat (10/10min), RSVP/recado (20/10min), busca (30/10min). |
| 12 | Alto/Médio | `app/Config/Filters.php` | Filtro `secureheaders` ativado (X-Frame-Options, X-Content-Type-Options, Referrer-Policy…). |
| 13 | Médio | `app/Config/Security.php` | CSRF migrado para `session` e `tokenRandomize = true`. |
| 14 | Baixo | `app/Config/Session.php` | `regenerateDestroy = true`. |
| 15 | Médio | `public/uploads/.htaccess`, `vendor/.htaccess`, `.gitignore` | Bloqueio de execução de scripts em uploads e de acesso direto a `vendor/`. |
| 16 | Alto | `env.producao` | `cookie.secure = true`, `forceGlobalSecureRequests = true`, `DBDebug = false` (ver item 3). |
| 17 | Médio | `app/Services/AuthService.php`, `app/Controllers/Auth.php` | Destino pós-login validado (`destinoSeguro`): evita open redirect e evita cair em `/admin` sem permissão por um `redirect_url` antigo; `redirect_url` limpo no logout. |

### 2.1 Redefinição de senha ("esqueci minha senha")

Implementada do zero (não existia envio de e-mail no projeto):

- Migration `2026-10-06-000033_AddResetSenhaUsuarios.php` (campos
  `reset_senha_token` e `reset_senha_expira`).
- `app/Services/SenhaService.php`: token aleatório de 256 bits, guardado só como
  SHA-256, validade de 60 min, uso único, resposta sempre genérica (não revela se
  o e-mail existe).
- `app/Services/MailService.php`: envio por SMTP/nativo, configurável no `.env`.
- `app/Controllers/Auth.php` + rotas `esqueci-senha` e `redefinir-senha`.
- Views `auth/esqueci_senha.php` e `auth/redefinir_senha.php`; link no login.
- Throttle de 5 solicitações por IP a cada 15 min.

---

## 3. Bloqueadores de go-live (dependem de configuração/deploy)

1. **Gateway PIX (Crítico).** O seeder deixa `pix_gateway = sandbox` e
   `pix_webhook_token = sandbox-token`. Em produção:
   - definir `pix_gateway = mercadopago`;
   - preencher `mercadopago_access_token`, `mercadopago_public_key`,
     `mercadopago_webhook_secret` e `pix_webhook_token` (valor forte/aleatório);
   - cadastrar a URL pública do webhook (`.../webhooks/pix`).
   Sem isso, o sandbox aceita confirmação de pagamento (fraude).
2. **HTTPS.** `env.producao` já vem com `app.forceGlobalSecureRequests = true` e
   `cookie.secure = true`. **Só publique depois de emitir o SSL no hPanel**;
   senão há loop de redirecionamento. Se o proxy não repassar HTTPS, ajuste.
3. **`app.baseURL` fixo.** Descomente e defina `https://SEU-DOMINIO.com.br/` no
   `.env` de produção (hoje é detectado do header Host — sujeito a *host header
   injection* / open redirect).
4. **Chave de criptografia.** Rode `php spark key:generate` no servidor e defina
   `encryption.key` no `.env`.
5. **Rotacionar segredos** que já circularam em ambiente de desenvolvimento:
   `google.clientSecret`, senha do banco e `pix_webhook_token`.
6. **`.env` de dev no deploy.** Garanta que o `.env` de produção venha de
   `env.producao` (com `CI_ENVIRONMENT = production`); nunca envie o `.env` local
   (tem `CI_ENVIRONMENT=development`, debug/stack traces e credenciais).
7. **Layout do deploy.** Docroot apontando só para `public/`; mantenha `app/`,
   `vendor/`, `writable/` e `.env` fora de `public_html`; use
   `public/.htaccess.producao` no lugar do `.htaccess` de dev.
8. **`vendor/` versionado** inclui devDependencies e testes. Se possível, rode
   `composer install --no-dev --optimize-autoloader` antes de enviar.
9. **E-mail (SMTP).** O fluxo de redefinição de senha depende de envio de e-mail.
   Configure `email.*` no `.env` de produção (ver bloco comentado em
   `env.producao`); sem isso, os links não chegam aos usuários.

---

## 4. Achados restantes (não corrigidos — recomendações)

| Severidade | Local | Problema | Recomendação |
|---|---|---|---|
| Alto | `app/Services/PagamentoService.php` | Confirma o valor total do pedido sem comparar com o `transaction_amount` retornado pelo gateway. | Comparar valores e recusar divergência; registrar o valor confirmado. |
| Médio | `app/Services/PagamentoService.php` | Idempotência não atômica (corrida entre requisições pode creditar em duplicidade). | `UPDATE ... WHERE status='pendente'` + checar `affectedRows()`; chave única em `gateway_transacao_id`. |
| Médio | `app/Controllers/Public/Checkout.php` / `Busca.php` | Pedido acessível apenas pelo protocolo (compartilhável/enumerável). | Segundo fator (token por e-mail) e resposta uniforme na busca. |
| Médio | `app/Services/SuporteService.php` | Retomada de conversa por código sem confirmar identidade (throttle já adicionado). | Exigir confirmação de e-mail/telefone; ampliar o código. |
| Médio | `app/Config/App.php` | `CSPEnabled = false` (app usa muito JS/CSS inline). HSTS depende do HTTPS. | Planejar CSP com nonce; ativar HSTS após SSL. |
| Baixo | `app/Views/templates/partials/chat.php` | `innerHTML` com o protocolo (hoje gerado no servidor, não explorável). | Usar `textContent`. |
| Baixo | `app/Services/UploadService.php` | Imagens "polyglot" não são recodificadas. | Recodificar via GD antes de salvar (defesa em profundidade). |
| Baixo | `app/Config/Encryption.php` | `$key` vazio (o app ainda não usa o Encrypter). | Gerar a chave no deploy. |

---

## 5. Checklist de go-live

- [ ] `CI_ENVIRONMENT = production` no `.env` do servidor
- [ ] `app.baseURL` com o domínio real (https)
- [ ] SSL emitido + `forceGlobalSecureRequests` / `cookie.secure` ativos
- [ ] `pix_gateway = mercadopago` + tokens/segredos preenchidos
- [ ] `pix_webhook_token` forte (nunca `sandbox-token`)
- [ ] `database.default.DBDebug = false`
- [ ] `encryption.key` gerada
- [ ] Segredos de dev rotacionados
- [ ] Docroot = `public/`; `.htaccess.producao` aplicado
- [ ] `php spark migrate` / seeds rodados
- [ ] SMTP de e-mail configurado (`email.*` no `.env`)
- [ ] Fluxo `esqueci-senha` testado (link chega no e-mail)

---

## 6. Observação sobre os testes

`php vendor/phpunit/phpunit/phpunit -c phpunit.dist.xml` → **26 de 27 testes
passam**. A falha `HealthTest::testBaseUrlHasBeenSet` é **pré-existente**: o teste
lê o valor cru `baseURL = ''` de `app/Config/App.php` (a URL real é resolvida no
construtor), então já falhava antes destas mudanças e não está relacionada a elas.
