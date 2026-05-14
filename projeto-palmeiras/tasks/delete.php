<?php
session_start();
require_once '../includes/session_check.php';
require_once '../config/db.php';

verificarSessao();
if (($_SESSION['cargo'] ?? '') !== 'admin') {
    header('Location: ../dashboard/index.php');
    exit;
}

$conn = conectar();
$id_tarefa = (int) ($_GET['id'] ?? $_POST['id_tarefa'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt_del = $conn->prepare("DELETE FROM tarefas WHERE id = ?");
    $stmt_del->bind_param('i', $id_tarefa);
    $stmt_del->execute();
    $stmt_del->close();
    $conn->close();
    header('Location: ../dashboard/index.php');
    exit;
}

$stmt = $conn->prepare("SELECT titulo FROM tarefas WHERE id = ?");
$stmt->bind_param('i', $id_tarefa);
$stmt->execute();
$tarefa = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

$titulo_pagina = 'Excluir Tarefa';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $titulo_pagina ?> — Palmeiras FC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/task.css">
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<main class="task-container">
    <h2 class="task-titulo">Confirmar Exclusão</h2>
    <div class="task-confirm" style="text-align: center; padding: 40px; background: #fff; border-radius: 8px; box-shadow: var(--sombra-leve);">
        <p>Tem certeza que deseja excluir a tarefa: <strong>"<?= htmlspecialchars($tarefa['titulo'] ?? '') ?>"</strong>?</p>
        <p style="color: var(--vermelho); margin: 15px 0;">Esta ação não pode ser desfeita.</p>
        <form action="delete.php" method="POST" style="display: flex; justify-content: center; gap: 15px;">
            <input type="hidden" name="id_tarefa" value="<?= $id_tarefa ?>">
            <button type="submit" class="btn btn--vermelho">Sim, excluir</button>
            <a href="view.php?id=<?= $id_tarefa ?>" class="btn btn--cinza">Cancelar</a>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>