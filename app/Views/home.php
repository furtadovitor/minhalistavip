<?= $this->extend('templates/layouts/public') ?>

<?= $this->section('conteudo') ?>

<!-- ============================ HERO ============================ -->
<section class="hero-gradient py-5">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge badge-soft rounded-pill px-3 py-2 fs-8 text-uppercase mb-3">
                    <i class="bi bi-stars me-1"></i> Presentes em dinheiro via PIX
                </span>
                <h1 class="display-5 fw-bold lh-sm mb-3">
                    A lista de presentes <span class="text-brand">do seu evento</span>, sem sair de casa
                </h1>
                <p class="fs-5 text-secondary mb-4">
                    Crie a lista do seu casamento, chá de bebê ou aniversário em minutos.
                    O convidado escolhe um presente e o valor cai direto na sua conta via PIX —
                    com RSVP e mural de recados inclusos.
                </p>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <a class="btn btn-brand btn-lg px-4" href="<?= site_url('registro') ?>">
                        <i class="bi bi-rocket-takeoff me-2"></i>Criar minha lista grátis
                    </a>
                    <a class="btn btn-outline-brand btn-lg px-4" href="#exemplos">Ver exemplos</a>
                </div>
                <p class="fs-7 text-muted mb-0">
                    <i class="bi bi-check-circle-fill text-success me-1"></i> Sem mensalidade para começar
                    <span class="mx-2">·</span>
                    <i class="bi bi-check-circle-fill text-success me-1"></i> Receba por PIX
                </p>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <p class="fs-8 text-uppercase text-muted fw-semibold mb-3">Como o convidado vê</p>
                        <div class="d-flex align-items-center gap-3 p-3 rounded-4 bg-indigo-100 mb-3">
                            <div class="bg-brand text-white rounded-3 d-flex align-items-center justify-content-center"
                                 style="width:48px;height:48px;">
                                <i class="bi bi-gift-fill fs-5"></i>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0">Cota de Lua de Mel</p>
                                <p class="fs-7 text-muted mb-0">R$ 300,00 · 3 de 10 cotas presenteadas</p>
                            </div>
                            <a class="btn btn-brand btn-sm ms-auto px-3" href="<?= site_url('demo/casamento') ?>">Presentear</a>
                        </div>
                        <div class="d-flex align-items-center gap-3 p-3 rounded-4 border">
                            <div class="bg-white border rounded-3 d-flex align-items-center justify-content-center"
                                 style="width:48px;height:48px;">
                                <i class="bi bi-cash-coin text-success fs-5"></i>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0">Recebimento instantâneo</p>
                                <p class="fs-7 text-muted mb-0">O valor é creditado na carteira do organizador</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== BUSCA DO CONVIDADO ===================== -->
<section class="py-5" id="buscar">
    <div class="container" style="max-width: 820px;">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5 text-center">
                <h2 class="h4 fw-bold mb-2">Foi convidado?</h2>
                <p class="text-muted mb-4">
                    Cole o link que o organizador enviou, ou digite o código do seu pedido,
                    para acessar a lista.
                </p>

                <?= view('templates/partials/flash') ?>

                <form method="post" action="<?= site_url('buscar') ?>" class="row g-2 justify-content-center">
                    <?= csrf_field() ?>
                    <div class="col-md-8">
                        <input type="text" class="form-control form-control-lg" name="termo"
                               placeholder="ex.: minhalistavip.com.br/casamento-ana-e-joao ou MLV260929ABCD12"
                               value="<?= esc(old('termo')) ?>" required>
                    </div>
                    <div class="col-md-4 d-grid">
                        <button class="btn btn-brand btn-lg"><i class="bi bi-search me-2"></i>Buscar lista</button>
                    </div>
                </form>
                <p class="fs-8 text-muted mt-3 mb-0">
                    Dica: o link costuma vir assim <em>minhalistavip.com.br/<strong>nome-do-evento</strong></em>.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ======================= LISTAS DE EXEMPLO ==================== -->
