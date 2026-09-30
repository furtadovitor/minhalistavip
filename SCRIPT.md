# SCRIPT.md - Diretrizes do Projeto e Guia para Assistentes de IA (OpenCode)

Este documento define o objetivo, a arquitetura, os padrões de desenvolvimento, as regras de negócio e as restrições que devem ser rigorosamente seguidas por qualquer desenvolvedor ou assistente de IA (OpenCode) que atuar neste repositório.

---

## 1. OBJETIVO DO PROJETO

**Projeto:** Minha Lista VIP — `minhalistavip.com.br`

Construir uma plataforma SaaS híbrida e multi-eventos (Multisite) de arrecadação de presentes virtuais em dinheiro e gerenciamento de eventos sociais (Casamentos, Chás de Bebê/Fraldas, Aniversários, Chás de Panela, etc.).

O sistema permite que organizadores criem páginas personalizadas para seus eventos, disponibilizem listas de presentes fictícios (cujo valor é convertido em dinheiro/PIX direto na conta do organizador) e gerenciem confirmações de presença (RSVP) e recados de convidados. A plataforma rentabiliza através de retenção de comissão/taxa sobre as transações de presentes e planos de assinatura.

---

## 2. CONTEXTO E REGRAS DE NEGÓCIO

### Contexto do Sistema
* **Multi-tenant por Evento:** A plataforma possui um SuperAdmin global, um painel do Organizador (cliente) e uma interface pública para convidados (acessada via slug do evento).
* **Presentes Fictícios & Reais:** Os presentes são predominantemente fictícios (cotas de dinheiro/PIX). Presentes reais operam via redirecionamento de afiliados.
* **Modelo Monetário/Taxas:** A plataforma cobra uma comissão percentual parametrizada sobre cada compra de presente. A configuração do evento define se a taxa é descontada do organizador ou acrescida para o convidado.

### Regras de Negócio Fundamentais
1. **Atribuição de Taxa (`quem_paga_taxa`):**
   * Se `convidado`: `valor_total` = `valor_presentes` + `valor_taxa`.
   * Se `organizador`: `valor_total` = `valor_presentes` (o valor da comissão é abatido no saldo líquido do organizador).
2. **Ciclo do Pedido:** Um pedido somente gera saldo na carteira do organizador e publica mensagem no mural após confirmação do webhook de pagamento (`status = 'pago'`).
3. **Catálogo Geral vs. Presentes do Evento:** O organizador pode clonar itens do `catalogo_presentes` global para o `presentes_evento` ou cadastrar presentes totalmente customizados.
4. **Isolamento de Tenants:** Um organizador jamais pode visualizar, alterar ou sacar valores de eventos pertencentes a outro usuário.

---

## 3. STACK TECNOLÓGICA

* **Backend Framework:** PHP 8.1+ com CodeIgniter 4
* **Banco de Dados:** MySQL 8.0+ / MariaDB
* **Frontend:** HTML5, CSS3, JavaScript (ES6+), jQuery
* **Arquitetura:** MVC com Services e Entities do CodeIgniter 4

---

## 4. ARQUITETURA E ESTRUTURA DE PASTAS

O projeto segue estritamente a estrutura do CodeIgniter 4:

```text
app/
├── Config/                # Configurações do App, Rotas e Filtros
├── Controllers/
│   ├── Admin/             # Gestão do SuperAdmin (Taxas, Catálogo, Usuários)
│   ├── Host/              # Gestão do Organizador (Painel do Cliente)
│   ├── Public/            # Checkout e Páginas Públicas do Evento
│   └── BaseController.php
├── Database/
│   ├── Migrations/        # Controle de versão do esquema SQL
│   └── Seeds/             # Dados iniciais do sistema
├── Entities/              # Classes de Domínio/Objetos de Dados
├── Filters/               # Middlewares de Autenticação e Permissão (ACL)
├── Helpers/               # Funções utilitárias globais
├── Models/                # Mapeamento do Banco de Dados
├── Services/              # Regras de Negócio e Integrações com APIs (Gateways)
└── Views/
    ├── admin/             # Layouts do SuperAdmin
    ├── host/              # Layouts do Painel do Organizador
    ├── public/            # Páginas públicas
    └── templates/         # Temas dinâmicos de eventos (Casamento, Chá, etc.)
```

