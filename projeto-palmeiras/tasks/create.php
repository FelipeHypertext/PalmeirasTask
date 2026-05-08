<?php 
session_start();
require_once '../includes/session_check.php';
require_once '../config/db.php';
require_once '../history/log.php';
$erro = '';
$sucesso = '';

//Busca todos jogadores p/ o select de atribuir
$query_usuarios = "SELECT id, name, position FROM users WHERE role = 'member' ORDER BY name ASC";
$resultado_usuarios = mysqli_query($conn, $query_usuarios);
$usuarios = [];
while ($linha = mysqli_fetch_assoc($resultado_usuarios)){
    $usuarios[] = $linha;
}
//Processamento do formulario via POST
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $titulo      = trim(htmlspecialchars($_POST['titulo']     ??  ''));
    $descricao   = trim(htmlspecialchars($_POST['descricao']  ??  ''));
    $prazo       = trim(htmlspecialchars($_POST['prazo']      ??  ''));
    $responsavel = (int) ($_POST['responsavel'] ?? 0);
    $criado_por  = (int) $_SESSION['user_id'];

    //Validação básica
    if(empty($titulo)) {
        $erro = 'O título da tarefa é obrigatório.';
    } elseif (empty($prazo)) {
        $erro = 'O prazo é obrigatório.';
    } elseif ($responsavel <= 0) {
        $erro = 'Selecione um responsável para a tarefa.';
    } else {
        //Insere a tarefa no banco
        $sql = "INSERT INTO tasks (title, description, status, deadline, created_by, assigned_to) VALUES (?, ?, 'pendente', ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssii', $titulo, $descricao, $prazo, $criado_por, $responsavel);

        if(mysqli_stmt_execute($stmt)){
            $novo_id = mysqli_insert_id($conn);

            //Registra no historico
            logHistory($conn, $novo_id, 'criacao', '', "Tarefa '{$titulo}' criada com status 'pendente'");

            $sucesso = 'Tarefa criada com sucesso!';

            //Redireciona para o dashboard após 1s via meta refresh
            header('Refresh: 1; url=../dashboard/index.php');
        } else {
            $erro = 'Erro ao criar a tarefa. Tente novamente.';
        }

        mysqli_stmt_close($stmt);
    }
}
$titulo_pagina = 'Criar Tarefa';
require_once '../includes/header.php';
?>
<main class="task-container">
    <h2 class="task-titulo">Nova Tarefa</h2>

    <?php if (!empty($erro)): ?>
        <p class="task-msg task-msg--erro"><?= $erro ?></p>
    <?php endif; ?>

    <?php if (!empty($sucesso)): ?>
        <p class="task-msg task-msg--sucesso"><?= $sucesso ?></p>
    <?php endif; ?>

    <form class="task-form" action="create.php" method="post">

        <div class="task-form__grupo">
            <label for="titulo">Título <span class="obrigatorio">*</span></label>
            <input
                type="text"
                id="titulo"
                name="titulo"
                maxlength="200"
                placeholder="Ex: Treino tático – Sexta-feira"
                value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>"
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
            ><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
        </div>

        <div class="task-form__grupo">
            <label for="prazo">Prazo <span class="obrigatorio">*</span></label>
            <input
                type="date"
                id="prazo"
                name="prazo"
                value="<?= htmlspecialchars($_POST['prazo'] ?? '') ?>"
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
                        <?= htmlspecialchars($u['name']) ?>
                        <?= !empty($u['position']) ? '(' . htmlspecialchars($u['position']) . ')' : '' ?>
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
