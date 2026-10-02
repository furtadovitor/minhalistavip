# Retomada do projeto — próximos passos

> Atualizado ao final da sessão de 01/10/2026. O histórico completo das etapas está no `SCRIPT.md`.

## Onde paramos
- **Etapas 1 a 24 concluídas e commitadas** (ver `SCRIPT.md`).
- **Migrations locais em dia** (`php spark migrate` → `000018`–`000025`, batch 4) e dump
  `tools/db/minhalistavip-local.sql` regenerado. Corrige o erro local `Unknown column 'arquivado'`.
- **UTF-8 corrigido:** o banco local tinha **mojibake** (acentos como `Jo├úo`). Causa raiz:
  o `tools/exportar-banco.ps1` capturava a saída do `mysqldump` como texto no PowerShell
  (CP850) e regravava em UTF-8. **Script corrigido** (`--result-file`), **dados e comentários
  reparados** e dump regenerado limpo. Reparo reutilizável em `tools/corrigir-mojibake.sql`
  (idempotente, filtra pela assinatura `├`). Se a produção tiver o mesmo sintoma, rodar esse
  script ou reimportar o dump limpo.
- **Local:** `http://localhost/minhalistavip/public/`
- **Login sem senha** ativo (basta o e-mail): `admin@minhalistavip.com.br` (SuperAdmin) e
  `organizador@minhalistavip.com.br` (Organizador). Interruptor: `AuthService::EXIGIR_SENHA`.
- **Repositório:** `github.com/furtadovitor/minhalistavip` (branch `master`).
- **ZIP para upload:** `tools/deploy/minhalistavip-hostinger.zip`
- **Dump do banco:** `tools/db/minhalistavip-local.sql` (regenerado com as migrations novas).

## O que já está pronto
Eventos, presentes (com imagem), catálogo global, checkout + PIX sandbox + webhook + carteira,
saques (com processamento pelo SuperAdmin), **lista de convidados com homologação/limite**,
**acompanhantes por categoria (adulto/criança/bebê)**, **RSVP em destaque com modal no hotsite**,
Home + listas de exemplo (com galeria), erros 404/500 personalizados, **painel do organizador
"Minhas listas"** (menu colapsável, abas Ativas/Arquivadas, workspace com Galeria, Recadinhos,
Compartilhar, Pagamentos, Forma de pagamento, Funcionalidades, Aparência, Informações e
Configurações) e **botões/campos padronizados**.
- **Novo:** Home com **atalhos por tipo de evento** (24 ocasiões: chá de bebê, lingerie, amigo
  secreto, natal, pet, igreja, etc.) e **criação rápida** em `/criar-lista-de-presente/{tipo}`
  (pede nome e descrição; cria na hora se logado, senão pede login e conclui depois).
- **Novo:** **Check-in presencial** (`painel/eventos/{id}/checkin`) — busca por nome, cartões
  grandes para celular, contador de presentes e desfazer; badge na lista de convidados e coluna
  no CSV.
- **Novo:** **Gateway PIX plugável** com **Mercado Pago** (além do sandbox) — escolha em
  Configurações (`pix_gateway`), com access token e segredo do webhook. Webhook único
  `/webhooks/pix` valida a origem e consulta o status no MP antes de confirmar.
- **Novo:** **Busca avançada e edição inline na lista de convidados** — filtros de presença no
  check-in, acompanhantes (com criança/bebê) e ordenação, com os filtros preservados ao
  aprovar/recusar/remover; edição do titular e dos acompanhantes direto na tela de detalhe;
  a quantidade declarada é sincronizada e o limite do evento conferido ao adicionar acompanhante.
- **Novo:** **Checkout Bricks do Mercado Pago (PIX + cartão de crédito)** — formulário embutido
  na página do pedido (`sdk.mercadopago.com/js/v2`), com 3DS 2.0 e Status Screen para PIX/desafio.
  Requer **Access Token + Public Key** coerentes (teste `TEST-...` ou produção `APP_USR-...`):
  token de conta de teste isolado dá `401 Unauthorized use of live credentials`.
