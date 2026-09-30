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
⚠️ **Login sem senha ativo** (`AuthService::EXIGIR_SENHA = false`): basta informar o e-mail.
Para voltar a exigir senha, mude a constante para `true` em `app/Services/AuthService.php`.

| Perfil | E-mail | Senha (quando exigida) |
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

### Etapa 12 — Painel do SuperAdmin completo (concluída)
Menu lateral do SuperAdmin ampliado: **Painel, Financeiro, Catálogo, Categorias, Planos, Usuários,
Saques e Configurações**.

* **Financeiro** (`Admin\Financeiro`): conciliação global — arrecadado, taxas retidas, saques
  pagos/pendentes, pedidos pagos, ticket médio, resultado da plataforma e últimos pagamentos.
* **Configurações** (`Admin\Configuracoes`): edição de `configuracoes` (taxa padrão, valor mínimo de
  saque, dados da plataforma e chaves PIX). `ConfiguracaoService` ganhou `todas()` e `definir()`.
* **Catálogo global** (`Admin\Catalogo`): CRUD completo (inclui ativo/destaque, valor sugerido,
  link de afiliado) e **Categorias** (`Admin\Categorias`) com edição inline, contagem de itens e
  slug automático.
* **Planos** (`Admin\Planos` + `PlanoModel`): CRUD com preço, período, taxa, limite de eventos e
  recursos em JSON (RSVP, recados, destaque).
* **Usuários** (`Admin\Usuarios`): lista com filtros (nível/status/busca), detalhe com métricas
  (arrecadado, saldo, taxas), eventos e saques; ativar/suspender (bloqueia suspender a própria conta).

Rotas novas (grupo `/admin`):
`/admin/financeiro`, `/admin/configuracoes`, `/admin/catalogo[/novo|/{id}/editar|/{id}/alternar|/{id}/excluir]`,
`/admin/categorias[/{id}|/{id}/alternar|/{id}/excluir]`,
`/admin/planos[/novo|/{id}/editar|/{id}/alternar|/{id}/excluir]`,
`/admin/usuarios[/{id}|/{id}/alternar]`.

### Etapa 13 — Login sem senha e hotsite redesenhado (concluída)
* **Login sem senha** (`AuthService::EXIGIR_SENHA = false`): o login pede apenas o e-mail. A
  constante é o interruptor para voltar a exigir senha. O formulário esconde o campo de senha
  quando ativo e mostra um aviso.
* **Hotsite redesenhado** — view única `Views/hotsite/lista.php`, usada por eventos reais e pelas
  listas de exemplo (substitui `public/evento.php` e `demo/lista.php`):
  * **Hero** com imagem de capa + overlay, badge do tipo, data/local, **contador regressivo**,
    indicadores (cotas presenteadas, confirmações, % da meta) e CTA de compartilhar/copiar link.
  * **Barra de progresso da meta** do evento.
  * **Navegação sticky** (Presentes / Presença / Recados) com destaque da seção ativa.
  * **Grade de presentes** com busca, filtro (cotas/presentes reais) e ordenação (menor/maior
    valor, mais presenteados), cards com imagem, barra de cotas e botão "Presentear".
  * **Animações de entrada** (IntersectionObserver), hover nos cards, tema escuro nos exemplos.
  * RSVP e mural de recados reorganizados, com avatares nos recados.
  * No modo demonstração, as ações abrem um modal explicando que é exemplo.

### Etapa 14 — Imagens nos presentes e mais personalização (concluída)
* **UploadService** reutilizável (`app/Services/UploadService.php`): valida JPG/PNG/WEBP até 2 MB,
  gera nome aleatório e remove o arquivo anterior. Centraliza a lógica antes duplicada.
* **Imagem do presente** (painel do organizador): campo no formulário, opção de remover, miniatura
  na listagem, e a imagem aparece nos cards do hotsite. O arquivo é removido ao trocar/excluir.
* **Imagem do item do catálogo** (SuperAdmin): mesmo fluxo.
* **Capa do evento**: passou a usar o serviço (comportamento mantido).
* **Personalização por tema**: `design_evento` agora aplica **tipografia e arredondamento** conforme
  o tema — `classico` (Playfair Display), `casamento` (Cormorant Garamond), `cha_bebe` (Poppins),
  `infantil` (Baloo 2) e `moderno` (Space Grotesk). Vale para hotsite, checkout e pedido.
* **Formulário do evento**: **presets de paleta** (Clássico, Casamento, Chá de bebê, Infantil,
  Moderno) que aplicam cores + tema de uma vez, e **pré-visualização ao vivo** (hero e card de
  presente) que reflete título, tipo, data, local, cores, tema e a capa escolhida.

### Etapa 15 — Lista de convidados com homologação e limite (concluída)
* **Nova tela** `Host\Convidados` (`/painel/eventos/{id}/convidados`): KPIs (confirmados, aguardando,
  recusados, limite/vagas), barra de ocupação do limite, filtros (status/busca), **adicionar
  convidado manualmente**, **aprovar / recusar / remover**, e **exportar CSV**.
* **Homologação**: a confirmação pública passa a entrar como **`pendente`** e só conta como
  confirmada após a aprovação do organizador (quem responde "não vou" entra como `recusado`).
* **Contagem por pessoas**: considera registro + acompanhantes (`ConvidadoService::resumo()`).
* **Limite de convidados** (`eventos.limite_convidados`, migration `2026-09-30-000016`):
  * bloqueia novas confirmações públicas quando o limite é atingido (o hotsite mostra aviso);
  * bloqueia a aprovação que ultrapassaria o limite (mensagem orienta ajustar o limite ou recusar);
  * campo no formulário do evento (vazio = ilimitado).
* Botões "Convidados" na listagem de eventos e no topo da lista de presentes.

### Etapa 16 — Acompanhantes com nome, idade e menor/maior automático (concluída)
* **Tabela `rsvp_acompanhantes`** (migration `2026-09-30-000017`): nome completo, idade e o campo
  `menor` **derivado automaticamente** pela regra `idade < 18 ⇒ menor`.
* **Tela de detalhe do convidado** (`/painel/eventos/{id}/convidados/{id}`): dados do convidado,
  resumo de **menores/maiores**, formulário para registrar acompanhantes (nome completo + idade) e
  remoção. Mostra badges “Menor de idade”, “Maior de idade” e “Idade não informada”.
* **Obrigatório no front**: o formulário público pede a quantidade de acompanhantes e, para cada um,
  **nome completo + idade** (campos obrigatórios, gerados dinamicamente). O titular é criado como
  pendente e os acompanhantes são gravados junto. Quem marca "não vou" não precisa preencher.
* A classificação **menor/maior** continua **automática** (idade < 18 ⇒ menor) — o convidado não
  escolhe; o sistema decide.
* Botão **Acompanhantes** na listagem e resumo de menores/maiores nos KPIs; o **CSV** passou a
  incluir colunas de menores/maiores e os nomes dos acompanhantes.

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
