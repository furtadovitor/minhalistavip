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

## Passo 3 — Document root na pasta `public/`

hPanel → **Domains** → seu domínio → **Document root** → aponte para a pasta `public` do projeto:

```
domains/SEU-DOMINIO.com.br/minhalistavip/public
```

Isso deixa a URL limpa (`https://SEU-DOMINIO.com.br/login`) e mantém `app/`, `vendor/`,
`.env` etc. **fora** do acesso público — mais seguro.

<details>
<summary>Não consigo alterar o document root? (alternativa)</summary>

Mova o conteúdo de `public/` para `public_html/` e o restante do projeto para uma pasta fora
do web root (ex.: `~/minhalistavip-app/`). Depois, em `public_html/index.php`, ajuste:

```php
$pathsPath = realpath(FCPATH . '../minhalistavip-app/app/Config/Paths.php');
```

Nesse caso o `app.baseURL` continua sendo a raiz do domínio.
</details>

---

## Passo 4 — Criar o `.env` no servidor

Na raiz do projeto no servidor, crie o arquivo `.env` (o `env.producao` deste repositório já
é um modelo pronto — copie o conteúdo). Preencha:

```dotenv
CI_ENVIRONMENT = production
app.baseURL = 'https://SEU-DOMINIO.com.br/'
app.indexPage = ''
app.appTimezone = 'America/Sao_Paulo'
app.forceGlobalSecureRequests = false   # mude para true após emitir o SSL

database.default.hostname = localhost
database.default.database = u000000000_minhalistavip
database.default.username = u000000000_mlvuser
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

1. hPanel → **Databases → MySQL Databases** → crie o banco e o usuário (anote o prefixo `uXXXXXX_`).
2. Suba o esquema e os dados:
   - **Levar os dados locais (recomendado):** hPanel → **phpMyAdmin** → selecione o banco →
     **Importar** → envie `tools/db/minhalistavip-local.sql` (dump gerado do seu MySQL local).
   - **Começar limpo (SSH):**
     ```bash
     php spark migrate
     php spark db:seed PlataformaSeeder
     php spark db:seed CatalogoPresentesSeeder
     php spark db:seed EventoDemoSeeder   # opcional
     ```

---

## Passo 6 — `RewriteBase` para o domínio raiz

Como no servidor o site fica na **raiz** (document root = `public/`), edite `public/.htaccess`:

```apache
RewriteBase /
```

(o local estava como `/minhalistavip/public/`).

---

## Passo 7 — Permissões

Garanta que `writable/` seja gravável pelo PHP (SSH):

```bash
chmod -R 755 writable
```

Pelo File Manager, as pastas costumam ficar em `755`.

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
domains/SEU-DOMINIO.com.br/
└── minhalistavip/
    ├── app/
    ├── vendor/            <- Passo 2
    ├── writable/          <- gravável
    ├── public/            <- DOCUMENT ROOT
    │   ├── index.php
    │   ├── .htaccess      <- RewriteBase /
    │   └── uploads/
    ├── .env               <- Passo 4
    ├── env.producao
    └── spark
```
