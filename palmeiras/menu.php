<a href="./?p=home" class="nav-logo">SE <span>Palmeiras</span></a>

<div class="nav-links">
    <?php if (!$autenticado): ?>
    <a href="./?p=home">Home</a>
    <a href="./?p=noticias">Notícias</a>
    <a href="./?p=sobre">Sobre</a>
    <?php else: ?>
    <a href="./?p=tarefas">Tarefas</a>
    <?php if (($_SESSION["cargo"] ?? "") === "admin"): ?>
    <a href="./?p=cad-tarefa">Nova Tarefa</a>
    <a href="./?p=usuarios">Jogadores</a>
    <a href="./?p=cad-noticia">Nova Notícia</a>
    <?php endif; ?>
    <?php endif; ?>
</div>

<div class="nav-usuario">
    <?php if ($autenticado): ?>
    <span>Olá, <strong><?= htmlentities($_SESSION["nome_usuario"] ?? "") ?></strong></span>
    <a href="./?p=sair" class="btn btn-sair">Sair</a>
    <?php else: ?>
    <a href="./?p=login"   class="btn btn-outline" style="padding:7px 16px;font-size:.85rem">Entrar</a>
    <a href="./?p=registro" class="btn btn-verde"  style="padding:7px 16px;font-size:.85rem">Cadastrar</a>
    <?php endif; ?>
</div>