---

## 5. ESTADO ATUAL DA IMPLEMENTAÇÃO

### Etapa 1 — Estrutura e configuração (concluída)
* Projeto em `C:\wamp64\www\minhalistavip`, domínio local **minhalistavip.com.br** (vhost do WAMP).
* CodeIgniter **4.7.4** sobre **PHP 8.2**, banco **MySQL 9.1** (`minhalistavip`).
* `.env` configurado: `CI_ENVIRONMENT=development`,
  `app.baseURL=http://localhost/minhalistavip/public/` (WAMP local),
  `app.indexPage=''` (URLs limpas, sem `index.php`), `app.appTimezone=America/Sao_Paulo` e credenciais
  `root` sem senha.
* Pastas criadas conforme a seção 4 (`Controllers/{Admin,Host,Public}`, `Entities`, `Services`,
  `Views/{admin,host,public,templates}`).
* `public/.htaccess` com `RewriteBase /minhalistavip/public/` — mod_rewrite validado no Apache do WAMP
  (o app roda na subpasta padrão do WAMP, sem exigir vhost nem hosts).
* **Opcional (domínio limpo):** vhost em `httpd-vhosts.conf` (DocumentRoot = `.../minhalistavip/public`)
  + script `tools/setup-vhost-admin.ps1` (entrada `127.0.0.1 minhalistavip.com.br` no `hosts` e
  restart do Apache, como Administrador). Se usar o domínio, reverta `app.baseURL` e `RewriteBase`.

### Etapa 2 — Banco de dados (concluída)
15 migrations em `app/Database/Migrations` (todas InnoDB, 16 FKs):

| Tabela | Papel |
| --- | --- |
| `usuarios` | SuperAdmin e Organizadores (soft delete) |
| `eventos` | Tenant do organizador, com `quem_paga_taxa` e `percentual_taxa` |
| `categorias` | Categorias do catálogo global |
| `catalogo_presentes` | Catálogo geral mantido pelo SuperAdmin |
| `presentes_evento` | Itens clonados/customizados do evento |
| `pedidos` | Snapshot das regras de taxa + status do pedido |
| `pagamentos` | Transações no gateway (payload JSON) |
| `carteira_movimentacoes` | Extrato (crédito, taxa, saque, estorno, ajuste) |
| `saques` | Solicitações de saque do organizador |
| `rsvp_confirmacoes` | Confirmações de presença |
| `mural_recados` | Recados (publicados só após aprovação) |
| `planos` / `assinaturas` | Receita recorrente |
| `configuracoes` | Chave/valor global (ex.: `percentual_taxa_padrao`) |
| `webhooks_log` | Log bruto e idempotência de webhooks |

Seeds: `PlataformaSeeder` (configurações, categorias, planos e usuários) e
`EventoDemoSeeder` (evento público de exemplo).

### Etapa 3 — Rotas, filtros e autenticação (concluída)
* **Rotas:** `/`, `/login`, `/registro`, `/logout`, `/painel` (auth), `/admin`
  (auth + `role:superadmin`) e os **hotsites na raiz**: `/{slug}`, `/{slug}/rsvp` e `/{slug}/recado`
  (público). Slugs reservados (`EventoService::SLUGS_RESERVADOS`) são bloqueados.
* **Filtros (ACL):** `auth`, `role:superadmin`, `guest` — além de **CSRF** global
  (exceto `webhooks/*`).
* **Services:** `AuthService` (sessão) e `TaxaService` (regra de atribuição de taxa da seção 2.1).
* **Entidades/Models:** `Usuario`, `Evento` e models correspondentes + presentes, recados e RSVP.

### Credenciais de teste
| Perfil | E-mail | Senha |
| --- | --- | --- |
| SuperAdmin | `admin@minhalistavip.com.br` | `Admin@123` |
| Organizador | `organizador@minhalistavip.com.br` | `Demo@123` |

### Como executar
```bash
php spark migrate                          # cria as tabelas
php spark db:seed PlataformaSeeder         # dados iniciais + usuários
php spark db:seed CatalogoPresentesSeeder  # catálogo global de presentes
php spark db:seed EventoDemoSeeder         # evento público de exemplo
php spark serve                            # servidor local (dev)
```

