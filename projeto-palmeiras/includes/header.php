<?php
    $isAdmin = isset($_SESSION['cargo']) && $_SESSION['cargo'] === 'admin';
    $nomeUsuario = htmlspecialchars($_SESSION['nome_usuario'] ?? 'Visitante');
?>

<header>
    <div class="header-inner">
        <div class="header-logo">
            <img src="../assets/img/logo_palmeiras.png" alt="Palmeiras FC" class="logo">
            <h1 class="header-titulo">Gerenciador de Tarefas</h1>
        </div>

        <?php if (isset($_SESSION['id_usuario'])): ?>
        <nav>
            <ul>
                <li><a href="../dashboard/index.php">Dashboard</a></li>
                <li><a href="../tasks/create.php">Nova Tarefa</a></li>
                <?php if ($isAdmin): ?>
                    <li><a href="../admin/users.php">Jogadores</a></li>
                    <li><a href="../auth/register.php">Cadastrar</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="header-usuario">
            <span>Olá, <strong><?= $nomeUsuario ?></strong></span>
            <a href="../auth/logout.php" class="btn-logout">Sair</a>
        </div>
        <?php endif; ?>
    </div>
</header>