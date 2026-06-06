<?php
namespace App\View;

use App\Model\Usuario;

class UsuarioView {
    public static function listar(array $usuarios, ?int $deletar = null): void { ?>
        <?php if ($deletar !== null): ?>
        <div class="alert-box alert-alerta">
            Deseja realmente excluir este jogador?
            <span>
                <a href="./?p=del-usuario&deletar=<?= $deletar ?>" class="btn btn-vermelho" style="padding:6px 14px;font-size:.85rem">Confirmar</a>
                <a href="./?p=usuarios" class="btn btn-cinza" style="padding:6px 14px;font-size:.85rem">Cancelar</a>
            </span>
            <button class="alert-fechar" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
        <?php endif; ?>

        <section class="tabela-wrapper">
            <div class="tabela-actions">
                <h2>Jogadores / Equipe</h2>
                <a href="./?p=cad-usuario" class="btn btn-verde">+ Cadastrar Jogador</a>
            </div>
            <table>
                <caption style="display:none">Lista de jogadores do Palmeiras FC</caption>
                <thead>
                    <tr>
                        <th><a href="./?p=usuarios&order=id">#</a></th>
                        <th><a href="./?p=usuarios&order=nome">Nome</a></th>
                        <th>E-mail</th>
                        <th>Posição</th>
                        <th><a href="./?p=usuarios&order=cargo">Cargo</a></th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= $usuario->getId() ?></td>
                        <td style="text-align:left;font-weight:500"><?= $usuario->getNome() ?></td>
                        <td><?= $usuario->getEmail() ?></td>
                        <td><?= $usuario->getPosicao() !== "" ? $usuario->getPosicao() : "—" ?></td>
                        <td>
                            <span class="badge <?= $usuario->getCargo() === "admin" ? "badge-admin" : "badge-membro" ?>">
                                <?= $usuario->getCargo() === "admin" ? "Admin" : "Membro" ?>
                            </span>
                        </td>
                        <td>
                            <div class="td-acoes">
                                <a href="./?p=alt-usuario&alt=<?= $usuario->getId() ?>" class="btn btn-cinza" style="padding:5px 12px;font-size:.8rem">Editar</a>
                                <a href="./?p=del-usuario&del=<?= $usuario->getId() ?>" class="btn btn-vermelho" style="padding:5px 12px;font-size:.8rem">Excluir</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php }

    public static function formulario(?string $msg, ?Usuario $usuario): void {
        $posicoes = [
            "Goleiro", "Lateral Direito", "Lateral Esquerdo",
            "Zagueiro", "Volante", "Meia", "Meia-Atacante",
            "Atacante", "Centroavante", "Técnico", "Auxiliar Técnico"
        ]; ?>

        <section class="form-container">
            <h2 class="form-titulo"><?= isset($usuario) ? "Editar Jogador" : "Cadastrar Jogador" ?></h2>

            <?php if ($msg !== null): ?>
            <div class="alert-box alert-erro">
                <?= $msg ?>
                <button class="alert-fechar" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
            <?php endif; ?>

            <form action="<?= isset($usuario) ? "./?p=alt-usuario" : "./?p=cad-usuario" ?>" method="post">
                <?php if (isset($usuario)): ?>
                <input type="hidden" name="id" value="<?= $usuario->getId() ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-grupo">
                        <label for="nome">Nome <span class="form-obrigatorio">*</span></label>
                        <input type="text" name="nome" id="nome"
                               value="<?= isset($usuario) ? $usuario->getNome() : "" ?>"
                               placeholder="Nome completo">
                    </div>
                    <div class="form-grupo">
                        <label for="email">E-mail <span class="form-obrigatorio">*</span></label>
                        <input type="text" name="email" id="email"
                               value="<?= isset($usuario) ? $usuario->getEmail() : "" ?>"
                               placeholder="email@palmeiras.com">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-grupo">
                        <label for="cpf">CPF <span class="form-obrigatorio">*</span></label>
                        <input type="text" name="cpf" id="cpf"
                               value="<?= isset($usuario) ? $usuario->getCpf() : "" ?>"
                               placeholder="000.000.000-00" maxlength="14">
                    </div>
                    <div class="form-grupo">
                        <label for="data_nascimento">Data de nascimento <span class="form-obrigatorio">*</span></label>
                        <input type="date" name="data_nascimento" id="data_nascimento"
                               value="<?= isset($usuario) ? $usuario->getDataNascimento() : "" ?>">
                    </div>
                </div>
                <?php if (!isset($usuario)): ?>
                <div class="form-grupo">
                    <label for="senha">Senha <span class="form-obrigatorio">*</span></label>
                    <input type="password" name="senha" id="senha" placeholder="Mínimo 6 caracteres">
                </div>
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-grupo">
                        <label for="posicao">Posição</label>
                        <select name="posicao" id="posicao">
                            <option value="">— Selecione —</option>
                            <?php foreach ($posicoes as $pos): ?>
                            <option value="<?= $pos ?>" <?= (isset($usuario) && $usuario->getPosicao() === $pos) ? "selected" : "" ?>>
                                <?= $pos ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label for="cargo">Cargo <span class="form-obrigatorio">*</span></label>
                        <select name="cargo" id="cargo">
                            <option value="membro" <?= (!isset($usuario) || $usuario->getCargo() === "membro") ? "selected" : "" ?>>Membro (Jogador)</option>
                            <option value="admin"  <?= (isset($usuario) && $usuario->getCargo() === "admin")   ? "selected" : "" ?>>Admin (Técnico)</option>
                        </select>
                    </div>
                </div>

                <div class="form-acoes">
                    <button type="submit" class="btn btn-verde"><?= isset($usuario) ? "Confirmar" : "Cadastrar" ?></button>
                    <a href="./?p=usuarios" class="btn btn-cinza">Cancelar</a>
                </div>
            </form>
        </section>
    <?php }
}
