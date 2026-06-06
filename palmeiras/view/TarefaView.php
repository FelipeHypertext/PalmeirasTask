<?php
namespace App\View;

use App\Model\Tarefa;

class TarefaView {
    public static function listar(array $tarefas, array $mapaUsuarios, ?int $deletar = null): void {
        $isAdmin   = ($_SESSION["cargo"] ?? "") === "admin";
        $idUsuario = (int) ($_SESSION["id_usuario"] ?? 0);
        ?>
        <?php if ($deletar !== null): ?>
        <div class="alert-box alert-alerta">
            Deseja realmente excluir esta tarefa?
            <span>
                <a href="./?p=del-tarefa&deletar=<?= $deletar ?>" class="btn btn-vermelho" style="padding:6px 14px;font-size:.85rem">Confirmar</a>
                <a href="./?p=tarefas" class="btn btn-cinza" style="padding:6px 14px;font-size:.85rem">Cancelar</a>
            </span>
            <button class="alert-fechar" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
        <?php endif; ?>

        <section class="tabela-wrapper">
            <div class="tabela-actions">
                <h2>Tarefas</h2>
                <?php if ($isAdmin): ?>
                <a href="./?p=cad-tarefa" class="btn btn-verde">+ Nova Tarefa</a>
                <?php endif; ?>
            </div>

            <?php if (empty($tarefas)): ?>
            <div class="empty-state">
                <h3>Nenhuma tarefa cadastrada</h3>
                <p>Crie a primeira tarefa usando o botão acima.</p>
            </div>
            <?php else: ?>
            <table>
                <caption style="display:none">Lista de tarefas do Palmeiras FC</caption>
                <thead>
                    <tr>
                        <th><a href="./?p=tarefas&order=id">#</a></th>
                        <th><a href="./?p=tarefas&order=titulo">Título</a></th>
                        <th><a href="./?p=tarefas&order=status">Status</a></th>
                        <th><a href="./?p=tarefas&order=prazo">Prazo</a></th>
                        <th>Responsável</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($tarefas as $tarefa): ?>
                    <tr>
                        <td><?= $tarefa->getId() ?></td>
                        <td style="text-align:left;font-weight:500"><?= $tarefa->getTitulo() ?></td>
                        <td>
                            <?php
                            $statusMap = [
                                "pendente"     => ["label" => "Pendente",     "classe" => "badge badge-pendente"],
                                "em_andamento" => ["label" => "Em andamento", "classe" => "badge badge-andamento"],
                                "concluida"    => ["label" => "Concluída",    "classe" => "badge badge-concluida"],
                            ];
                            $s = $statusMap[$tarefa->getStatus()] ?? ["label" => $tarefa->getStatus(), "classe" => "badge"];
                            ?>
                            <span class="<?= $s["classe"] ?>"><?= $s["label"] ?></span>
                        </td>
                        <td><?= $tarefa->getPrazo() ?></td>
                        <td><?= $mapaUsuarios[$tarefa->getDesignadoPara()] ?? "—" ?></td>
                        <td>
                            <?php if ($isAdmin): ?>
                            <div class="td-acoes">
                                <a href="./?p=alt-tarefa&alt=<?= $tarefa->getId() ?>" class="btn btn-cinza" style="padding:5px 12px;font-size:.8rem">Editar</a>
                                <a href="./?p=del-tarefa&del=<?= $tarefa->getId() ?>" class="btn btn-vermelho" style="padding:5px 12px;font-size:.8rem">Excluir</a>
                            </div>
                            <?php elseif ($tarefa->getDesignadoPara() === $idUsuario): ?>
                            <a href="./?p=alt-tarefa&alt=<?= $tarefa->getId() ?>" class="btn btn-cinza" style="padding:5px 12px;font-size:.8rem">Atualizar status</a>
                            <?php else: ?>
                            —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>
    <?php }

