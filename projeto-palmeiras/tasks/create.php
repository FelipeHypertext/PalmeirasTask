<?php
session_start();
require_once '../includes/session_check.php';
require_once '../config/db.php';
require_once '../history/log.php';

verificarSessao();

$conn = conectar();

$erro = '';
$sucesso = '';

$query_usuarios = "SELECT id, nome, posicao FROM usuarios WHERE cargo = 'membro' ORDER BY nome ASC";
$resultado_usuarios = $conn->query($query_usuarios);
$usuarios = [];
while ($linha = $resultado_usuarios->fetch_assoc()) {
    $usuarios[] = $linha;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo      = trim(htmlspecialchars($_POST['titulo']    ?? ''));
    $descricao   = trim(htmlspecialchars($_POST['descricao'] ?? ''));
    $prazo       = trim(htmlspecialchars($_POST['prazo']     ?? ''));
    $responsavel = (int) ($_POST['responsavel'] ?? 0);
    $criado_por  = (int) $_SESSION['id_usuario'];

    if (empty($titulo)) {
        $erro = 'O título da tarefa é obrigatório.';
    } elseif (empty($prazo)) {
        $erro = 'O prazo é obrigatório.';
    } elseif ($responsavel <= 0) {
        $erro = 'Selecione um responsável para a tarefa.';
    } else {
        $sql  = "INSERT INTO tarefas (titulo, description, status, prazo, criado_por, designado_para) VALUES (?, ?, 'pendente', ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssii', $titulo, $descricao, $prazo, $criado_por, $responsavel);

        if ($stmt->execute()) {
            $novo_id = $conn->insert_id;

            logHistory($conn, $novo_id, 'criacao', '', "Tarefa '{$titulo}' criada com status 'pendente'");

            $sucesso = 'Tarefa criada com sucesso!';
            header('Refresh: 1; url=../dashboard/index.php');
            exit;
        } else {
            $erro = 'Erro ao criar a tarefa. Tente novamente.';
        }

        $stmt->close();
    }
}

$conn->close();

$titulo_pagina = 'Criar Tarefa';
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
    <h2 class="task-titulo">Nova Tarefa</h2>

    <?php if (!empty($erro)): ?>
        <p class="task-msg task-msg--erro"><?= $erro ?></p>
    <?php endif; ?>

    <?php if (!empty($sucesso)): ?>
        <p class="task-msg task-msg--sucesso"><?= $sucesso ?></p>
    <?php endif; ?>

    <form class="task-form" action="create.php" method="POST">

        <div class="task-form__grupo">
            <label for="titulo">Título <span class="obrigatorio">*</span></label>
            <input
                type="text"
                id="titulo"
                name="titulo"
                maxlength="200"
                placeholder="Ex: Treino tático – Sexta-feira"
                value="<?= $_POST['titulo'] ?? '' ?>"
                required
            >
        </div>

        <div class="task-form__grupo">
            <label for="descricao">Descrição</label>
            <textarea
                id="descricao"
                name="descricao"
                rows="4"
                placeholder="Detalhes da tarefa (opcional)"
            ><?= $_POST['descricao'] ?? '' ?></textarea>
        </div>

        <div class="task-form__grupo">
            <label for="prazo">Prazo <span class="obrigatorio">*</span></label>
            <input
                type="date"
                id="prazo"
                name="prazo"
                value="<?= $_POST['prazo'] ?? '' ?>"
                required
            >
        </div>

        <div class="task-form__grupo">
            <label for="responsavel">Atribuir para <span class="obrigatorio">*</span></label>
            <select id="responsavel" name="responsavel" required>
                <option value="">-- Selecione um jogador --</option>
                <?php foreach ($usuarios as $u): ?>
                    <option
                        value="<?= $u['id'] ?>"
                        <?= (isset($_POST['responsavel']) && (int)$_POST['responsavel'] === (int)$u['id']) ? 'selected' : '' ?>
                    >
                        <?= $u['nome'] ?>
                        <?= !empty($u['posicao']) ? '(' . $u['posicao'] . ')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="task-form__acoes">
            <button type="submit" class="btn btn--verde">Criar Tarefa</button>
            <a href="../dashboard/index.php" class="btn btn--cinza">Cancelar</a>
        </div>

    </form>
</main>

<?php require_once '../includes/footer.php'; ?>

</body>
</html>