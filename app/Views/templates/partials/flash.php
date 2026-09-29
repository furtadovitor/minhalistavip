<?php if ($erro = session()->getFlashdata('erro')): ?>
    <div class="alert alert-danger"><?= esc($erro) ?></div>
<?php endif; ?>

<?php if ($sucesso = session()->getFlashdata('sucesso')): ?>
    <div class="alert alert-success"><?= esc($sucesso) ?></div>
<?php endif; ?>

<?php if ($erros = session()->getFlashdata('erros')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ((array) $erros as $mensagem): ?>
                <li><?= esc($mensagem) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