    public static function formulario(?string $msg, ?Tarefa $tarefa, array $usuarios): void { ?>
        <section class="form-container">
            <h2 class="form-titulo"><?= isset($tarefa) ? "Editar Tarefa" : "Nova Tarefa" ?></h2>

            <?php if ($msg !== null): ?>
            <div class="alert-box alert-erro">
                <?= $msg ?>
                <button class="alert-fechar" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
            <?php endif; ?>

            <form action="<?= isset($tarefa) ? "./?p=alt-tarefa" : "./?p=cad-tarefa" ?>" method="post">
                <input type="hidden" name="csrf" value="<?= $_SESSION["csrf_token"] ?>">
                <?php if (isset($tarefa)): ?>
                <input type="hidden" name="id"         value="<?= $tarefa->getId() ?>">
                <input type="hidden" name="criado_por" value="<?= $tarefa->getCriadoPor() ?>">
                <?php endif; ?>

                <div class="form-grupo">
                    <label for="titulo">Título <span class="form-obrigatorio">*</span></label>
                    <input type="text" name="titulo" id="titulo"
                           value="<?= isset($tarefa) ? $tarefa->getTitulo() : "" ?>"
                           placeholder="Ex: Treino tático — sexta-feira">
                </div>
                <div class="form-grupo">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" id="descricao"
                              placeholder="Detalhes da tarefa (opcional)"><?= isset($tarefa) ? $tarefa->getDescricao() : "" ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-grupo">
                        <label for="prazo">Prazo <span class="form-obrigatorio">*</span></label>
                        <input type="date" name="prazo" id="prazo"
                               value="<?= isset($tarefa) ? $tarefa->getPrazo() : "" ?>">
                    </div>
                    <?php if (isset($tarefa)): ?>
                    <div class="form-grupo">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="pendente"     <?= $tarefa->getStatus() === "pendente"     ? "selected" : "" ?>>Pendente</option>
                            <option value="em_andamento" <?= $tarefa->getStatus() === "em_andamento" ? "selected" : "" ?>>Em andamento</option>
                            <option value="concluida"    <?= $tarefa->getStatus() === "concluida"    ? "selected" : "" ?>>Concluída</option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="form-grupo">
                    <label for="designado_para">Responsável <span class="form-obrigatorio">*</span></label>
                    <select name="designado_para" id="designado_para">
                        <option value="">— Selecione —</option>
                        <?php foreach ($usuarios as $u): ?>
                        <option value="<?= $u->getId() ?>"
                            <?= (isset($tarefa) && $tarefa->getDesignadoPara() === $u->getId()) ? "selected" : "" ?>>
                            <?= $u->getNome() ?> (<?= $u->getCargo() ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-acoes">
                    <button type="submit" class="btn btn-verde"><?= isset($tarefa) ? "Confirmar" : "Criar Tarefa" ?></button>
                    <a href="./?p=tarefas" class="btn btn-cinza">Cancelar</a>
                </div>
            </form>
        </section>
    <?php }

    public static function formularioStatus(?string $msg, Tarefa $tarefa): void { ?>
        <section class="form-container">
            <h2 class="form-titulo">Atualizar Status</h2>
            <p style="color:var(--cinza-texto);margin-bottom:20px;font-size:.95rem">
                Tarefa: <strong><?= htmlentities($tarefa->getTitulo()) ?></strong>
            </p>

            <?php if ($msg !== null): ?>
            <div class="alert-box alert-erro">
                <?= $msg ?>
                <button class="alert-fechar" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
            <?php endif; ?>

            <form action="./?p=alt-tarefa" method="post">
                <input type="hidden" name="csrf" value="<?= $_SESSION["csrf_token"] ?>">
                <input type="hidden" name="id"   value="<?= $tarefa->getId() ?>">

                <div class="form-grupo">
                    <label for="status">Status <span class="form-obrigatorio">*</span></label>
                    <select name="status" id="status">
                        <option value="pendente"     <?= $tarefa->getStatus() === "pendente"     ? "selected" : "" ?>>Pendente</option>
                        <option value="em_andamento" <?= $tarefa->getStatus() === "em_andamento" ? "selected" : "" ?>>Em andamento</option>
                        <option value="concluida"    <?= $tarefa->getStatus() === "concluida"    ? "selected" : "" ?>>Concluída</option>
                    </select>
                </div>

                <div class="form-acoes">
                    <button type="submit" class="btn btn-verde">Salvar</button>
                    <a href="./?p=tarefas" class="btn btn-cinza">Cancelar</a>
                </div>
            </form>
        </section>
    <?php }
}
