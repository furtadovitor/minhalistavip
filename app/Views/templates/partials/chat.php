<?php
/**
 * Widget do chat de suporte ao vivo (lado cliente).
 *
 * @var string      $canal        'painel' (organizador) ou 'site' (visitante)
 * @var int|null    $eventoId     Contexto, quando aberto dentro de um evento
 * @var bool        $mostrarCanais Exibe o atalho para os canais (e-mail/WhatsApp)
 * @var string      $variante     'hotsite' ajusta a posição no mobile
 * @var string|null $corPrimaria  Cor do evento (hotsite)
 * @var string|null $corSecundaria
 */
$canal         = $canal ?? 'site';
$eventoId      = $eventoId ?? null;
$mostrarCanais = $mostrarCanais ?? false;
$variante      = $variante ?? '';
$corPrimaria   = $corPrimaria ?? null;
$corSecundaria = $corSecundaria ?? null;

$estiloCores = '';
if ($corPrimaria !== null && $corPrimaria !== '') {
    $estiloCores  = '--brand:' . cor_hex($corPrimaria, '#4F46E5') . ';';
    $estiloCores .= '--brand-dark:' . cor_hex($corSecundaria ?? null, '#4338CA') . ';';
}

$urlEstado   = site_url('suporte/conversa');
$urlEnviar   = site_url('suporte/conversa/mensagens');
$urlRetomar  = site_url('suporte/conversa/retomar');
$urlEncerrar = site_url('suporte/conversa/encerrar');
?>
<style>
    .mlv-chat-fab {
        position: fixed; right: 1.25rem; bottom: 1.25rem; z-index: 1055;
        width: 58px; height: 58px; border-radius: 50%; border: 0;
        background: linear-gradient(135deg, var(--brand, #4F46E5), var(--brand-dark, #4338CA));
        color: #fff; display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.5rem; box-shadow: 0 12px 28px rgba(17,24,39,.30);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .mlv-chat-fab:hover { transform: translateY(-2px); color: #fff; box-shadow: 0 16px 34px rgba(17,24,39,.36); }
    .mlv-chat-fab .mlv-chat-badge {
        position: absolute; top: -4px; right: -4px; min-width: 22px; height: 22px; padding: 0 5px;
        border-radius: 999px; background: #EF4444; color: #fff; font-size: .72rem; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center; border: 2px solid #fff;
    }
    .mlv-chat-fab.pulsar { animation: mlvPulse 1.8s infinite; }
    @keyframes mlvPulse {
        0% { box-shadow: 0 0 0 0 rgba(79,70,229,.45); }
        70% { box-shadow: 0 0 0 16px rgba(79,70,229,0); }
        100% { box-shadow: 0 0 0 0 rgba(79,70,229,0); }
    }
    @media (max-width: 991.98px) {
        .mlv-chat--hotsite .mlv-chat-fab { right: auto; left: 1rem; }
    }

    .mlv-chat-panel {
        position: fixed; right: 1.25rem; bottom: 1.25rem; z-index: 1060;
        width: 372px; max-width: calc(100vw - 1.5rem); height: 560px; max-height: calc(100vh - 2rem);
        background: #fff; border-radius: 1.1rem; overflow: hidden;
        box-shadow: 0 24px 60px rgba(17,24,39,.32);
        display: flex; flex-direction: column;
    }
    @media (max-width: 575.98px) {
        .mlv-chat-panel { right: .5rem; left: .5rem; bottom: .5rem; width: auto; height: calc(100vh - 1rem); max-height: none; }
    }
    .mlv-chat-panel[hidden] { display: none; }

    .mlv-chat-head {
        display: flex; align-items: center; gap: .6rem;
        padding: .85rem 1rem; color: #fff;
        background: linear-gradient(135deg, var(--brand, #4F46E5), var(--brand-dark, #4338CA));
    }
    .mlv-chat-head .mlv-chat-avatar {
        width: 38px; height: 38px; border-radius: 50%; flex: none;
        background: rgba(255,255,255,.2); display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem;
    }
    .mlv-chat-head .titulo { font-weight: 700; font-size: .95rem; line-height: 1.1; }
    .mlv-chat-head .sub { font-size: .72rem; opacity: .85; display: inline-flex; align-items: center; gap: .3rem; }
    .mlv-chat-head .sub .dot { width: 7px; height: 7px; border-radius: 50%; background: #4ADE80; display: inline-block; }
    .mlv-chat-head-actions { margin-left: auto; display: flex; gap: .15rem; }
    .mlv-chat-head-actions button {
        background: transparent; border: 0; color: #fff; width: 30px; height: 30px; border-radius: .5rem;
        display: inline-flex; align-items: center; justify-content: center; font-size: 1rem;
    }
    .mlv-chat-head-actions button:hover { background: rgba(255,255,255,.18); }

    .mlv-chat-body {
        flex: 1 1 auto; overflow-y: auto; padding: 1rem;
        background: #F3F4F6; display: flex; flex-direction: column; gap: .5rem;
    }
    .mlv-chat-body .mlv-msg { max-width: 82%; padding: .55rem .75rem; border-radius: .9rem; font-size: .86rem; line-height: 1.35; white-space: pre-wrap; word-wrap: break-word; }
    .mlv-chat-body .mlv-msg .hora { display: block; font-size: .65rem; opacity: .65; margin-top: .2rem; }
    .mlv-msg.cliente { align-self: flex-end; background: var(--brand, #4F46E5); color: #fff; border-bottom-right-radius: .25rem; }
    .mlv-msg.atendente { align-self: flex-start; background: #fff; color: #111827; border: 1px solid #E5E7EB; border-bottom-left-radius: .25rem; }
    .mlv-msg.sistema { align-self: center; background: transparent; color: #6B7280; font-size: .72rem; text-align: center; }
    .mlv-chat-vazio { text-align: center; color: #9CA3AF; font-size: .82rem; margin: auto; padding: 1rem; }

    .mlv-chat-codigo-card {
        background: #FFFBEB; border: 1px solid #FCD34D; border-radius: .85rem;
        padding: .6rem .7rem; display: flex; gap: .55rem; font-size: .76rem; color: #78350F;
    }
    .mlv-chat-codigo-card i { font-size: 1.1rem; color: #D97706; }
    .mlv-chat-codigo-card .cod { display: flex; align-items: center; gap: .5rem; margin-top: .25rem; }
    .mlv-chat-codigo-card code { font-size: .95rem; font-weight: 700; letter-spacing: .04em; color: #92400E; }
    .mlv-chat-codigo-card [data-copiar-codigo] {
        border: 1px solid #FCD34D; background: #fff; border-radius: .5rem; padding: .1rem .5rem;
        font-size: .7rem; font-weight: 600; color: #92400E;
    }

    .mlv-chat-form { border-top: 1px solid #E5E7EB; padding: .65rem .7rem .55rem; background: #fff; }
    .mlv-chat-ident { display: grid; gap: .4rem; margin-bottom: .5rem; }
    .mlv-chat-ident input, .mlv-chat-codigo-linha input { font-size: .85rem; }
    .mlv-chat-link { background: none; border: 0; color: var(--brand, #4F46E5); font-size: .74rem; font-weight: 600; padding: .15rem 0; text-align: left; }
    .mlv-chat-codigo { display: grid; gap: .45rem; margin-bottom: .55rem; }
    .mlv-chat-codigo-titulo { font-size: .82rem; font-weight: 600; margin: 0; color: #111827; }
    .mlv-chat-codigo-linha { display: flex; gap: .4rem; }
    .mlv-chat-codigo-linha input { flex: 1 1 auto; text-transform: uppercase; }
    .mlv-chat-codigo-linha button { border: 0; border-radius: .6rem; background: var(--brand); color: #fff; font-weight: 600; padding: 0 .95rem; }
    .mlv-chat-compose { display: flex; align-items: flex-end; gap: .5rem; }
    .mlv-chat-compose textarea {
        flex: 1 1 auto; resize: none; max-height: 120px; border: 1px solid #D1D5DB;
        border-radius: .8rem; padding: .5rem .7rem; font-size: .88rem; line-height: 1.3; width: 100%;
    }
    .mlv-chat-compose textarea:focus { outline: 0; border-color: var(--brand, #4F46E5); box-shadow: 0 0 0 3px rgba(79,70,229,.15); }
    .mlv-chat-compose button {
        flex: none; width: 40px; height: 40px; border-radius: 50%; border: 0; color: #fff;
        background: var(--brand, #4F46E5); display: inline-flex; align-items: center; justify-content: center; font-size: 1.05rem;
    }
    .mlv-chat-compose button:disabled { opacity: .5; }
    .mlv-chat-hint { font-size: .68rem; color: #9CA3AF; text-align: center; margin: .4rem 0 0; }
</style>

<div class="mlv-chat <?= $variante === 'hotsite' ? 'mlv-chat--hotsite' : '' ?>" id="mlvChat"
     style="<?= esc($estiloCores) ?>"
     data-canal="<?= esc($canal, 'attr') ?>"
     data-evento="<?= $eventoId !== null ? (int) $eventoId : '' ?>"
     data-estado="<?= esc($urlEstado) ?>"
     data-enviar="<?= esc($urlEnviar) ?>"
     data-retomar="<?= esc($urlRetomar) ?>"
     data-encerrar="<?= esc($urlEncerrar) ?>"
     data-csrf="<?= esc(csrf_hash(), 'attr') ?>">

    <button type="button" class="mlv-chat-fab" id="mlvChatFab" aria-label="Abrir chat de suporte">
        <i class="bi bi-chat-dots-fill"></i>
        <span class="mlv-chat-badge" id="mlvChatBadge" hidden>0</span>
    </button>

    <section class="mlv-chat-panel" id="mlvChatPanel" hidden aria-live="polite" aria-label="Chat de suporte">
        <header class="mlv-chat-head">
            <span class="mlv-chat-avatar"><i class="bi bi-headset"></i></span>
            <div>
                <div class="titulo">Suporte Minha Lista VIP</div>
                <div class="sub" id="mlvChatStatus"><span class="dot"></span> Fale com a gente</div>
            </div>
            <div class="mlv-chat-head-actions">
                <?php if ($mostrarCanais): ?>
                    <button type="button" title="Outros canais" data-bs-toggle="modal" data-bs-target="#modalSuporte">
                        <i class="bi bi-envelope"></i>
                    </button>
                <?php endif; ?>
                <button type="button" id="mlvChatMin" title="Minimizar" aria-label="Minimizar">
                    <i class="bi bi-dash-lg"></i>
                </button>
            </div>
        </header>

        <div class="mlv-chat-body" id="mlvChatBody">
            <p class="mlv-chat-vazio">Envie uma mensagem para falar com o nosso time.</p>
        </div>

        <form class="mlv-chat-form" id="mlvChatForm" autocomplete="off">
            <div class="mlv-chat-ident" id="mlvChatIdent" hidden>
                <input type="text" class="form-control" name="nome" maxlength="120" placeholder="Seu nome *">
                <input type="tel" class="form-control" name="telefone" maxlength="30" placeholder="Telefone / WhatsApp *">
                <input type="email" class="form-control" name="email" maxlength="150" placeholder="E-mail *">
                <button type="button" class="mlv-chat-link" id="mlvChatTemCodigo">Já tenho um código de atendimento</button>
            </div>

            <div class="mlv-chat-codigo" id="mlvChatCodigoBox" hidden>
                <p class="mlv-chat-codigo-titulo">Informe seu código de atendimento</p>
                <div class="mlv-chat-codigo-linha">
                    <input type="text" class="form-control" id="mlvChatCodigo" maxlength="20" placeholder="Ex.: SUP-8FK2ZP">
                    <button type="button" id="mlvChatAcessar">Acessar</button>
                </div>
                <button type="button" class="mlv-chat-link" id="mlvChatVoltar">Voltar</button>
            </div>

            <div class="mlv-chat-compose" id="mlvChatCompose">
                <textarea id="mlvChatTexto" rows="1" maxlength="4000" placeholder="Escreva sua mensagem..."></textarea>
                <button type="submit" id="mlvChatEnviar" aria-label="Enviar"><i class="bi bi-send-fill"></i></button>
            </div>
            <p class="mlv-chat-hint" id="mlvChatHint">Um atendente responde por aqui.</p>
        </form>
    </section>
</div>

<script>
(function () {
    var root = document.getElementById('mlvChat');
    if (!root) { return; }

    var fab     = document.getElementById('mlvChatFab');
    var panel   = document.getElementById('mlvChatPanel');
    var badge   = document.getElementById('mlvChatBadge');
    var body    = document.getElementById('mlvChatBody');
    var form    = document.getElementById('mlvChatForm');
    var texto   = document.getElementById('mlvChatTexto');
    var ident   = document.getElementById('mlvChatIdent');
    var codigoBox = document.getElementById('mlvChatCodigoBox');
    var compose = document.getElementById('mlvChatCompose');
    var statusEl = document.getElementById('mlvChatStatus');
    var hint    = document.getElementById('mlvChatHint');

    var cfg = {
        canal: root.dataset.canal || 'site',
        evento: root.dataset.evento || '',
        estado: root.dataset.estado,
        enviar: root.dataset.enviar,
        retomar: root.dataset.retomar,
        encerrar: root.dataset.encerrar,
        csrf: root.dataset.csrf || ''
    };

    var st = { aberto: false, conversaId: null, ultimoId: 0, autenticado: false, enviando: false, timer: null };
    var vazioEl = body.querySelector('.mlv-chat-vazio');

    function rolar() {
        var perto = body.scrollHeight - body.scrollTop - body.clientHeight < 120;
        if (perto) { body.scrollTop = body.scrollHeight; }
    }

    function bolha(m) {
        var div = document.createElement('div');
        div.className = 'mlv-msg ' + (m.autor || 'sistema');
        var t = document.createElement('span');
        t.textContent = m.texto;
        div.appendChild(t);
        if (m.hora) {
            var h = document.createElement('span');
            h.className = 'hora';
            h.textContent = m.hora;
            div.appendChild(h);
        }
        body.appendChild(div);
    }

    function sistemaLocal(texto) { bolha({ autor: 'sistema', texto: texto }); }

    function render(lista) {
        if (!lista || !lista.length) { return; }
        if (vazioEl) { vazioEl.remove(); vazioEl = null; }
        lista.forEach(bolha);
        rolar();
    }

    function mostrarCodigo(protocolo) {
        var antigo = body.querySelector('.mlv-chat-codigo-card');
        if (antigo) { antigo.remove(); }

        var card = document.createElement('div');
        card.className = 'mlv-chat-codigo-card';
        card.innerHTML =
            '<i class="bi bi-shield-lock-fill"></i>' +
            '<div><strong>Guarde seu código de atendimento</strong>' +
            '<div>Você vai usar este código para falar com a gente de novo.</div>' +
            '<div class="cod"><code>' + protocolo + '</code>' +
            '<button type="button" data-copiar-codigo>Copiar</button></div></div>';

        body.insertBefore(card, body.firstChild);
        card.querySelector('[data-copiar-codigo]').addEventListener('click', function () {
            try {
                if (navigator.clipboard) { navigator.clipboard.writeText(protocolo); }
            } catch (e) {}
            this.textContent = 'Copiado!';
        });
    }

    function setBadge(n) {
        if (n > 0 && !st.aberto) {
            badge.hidden = false;
            badge.textContent = n > 9 ? '9+' : n;
            fab.classList.add('pulsar');
        } else {
            badge.hidden = true;
            fab.classList.remove('pulsar');
        }
    }

    function setStatus(c) {
        if (!c) { statusEl.innerHTML = '<span class="dot"></span> Fale com a gente'; return; }
        if (c.tem_atendente) {
            statusEl.innerHTML = '<span class="dot"></span> Atendente na conversa';
        } else {
            statusEl.innerHTML = '<span class="dot"></span> ' + (c.status === 'aguardando' ? 'Na fila — aguarde' : 'Fale com a gente');
        }
    }

    function mostrarIdent() {
        ident.hidden = false;
        codigoBox.hidden = true;
        compose.hidden = false;
        hint.textContent = 'Guarde o código que vamos gerar — é o seu meio de contato.';
    }

    function tratar(d) {
        if (d && d.csrf) { cfg.csrf = d.csrf; }
        if (!d || !d.ok) { return; }
        st.autenticado = !!d.autenticado;

        if (d.expirou) {
            sistemaLocal('Seu código de atendimento expirou. Inicie um novo atendimento abaixo.');
        }

        if (!d.conversa) {
            if (st.conversaId) {
                sistemaLocal('Atendimento encerrado. Se precisar, envie uma nova mensagem.');
                st.conversaId = null; st.ultimoId = 0;
            }
            if (!st.autenticado) { mostrarIdent(); } else { ident.hidden = true; codigoBox.hidden = true; compose.hidden = false; }
            setStatus(null);
            setBadge(0);
            return;
        }

        var nova = st.conversaId !== d.conversa.id;
        st.conversaId = d.conversa.id;
        if (nova) { body.innerHTML = ''; if (vazioEl) { vazioEl.remove(); vazioEl = null; } }
        if (d.conversa.anonimo && d.conversa.protocolo) { mostrarCodigo(d.conversa.protocolo); }
        render(d.mensagens);
        if (typeof d.ultimo_id === 'number' && d.ultimo_id > 0) { st.ultimoId = d.ultimo_id; }
        ident.hidden = true;
        codigoBox.hidden = true;
        compose.hidden = false;
        setStatus(d.conversa);
        setBadge(d.conversa.nao_lidas || 0);
    }

    function ciclo() {
        var url = cfg.estado + '?desde=' + st.ultimoId + (st.aberto ? '&presenca=1' : '');
        fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(tratar)
            .catch(function () {})
            .then(agendar);
    }

    function agendar() {
        clearTimeout(st.timer);
        st.timer = setTimeout(ciclo, st.aberto ? 4000 : 30000);
    }

    function abrir() {
        st.aberto = true;
        panel.hidden = false;
        fab.hidden = true;
        setBadge(0);
        setTimeout(function () { texto.focus(); rolar(); }, 50);
        ciclo();
    }

    function fechar() {
        st.aberto = false;
        panel.hidden = true;
        fab.hidden = false;
        ciclo();
    }

    function post(url, dados) {
        var fd = new FormData();
        Object.keys(dados || {}).forEach(function (k) { fd.append(k, dados[k]); });
        return fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
            body: fd,
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (d && d.csrf) { cfg.csrf = d.csrf; }
            return d;
        });
    }

    fab.addEventListener('click', abrir);
    document.getElementById('mlvChatMin').addEventListener('click', fechar);

    Array.prototype.slice.call(document.querySelectorAll('[data-mlv-chat-abrir]')).forEach(function (botao) {
        botao.addEventListener('click', function (e) { e.preventDefault(); abrir(); });
    });

    document.getElementById('mlvChatTemCodigo').addEventListener('click', function () {
        ident.hidden = true;
        compose.hidden = true;
        codigoBox.hidden = false;
        document.getElementById('mlvChatCodigo').focus();
    });

    document.getElementById('mlvChatVoltar').addEventListener('click', function () {
        codigoBox.hidden = true;
        if (!st.autenticado) { ident.hidden = false; }
        compose.hidden = false;
    });

    function acessarCodigo() {
        var codigo = document.getElementById('mlvChatCodigo').value.trim();
        if (codigo === '') { return; }
        hint.textContent = 'Verificando...';
        post(cfg.retomar, { codigo: codigo }).then(function (d) {
            if (d && d.ok) {
                document.getElementById('mlvChatCodigo').value = '';
                tratar(d);
                hint.textContent = 'Um atendente responde por aqui.';
            } else {
                hint.textContent = (d && d.erro) ? d.erro : 'Não foi possível acessar.';
            }
        }).catch(function () { hint.textContent = 'Falha ao verificar o código.'; });
    }

    document.getElementById('mlvChatAcessar').addEventListener('click', acessarCodigo);
    document.getElementById('mlvChatCodigo').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); acessarCodigo(); }
    });

    texto.addEventListener('input', function () {
        texto.style.height = 'auto';
        texto.style.height = Math.min(texto.scrollHeight, 120) + 'px';
    });
    texto.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = texto.value.trim();
        if (msg === '' || st.enviando) { return; }

        st.enviando = true;
        var botao = document.getElementById('mlvChatEnviar');
        botao.disabled = true;

        var dados = { texto: msg, canal: cfg.canal, evento_id: cfg.evento };
        if (ident && !ident.hidden) {
            dados.nome = form.querySelector('[name="nome"]').value;
            dados.telefone = form.querySelector('[name="telefone"]').value;
            dados.email = form.querySelector('[name="email"]').value;
        }

        post(cfg.enviar, dados).then(function (d) {
            if (!d || !d.ok) {
                if (d && d.precisa_dados) {
                    ident.hidden = false;
                    hint.textContent = d.erro || 'Informe nome, telefone e e-mail.';
                    var campo = (d.faltando && d.faltando[0]) ? d.faltando[0] : 'nome';
                    var input = form.querySelector('[name="' + campo + '"]');
                    if (input) { input.focus(); }
                } else {
                    hint.textContent = (d && d.erro) ? d.erro : 'Não foi possível enviar.';
                }
                return;
            }
            texto.value = '';
            texto.style.height = 'auto';
            tratar(d);
            hint.textContent = 'Um atendente responde por aqui.';
        }).catch(function () {
            hint.textContent = 'Falha ao enviar. Tente novamente.';
        }).then(function () {
            st.enviando = false;
            botao.disabled = false;
        });
    });

    // Estado inicial (badge / retomar conversa) e ciclo de polling.
    ciclo();
})();
</script>
