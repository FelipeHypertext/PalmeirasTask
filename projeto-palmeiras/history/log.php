<?php
/**
 * 
 *
 * @param mysqli $conn           Conexão com o banco de dados
 * @param int    $id_tarefa      ID da tarefa alterada
 * @param string $campo          Campo que foi alterado (ex: 'status', 'titulo')
 * @param string $valor_antigo   Valor antes da alteração
 * @param string $valor_novo     Valor depois da alteração
 */
function logHistory(mysqli $conn, int $id_tarefa, string $campo, string $valor_antigo, string $valor_novo): void {

    $atualizado_por = (int) ($_SESSION['id_usuario'] ?? 0);

    if ($atualizado_por <= 0) {
        return;
    }

    $stmt = $conn->prepare(
        "INSERT INTO historico_tarefas (id_tarefa, atualizado_por, campo_atualizado, valor_antigo, valor_novo)
         VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->bind_param('iisss', $id_tarefa, $atualizado_por, $campo, $valor_antigo, $valor_novo);
    $stmt->execute();
    $stmt->close();
}
?>