**Acesso local (padrão):** <http://localhost/minhalistavip/public/>
(MySQL do WAMP em `localhost:3306`; MariaDB em `localhost:3307`).
O hotsite de cada evento fica na raiz do app: `http://localhost/minhalistavip/public/{slug}`.

**Opcional — domínio limpo:** rode `tools/setup-vhost-admin.ps1` como Administrador e ajuste
`app.baseURL` para `http://minhalistavip.com.br/` e o `RewriteBase` do `public/.htaccess` para `/`.

### Etapa 4 — Painel do Organizador (concluída)
* **CRUD de eventos** (`Host\Eventos`): listar, criar, editar, publicar/despublicar e excluir
  (soft delete). Slug gerado automaticamente e único (`EventoService::gerarSlugUnico`).
* **Upload de capa** (`public/uploads/eventos`) com validação de imagem (JPG/PNG/WEBP, até 2 MB),
  nome aleatório e remoção do arquivo anterior.
* **Presentes do evento** (`Host\Presentes`): cadastro customizado, edição, ativar/desativar
  e exclusão, além de **clonagem do catálogo global** para o evento (Regra 2.3), ignorando
  duplicados.
* **Isolamento de tenants** (Regra 2.4): todo acesso passa por `EventoService::doOrganizador()`
  e `PresenteEventoService::um()`, que respondem **404** quando o recurso é de outro organizador.
* **Seed** `CatalogoPresentesSeeder` com 18 itens distribuídos nas categorias.

Rotas novas:
| Método | Rota | Ação |
| --- | --- | --- |
| GET | `/painel/eventos` | Lista os eventos do organizador |
| GET/POST | `/painel/eventos/novo` · `/painel/eventos` | Formulário e criação |
| GET/POST | `/painel/eventos/{id}/editar` · `/painel/eventos/{id}` | Formulário e atualização |
| POST | `/painel/eventos/{id}/publicar` | Alterna rascunho/publicado |
| POST | `/painel/eventos/{id}/excluir` | Remove (soft delete) |
| GET/POST | `/painel/eventos/{id}/presentes[/novo]` | Lista e cadastro de presentes |
| GET/POST | `/painel/eventos/{id}/presentes/catalogo` · `/clonar` | Catálogo global e clonagem |
| GET/POST | `/painel/eventos/{id}/presentes/{pid}/editar` | Formulário e atualização |
| POST | `/painel/eventos/{id}/presentes/{pid}/alternar` · `/excluir` | Ativar/desativar e excluir |

### Etapa 5 — Rebranding e hotsites na raiz (concluída)
* Projeto renomeado para **Minha Lista VIP** / `minhalistavip.com.br`: pasta, banco de dados,
  vhost, marca nas views e e-mails dos usuários atualizados.
* Hotsites passaram de `/e/{slug}` para a **raiz**: `minhalistavip.com.br/{slug}`.
* **Slugs reservados** (`login`, `registro`, `logout`, `painel`, `admin`, `api`, `uploads`, ...)
  são bloqueados: se o organizador digitar um deles, recebe erro de validação; se o slug for
  gerado a partir do título, ganha sufixo automático (título "Painel" → `painel-2`).
* `tools/setup-vhost-admin.ps1` adiciona `127.0.0.1 minhalistavip.com.br` ao `hosts` e reinicia o Apache.

### Etapa 6 — Checkout, PIX (sandbox) e carteira (concluída)
Fecha o ciclo financeiro do sistema: convidado escolhe a cota → pedido com snapshot da taxa →
cobrança PIX → webhook confirma → crédito na carteira e recado publicado no mural (Regra 2.2).

* **Checkout do convidado** (`Public\Checkout`): formulário com dados e mensagem, aplicação da
  regra de taxa via `TaxaService` (snapshot em `pedidos`), controle de cotas com revalidação
  dentro de transação (evita venda concorrente). Presentes reais (`tipo = real`) saem pelo
  link de afiliado, sem checkout.
* **PIX sandbox** (`PixService`): gera um BR Code EMV real (CRC16/CCITT-FALSE) apontando para a
  chave da plataforma (`configuracoes`), com expiração configurável. Não há provedor externo:
  trocar pela API real mantendo o mesmo contrato de retorno (`cobranca`).
* **Webhook** (`POST /webhooks/pix`, isento de CSRF): autenticado por token
  (`pix_webhook_token`); idempotente por `webhooks_log` (mesma referência não credita duas vezes).
