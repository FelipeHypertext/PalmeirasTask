<?php
session_start();
require_once '../includes/session_check.php';
require_once '../config/db.php';

verificarSessao();

// Somente admin pode excluir tarefas
if (($_SESSION['cargo'] ?? '') !== 'admin') {
    header('Location: ../dashboard/index.php');
    exit;
}

$conn = conectar();

// ── Tela de confirmação (GET) ──
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $id_tarefa = (int) ($_GET['id'] ?? 0);

    if ($id_tarefa <= 0) {
        $conn->close();
        header('Location: ../dashboard/index.php');
        exit;
    }

    $stmt = $conn->prepare("SELECT id, titulo FROM tarefas WHERE id = ?");
    $stmt->bind_param('i', $id_tarefa);
    $stmt->execute();
    $tarefa = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

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
            <p class="task-confirm__nome">"<?= htmlspecialchars($tarefa['titulo']) ?>"</p>
            <p class="task-confirm__aviso">Esta ação não pode ser desfeita.</p>

            <form action="delete.php" method="POST" class="task-confirm__form">
                <input type="hidden" name="id_tarefa" value="<?= $tarefa['id'] ?>">
                <button type="submit" class="btn btn--vermelho">Sim, excluir</button>
                <a href="../tasks/view.php?id=<?= $tarefa['id'] ?>" class="btn btn--cinza">Cancelar</a>
            </form>
        </div>
    </main>
    <?php
    require_once '../includes/footer.php';
    exit;

// ── Execução da exclusão (POST) ──
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_tarefa = (int) ($_POST['id_tarefa'] ?? 0);

    if ($id_tarefa <= 0) {
        $conn->close();
        header('Location: ../dashboard/index.php');
        exit;
    }

    // Verifica se a tarefa existe
    $stmt_check = $conn->prepare("SELECT id FROM tarefas WHERE id = ?");
    $stmt_check->bind_param('i', $id_tarefa);
    $stmt_check->execute();
    $stmt_check->store_result();
    $existe = $stmt_check->num_rows > 0;
    $stmt_check->close();

    if (!$existe) {
        $conn->close();
        header('Location: ../dashboard/index.php');
        exit;
    }

    // ON DELETE CASCADE no banco remove automaticamente comentários e histórico
    $stmt_del = $conn->prepare("DELETE FROM tarefas WHERE id = ?");
    $stmt_del->bind_param('i', $id_tarefa);

    if ($stmt_del->execute()) {
        $stmt_del->close();
        $conn->close();
        header('Location: ../dashboard/index.php');
        exit;
    } else {
        $stmt_del->close();
        $conn->close();

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
?>