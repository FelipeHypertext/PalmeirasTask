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

// Valida se o ID foi informado
if ($id_tarefa <= 0) {
    header('Location: ../dashboard/index.php');
    exit;
}

// Busca os dados atuais da tarefa
$stmt_tarefa = $conn->prepare("SELECT * FROM tarefas WHERE id = ?");
$stmt_tarefa->bind_param('i', $id_tarefa);
$stmt_tarefa->execute();
$tarefa = $stmt_tarefa->get_result()->fetch_assoc();
$stmt_tarefa->close();

// Tarefa não encontrada
if (!$tarefa) {
    $conn->close();
    header('Location: ../dashboard/index.php');
    exit;
}

// Verifica permissão: somente o criador ou o admin podem editar
if ($cargo !== 'admin' && (int)$tarefa['criado_por'] !== $id_usuario) {
    $conn->close();
    header('Location: ../dashboard/index.php');
    exit;
}

// Busca todos os jogadores (membros) para o <select>
$res_usuarios = $conn->query("SELECT id, nome, posicao FROM usuarios WHERE cargo = 'membro' ORDER BY nome ASC");
$usuarios = [];
while ($linha = $res_usuarios->fetch_assoc()) {
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
        // Registra no histórico apenas os campos que mudaram
        if ($tarefa['titulo'] !== $novo_titulo) {
            logHistory($conn, $id_tarefa, 'titulo', $tarefa['titulo'], $novo_titulo);
        }
        if ($tarefa['description'] !== $nova_descricao) {
            logHistory($conn, $id_tarefa, 'description', $tarefa['description'], $nova_descricao);
        }
        if ($tarefa['prazo'] !== $novo_prazo) {
            logHistory($conn, $id_tarefa, 'prazo', $tarefa['prazo'], $novo_prazo);
        }
        if ((int)$tarefa['designado_para'] !== $novo_responsavel) {
            logHistory($conn, $id_tarefa, 'designado_para', (string)$tarefa['designado_para'], (string)$novo_responsavel);
        }

        // Atualiza a tarefa no banco
        $stmt_upd = $conn->prepare(
            "UPDATE tarefas SET titulo = ?, description = ?, prazo = ?, designado_para = ? WHERE id = ?"
        );
        $stmt_upd->bind_param('sssii', $novo_titulo, $nova_descricao, $novo_prazo, $novo_responsavel, $id_tarefa);

        if ($stmt_upd->execute()) {
            $tarefa['titulo']          = $novo_titulo;
            $tarefa['description']     = $nova_descricao;
            $tarefa['prazo']           = $novo_prazo;
            $tarefa['designado_para']  = $novo_responsavel;

            $sucesso = 'Tarefa atualizada com sucesso!';
            header('Refresh: 1; url=../tasks/view.php?id=' . $id_tarefa);
        } else {
            $erro = 'Erro ao atualizar a tarefa. Tente novamente.';
        }

        $stmt_upd->close();
    }
}

$conn->close();

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

    <form class="task-form" action="edit.php?id=<?= $id_tarefa ?>" method="POST">

        <div class="task-form__grupo">
            <label for="titulo">Título <span class="obrigatorio">*</span></label>
            <input
                type="text"
                id="titulo"
                name="titulo"
                maxlength="200"
                value="<?= htmlspecialchars($_POST['titulo'] ?? $tarefa['titulo']) ?>"
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
                value="<?= htmlspecialchars($_POST['prazo'] ?? $tarefa['prazo']) ?>"
                required
            >
        </div>

        <div class="task-form__grupo">
            <label for="responsavel">Atribuir para <span class="obrigatorio">*</span></label>
            <select id="responsavel" name="responsavel" required>
                <option value="">-- Selecione um jogador --</option>
                <?php
                $responsavel_atual = (int) ($_POST['responsavel'] ?? $tarefa['designado_para']);
                foreach ($usuarios as $u):
                ?>
                    <option
                        value="<?= $u['id'] ?>"
                        <?= ((int)$u['id'] === $responsavel_atual) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($u['nome']) ?>
                        <?= !empty($u['posicao']) ? '(' . htmlspecialchars($u['posicao']) . ')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="task-form__acoes">
            <button type="submit" class="btn btn--verde">Salvar Alterações</button>
            <a href="../tasks/view.php?id=<?= $id_tarefa ?>" class="btn btn--cinza">Cancelar</a>
        </div>

    </form>
</main>

<?php require_once '../includes/footer.php'; ?>