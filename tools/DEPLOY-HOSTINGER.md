# Deploy na Hostinger — Web Hosting (PHP/MySQL)

Guia de publicação do **Minha Lista VIP** (CodeIgniter 4) no plano de **Web Hosting (PHP + MySQL)**
da Hostinger. Não use o fluxo **Node.js** do hPanel.

---

## Por que apareceu "Não foi possível encontrar o arquivo package.json"

Esse erro é do **builder de Node.js** da Hostinger, que procura `package.json` para rodar `npm install`.
Este projeto é **PHP/CodeIgniter 4** e não tem (nem deve ter) `package.json`.

- Se você criou um **aplicativo Node.js** no hPanel, **exclua-o** (hPanel → Websites → o app → Delete).
- Use a hospedagem padrão em `public_html`, via **Git**, **FTP** ou **File Manager**.

Requisitos: **PHP 8.2+** (hPanel → Advanced → PHP Configuration) e **MySQL**.

---

## Passo 1 — Enviar o código

### Opção A — Git (recomendado)
hPanel → **Advanced → GIT** → *Create repository* → conecte o repositório do GitHub,
branch `main` e escolha o diretório de destino, por exemplo:

```
domains/SEU-DOMINIO.com.br/minhalistavip
```

Depois use **Deploy** para baixar o código.

### Opção B — Upload
Compacte o projeto (pode excluir `writable/logs/*`, `writable/cache/*`) e envie o `.zip`
pelo **File Manager**, extraindo em `domains/SEU-DOMINIO.com.br/minhalistavip`.

> **Atenção:** o `.gitignore` ignora `vendor/` e `.env` (Passos 2 e 4).

---

## Passo 2 — Dependências (`vendor/`)

O repositório Git **não inclui** a pasta `vendor/` (dependências do Composer). Escolha uma opção:

**Opção A — SSH (se o seu plano tiver):**
```bash
cd ~/domains/SEU-DOMINIO.com.br/minhalistavip
composer install --no-dev --optimize-autoloader
```

**Opção B — sem SSH:** gere o `vendor/` localmente e envie junto:
```bash
composer install --no-dev --optimize-autoloader
```
e faça upload da pasta `vendor/` por FTP/File Manager.
(Alternativa: remova a linha `vendor/` do `.gitignore`, faça commit e use o deploy do Git.)

---

## Passo 3 — Ajustar a estrutura (a Hostinger não muda o document root)

Na **Web Hosting** da Hostinger o document root é **fixo em `public_html`** — não existe opção
para apontá-lo para a pasta `public/` do CI4. O layout correto é:

```
/home/uXXXXXX/domains/SEU-DOMINIO.com.br/
├── public_html/            <- conteúdo de public/ (index.php, .htaccess, uploads/)
└── minhalistavip/          <- app/, vendor/, writable/, .env, spark  (FORA do site)
```

**No File Manager:**
1. Mova a pasta `minhalistavip/` de **dentro** de `public_html/` para o diretório que **contém**
   o `public_html` (ela passa a ser pasta irmã do `public_html`).
2. Abra `minhalistavip/public/`, selecione tudo e **Mova** para dentro de `public_html/`.
3. Edite `public_html/index.php` e ajuste o require (linha ~51):
   ```php
   require FCPATH . '../minhalistavip/app/Config/Paths.php';
   ```
4. Edite `public_html/.htaccess` (Passo 6) e crie o `.env` em `minhalistavip/` (Passo 4).

Assim o site serve a partir de `public_html` e o app/`vendor`/`.env` ficam **inacessíveis** pela web.

> **Se não conseguir mover pastas para fora do `public_html`:** mantenha o projeto em
> `public_html/minhalistavip/`, mova apenas os arquivos de `minhalistavip/public/*` para
> `public_html/`, use `require FCPATH . 'minhalistavip/app/Config/Paths.php';` e crie
> `public_html/minhalistavip/.htaccess` com `Require all denied` (bloqueia app, vendor e `.env`).

> **Com SSH (atalho):**
> ```bash
> cd ~/domains/SEU-DOMINIO.com.br
> mv public_html/minhalistavip ./minhalistavip
> mv minhalistavip/public/* public_html/
> ```

---

## Passo 4 — Criar o `.env` no servidor

Na raiz do projeto no servidor, crie o arquivo `.env` (o `env.producao` deste repositório já
é um modelo pronto — copie o conteúdo). Preencha:

