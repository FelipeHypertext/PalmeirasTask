<?php
session_start();
require_once '../config/db.php';
require_once '../includes/session_check.php';
require_once '../history/log.php';

verificarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id_tarefa'] ?? 0);
    $novo_status = $_POST['status'] ?? '';

    if ($id > 0 && in_array($novo_status, ['pendente', 'em_andamento', 'concluida'])) {
        $conn = conectar();

        $stmt = $conn->prepare("SELECT status FROM tarefas WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $status_antigo = $res['status'] ?? '';
        $stmt->close();

        $stmt_upd = $conn->prepare("UPDATE tarefas SET status = ? WHERE id = ?");
        $stmt_upd->bind_param('si', $novo_status, $id);
        
        if ($stmt_upd->execute()) {
            if ($status_antigo && $status_antigo !== $novo_status) {
                logHistory($conn, $id, 'status', $status_antigo, $novo_status);
            }
            echo "OK";
        } else {
            echo "Erro do MySQL: " . $conn->error;
        }
        $stmt_upd->close();
        $conn->close();
    } else {
        echo "Dados inválidos.";
    }
} else {
    echo "Metodo incorreto.";
}
?>