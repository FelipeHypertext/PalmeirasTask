<?php
session_start();
require_once '../config/db.php';
require_once '../includes/session_check.php';

verificarAdmin();

$conn = conectar();
$erro = '';
$sucesso = '';

$id_usuario = (int) ($_GET['id'] ?? 0);

if ($id_usuario <= 0) {
    header('Location: users.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome    = validaInput($_POST['nome'] ?? '');
    $email   = validaInput($_POST['email'] ?? '');
    $posicao = validaInput($_POST['posicao'] ?? '');
    $cargo   = validaInput($_POST['cargo'] ?? 'membro');

    if (empty($nome) || empty($email)) {
        $erro = 'Nome e e-mail são obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } else {
        // Verifica se o email já existe para OUTRO usuário
        $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt_check->bind_param('si', $email, $id_usuario);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $erro = 'Este e-mail já está sendo usado por outro jogador.';
        } else {
            $stmt_upd = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, posicao = ?, cargo = ? WHERE id = ?");
            $stmt_upd->bind_param('ssssi', $nome, $email, $posicao, $cargo, $id_usuario);
            
            if ($stmt_upd->execute()) {
                $sucesso = 'Dados atualizados com sucesso!';
            } else {
                $erro = 'Erro ao atualizar dados.';
            }
            $stmt_upd->close();
        }
        $stmt_check->close();
    }
}

$stmt = $conn->prepare("SELECT nome, email, posicao, cargo FROM usuarios WHERE id = ?");
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header('Location: users.php');
    exit;
}

$usuario_atual = $resultado->fetch_assoc();
$stmt->close();
$conn->close();

$posicoes = [
    'Goleiro', 'Lateral Direito', 'Lateral Esquerdo',
    'Zagueiro', 'Volante', 'Meia', 'Meia-Atacante',
    'Atacante', 'Centroavante', 'Técnico', 'Auxiliar Técnico'
];

$titulo_pagina = 'Editar Jogador';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?> — Palmeiras FC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/task.css">
</head>
<body>

<?php require_once '../includes/header.php'; ?>

<main class="task-container">
    <h2 class="task-titulo">Editar Jogador</h2>

    <?php if (!empty($sucesso)): ?>
        <p class="task-msg task-msg--sucesso"><?= htmlspecialchars($sucesso) ?></p>
    <?php endif; ?>

    <?php if (!empty($erro)): ?>
        <p class="task-msg task-msg--erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form class="task-form" action="edit_user.php?id=<?= $id_usuario ?>" method="POST">

        <div class="task-form__grupo">
            <label for="nome">Nome completo <span class="obrigatorio">*</span></label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? $usuario_atual['nome']) ?>" required>
        </div>

        <div class="task-form__grupo">
            <label for="email">E-mail <span class="obrigatorio">*</span></label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $usuario_atual['email']) ?>" required>
        </div>

        <div class="task-form__grupo">
            <label for="posicao">Posição</label>
            <select id="posicao" name="posicao">
                <option value="">— Selecione —</option>
                <?php foreach ($posicoes as $pos): ?>
                    <option value="<?= $pos ?>" <?= (($_POST['posicao'] ?? $usuario_atual['posicao']) === $pos) ? 'selected' : '' ?>>
                        <?= $pos ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="task-form__grupo">
            <label for="cargo">Cargo <span class="obrigatorio">*</span></label>
            <select id="cargo" name="cargo" required>
                <?php $cargo_selecionado = $_POST['cargo'] ?? $usuario_atual['cargo']; ?>
                <option value="membro" <?= ($cargo_selecionado === 'membro') ? 'selected' : '' ?>>Membro (Jogador)</option>
                <option value="admin" <?= ($cargo_selecionado === 'admin') ? 'selected' : '' ?>>Admin (Técnico)</option>
            </select>
        </div>

        <div class="task-form__acoes">
            <button type="submit" class="btn btn--verde">Salvar Alterações</button>
            <a href="users.php" class="btn btn--cinza">Voltar</a>
        </div>

    </form>
</main>

<?php require_once '../includes/footer.php'; ?>

</body>
</html>