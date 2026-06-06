<?php
use App\Dal\NoticiaDao;
$ultimasNoticias = NoticiaDao::listar(3);
$autenticadoHome = isset($_SESSION["id_usuario"]);
?>

<section class="hero">
    <div class="hero__conteudo">
        <h1 class="hero__titulo">Palmeiras FC<br><span>Gerenciador</span> de Tarefas</h1>
        <p class="hero__subtitulo">Organize, delegue e acompanhe as atividades do clube</p>
        <?php if (!$autenticadoHome): ?>
        <nav class="hero__acoes">
            <a href="./?p=login"   class="btn btn-verde">Entrar no sistema</a>
            <a href="./?p=registro" class="btn btn-outline" style="border-color:#fff;color:#fff">Criar conta</a>
        </nav>
        <?php else: ?>
        <nav class="hero__acoes">
            <a href="./?p=tarefas" class="btn btn-verde">Ver tarefas</a>
        </nav>
        <?php endif; ?>
    </div>
</section>

<section>
    <h2 class="page-titulo">Últimas Notícias</h2>

    <?php if (empty($ultimasNoticias)): ?>
    <div class="empty-state">
        <h3>Nenhuma notícia publicada ainda</h3>
    </div>
    <?php else: ?>
    <div class="card-grid">
        <?php foreach ($ultimasNoticias as $noticia): ?>
        <article class="card-noticia">
            <div class="card-noticia__topo"></div>
            <div class="card-noticia__corpo">
                <h3 class="card-noticia__titulo"><?= $noticia->getTitulo() ?></h3>
                <p class="card-noticia__resumo">
                    <?= mb_substr($noticia->getConteudo(), 0, 120) ?><?= mb_strlen($noticia->getConteudo()) > 120 ? "…" : "" ?>
                </p>
                <a href="./?p=noticia&id=<?= $noticia->getId() ?>" class="btn btn-outline" style="padding:6px 14px;font-size:.85rem">Ler mais</a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <div style="text-align:center;margin-top:24px">
        <a href="./?p=noticias" class="btn btn-cinza">Ver todas as notícias</a>
    </div>
    <?php endif; ?>
</section>
