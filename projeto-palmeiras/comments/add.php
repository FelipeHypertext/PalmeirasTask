<?php
session_start();
require_once '../config/db.php';
require_once '../includes/session_check.php';

verificarSessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard/index.php');
    exit;
}

$id_tarefa  = (int) ($_POST['id_tarefa']  ?? 0);
$conteudo   = trim($_POST['conteudo'] ?? '');
$id_usuario = (int) $_SESSION['id_usuario'];

if ($id_tarefa <= 0 || empty($conteudo)) {
    header('Location: ../tasks/view.php?id=' . $id_tarefa . '&erro=comentario_vazio');
    exit;
}

$conn = conectar();

$stmt_check = $conn->prepare("SELECT id FROM tarefas WHERE id = ?");
$stmt_check->bind_param('i', $id_tarefa);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows === 0) {
    $stmt_check->close();
    $conn->close();
    header('Location: ../dashboard/index.php');
    exit;
}
$stmt_check->close();

$stmt = $conn->prepare(
    "INSERT INTO comentarios (id_tarefa, id_usuario, conteudo) VALUES (?, ?, ?)"
);
$stmt->bind_param('iis', $id_tarefa, $id_usuario, $conteudo);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header('Location: ../tasks/view.php?id=' . $id_tarefa . '&sucesso=comentario_adicionado');
    exit;
} else {
    $stmt->close();
    $conn->close();
    header('Location: ../tasks/view.php?id=' . $id_tarefa . '&erro=falha_comentario');
    exit;
}
?>