* **`PagamentoService`**: ao confirmar `pago` — marca o pedido, incrementa `quantidade_vendida`,
  credita a carteira e publica a mensagem no mural. Usa o **mesmo caminho** do botão de
  simulação do sandbox (disponível apenas em `development`).
* **Carteira** (`Host\Carteira`): saldo, extrato, total arrecadado/taxas/sacado e solicitação de
  saque (valor mínimo e reserva por débito imediato). No evento com taxa paga pelo organizador,
  a carteira recebe crédito do presente e débito da comissão.
* **Pedidos** (`Host\Pedidos`): listagem com filtros por evento e status, isolada por tenant.
* Novos helpers de rótulo em `formato_helper.php` (status de pedido/saque e tipo de movimentação).
* `PlataformaSeeder` ganhou as chaves do grupo `pix` (`pix_chave_plataforma`, `pix_nome_plataforma`,
  `pix_cidade_plataforma`, `pix_expiracao_minutos`, `pix_webhook_token`).

Rotas novas:
| Método | Rota | Ação |
| --- | --- | --- |
| GET | `/{slug}/presentear/{id}` | Formulário de checkout da cota |
| POST | `/{slug}/presentear/{id}` | Cria o pedido e a cobrança PIX |
| GET | `/{slug}/pedido/{protocolo}` | Status do pedido com QR Code e Copia e Cola |
| POST | `/{slug}/pedido/{protocolo}/simular` | Simula o pagamento (somente em development) |
| POST | `/webhooks/pix` | Confirmação do gateway (token + idempotência) |
| GET | `/painel/pedidos` | Pedidos do organizador (filtro por evento/status) |
| GET | `/painel/carteira` | Saldo, extrato e saques |
| GET/POST | `/painel/carteira/saque` | Formulário e solicitação de saque |

### Etapa 7 — Processamento de saques pelo SuperAdmin (concluída)
* **Tela de saques** (`Admin\Saques`, rota `/admin/saques`): lista todos os pedidos com dados do
  organizador, filtro por status e resumo (pendentes/pagos em quantidade e valor).
* **Fluxo de status**: `solicitado → processando → pago`, com **recusa/cancelamento a qualquer
  momento** antes do pagamento. Transições inválidas são bloqueadas.
* **Recusa com estorno**: ao recusar, o valor reservado volta à carteira do organizador via
  lançamento `estorno` (com o motivo na descrição do extrato) e `processado_em` é registrado.
* `CarteiraService` ganhou `saquesAdmin()`, `resumoSaques()`, `saque()` e `alterarStatusSaque()`.
* Dashboard do SuperAdmin passou a exibir números reais (organizadores, eventos publicados,
  pedidos pagos e saques pendentes) e atalhos para a gestão.
* Link `Saques` no menu superior para quem é SuperAdmin.

Rotas novas:
| Método | Rota | Ação |
| --- | --- | --- |
| GET | `/admin/saques` | Lista e resumo dos saques (filtro por status) |
| POST | `/admin/saques/{id}/processar` | Marca como em processamento |
| POST | `/admin/saques/{id}/pagar` | Confirma o pagamento (registra data) |
| POST | `/admin/saques/{id}/recusar` | Recusa e devolve o valor à carteira |

### Etapa 8 — Front-end da Home + listas de exemplo (concluída)
Conforme `FRONTEND_SPEC.md`:

* **Layout público de marketing** (`Views/templates/layouts/public.php`): Bootstrap 5.3 +
  Bootstrap Icons + Google Fonts (Plus Jakarta Sans / Inter) e um CSS próprio com os tokens da
  paleta (Primary `#4F46E5`, Secondary `#10B981`, accents por tema) e utilitários
  (`fs-7`, `fs-8`, `bg-indigo-100`, `text-indigo-700`, `transition-hover`).
* **Home reformulada** (`Views/home.php`): Hero com CTA, **busca do convidado**
  (`POST /buscar`), seção de **3 listas de exemplo**, "Como funciona / vantagens" e CTA final.
* **Listas de exemplo** (`Public\Demo`, rotas `/demo/{slug}`): Casamento *Marina & Gabriel*,
  Aniversário *30 Anos do Lucas* (tema dark) e Chá de Bebê *Chá da Sofia* — dados **estáticos**,
  com faixa de aviso e modal (nunca expõem eventos reais, conforme diretriz de privacidade).