<section class="py-5 bg-light" id="exemplos">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge badge-soft px-3 py-2 rounded-pill fw-semibold text-uppercase fs-8">Inspire-se</span>
            <h2 class="fw-bold fs-2 mt-2 text-dark">Veja como fica a sua lista</h2>
            <p class="text-muted fs-6">
                Navegue pelas 3 listas de exemplo e veja como é fácil e elegante presentear.
                As listas de clientes reais são privadas.
            </p>
        </div>

        <div class="row g-4">
            <?php foreach ($demos as $demo): ?>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden transition-hover">
                        <div class="position-relative">
                            <img src="<?= esc($demo['capa'], 'attr') ?>" class="card-img-top object-fit-cover"
                                 alt="Exemplo <?= esc($demo['tipo']) ?>" style="height: 220px;">
                            <span class="position-absolute top-0 end-0 m-3 badge bg-<?= esc($demo['badge'], 'attr') ?> text-<?= $demo['badge'] === 'warning' ? 'dark' : 'white' ?> px-3 py-2 rounded-pill fw-bold fs-8">
                                <?= esc($demo['icone']) ?> <?= esc($demo['tipo']) ?>
                            </span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <h3 class="card-title fw-bold fs-5 text-dark mb-1"><?= esc($demo['titulo']) ?></h3>
                            <p class="text-muted fs-7 mb-3"><?= esc($demo['data']) ?> • <?= esc($demo['local']) ?></p>
                            <p class="card-text fs-7 text-secondary flex-grow-1"><?= esc($demo['resumo']) ?></p>
                            <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                                <span class="fs-8 text-muted"><?= esc($demo['presentes']) ?> presentes</span>
                                <a href="<?= site_url('demo/' . $demo['slug']) ?>" class="btn btn-outline-brand btn-sm rounded-pill fw-semibold px-3">
                                    Ver exemplo &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="text-center mt-4 mb-0">
            <a class="btn btn-outline-secondary rounded-pill px-4" href="<?= site_url('registro') ?>">
                Quero uma lista assim para o meu evento
            </a>
        </p>
    </div>
</section>

<!-- ================== COMO FUNCIONA / VANTAGENS ================= -->
<section class="py-5" id="como-funciona">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold fs-2">Como funciona</h2>
            <p class="text-muted">Do presente ao PIX, em três passos simples.</p>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="bg-indigo-100 text-indigo-700 rounded-3 d-inline-flex align-items-center justify-content-center mb-3"
                             style="width:48px;height:48px;"><i class="bi bi-pencil-square fs-5"></i></div>
                        <h3 class="h6 fw-bold">1. Monte a lista</h3>
                        <p class="fs-7 text-muted mb-0">
                            Escolha itens do catálogo ou crie cotas personalizadas. Defina valores,
                            metragem de cotas e o tema do seu evento.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="bg-indigo-100 text-indigo-700 rounded-3 d-inline-flex align-items-center justify-content-center mb-3"
                             style="width:48px;height:48px;"><i class="bi bi-share fs-5"></i></div>
                        <h3 class="h6 fw-bold">2. Compartilhe o link</h3>
                        <p class="fs-7 text-muted mb-0">
                            Envie o link do seu hotsite. Os convidados escolhem a cota, pagam no PIX e
                            deixam um recado no mural.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="bg-indigo-100 text-indigo-700 rounded-3 d-inline-flex align-items-center justify-content-center mb-3"
                             style="width:48px;height:48px;"><i class="bi bi-cash-stack fs-5"></i></div>
                        <h3 class="h6 fw-bold">3. Receba por PIX</h3>
                        <p class="fs-7 text-muted mb-0">
                            O valor entra na sua carteira assim que o pagamento é confirmado.
                            Acompanhe o extrato e solicite saques quando quiser.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="p-4 rounded-4 h-100" style="background: var(--brand-soft);">
                    <h3 class="h6 fw-bold text-indigo-700 mb-3"><i class="bi bi-heart-fill me-2"></i>Para o organizador</h3>
                    <ul class="fs-7 mb-0 ps-3">
                        <li class="mb-1">Cotas em dinheiro, presentes reais por link e cotas livres.</li>
                        <li class="mb-1">RSVP (confirmação de presença) e mural de recados.</li>
                        <li class="mb-1">Carteira com extrato, taxas transparentes e saque via PIX.</li>
                        <li class="mb-0">Página personalizada com o tema e as cores do evento.</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-4 rounded-4 h-100" style="background: #ECFDF5;">
                    <h3 class="h6 fw-bold text-success mb-3"><i class="bi bi-people-fill me-2"></i>Para o convidado</h3>
                    <ul class="fs-7 mb-0 ps-3">
                        <li class="mb-1">Escolhe a cota sem precisar criar conta.</li>
                        <li class="mb-1">Pagamento rápido por PIX (QR Code e Copia e Cola).</li>
                        <li class="mb-1">Recado publicado no mural após o pagamento.</li>
                        <li class="mb-0">Acompanha o status do pedido pelo código.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================== CTA FINAL ========================= -->
<section class="py-5">
    <div class="container">
        <div class="p-5 rounded-4 text-white text-center" style="background: linear-gradient(135deg, #4F46E5, #4338CA);">
            <h2 class="fw-bold mb-2">Crie a sua lista agora, é grátis</h2>
            <p class="mb-4 opacity-75">Em poucos minutos o seu evento está no ar para receber presentes via PIX.</p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a class="btn btn-light btn-lg px-4 fw-semibold" href="<?= site_url('registro') ?>">Criar conta grátis</a>
                <a class="btn btn-outline-light btn-lg px-4" href="<?= site_url('login') ?>">Já tenho conta</a>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
