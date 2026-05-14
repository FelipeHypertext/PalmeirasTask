<?php
session_start();
require_once '../config/db.php';
require_once '../includes/session_check.php';
verificarAdmin();

$conn = conectar();
$sql = "SELECT u.id, u.nome, u.email, u.posicao, u.cargo, COUNT(t.id) as total_tarefas 
        FROM usuarios u LEFT JOIN tarefas t ON u.id = t.designado_para 
        GROUP BY u.id ORDER BY u.nome ASC";
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Jogadores — Palmeiras FC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<main class="task-container" style="max-width: 1000px;">
    <h2 class="task-titulo">Equipe / Jogadores</h2>
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Posição</th>
                    <th>Cargo</th>
                    <th style="text-align: center;">Tarefas</th>
                    <th style="text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($user['nome']) ?></strong></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['posicao'] ?? 'N/A') ?></td>
                    <td><span class="badge-cargo <?= $user['cargo'] === 'admin' ? 'badge-admin' : 'badge-membro' ?>">
                        <?= ucfirst($user['cargo']) ?></span></td>
                    <td style="text-align: center;"><?= $user['total_tarefas'] ?></td>
                    <td style="text-align: center;">
                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn--cinza">Editar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>