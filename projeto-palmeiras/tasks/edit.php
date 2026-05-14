<?php
session_start();
require_once '../includes/session_check.php';
require_once '../config/db.php';
require_once '../history/log.php';

verificarSessao();

$conn = conectar();

$erro    = '';
$sucesso = '';

$id_tarefa  = (int) ($_GET['id'] ?? 0);
$id_usuario = (int) $_SESSION['id_usuario'];
$cargo      = $_SESSION['cargo'] ?? 'membro';

if ($id_tarefa <= 0) {
    header('Location: ../dashboard/index.php');
    exit;
}

$stmt_tarefa = $conn->prepare("SELECT * FROM tarefas WHERE id = ?");
$stmt_tarefa->bind_param('i', $id_tarefa);
$stmt_tarefa->execute();
$tarefa = $stmt_tarefa->get_result()->fetch_assoc();
$stmt_tarefa->close();

if (!$tarefa) {
    $conn->close();
    header('Location: ../dashboard/index.php');
    exit;
}

if ($cargo !== 'admin' && (int)$tarefa['criado_por'] !== $id_usuario) {
    $conn->close();
    header('Location: ../dashboard/index.php');
    exit;
}

$res_usuarios = $conn->query("SELECT id, nome, posicao FROM usuarios WHERE cargo = 'membro' ORDER BY nome ASC");
$usuarios = [];
while ($linha = $res_usuarios->fetch_assoc()) {
    $usuarios[] = $linha;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_titulo      = trim(htmlspecialchars($_POST['titulo']      ?? ''));
    $nova_descricao   = trim(htmlspecialchars($_POST['descricao']   ?? ''));
    $novo_prazo       = trim(htmlspecialchars($_POST['prazo']       ?? ''));
    $novo_responsavel = (int) ($_POST['responsavel'] ?? 0);

    if (empty($novo_titulo) || empty($novo_prazo) || $novo_responsavel <= 0) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } else {
        if ($tarefa['titulo'] !== $novo_titulo) logHistory($conn, $id_tarefa, 'titulo', $tarefa['titulo'], $novo_titulo);
        if ($tarefa['description'] !== $nova_descricao) logHistory($conn, $id_tarefa, 'description', $tarefa['description'], $nova_descricao);
        if ($tarefa['prazo'] !== $novo_prazo) logHistory($conn, $id_tarefa, 'prazo', $tarefa['prazo'], $novo_prazo);
        if ((int)$tarefa['designado_para'] !== $novo_responsavel) logHistory($conn, $id_tarefa, 'designado_para', (string)$tarefa['designado_para'], (string)$novo_responsavel);

        $stmt_upd = $conn->prepare("UPDATE tarefas SET titulo = ?, description = ?, prazo = ?, designado_para = ? WHERE id = ?");
        $stmt_upd->bind_param('sssii', $novo_titulo, $nova_descricao, $novo_prazo, $novo_responsavel, $id_tarefa);

        if ($stmt_upd->execute()) {
            $sucesso = 'Tarefa atualizada com sucesso!';
            header('Refresh: 1; url=../tasks/view.php?id=' . $id_tarefa);
        } else {
            $erro = 'Erro ao atualizar a tarefa.';
        }
        $stmt_upd->close();
    }
}
$conn->close();
$titulo_pagina = 'Editar Tarefa';
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
    <h2 class="task-titulo">Editar Tarefa</h2>

    <?php if ($erro): ?><p class="task-msg task-msg--erro"><?= $erro ?></p><?php endif; ?>
    <?php if ($sucesso): ?><p class="task-msg task-msg--sucesso"><?= $sucesso ?></p><?php endif; ?>

    <form class="task-form" action="edit.php?id=<?= $id_tarefa ?>" method="POST">
        <div class="task-form__grupo">
            <label for="titulo">Título <span class="obrigatorio">*</span></label>
            <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($tarefa['titulo']) ?>" required>
        </div>
        <div class="task-form__grupo">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" rows="4"><?= htmlspecialchars($tarefa['description']) ?></textarea>
        </div>
        <div class="task-form__grupo">
            <label for="prazo">Prazo <span class="obrigatorio">*</span></label>
            <input type="date" id="prazo" name="prazo" value="<?= $tarefa['prazo'] ?>" required>
        </div>
        <div class="task-form__grupo">
            <label for="responsavel">Atribuir para <span class="obrigatorio">*</span></label>
            <select id="responsavel" name="responsavel" required>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= ((int)$u['id'] === (int)$tarefa['designado_para']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['nome']) ?> (<?= htmlspecialchars($u['posicao']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="task-form__acoes">
            <button type="submit" class="btn btn--verde">Salvar Alterações</button>
            <a href="view.php?id=<?= $id_tarefa ?>" class="btn btn--cinza">Cancelar</a>
        </div>
    </form>
</main>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>