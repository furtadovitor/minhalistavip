# Retomada do projeto — próximos passos

> Atualizado ao final da sessão de 03/10/2026. O histórico completo das etapas está no `SCRIPT.md`.

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
- **Novo:** **Expiração automática + conciliação + agradecimento** — comando `pedidos:conciliar`
  (`ConciliacaoService`) consulta o gateway, confirma pagamentos pendentes e expira pedidos
  vencidos; o PIX pendente faz **polling** em `/{slug}/pedido/{protocolo}/status` e redireciona
  sozinho para a **página de agradecimento** `/{slug}/obrigado/{protocolo}` quando confirmado.
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
- **Novo (admin):** **Listas de exemplo (demos)** gerenciáveis em `admin/demos` — CRUD completo
  (criar, editar, publicar/ocultar, reordenar e remover) das listas que aparecem na Home e em
  `/demo/{slug}`. Tudo em uma tela: dados do card, capa (URL ou upload), cores/tema e as listas
  internas de **presentes/cotas, recados e galeria** (linhas dinâmicas). Os 3 exemplos que eram
  fixos no código foram importados para a tabela `demo_listas` (migration `000028`) e há a ação
  **"Restaurar padrão"**. O `Public\Demo` agora lê do banco; a Home exibe só as publicadas.
- **Novo (front-end):** **nova tela de "Criar minha lista"** (`/criar-lista-de-presente`) dividida
  ao meio: à esquerda um **carrossel** de imagens com informes (galeria/link/Pix e cartão) e, à
  direita, o **pré-registro** — nome da lista, **modelo visual**, tipo de evento, data, hora e local.
  Ao **Avançar** já logado a lista é criada na hora; sem login, vai para o login e conclui sozinho
  depois. O catálogo central **`ModeloService`** passou a ter **12 modelos visuais** (tipografia +
  cores), usados também nos selects/paletas do painel e no design do hotsite. A antiga grade de
  tipos (`criar_lista_tipos.php`) deixou de existir (os atalhos por tipo continuam na Home).
- **Novo (publicação):** **regras para publicar a lista** — o site só vai ao ar com **modelo visual,
  tipo de evento, data, local e ao menos um presente ativo**. No painel do organizador, o botão
  Publicar mostra exatamente o que está faltando; no admin vale para a publicação individual e em
  lote (o lote publica só as listas completas). Centralizado em
  `EventoService::pendenciasPublicacao()`.
- **Novo (suporte):** **chat ao vivo** por **polling** (roda na Hostinger, sem WebSocket). O
  organizador (painel) e o visitante do site/hotsite abrem conversa pelo botão flutuante; o
  **admin** vê a **fila** em `admin/suporte`, **assume** (atômico, sem duplicar entre atendentes)
  e responde. A conversa fica no banco: se o cliente fechar o navegador e voltar em até **5 min**,
  o widget retoma de onde parou; passando disso, é **encerrada automaticamente**. Selo de não
  lidas no botão e no menu do admin. Cron `php spark suporte:fechar-inativos`. O visitante sem
  login informa **nome, telefone e e-mail** e recebe um **código de atendimento** (`SUP-XXXXXX`):
  a partir daí retoma a conversa informando o código, de qualquer navegador. O código expira
  **10 dias após o evento** (ou, sem evento, 10 dias após a última mensagem). Os canais de
  E-mail/WhatsApp seguem no modal dentro do chat (`templates/partials/suporte.php`, com
  placeholder **`xxxx`**, trocar antes de divulgar). Não aparece para o SuperAdmin como cliente.
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
- **Novo (identidade):** **logo** "Minha Lista VIP" — o **presente** à esquerda + wordmark
  **"Minha Lista"** em preto e o selo **"VIP"** em roxo coroado (coroa colorida). O partial
  `templates/partials/logo.php` usa os rasters `public/assets/logo_mlvp_real.png` (lockup compacto;
  fonte com margens em `logo_mlvp_real_fonte.png`), `logo_mlvp_real_claro.png` (variante clara p/
  fundo escuro, ex.: rodapé) e `logo_mlvp_marca.png` (só o presente, no favicon e no menu recolhido),
  usada nos layouts público, painel e autenticação. Favicon (o presente): `public/favicon.svg`,
  `public/favicon.ico`, `favicon-192.png` e `apple-touch-icon.png` (fonte `public/logo_mlvp.png`).
  Rasters gerados por `tools/gerar-favicon.php` e `tools/gerar-logo.php` (GD).
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
   `eventos.tipo_evento`, o check-in (`rsvp_confirmacoes.check_in_em/check_in_por`), as
   configurações do gateway PIX (`pix_gateway`, Mercado Pago e `mercadopago_public_key`) e a
   tabela `demo_listas` (listas de exemplo gerenciáveis no admin) e as tabelas do chat de suporte
   (`suporte_conversas` e `suporte_mensagens`, com `telefone`/`expira_em`), além da correção das
   cores das listas (`cor_primaria`/`cor_secundaria` que ficaram gravadas com `%23` e deixavam o
   hotsite branco).
2. Garantir **`public/uploads/` gravável** (imagens dos presentes/capas/galeria).
3. Antes de divulgar: **religar senha** (`AuthService::EXIGIR_SENHA = true`), definir
   `pix_gateway = mercadopago` com **Access Token + Public Key do mesmo ambiente E do mesmo
   aplicativo**, **segredo do webhook**, cadastrar a URL `https://SEU-DOMINIO/webhooks/pix` no
   painel do Mercado Pago, e trocar o `pix_webhook_token`.
   > **Erro `401 Unauthorized use of live credentials`:** normalmente é credencial misturada.
   > Um token de **usuário de teste** pode vir com prefixo `APP_USR-` (parece produção, mas é
   > teste) — se a Public Key for de **produção**, o MP recusa. Confirme com
   > `GET https://api.mercadopago.com/users/me` (`test_data.test_user`): `true` → use o par
   > `TEST-...`; `false` → use o par `APP_USR-...`. Use sempre as duas do **mesmo aplicativo**.
   > O painel de Configurações agora valida e bloqueia o par incoerente, e o gateway registra o
   > motivo real no log (`writable/logs/`) devolvendo mensagem neutra ao convidado.
4. **Cron da conciliação** — agendar `php spark pedidos:conciliar` a cada 5 minutos (expira
   pedidos vencidos e confirma pagamentos com webhook perdido). Passo a passo no
   `tools/DEPLOY-HOSTINGER.md`.
5. **Cron do suporte** — agendar `php spark suporte:fechar-inativos` (a cada 5 minutos) para
   encerrar conversas do chat de suporte sem presença recente do cliente.

## Próximos módulos sugeridos
1. **Convite nominal / link por convidado** (pré-cadastro + confirmação sem duplicados).
2. **E-mails/WhatsApp transacionais** (recibo, confirmação aprovada, saque pago, lembrete).
3. **Relatórios** (financeiro por evento, ocupação, lista consolidada).

## Como retomar amanhã
Envie algo como:
> “Continuar de onde paramos (ver `PROXIMOS-PASSOS.md`). Quero implementar o módulo **X**.”

e substitua **X** por um dos itens acima (ou descreva o que precisa).
