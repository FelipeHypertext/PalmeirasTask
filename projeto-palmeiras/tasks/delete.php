<?php

session_start();
require_once '../includes/session_check.php';
require_once '../config/db.php';

// Somente admin pode excluir tarefas
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../dashboard/index.php');
    exit;
}

// --- Tela de confirmação (GET) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $task_id = (int) ($_GET['id'] ?? 0);

    if ($task_id <= 0) {
        header('Location: ../dashboard/index.php');
        exit;
    }

    $sql_get = "SELECT id, title FROM tasks WHERE id = ?";
    $stmt_get = mysqli_prepare($conn, $sql_get);
    mysqli_stmt_bind_param($stmt_get, 'i', $task_id);
    mysqli_stmt_execute($stmt_get);
    $res_get = mysqli_stmt_get_result($stmt_get);
    $tarefa  = mysqli_fetch_assoc($res_get);
    mysqli_stmt_close($stmt_get);

    if (!$tarefa) {
        header('Location: ../dashboard/index.php');
        exit;
    }

    $titulo_pagina = 'Excluir Tarefa';
    require_once '../includes/header.php';
    ?>
    <main class="task-container">
        <h2 class="task-titulo">Confirmar Exclusão</h2>

        <div class="task-confirm">
            <p>Tem certeza que deseja excluir a tarefa:</p>
            <p class="task-confirm__nome">"<?= htmlspecialchars($tarefa['title']) ?>"</p>
            <p class="task-confirm__aviso">Esta ação não pode ser desfeita.</p>

            <form action="delete.php" method="post" class="task-confirm__form">
                <input type="hidden" name="task_id" value="<?= $tarefa['id'] ?>">
                <button type="submit" class="btn btn--vermelho">Sim, excluir</button>
                <a href="../tasks/view.php?id=<?= $tarefa['id'] ?>" class="btn btn--cinza">Cancelar</a>
            </form>
        </div>
    </main>
    <?php
    require_once '../includes/footer.php';
    exit;

// --- Execução da exclusão (POST) ---
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $task_id = (int) ($_POST['task_id'] ?? 0);

    if ($task_id <= 0) {
        header('Location: ../dashboard/index.php');
        exit;
    }

    // Verifica se a tarefa existe antes de excluir
    $sql_check  = "SELECT id FROM tasks WHERE id = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, 'i', $task_id);
    mysqli_stmt_execute($stmt_check);
    $res_check = mysqli_stmt_get_result($stmt_check);
    $existe    = mysqli_fetch_assoc($res_check);
    mysqli_stmt_close($stmt_check);

    if (!$existe) {
        header('Location: ../dashboard/index.php');
        exit;
    }

    // O ON DELETE CASCADE no banco remove automaticamente
    // os comentários e o histórico vinculados a esta tarefa.
    $sql_del  = "DELETE FROM tasks WHERE id = ?";
    $stmt_del = mysqli_prepare($conn, $sql_del);
    mysqli_stmt_bind_param($stmt_del, 'i', $task_id);

    if (mysqli_stmt_execute($stmt_del)) {
        mysqli_stmt_close($stmt_del);
        header('Location: ../dashboard/index.php');
        exit;
    } else {
        mysqli_stmt_close($stmt_del);

        $titulo_pagina = 'Erro ao Excluir';
        require_once '../includes/header.php';
        ?>
        <main class="task-container">
            <h2 class="task-titulo">Erro ao excluir</h2>
            <p class="task-msg task-msg--erro">Não foi possível excluir a tarefa. Tente novamente.</p>
            <a href="../dashboard/index.php" class="btn btn--cinza">Voltar ao Dashboard</a>
        </main>
        <?php
        require_once '../includes/footer.php';
        exit;
    }
}