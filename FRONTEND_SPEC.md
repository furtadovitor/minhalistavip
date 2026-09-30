# FRONTEND_SPEC.md — Especificações de Interface e UI/UX

Documento de diretrizes de design, estrutura de componentes e exemplos de código para o
front-end da plataforma.

---

## 1. Diretrizes de Privacidade e Conceito Visual

### Privacidade em primeiro lugar
As listas criadas pelos clientes reais **NÃO são exibidas publicamente na Home**, para preservar
a privacidade do organizador e dos convidados.

### Listas de exemplo demonstrativas
Na Home e na seção de inspiração são exibidas **3 listas estáticas de demonstração**, para que
potenciais clientes naveguem e experimentem a interface antes de criar a própria lista:

1. **Casamento:** *Marina & Gabriel* (Tema Elegante / Tons Neutros)
2. **Aniversário:** *30 Anos do Lucas* (Tema Moderno / Neon Dark)
3. **Chá de Bebê:** *Chá de Fraldas da Sofia* (Tema Pastel / Infantil Soft)

Rotas: `/demo/casamento`, `/demo/aniversario`, `/demo/cha-de-bebe`.

---

## 2. Design System & Tecnologias

* **Framework principal:** Bootstrap 5.3 (grid, modais, componentes)
* **Utilitários/estilo extra:** *Custom CSS* (optou-se por CSS próprio em vez do Tailwind CDN para
  evitar conflito de *preflight* com o Bootstrap).
* **Ícones:** Bootstrap Icons
* **Fontes (Google Fonts):** `Plus Jakarta Sans` (títulos) e `Inter` (texto)
* **Paleta:**
  * Primary (Brand): `#4F46E5` (Indigo)
  * Secondary: `#10B981` (Emerald)
  * Accent Casamento: `#D97706` (Dourado)
  * Accent Chá de Bebê: `#06B6D4` (Azul pastel)
  * Accent Aniversário: `#EC4899` (Pink)
  * Background neutro: `#F9FAFB`

---

## 3. Estrutura da Home

1. **Hero Section:** apresentação + CTA para criar lista grátis.
2. **Caixa de busca do convidado:** buscar evento por código ou link direto.
3. **Seção de listas demonstrativas:** 3 exemplos prontos.
4. **Vantagens & monetização:** como o presente vira PIX.
5. **CTA final:** cadastro e criação de conta.

---

## 4. Componente da seção de exemplos

O markup de referência (cards de exemplo) está implementado em
`app/Views/home.php`, seção `#exemplos`, usando as classes utilitárias definidas em
`app/Views/templates/layouts/public.php` (`fs-7`, `fs-8`, `bg-indigo-100`, `text-indigo-700`,
`transition-hover`).
