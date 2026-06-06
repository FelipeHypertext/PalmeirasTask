<?php
namespace App\View;

use App\Model\Noticia;

class NoticiaView {
    public static function listar(array $noticias, ?int $deletar = null): void { ?>
        <?php if ($deletar !== null): ?>
        <div class="alert-box alert-alerta" style="max-width:100%;margin-bottom:20px">
            Deseja realmente excluir esta notícia?
            <span>
                <a href="./?p=del-noticia&deletar=<?= $deletar ?>" class="btn btn-vermelho" style="padding:6px 14px;font-size:.85rem">Confirmar</a>
                <a href="./?p=noticias" class="btn btn-cinza" style="padding:6px 14px;font-size:.85rem">Cancelar</a>
            </span>
            <button class="alert-fechar" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
        <?php endif; ?>

        <section>
            <div class="tabela-actions">
                <h2>Notícias</h2>
                <?php if (isset($_SESSION["cargo"]) && $_SESSION["cargo"] === "admin"): ?>
                <a href="./?p=cad-noticia" class="btn btn-verde">+ Nova Notícia</a>
                <?php endif; ?>
            </div>

            <?php if (empty($noticias)): ?>
            <div class="empty-state">
                <h3>Nenhuma notícia ainda</h3>
                <p>Em breve novidades do clube!</p>
            </div>
            <?php else: ?>
            <div class="card-grid">
                <?php foreach ($noticias as $noticia): ?>
                <article class="card-noticia">
                    <div class="card-noticia__topo"></div>
                    <div class="card-noticia__corpo">
                        <h3 class="card-noticia__titulo"><?= $noticia->getTitulo() ?></h3>
                        <p class="card-noticia__resumo">
                            <?= mb_substr($noticia->getConteudo(), 0, 140) ?><?= mb_strlen($noticia->getConteudo()) > 140 ? "…" : "" ?>
                        </p>
                        <div style="display:flex;gap:8px;align-items:center;justify-content:space-between">
                            <a href="./?p=noticia&id=<?= $noticia->getId() ?>" class="btn btn-outline" style="padding:6px 14px;font-size:.85rem">Ler mais</a>
                            <?php if (isset($_SESSION["cargo"]) && $_SESSION["cargo"] === "admin"): ?>
                            <span style="display:flex;gap:6px">
                                <a href="./?p=alt-noticia&alt=<?= $noticia->getId() ?>" class="btn btn-cinza" style="padding:6px 12px;font-size:.8rem">Editar</a>
                                <a href="./?p=del-noticia&del=<?= $noticia->getId() ?>" class="btn btn-vermelho" style="padding:6px 12px;font-size:.8rem">Excluir</a>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
    <?php }

    public static function ver(Noticia $noticia): void { ?>
        <article class="noticia-detalhe">
            <header class="noticia-detalhe__header">
                <h1 class="noticia-detalhe__titulo"><?= $noticia->getTitulo() ?></h1>
                <?php if ($noticia->getCriadoEm() !== ""): ?>
                <time><?= $noticia->getCriadoEm() ?></time>
                <?php endif; ?>
            </header>
            <div class="noticia-detalhe__corpo">
                <p><?= nl2br($noticia->getConteudo()) ?></p>
                <div style="margin-top:32px">
                    <a href="./?p=noticias" class="btn btn-cinza">← Voltar às notícias</a>
                </div>
            </div>
        </article>
    <?php }

    public static function formulario(?string $msg, ?Noticia $noticia): void { ?>
        <section class="form-container">
            <h2 class="form-titulo"><?= isset($noticia) ? "Editar Notícia" : "Nova Notícia" ?></h2>

            <?php if ($msg !== null): ?>
            <div class="alert-box alert-erro">
                <?= $msg ?>
                <button class="alert-fechar" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
            <?php endif; ?>

            <form action="<?= isset($noticia) ? "./?p=alt-noticia" : "./?p=cad-noticia" ?>" method="post">
                <?php if (isset($noticia)): ?>
                <input type="hidden" name="id" value="<?= $noticia->getId() ?>">
                <?php endif; ?>

                <div class="form-grupo">
                    <label for="titulo">Título <span class="form-obrigatorio">*</span></label>
                    <input type="text" name="titulo" id="titulo"
                           value="<?= isset($noticia) ? $noticia->getTitulo() : "" ?>"
                           placeholder="Ex: Palmeiras vence o Clássico">
                </div>
                <div class="form-grupo">
                    <label for="conteudo">Conteúdo <span class="form-obrigatorio">*</span></label>
                    <textarea name="conteudo" id="conteudo" style="height:180px"
                              placeholder="Escreva o conteúdo da notícia..."><?= isset($noticia) ? $noticia->getConteudo() : "" ?></textarea>
                </div>

                <div class="form-acoes">
                    <button type="submit" class="btn btn-verde"><?= isset($noticia) ? "Confirmar" : "Publicar" ?></button>
                    <a href="./?p=noticias" class="btn btn-cinza">Cancelar</a>
                </div>
            </form>
        </section>
    <?php }
}
