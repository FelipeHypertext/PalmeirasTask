<?php

session_start();
require_once '../config/db.php';
require_once '../includes/session_check.php';

// CHAMA A FUNÇÃO DO SESSION_CHECK E GARANTE QUE APENAS UM USUÁRIO COM CARGO ADMIN POSSA FAZER O CADASTRO
verificarAdmin();

//INICIALIZA AS VARIAVEIS
$erro    = '';
$sucesso = '';

// POST DO FORMULÁRIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome    = validaInput($_POST['nome']    ?? '');
    $email   = validaInput($_POST['email']   ?? '');
    $posicao = validaInput($_POST['posicao'] ?? '');
    $cargo   = validaInput($_POST['cargo']   ?? 'membro');
    $senha   = $_POST['senha']              ?? '';
    $confirma = $_POST['confirma_senha']    ?? '';

    // VALIDAÇÕES
    if (empty($nome) || empty($email) || empty($senha) || empty($confirma)) {
        $erro = 'Preencha todos os campos obrigatórios.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Formato de e-mail inválido.';

    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';

    } elseif ($senha !== $confirma) {
        $erro = 'As senhas não coincidem.';

    } elseif (!in_array($cargo, ['admin', 'membro'])) {
        $erro = 'Cargo inválido.';

    } else {
        $conn = conectar();

        // VERIFICA SE O EMAIL JÁ ESTÁ CADASTRADO
        $stmtCheck = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmtCheck->bind_param('s', $email);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            $erro = 'Este e-mail já está cadastrado.';
        } else {
            // HASH SEGURO DA SENHA
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            // INSERE NOVO USUARIO (? é um placeholder)
            $stmtInsert = $conn->prepare(
                "INSERT INTO usuarios (nome, email, senha, cargo, posicao) VALUES (?, ?, ?, ?, ?)"
            );
            $stmtInsert->bind_param('sssss', $nome, $email, $senhaHash, $cargo, $posicao);

            if ($stmtInsert->execute()) {
                $sucesso = "Jogador \"$nome\" cadastrado com sucesso!";
                $nome = $email = $posicao = $cargo = ''; // LIMPA AS VARIAVEIS PARA PROXIMO CADASTRO
            } else {
                $erro = 'Erro ao cadastrar. Tente novamente.';
            }

            $stmtInsert->close();
        }

        $stmtCheck->close();
        $conn->close();
    }
}

// POSIÇÕES DISPONÍVEIS PARA USAR NO SELECT
$posicoes = [
    'Goleiro', 'Lateral Direito', 'Lateral Esquerdo',
    'Zagueiro', 'Volante', 'Meia', 'Meia-Atacante',
    'Atacante', 'Centroavante', 'Técnico', 'Auxiliar Técnico'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Jogador — Palmeiras FC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<?php require_once '../includes/header.php'; ?>

<main class="auth-main">
    <div class="auth-container auth-container--register">

        <div class="auth-cabecalho">
            <h2>Cadastrar Jogador</h2>
            <p>Apenas o administrador pode registrar novos membros.</p>
        </div>

        <?php if (!empty($sucesso)): ?>
            <div class="auth-sucesso"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div class="auth-erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="auth-form">

            <!-- NOME -->
            <div class="auth-campo">
                <label for="nome">Nome completo <span class="obrigatorio">*</span></label>
                <input
                    type="text"
                    id="nome"
                    name="nome"
                    placeholder="Ex: Rony"
                    value="<?= htmlspecialchars($nome ?? '') ?>"
                    required
                    maxlength="100"
                >
            </div>

            <!-- EMAIL -->
            <div class="auth-campo">
                <label for="email">E-mail <span class="obrigatorio">*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="jogador@palmeiras.com"
                    value="<?= htmlspecialchars($email ?? '') ?>"
                    required
                    maxlength="150"
                >
            </div>

            <!-- POSIÇÃO -->
            <div class="auth-campo">
                <label for="posicao">Posição</label>
                <select id="posicao" name="posicao">
                    <option value="">— Selecione —</option>
                    <?php foreach ($posicoes as $pos): ?>
                        <option value="<?= $pos ?>" <?= (($posicao ?? '') === $pos ? 'selected' : '') ?>>
                            <?= $pos ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- CARGO (ADM OU MEMBRO) -->
            <div class="auth-campo">
                <label for="cargo">Cargo <span class="obrigatorio">*</span></label>
                <select id="cargo" name="cargo" required>
                    <option value="membro" <?= (($cargo ?? 'membro') === 'membro' ? 'selected' : '') ?>>Membro (Jogador)</option>
                    <option value="admin"  <?= (($cargo ?? '') === 'admin'  ? 'selected' : '') ?>>Admin (Técnico)</option>
                </select>
            </div>

            <!-- SENHA -->
            <div class="auth-campo">
                <label for="senha">Senha <span class="obrigatorio">*</span></label>
                <input
                    type="password"
                    id="senha"
                    name="senha"
                    placeholder="Mínimo 6 caracteres"
                    required
                    minlength="6"
                    autocomplete="new-password"
                >
            </div>

            <!-- CONFIRMAÇÃO DE SENHA -->
            <div class="auth-campo">
                <label for="confirma_senha">Confirmar senha <span class="obrigatorio">*</span></label>
                <input
                    type="password"
                    id="confirma_senha"
                    name="confirma_senha"
                    placeholder="Repita a senha"
                    required
                    minlength="6"
                    autocomplete="new-password"
                >
            </div>

            <div class="auth-acoes">
                <button type="submit" class="btn-primario btn-bloco">Cadastrar Jogador</button>
                <a href="../admin/users.php" class="btn-secundario btn-bloco">Ver todos os jogadores</a>
            </div>

        </form>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

</body>
</html>