- **Novo (front-end):** **Home reformulada** (`app/Views/home.php`) como landing completa, no
  estilo do temfestinha, evoluindo a identidade indigo atual. Reforça **grátis** e **rapidez**,
  e apresenta três pilares: **site personalizado**, **convite online** e **lista de presentes**.
  Novas seções: faixa de confiança, "3 passos" (com selos de tempo), pilares, grade de recursos,
  benefícios organizador/convidado, **depoimentos** e **FAQ** (accordion). Reforço de **grátis** e
  **1 minuto** no hero, na faixa de confiança, na seção "3 passos" e na página
  `public/criar_lista_tipos.php`. Estilos escopados com prefixo `.lp-` (não altera layout/footer).
  Todas as features anunciadas existem na plataforma.
- **Novo (front-end):** **depoimentos e exemplos viraram carrosséis** responsivos
  (scroll-snap + setas + indicadores por página, `role`/`aria-label`), com JS próprio no fim de
  `home.php` (sem dependência de lib). Inclui **passe de responsivo**: título do hero com
  `clamp()`, offsets dos selos flutuantes por breakpoint, botões longos que quebram a linha
  (`max-width:100%`), padding do CTA final menor no mobile e `overflow-wrap` nos textos.
- **Novo (admin):** **Admin → Listas** (`admin/listas`) — o SuperAdmin enxerga TODAS as listas da
  plataforma (não só do seu tenant), com cartões de resumo clicáveis (total, publicadas,
  rascunhos, encerradas, ativas, arquivadas), filtros (busca por título/slug/organizador,
  status, situação, tipo de evento e organizador), paginação e ações de **publicar/despublicar**
  e **arquivar/reativar**. Cada linha mostra dono (link para a ficha do usuário) e a página de
  detalhe traz dados do evento, do organizador e números (presentes, convidados/confirmados,
  pedidos pagos, arrecadado e taxas). Novo em: `Admin\Eventos`, `EventoModel::paginarAdmin()`,
  views `admin/eventos/{index,ver}.php`, rotas e item no menu lateral.
- **Novo (front-end):** **hotsite público refinado** (`hotsite/lista.php`) — revelação em cascata
  (stagger) e **progressive enhancement** (antes o conteúdo sumia se o JS falhasse), estado visual
  **"Esgotado"** + "faltam X cotas", chips no hero, rótulo da contagem, título com `clamp()`,
  `:focus-visible` (acessibilidade) e **botão flutuante de WhatsApp** no mobile.
- **Novo (admin):** **listas** ganharam **export CSV** (respeita os filtros; UTF-8 com BOM e `;`),
  **filtro por período** (7/30/90 dias) e **ações em massa** (publicar/despublicar/arquivar/reativar
  várias de uma vez), com seleção por checkbox no cabeçalho e contador.
- **Novo (painel do organizador):** refinamento de UI/UX. **Minhas listas**: stat cards com ícones,
  **busca e filtro de listas** por nome/tipo (com contadores atualizados nas abas), miniatura da capa
  com zoom, selo de rascunho, botões de **compartilhar (copiar link)** e **publicar rápido**.
  **`painel/eventos`**: miniatura + "Gerenciar" + menu de ações (dropdown). **Convidados**: stat cards
  com ícones. `.stat-card` / `.stat-icon` viraram padrão do layout do painel.
- **Novo (financeiro do organizador):** a tela `painel/carteira` virou **"Financeiro"** com:
  **Dados do Responsável** (nome, e-mail, telefone, CPF, nascimento), **Dados Bancários**
  (tipo do pagamento, tipo da chave, chave PIX) e **Endereço de Correspondência** (CEP, endereço,
  bairro, cidade, UF) — salvos em `usuarios` (migration `000026`); **Valores aguardando liberação**
  (cartão, janela de 30 dias); **Solicitar resgate** (habilita só com os dados completos) e
  **Status de resgates**. O resgate usa a **chave PIX salva** como padrão. Os dados são privados e
  **não aparecem no site do evento**. No **admin → Saques** cada solicitação tem o botão
  **"Dados de repasse"** (modal com responsável, dados bancários e endereço, com "Copiar dados" e
  aviso quando incompleto), para o SuperAdmin pagar o PIX.
