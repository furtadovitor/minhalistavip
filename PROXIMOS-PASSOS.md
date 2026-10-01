# Retomada do projeto — próximos passos

> Atualizado ao final da sessão de 01/10/2026. O histórico completo das etapas está no `SCRIPT.md`.

## Onde paramos
- **Etapas 1 a 24 concluídas e commitadas** (ver `SCRIPT.md`).
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