```dotenv
CI_ENVIRONMENT = production
# app.baseURL comentado = detecta o domínio automaticamente.
# Para fixar o endereço, use: app.baseURL = 'https://SEU-DOMINIO.com.br/'
app.indexPage = ''
app.appTimezone = 'America/Sao_Paulo'
app.forceGlobalSecureRequests = false   # mude para true após emitir o SSL

database.default.hostname = localhost
database.default.database = u776139543_minhalistav
database.default.username = u776139543_SEU_USUARIO
database.default.password = 'SUA-SENHA'
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci
```

Gere a chave de criptografia (SSH): `php spark key:generate`.

---

## Passo 5 — Banco de dados

1. hPanel → **Databases → MySQL Databases** → crie o banco e o usuário (anote o prefixo `u776139543_`).
   O banco de produção deste projeto é **`u776139543_minhalistav`**.
2. Suba o esquema e os dados:
   - **Levar os dados locais (recomendado):** o dump já está em `tools/db/minhalistavip-local.sql`
     (UTF-8 **sem BOM**, **sem `CREATE DATABASE`/`USE`** — importa no banco que você selecionar).
     No hPanel → **phpMyAdmin** → selecione `u776139543_minhalistav` → **Importar** → envie o arquivo.
     Para regerar: `tools/exportar-banco.ps1` (WAMP) ou `mysqldump ... minhalistavip`.
   - **Começar limpo (SSH):**
     ```bash
     php spark migrate
     php spark db:seed PlataformaSeeder
     php spark db:seed CatalogoPresentesSeeder
     php spark db:seed EventoDemoSeeder   # opcional
     ```

> **Atualizações de esquema:** sempre que houver uma migration nova, rode `php spark migrate`
> (por SSH) ou reimporte o dump atualizado em `tools/db/minhalistavip-local.sql`.
> Exemplos recentes: `000016`–`000020` (limite de convidados, acompanhantes, arquivar, galeria,
> categoria); `000021`–`000022` (tipos de evento e check-in); `000023`–`000025` (gateway PIX /
> Mercado Pago) e `000026` (dados de repasse do organizador).

> **Acentos / UTF-8 (mojibake):** se os acentos aparecerem como `Jo├úo`, `Espa├ºo`, `Beb├¬`,
> o banco sofreu **dupla codificação** (dump exportado por um script que capturava a saída do
> `mysqldump` como texto no PowerShell — decodificada em CP850 e regravada em UTF-8). O
> exportador `tools/exportar-banco.ps1` **já foi corrigido** (usa `--result-file`, gravando os
> bytes direto). Para **reparar** um banco já corrompido (local ou produção), rode:
>
> ```bash
> mysql -u USUARIO -p --default-character-set=utf8mb4 SEU_BANCO < tools/corrigir-mojibake.sql
> ```
>
> O script é idempotente e só altera registros com a assinatura do mojibake (o caractere `├`),
> preservando dados que já estejam corretos. Faça backup antes.

---

## Passo 6 — `RewriteBase` para o domínio raiz

Como no servidor o site fica na **raiz** (os arquivos do `public/` vão para `public_html/`),
o `.htaccess` precisa de `RewriteBase /`.

Use o arquivo pronto **`public/.htaccess.producao`**: no servidor, renomeie-o para
`public_html/.htaccess` (substituindo o de desenvolvimento, que aponta para `/minhalistavip/public/`).

> Se as URLs aparecerem com `index.php` (ex.: `/index.php/admin/...`) ou derem erro 500, é sinal de
> `RewriteBase` errado e/ou `app.indexPage` diferente de `''` no `.env`.

---

## Passo 7 — Permissões

Garanta que `writable/` e `public/uploads/` sejam graváveis pelo PHP (SSH):

```bash
chmod -R 755 writable
chmod -R 755 public/uploads
```

Pelo File Manager, as pastas costumam ficar em `755`. A pasta `public/uploads/` é criada
automaticamente pelo `UploadService` (subpastas `eventos`, `presentes`, `catalogo`) e guarda as
capas dos eventos e as imagens dos presentes.

---

## Passo 8 — SSL e testes finais

1. hPanel → **Security → SSL** → emita o certificado do domínio.
2. Acesse `https://SEU-DOMINIO.com.br/` e valide: home, `/login`, painel, hotsite e checkout.