- **Novo (login):** **"Entrar com Google"** (OAuth 2.0 / OpenID Connect, sem dependência nova —
  usando o `CURLRequest` do CI4). Rotas `auth/google` e `auth/google/callback` com validação de
  `state`; vincula por e-mail (conecta conta existente) ou **cria o organizador** na hora; guarda
  `usuarios.google_id` (migration `000027`). O botão aparece no login/registro **só quando**
  `google.clientId`/`google.clientSecret` estão no `.env` (Config\Google). Passo a passo no
  `tools/DEPLOY-HOSTINGER.md`.
- **Novo (identidade):** **logo** "Minha Lista VIP" — monograma **"M"** branco sobre um squircle com
  gradiente indigo→violeta. Partial inline `templates/partials/logo.php` (marca + wordmark, com
  variante clara e "VIP" em gradiente), usada nos layouts público, painel e autenticação. Assets:
  `public/favicon.svg`, `public/favicon.ico`, `public/favicon-192.png`, `public/apple-touch-icon.png`
  e `public/assets/logo.svg`. Rasters gerados por `tools/gerar-favicon.php` (GD).
- **Novo (front-end):** **animações de UI** na Home — revelação ao rolar com
  `IntersectionObserver` + entrada escalonada ("stagger") no hero, seções, cards, carrosséis e
  FAQ; barras de progresso do mockup animando; blob de fundo do hero flutuando; micro-interações
  (seta do botão desliza, ícone do card dá zoom, botão afunda no `:active`) e `:focus-visible`
  nos controles do carrossel. Usa animação com `backwards` (não `forwards`) para **não quebrar o
  hover dos cards**. Tudo **respeita `prefers-reduced-motion`** e é **progressive enhancement**:
  sem JS (classe `lp-js`) o conteúdo permanece visível.
  > **Pendência:** os **depoimentos** em `home.php` são **placeholder** — substituir por relatos
  > reais e autorizados antes de divulgar.

## Pendências no servidor (Hostinger)
1. Rodar as **migrations novas** (ou reimportar o dump):
   `eventos.limite_convidados`, tabela `rsvp_acompanhantes`, `eventos.arquivado`,
   tabela `evento_galeria`, `rsvp_acompanhantes.categoria`, o ENUM ampliado de
   `eventos.tipo_evento`, o check-in (`rsvp_confirmacoes.check_in_em/check_in_por`) e as
   configurações do gateway PIX (`pix_gateway`, Mercado Pago e `mercadopago_public_key`).
2. Garantir **`public/uploads/` gravável** (imagens dos presentes/capas/galeria).
3. Antes de divulgar: **religar senha** (`AuthService::EXIGIR_SENHA = true`), definir
   `pix_gateway = mercadopago` com **Access Token + Public Key do mesmo ambiente** (teste
   `TEST-...` ou produção `APP_USR-...` de conta real), **segredo do webhook**, cadastrar a URL
   `https://SEU-DOMINIO/webhooks/pix` no painel do Mercado Pago, e trocar o `pix_webhook_token`.

## Próximos módulos sugeridos
1. **Convite nominal / link por convidado** (pré-cadastro + confirmação sem duplicados).
2. **E-mails/WhatsApp transacionais** (recibo, confirmação aprovada, saque pago, lembrete).
3. **Relatórios** (financeiro por evento, ocupação, lista consolidada).
4. **Expiração automática de pedidos `pendente`** (job/cron) e conciliação de transações.

## Como retomar amanhã
Envie algo como:
> “Continuar de onde paramos (ver `PROXIMOS-PASSOS.md`). Quero implementar o módulo **X**.”

e substitua **X** por um dos itens acima (ou descreva o que precisa).
