<?php
namespace App\View;

class AuthView {

    public static function formularioLogin(?string $msg): void { ?>
        <div class="auth-container">
            <header class="auth-header">
                <h2>Palmeiras FC</h2>
                <p>Gerenciador de Tarefas</p>
            </header>
            <div class="auth-body">
                <?php if (isset($_GET["cadastro"])): ?>
                <div class="alert-box alert-sucesso">
                    Cadastro realizado! Faça seu login.
                    <button class="alert-fechar" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
                <?php endif; ?>
                <?php if (isset($_GET["recuperou"])): ?>
                <div class="alert-box alert-sucesso">
                    Senha atualizada com sucesso!
                    <button class="alert-fechar" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
                <?php endif; ?>
                <?php if ($msg !== null): ?>
                <div class="alert-box alert-erro">
                    <?= $msg ?>
                    <button class="alert-fechar" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
                <?php endif; ?>

                <form action="./?p=login" method="post">
                    <input type="hidden" name="csrf" value="<?= $_SESSION["csrf_token"] ?? "" ?>">

                    <div class="form-grupo">
                        <label for="email">E-mail</label>
                        <input type="text" name="email" id="email"
                               value="<?= htmlentities($_POST["email"] ?? "") ?>"
                               placeholder="seu@email.com">
                    </div>
                    <div class="form-grupo">
                        <label for="senha">Senha</label>
                        <input type="password" name="senha" id="senha" placeholder="••••••••">
                    </div>

                    <label class="lembrar-grupo">
                        <input type="checkbox" name="lembrar" value="1">
                        Lembrar-me por 30 dias
                    </label>

                    <button type="submit" class="btn btn-verde" style="width:100%">Entrar</button>
                </form>

                <nav class="auth-links">
                    <a href="./?p=registro">Criar conta</a>
                    &nbsp;·&nbsp;
                    <a href="./?p=recuperar">Esqueci minha senha</a>
                </nav>
            </div>
        </div>
    <?php }

    public static function formularioRegistro(?string $msg): void { ?>
        <div class="auth-container" style="max-width:520px">
            <header class="auth-header">
                <h2>Criar Conta</h2>
                <p>Palmeiras FC — Gerenciador de Tarefas</p>
            </header>
            <div class="auth-body">
                <?php if ($msg !== null): ?>
                <div class="alert-box alert-erro">
                    <?= $msg ?>
                    <button class="alert-fechar" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
                <?php endif; ?>

                <form action="./?p=registro" method="post">
                    <input type="hidden" name="csrf" value="<?= $_SESSION["csrf_token"] ?? "" ?>">

                    <div class="form-grupo">
                        <label for="nome">Nome completo <span class="form-obrigatorio">*</span></label>
                        <input type="text" name="nome" id="nome"
                               value="<?= htmlentities($_POST["nome"] ?? "") ?>"
                               placeholder="Ex: Rony Lopes">
                    </div>
                    <div class="form-grupo">
                        <label for="email">E-mail <span class="form-obrigatorio">*</span></label>
                        <input type="text" name="email" id="email"
                               value="<?= htmlentities($_POST["email"] ?? "") ?>"
                               placeholder="seu@email.com">
                    </div>
                    <div class="form-row">
                        <div class="form-grupo">
                            <label for="cpf">CPF <span class="form-obrigatorio">*</span></label>
                            <input type="text" name="cpf" id="cpf"
                                   value="<?= htmlentities($_POST["cpf"] ?? "") ?>"
                                   placeholder="000.000.000-00" maxlength="14">
                        </div>
                        <div class="form-grupo">
                            <label for="data_nascimento">Data de nascimento <span class="form-obrigatorio">*</span></label>
                            <input type="date" name="data_nascimento" id="data_nascimento"
                                   value="<?= htmlentities($_POST["data_nascimento"] ?? "") ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-grupo">
                            <label for="senha">Senha <span class="form-obrigatorio">*</span></label>
                            <input type="password" name="senha" id="senha" placeholder="Mínimo 6 caracteres">
                        </div>
                        <div class="form-grupo">
                            <label for="confirma">Confirmar senha <span class="form-obrigatorio">*</span></label>
                            <input type="password" name="confirma" id="confirma" placeholder="Repita a senha">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-verde" style="width:100%">Cadastrar</button>
                </form>

                <nav class="auth-links">
                    Já tem conta? <a href="./?p=login">Entrar</a>
                </nav>
            </div>
        </div>
    <?php }

    public static function formularioRecuperar(?string $msg, bool $etapa2 = false): void { ?>
        <div class="auth-container">
            <header class="auth-header">
                <h2>Recuperar Senha</h2>
                <p>Palmeiras FC — Gerenciador de Tarefas</p>
            </header>
            <div class="auth-body">
                <?php if ($msg !== null): ?>
                <div class="alert-box alert-erro">
                    <?= $msg ?>
                    <button class="alert-fechar" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
                <?php endif; ?>

                <?php if ($etapa2): ?>
                <p style="margin-bottom:16px;color:var(--cinza-texto);font-size:.9rem">
                    Identidade confirmada. Defina a nova senha:
                </p>
                <form action="./?p=recuperar" method="post">
                    <input type="hidden" name="csrf" value="<?= $_SESSION["csrf_token"] ?? "" ?>">
                    <div class="form-grupo">
                        <label for="nova_senha">Nova senha <span class="form-obrigatorio">*</span></label>
                        <input type="password" name="nova_senha" id="nova_senha" placeholder="Mínimo 6 caracteres">
                    </div>
                    <div class="form-grupo">
                        <label for="confirma_senha">Confirmar nova senha <span class="form-obrigatorio">*</span></label>
                        <input type="password" name="confirma_senha" id="confirma_senha" placeholder="Repita a senha">
                    </div>
                    <button type="submit" class="btn btn-verde" style="width:100%">Atualizar senha</button>
                </form>
                <?php else: ?>
                <p style="margin-bottom:16px;color:var(--cinza-texto);font-size:.9rem">
                    Informe seu e-mail, CPF e data de nascimento cadastrados:
                </p>
                <form action="./?p=recuperar" method="post">
                    <input type="hidden" name="csrf" value="<?= $_SESSION["csrf_token"] ?? "" ?>">
                    <div class="form-grupo">
                        <label for="email">E-mail <span class="form-obrigatorio">*</span></label>
                        <input type="text" name="email" id="email" placeholder="seu@email.com">
                    </div>
                    <div class="form-grupo">
                        <label for="cpf">CPF <span class="form-obrigatorio">*</span></label>
                        <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" maxlength="14">
                    </div>
                    <div class="form-grupo">
                        <label for="data_nascimento">Data de nascimento <span class="form-obrigatorio">*</span></label>
                        <input type="date" name="data_nascimento" id="data_nascimento">
                    </div>
                    <button type="submit" class="btn btn-verde" style="width:100%">Verificar identidade</button>
                </form>
                <?php endif; ?>

                <nav class="auth-links">
                    <a href="./?p=login">← Voltar ao login</a>
                </nav>
            </div>
        </div>
    <?php }
}