### Checklist de segurança (obrigatório antes de ir ao ar)
- [ ] `CI_ENVIRONMENT = production` (desliga o debug toolbar e oculta erros).
- [ ] **Trocar a senha do SuperAdmin** (`admin@minhalistavip.com.br` → senha forte).
- [ ] Trocar/remover as contas de demonstração (`organizador@minhalistavip.com.br`).
- [ ] Atualizar a chave PIX real: `configuracoes.pix_chave_plataforma` e `pix_nome_plataforma`.
- [ ] Gerar um **novo** `pix_webhook_token` (não deixar `sandbox-token`) e usá-lo no gateway.
- [ ] Configurar `percentual_taxa_padrao` e `saque_valor_minimo` conforme o negócio.

### Mercado Pago (PIX + cartão de crédito via Checkout Bricks)
O sistema tem gateway plugável. Para usar o Mercado Pago em produção:

1. No Mercado Pago, em **Suas integrações → Credenciais**:
   - **Produção:** copie o **Access Token** (`APP_USR-...`) e a **Public Key** (`APP_USR-...`);
   - **Teste:** use o par `TEST-...` (Access Token + Public Key) no mesmo ambiente.
   > Atenção: as duas credenciais precisam ser do **mesmo ambiente**. Token de conta de teste
   > isolada gera `401 Unauthorized use of live credentials` na criação do pagamento.
2. No painel do SuperAdmin → **Configurações → PIX**, defina:
   - `pix_gateway` = **Mercado Pago**;
   - `mercadopago_access_token` = seu access token;
   - `mercadopago_public_key` = sua public key (obrigatória para o Brick de cartão/PIX);
   - `mercadopago_webhook_secret` = segredo de assinatura (Suas integrações → Webhooks);
   - `mercadopago_notification_url` = `https://SEU-DOMINIO.com.br/webhooks/pix` (opcional; se vazio, o sistema usa a URL do site).
3. Em **Webhooks** no Mercado Pago, cadastre a URL
   `https://SEU-DOMINIO.com.br/webhooks/pix` para o evento **Pagamentos (payment)**.
4. Faça um pedido real de valor baixo e confirme que o pagamento muda para **Pago**
   (o webhook consulta o status no MP antes de creditar a carteira). Para cartão use a
   página do pedido (Payment Brick embutido, com 3DS 2.0 e Status Screen).

> O valor do PIX cai no **saldo da conta Mercado Pago dona do access token** (a da plataforma).
> Os organizadores recebem pelo fluxo de **saques** do painel — não é split automático por evento.

> Dica: `sandbox` (padrão) gera um BR Code interno e não movimenta dinheiro real — use só para testes.
> Para testar com o MP no seu computador, exponha o local com um túnel (ex.: `cloudflared tunnel
> --url http://localhost`), preencha `mercadopago_notification_url` com a URL HTTPS do túnel e
> use o par de credenciais de **teste** (`TEST-...`) no access token e na public key.

---

## Problemas comuns

| Sintoma | Causa provável | Solução |
| --- | --- | --- |
| Erro 500 | `.env` ausente/ilegível ou permissão em `writable/` | Crie o `.env`; ajuste permissões; veja `writable/logs/` |
| Página em branco | `app.baseURL` errado | Confira a URL com `https://` e barra no final |
| Erro de conexão com o banco | Credenciais/prefixo do host | Revise o bloco `database.default.*` no `.env` |
| 404 em todas as rotas | `RewriteBase` ou mod_rewrite | `RewriteBase /` e confirme o `mod_rewrite` |
| "Vendor autoload is not found" | `vendor/` ausente | Passo 2 (`composer install` ou upload do `vendor/`) |
| CSS/layout quebrado | `app.baseURL` com caminho errado | Deve ser a raiz do domínio |

---

## Estrutura esperada no servidor

```
/home/uXXXXXX/domains/SEU-DOMINIO.com.br/
├── public_html/              <- DOCUMENT ROOT (fixo na Hostinger)
│   ├── index.php             <- require FCPATH . '../minhalistavip/app/Config/Paths.php';
│   ├── .htaccess             <- RewriteBase /
│   └── uploads/
└── minhalistavip/            <- fora do acesso público
    ├── app/
    ├── vendor/               <- Passo 2
    ├── writable/             <- gravável
    ├── .env                  <- Passo 4
    ├── env.producao
    └── spark
```
