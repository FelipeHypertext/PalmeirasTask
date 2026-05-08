<?php

session_start();
require_once '../includes/session_check.php';
require_once '../config/db.php';
require_once '../history/log.php';

$erro    = '';
$sucesso = '';

// Recebe o ID da tarefa via GET
$task_id    = (int) ($_GET['id'] ?? 0);
$usuario_id = (int) $_SESSION['user_id'];
$role       = $_SESSION['role'] ?? 'member';

// Valida se o ID foi informado
if ($task_id <= 0) {
    header('Location: ../dashboard/index.php');
    exit;
}

// Busca os dados atuais da tarefa
$sql_task = "SELECT * FROM tasks WHERE id = ?";
$stmt_task = mysqli_prepare($conn, $sql_task);
mysqli_stmt_bind_param($stmt_task, 'i', $task_id);
mysqli_stmt_execute($stmt_task);
$resultado = mysqli_stmt_get_result($stmt_task);
$tarefa    = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt_task);

// Tarefa não encontrada
if (!$tarefa) {
    header('Location: ../dashboard/index.php');
    exit;
}

// Verifica permissão: somente o criador ou o admin podem editar
if ($role !== 'admin' && (int)$tarefa['created_by'] !== $usuario_id) {
    header('Location: ../dashboard/index.php');
    exit;
}

// Busca todos os jogadores (members) para o <select>
$q_usuarios = "SELECT id, name, position FROM users WHERE role = 'member' ORDER BY name ASC";
$res_usuarios = mysqli_query($conn, $q_usuarios);
$usuarios = [];
while ($linha = mysqli_fetch_assoc($res_usuarios)) {
    $usuarios[] = $linha;
}

// Processamento do formulário via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $novo_titulo      = trim(htmlspecialchars($_POST['titulo']      ?? ''));
    $nova_descricao   = trim(htmlspecialchars($_POST['descricao']   ?? ''));
    $novo_prazo       = trim(htmlspecialchars($_POST['prazo']       ?? ''));
    $novo_responsavel = (int) ($_POST['responsavel'] ?? 0);

    // Validação básica
    if (empty($novo_titulo)) {
        $erro = 'O título da tarefa é obrigatório.';
    } elseif (empty($novo_prazo)) {
        $erro = 'O prazo é obrigatório.';
    } elseif ($novo_responsavel <= 0) {
        $erro = 'Selecione um responsável para a tarefa.';
    } else {
        // Registra alterações no histórico apenas dos campos que mudaram
        if ($tarefa['title'] !== $novo_titulo) {
            logHistory($conn, $task_id, 'title', $tarefa['title'], $novo_titulo);
        }
        if ($tarefa['description'] !== $nova_descricao) {
            logHistory($conn, $task_id, 'description', $tarefa['description'], $nova_descricao);
        }
        if ($tarefa['deadline'] !== $novo_prazo) {
            logHistory($conn, $task_id, 'deadline', $tarefa['deadline'], $novo_prazo);
        }
        if ((int)$tarefa['assigned_to'] !== $novo_responsavel) {
            logHistory($conn, $task_id, 'assigned_to', (string)$tarefa['assigned_to'], (string)$novo_responsavel);
        }

        // Atualiza a tarefa no banco
        $sql_upd = "UPDATE tasks
                    SET title = ?, description = ?, deadline = ?, assigned_to = ?
                    WHERE id = ?";
        $stmt_upd = mysqli_prepare($conn, $sql_upd);
        mysqli_stmt_bind_param($stmt_upd, 'sssii', $novo_titulo, $nova_descricao, $novo_prazo, $novo_responsavel, $task_id);

        if (mysqli_stmt_execute($stmt_upd)) {
            // Atualiza os dados locais para refletir no formulário
            $tarefa['title']       = $novo_titulo;
            $tarefa['description'] = $nova_descricao;
            $tarefa['deadline']    = $novo_prazo;
            $tarefa['assigned_to'] = $novo_responsavel;

            $sucesso = 'Tarefa atualizada com sucesso!';
            header('Refresh: 1; url=../tasks/view.php?id=' . $task_id);
        } else {
            $erro = 'Erro ao atualizar a tarefa. Tente novamente.';
        }

        mysqli_stmt_close($stmt_upd);
    }
}

$titulo_pagina = 'Editar Tarefa';
require_once '../includes/header.php';
?>

<main class="task-container">
    <h2 class="task-titulo">Editar Tarefa</h2>

    <?php if (!empty($erro)): ?>
        <p class="task-msg task-msg--erro"><?= $erro ?></p>
    <?php endif; ?>

    <?php if (!empty($sucesso)): ?>
        <p class="task-msg task-msg--sucesso"><?= $sucesso ?></p>
    <?php endif; ?>

    <form class="task-form" action="edit.php?id=<?= $task_id ?>" method="post">

        <div class="task-form__grupo">
            <label for="titulo">Título <span class="obrigatorio">*</span></label>
            <input
                type="text"
                id="titulo"
                name="titulo"
                maxlength="200"
                value="<?= htmlspecialchars($_POST['titulo'] ?? $tarefa['title']) ?>"
                required
            >
        </div>

        <div class="task-form__grupo">
            <label for="descricao">Descrição</label>
            <textarea
                id="descricao"
                name="descricao"
                rows="4"
            ><?= htmlspecialchars($_POST['descricao'] ?? $tarefa['description']) ?></textarea>
        </div>

        <div class="task-form__grupo">
            <label for="prazo">Prazo <span class="obrigatorio">*</span></label>
            <input
                type="date"
                id="prazo"
                name="prazo"
                value="<?= htmlspecialchars($_POST['prazo'] ?? $tarefa['deadline']) ?>"
                required
            >
        </div>

        <div class="task-form__grupo">
            <label for="responsavel">Atribuir para <span class="obrigatorio">*</span></label>
            <select id="responsavel" name="responsavel" required>
                <option value="">-- Selecione um jogador --</option>
                <?php
                $responsavel_atual = (int) ($_POST['responsavel'] ?? $tarefa['assigned_to']);
                foreach ($usuarios as $u):
                ?>
                    <option
                        value="<?= $u['id'] ?>"
                        <?= ((int)$u['id'] === $responsavel_atual) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($u['name']) ?>
                        <?= !empty($u['position']) ? '(' . htmlspecialchars($u['position']) . ')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="task-form__acoes">
            <button type="submit" class="btn btn--verde">Salvar Alterações</button>
            <a href="../tasks/view.php?id=<?= $task_id ?>" class="btn btn--cinza">Cancelar</a>
        </div>

    </form>
</main>

<?php require_once '../includes/footer.php'; ?>
