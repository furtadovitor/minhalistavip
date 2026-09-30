# Retomada do projeto — próximos passos

> Atualizado ao final da sessão de 30/09/2026. O histórico completo das etapas está no `SCRIPT.md`.

## Onde paramos
- **Etapas 1 a 20 concluídas e commitadas** (ver `SCRIPT.md`; última: `eb7d637`).
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

## Pendências no servidor (Hostinger)
1. Rodar as **migrations novas** (ou reimportar o dump):
   `eventos.limite_convidados`, tabela `rsvp_acompanhantes`, `eventos.arquivado`,
   tabela `evento_galeria`, `rsvp_acompanhantes.categoria` e o ENUM ampliado de
   `eventos.tipo_evento` (Etapa 20).
2. Garantir **`public/uploads/` gravável** (imagens dos presentes/capas/galeria).
3. Antes de divulgar: **religar senha** (`AuthService::EXIGIR_SENHA = true`), trocar o
   `pix_webhook_token` e a chave PIX da plataforma.

## Próximos módulos sugeridos
1. **Check-in do evento** (marcar quem já chegou, no dia).
2. **Convite nominal / link por convidado** (pré-cadastro + confirmação sem duplicados).
3. **E-mails/WhatsApp transacionais** (recibo, confirmação aprovada, saque pago, lembrete).
4. **Gateway PIX real** (trocar o sandbox do `PixService`).
5. **Relatórios** (financeiro por evento, ocupação, lista consolidada).
6. Melhorias de UX no painel (edição inline de acompanhantes, busca avançada, etc.).

## Como retomar amanhã
Envie algo como:
> “Continuar de onde paramos (ver `PROXIMOS-PASSOS.md`). Quero implementar o módulo **X**.”

e substitua **X** por um dos itens acima (ou descreva o que precisa).