* **Busca do convidado** (`Public\Busca`): aceita link/slug do evento **ou** o código do pedido
  (protocolo) e redireciona para o local certo.
* Slugs `demo`, `exemplos` e `buscar` adicionados a `EventoService::SLUGS_RESERVADOS`.

### Etapa 9 — Redesign de login, registro e checkout (concluída)
* **Partials de design compartilhados**: `templates/partials/design_system.php` (marca/plataforma:
  Home e autenticação) e `design_evento.php` (temático por evento, com suporte a tema escuro),
  evitando CSS duplicado.
* **Login e registro** (`layouts/auth.php` + `auth/login.php` + `auth/registro.php`): tela centralizada
  com gradiente, fontes do design system, ícones nos campos e `btn-brand`.
* **Checkout** (`public/checkout.php`): layout em duas colunas, resumo fixo (sticky) e cálculo
  dinâmico preservado; botão "Gerar PIX".
* **Pedido** (`public/pedido.php`): status, QR Code e Copia e Cola no mesmo padrão.
* **Hotsite** (`public/evento.php`) e **listas de exemplo** (`demo/lista.php`) passaram a usar o
  partial temático (`design_evento`), garantindo consistência de tipografia e componentes.

### Etapa 10 — Redesign do painel do organizador/SuperAdmin (concluída)
* **Layout do painel** (`templates/layouts/app.php`): sidebar responsiva (vira *offcanvas* no mobile),
  navegação com estado ativo, bloco do usuário, atalhos "Ver site"/"Sair" e o mesmo design system
  da plataforma.
* **Dashboard** do organizador com KPIs (eventos, publicados, arrecadado, saldo) e lista de eventos
  recentes; **Dashboard do SuperAdmin** com organizadores, eventos, pedidos pagos e saques pendentes.
* **Listas padronizadas**: eventos, presentes, catálogo, pedidos, carteira e saques com cards
  `rounded-4`, botões `btn-brand`/`btn-outline-brand` e estados vazios ilustrados.
* **Formulários** (evento e presente) e **catálogo** ajustados ao novo padrão.
* Correção: o formulário de evento mostrava o antigo prefixo `/e/` no slug; agora exibe o domínio raiz.

### Etapa 11 — Páginas de erro personalizadas (concluída)
* **404** (`errors/html/error_404.php`): página com a identidade (fonts, gradiente, marca), código
  estilizado, mensagem em PT-BR e **busca do convidado** (link/código do pedido) além dos atalhos
  para o início e para criar lista.
* **400** (`error_400.php`) e **erro genérico de produção** (`production.php`): mesmo padrão visual.
* Observação: o CodeIgniter responde **JSON** quando o cliente não aceita `text/html` (APIs/AJAX);
  o navegador recebe a página personalizada.

### Próximos módulos sugeridos
1. Complementar o Painel do SuperAdmin: taxas, catálogo global, usuários, planos e conciliação.
2. Substituir o PIX sandbox por um gateway real (Mercado Pago/Asaas) mantendo `PixService` e
   `PagamentoService`.
3. Notificações por e-mail (recibo do convidado, aviso de presente ao organizador, saque pago).
4. Retomada do tema visual dos eventos (`Views/templates`) e upload de imagem nos presentes.
5. Expiração automática de pedidos `pendente` (job/cron) usando `pedidos.expira_em`.

---

## 6. DEPLOY (Hostinger — Web Hosting PHP/MySQL)

O projeto é **PHP/CodeIgniter 4**: não publicar pelo fluxo **Node.js** da Hostinger
(é ele que exige `package.json` e causa o erro "Não foi possível encontrar o arquivo package.json").

* Guia completo: **`tools/DEPLOY-HOSTINGER.md`**.
* Modelo de ambiente de produção: **`env.producao`** (copiar para `.env` no servidor; o `.env` não vai para o Git).
* Dump do banco local para importar via phpMyAdmin: **`tools/db/minhalistavip-local.sql`**.
* Pontos que dependem do servidor: `vendor/` (Composer, fora do Git), `RewriteBase /` no
  `public/.htaccess`, document root apontando para `public/`, e troca das credenciais/chaves de teste.
