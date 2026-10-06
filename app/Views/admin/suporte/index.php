<?= $this->extend('templates/layouts/app') ?>

<?php
$filaJson = json_encode([
    'aguardando'    => array_values($fila['aguardando']),
    'em_atendimento' => array_values($fila['em_atendimento']),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>

<?= $this->section('conteudo') ?>
<style>
    .sup-wrap { display: flex; gap: 1rem; align-items: stretch; min-height: 74vh; }
    .sup-lista { width: 340px; flex: none; display: flex; flex-direction: column; }
    .sup-chat { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column;
        background: #fff; border: 1px solid #E5E7EB; border-radius: 1rem; overflow: hidden; }
    @media (max-width: 991.98px) { .sup-wrap { flex-direction: column; } .sup-lista { width: 100%; } }

    .sup-fila-card { background: #fff; border: 1px solid #E5E7EB; border-radius: 1rem; overflow: hidden; flex: 1 1 auto; display: flex; flex-direction: column; }
    .sup-fila-head { padding: .75rem 1rem; border-bottom: 1px solid #F3F4F6; display: flex; align-items: center; justify-content: space-between; }
    .sup-fila-scroll { overflow-y: auto; max-height: 62vh; }
    .sup-grupo-titulo { font-size: .68rem; text-transform: uppercase; letter-spacing: .07em; color: #9CA3AF; font-weight: 700; padding: .6rem 1rem .2rem; }
    .sup-item { width: 100%; text-align: left; border: 0; border-bottom: 1px solid #F3F4F6; background: #fff; padding: .7rem 1rem; display: block; }
    .sup-item:hover { background: #F9FAFB; }
    .sup-item.ativo { background: var(--brand-soft); }
    .sup-item .top { display: flex; align-items: center; gap: .4rem; }
    .sup-item .nome { font-weight: 600; font-size: .86rem; color: #111827; flex: 1 1 auto; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sup-item .meta { font-size: .72rem; color: #9CA3AF; display: flex; align-items: center; gap: .4rem; margin-top: .15rem; }
    .sup-item .prev { font-size: .76rem; color: #6B7280; margin-top: .2rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sup-badge { min-width: 20px; height: 20px; padding: 0 5px; border-radius: 999px; background: #EF4444; color: #fff; font-size: .68rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }

    .sup-chat-head { padding: .8rem 1rem; border-bottom: 1px solid #F3F4F6; display: flex; align-items: center; gap: .6rem; }
    .sup-chat-head .nome { font-weight: 700; color: #111827; }
    .sup-chat-head .sub { font-size: .74rem; color: #9CA3AF; }
    .sup-chat-body { flex: 1 1 auto; overflow-y: auto; padding: 1rem; background: #F3F4F6; display: flex; flex-direction: column; gap: .5rem; min-height: 300px; }
    .sup-msg { max-width: 74%; padding: .55rem .75rem; border-radius: .9rem; font-size: .87rem; line-height: 1.35; white-space: pre-wrap; word-wrap: break-word; }
    .sup-msg .hora { display: block; font-size: .64rem; opacity: .6; margin-top: .2rem; }
    .sup-msg.cliente { align-self: flex-start; background: #fff; border: 1px solid #E5E7EB; color: #111827; border-bottom-left-radius: .25rem; }
    .sup-msg.atendente { align-self: flex-end; background: var(--brand); color: #fff; border-bottom-right-radius: .25rem; }
    .sup-msg.sistema { align-self: center; background: transparent; color: #6B7280; font-size: .72rem; text-align: center; }
    .sup-vazio { margin: auto; text-align: center; color: #9CA3AF; }

    .sup-form { border-top: 1px solid #E5E7EB; padding: .65rem; display: flex; align-items: flex-end; gap: .5rem; background: #fff; }
    .sup-form textarea { flex: 1 1 auto; resize: none; max-height: 120px; border: 1px solid #D1D5DB; border-radius: .8rem; padding: .5rem .75rem; font-size: .88rem; }
    .sup-form button { width: 42px; height: 42px; flex: none; border: 0; border-radius: 50%; background: var(--brand); color: #fff; }
    .sup-form button:disabled { opacity: .5; }
</style>

<div class="row g-3 mb-3">
    <div class="col-auto">
        <div class="stat-card card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="h4 fw-bold mb-0" id="supTotalAguardando"><?= count($fila['aguardando']) ?></div>
                    <div class="text-muted fs-8">Aguardando</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-auto">
        <div class="stat-card card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-chat-dots"></i></div>
                <div>
                    <div class="h4 fw-bold mb-0" id="supTotalAtendimento"><?= count($fila['em_atendimento']) ?></div>
                    <div class="text-muted fs-8">Em atendimento</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sup-wrap">
    <div class="sup-lista">
        <div class="sup-fila-card">
            <div class="sup-fila-head">
                <strong class="fs-7">Fila de atendimento</strong>
                <button class="btn btn-sm btn-outline-secondary btn-icon" id="supAtualizar" title="Atualizar"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
            <div class="sup-fila-scroll" id="supFila"></div>
        </div>
    </div>

    <div class="sup-chat" id="supChat">
        <div class="sup-vazio" id="supChatVazio">
            <i class="bi bi-chat-square-text" style="font-size: 2.2rem;"></i>
            <p class="mt-2 mb-0">Selecione uma conversa ao lado para atender.</p>
        </div>
    </div>
</div>

<script>
(function () {
    var CSRF = <?= json_encode(csrf_hash()) ?>;
    var FLUXO = {
        fila: <?= json_encode(site_url('admin/suporte/fila')) ?>,
        base: <?= json_encode(site_url('admin/suporte')) ?>,
        eu: <?= json_encode((int) ($usuarioId ?? 0)) ?>
    };
    var fila = <?= $filaJson ?>;

    var elFila = document.getElementById('supFila');
    var elChat = document.getElementById('supChat');

    var st = { id: null, nome: '', status: '', canal: '', atendenteId: null, ultimoId: 0, enviando: false, timer: null };

    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }

    function tempo(iso) {
        if (!iso) { return ''; }
        var d = new Date(iso.replace(' ', 'T') + (iso.length <= 19 ? '' : ''));
        var seg = Math.max(1, Math.floor((Date.now() - d.getTime()) / 1000));
        if (seg < 60) { return 'agora'; }
        if (seg < 3600) { return 'há ' + Math.floor(seg / 60) + ' min'; }
        return 'há ' + Math.floor(seg / 3600) + ' h';
    }

    function post(url, dados) {
        var body = new FormData();
        Object.keys(dados || {}).forEach(function (k) { body.append(k, dados[k]); });
        return fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: body,
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (d && d.csrf) { CSRF = d.csrf; }
            return d;
        });
    }

    function itemHtml(c) {
        var classe = (c.id === st.id) ? ' ativo' : '';
        var badge = c.nao_lidas > 0 ? '<span class="sup-badge">' + (c.nao_lidas > 9 ? '9+' : c.nao_lidas) + '</span>' : '';
        var canal = c.canal === 'painel' ? '<i class="bi bi-speedometer2"></i> painel' : '<i class="bi bi-globe2"></i> site';
        var quem = c.atendente_id ? (Number(c.atendente_id) === FLUXO.eu ? ' · com você' : ' · atendendo') : '';
        return '<button type="button" class="sup-item' + classe + '" data-id="' + c.id + '">' +
            '<div class="top"><span class="nome">' + esc(c.nome_exibicao || 'Visitante') + '</span>' + badge + '</div>' +
            '<div class="meta"><span>' + c.protocolo + '</span><span>' + canal + '</span><span>' + tempo(c.criado_em) + quem + '</span></div>' +
            (c.ultima_mensagem_preview ? '<div class="prev">' + esc(c.ultima_mensagem_preview) + '</div>' : '') +
        '</button>';
    }

    function renderFila() {
        var html = '';
        if (fila.aguardando.length) {
            html += '<div class="sup-grupo-titulo">Aguardando (' + fila.aguardando.length + ')</div>';
            html += fila.aguardando.map(itemHtml).join('');
        }
        if (fila.em_atendimento.length) {
            html += '<div class="sup-grupo-titulo">Em atendimento</div>';
            html += fila.em_atendimento.map(itemHtml).join('');
        }
        if (!html) { html = '<div class="text-muted text-center py-4 fs-7">Nenhuma conversa.</div>'; }
        elFila.innerHTML = html;
        document.getElementById('supTotalAguardando').textContent = fila.aguardando.length;
        document.getElementById('supTotalAtendimento').textContent = fila.em_atendimento.length;
    }

    function carregarFila() {
        fetch(FLUXO.fila, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) { if (d && d.ok) { fila = d.fila; renderFila(); } })
            .catch(function () {});
    }

    function bolha(m) {
        var div = document.createElement('div');
        div.className = 'sup-msg ' + (m.autor || 'sistema');
        var t = document.createElement('span');
        t.textContent = m.texto;
        div.appendChild(t);
        if (m.hora) { var h = document.createElement('span'); h.className = 'hora'; h.textContent = m.hora; div.appendChild(h); }
        return div;
    }

    function corpo() { return elChat.querySelector('.sup-chat-body'); }

    function montarChat(c) {
        st.id = c.id; st.nome = c.nome; st.status = c.status; st.canal = c.canal; st.atendenteId = c.atendente_id;

        var assumirBotao = '';
        if (c.status === 'aguardando') {
            assumirBotao = '<button class="btn btn-sm btn-brand" id="supAssumir"><i class="bi bi-hand-index-thumb me-1"></i>Assumir</button>';
        }
        var encerrarBotao = '<button class="btn btn-sm btn-outline-danger" id="supEncerrar" title="Encerrar"><i class="bi bi-x-lg"></i></button>';

        elChat.innerHTML =
            '<div class="sup-chat-head">' +
                '<div class="flex-grow-1">' +
                    '<div class="nome">' + esc(c.nome) + ' <span class="badge text-bg-light">' + c.protocolo + '</span></div>' +
                    '<div class="sub">' + (c.canal === 'painel' ? 'Organizador (painel)' : 'Visitante (site)') +
                        (c.telefone ? ' · ' + esc(c.telefone) : '') +
                        (c.email ? ' · ' + esc(c.email) : '') + '</div>' +
                '</div>' +
                assumirBotao + encerrarBotao +
            '</div>' +
            '<div class="sup-chat-body"></div>' +
            '<form class="sup-form" id="supForm">' +
                '<textarea rows="1" id="supTexto" maxlength="4000" placeholder="Escreva sua resposta..."></textarea>' +
                '<button type="submit" id="supEnviar"><i class="bi bi-send-fill"></i></button>' +
            '</form>';

        var t = document.getElementById('supTexto');
        t.addEventListener('input', function () { t.style.height = 'auto'; t.style.height = Math.min(t.scrollHeight, 120) + 'px'; });
        t.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); document.getElementById('supForm').requestSubmit(); } });
        document.getElementById('supForm').addEventListener('submit', enviar);
        var bAssumir = document.getElementById('supAssumir');
        if (bAssumir) { bAssumir.addEventListener('click', assumir); }
        document.getElementById('supEncerrar').addEventListener('click', encerrar);
    }

    function renderMensagens(lista) {
        var area = corpo();
        var perto = area.scrollHeight - area.scrollTop - area.clientHeight < 120;
        (lista || []).forEach(function (m) { area.appendChild(bolha(m)); });
        if (perto) { area.scrollTop = area.scrollHeight; }
    }

    function abrir(id) {
        fetch(FLUXO.base + '/' + id + '/mensagens', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d || !d.ok) { return; }
                if (d.csrf) { CSRF = d.csrf; }
                st.ultimoId = d.ultimo_id || 0;
                montarChat(d.conversa);
                var area = corpo();
                (d.mensagens || []).forEach(function (m) { area.appendChild(bolha(m)); });
                area.scrollTop = area.scrollHeight;
                renderFila();
                agendar();
            })
            .catch(function () {});
    }

    function cicloAberto() {
        if (!st.id) { return; }
        fetch(FLUXO.base + '/' + st.id + '/mensagens?desde=' + st.ultimoId, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d || !d.ok) { return; }
                if (d.csrf) { CSRF = d.csrf; }
                if (d.ultimo_id) { st.ultimoId = d.ultimo_id; }
                renderMensagens(d.mensagens);
                if (d.conversa && (d.conversa.status !== st.status || d.conversa.atendente_id !== st.atendenteId)) {
                    var atualizarForm = document.getElementById('supForm');
                    montarChat(d.conversa);
                    var msg = d.mensagens || [];
                    if (atualizarForm && msg.length) { renderMensagens(msg); }
                }
            })
            .catch(function () {});
    }

    function assumir() {
        if (!st.id) { return; }
        post(FLUXO.base + '/' + st.id + '/assumir', {}).then(function (d) {
            if (d && d.ok) { abrir(st.id); carregarFila(); }
            else if (d && d.erro) { carregarFila(); alert(d.erro); }
        });
    }

    function enviar(e) {
        e.preventDefault();
        if (!st.id || st.enviando) { return; }
        var t = document.getElementById('supTexto');
        var texto = t.value.trim();
        if (texto === '') { return; }

        st.enviando = true;
        var botao = document.getElementById('supEnviar');
        botao.disabled = true;

        post(FLUXO.base + '/' + st.id + '/mensagens', { texto: texto }).then(function (d) {
            if (d && d.ok) {
                t.value = ''; t.style.height = 'auto';
                if (d.ultimo_id) { st.ultimoId = d.ultimo_id; }
                renderMensagens(d.mensagens);
                carregarFila();
            } else if (d && d.erro) {
                alert(d.erro);
            }
        }).then(function () { st.enviando = false; botao.disabled = false; });
    }

    function encerrar() {
        if (!st.id) { return; }
        if (!window.confirm('Encerrar esta conversa?')) { return; }
        post(FLUXO.base + '/' + st.id + '/encerrar', {}).then(function () {
            st.id = null; st.ultimoId = 0;
            elChat.innerHTML = '<div class="sup-vazio"><i class="bi bi-check2-circle" style="font-size:2.2rem;"></i><p class="mt-2 mb-0">Conversa encerrada.</p></div>';
            carregarFila();
        });
    }

    function agendar() {
        clearTimeout(st.timer);
        st.timer = setTimeout(function () {
            if (st.id) { cicloAberto(); }
            agendar();
        }, 4000);
    }

    elFila.addEventListener('click', function (e) {
        var item = e.target.closest('.sup-item');
        if (item) { abrir(Number(item.dataset.id)); }
    });
    document.getElementById('supAtualizar').addEventListener('click', carregarFila);

    renderFila();
    setInterval(carregarFila, 8000);
    agendar();
})();
</script>
<?= $this->endSection() ?>
