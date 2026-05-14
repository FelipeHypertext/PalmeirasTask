<?php

session_start();
require_once '../config/db.php';
require_once '../includes/session_check.php';

if (isset($_SESSION['id_usuario'])) {
    header('Location: ../dashboard/index.php');
    exit;
}


$erro = '';

if (isset($_COOKIE['lembrar_cookie']) && isset($_COOKIE['lembrar_usuario'])) {
    $conn      = conectar(); 
    $id_usuario    = (int) $_COOKIE['lembrar_usuario'];
    $hashCookie = hash('sha256', $_COOKIE['lembrar_cookie']); 
    $stmt = $conn->prepare("SELECT id, nome, cargo FROM usuarios WHERE id = ? AND lembrar_cookie = ?"); 
    $stmt->bind_param('is', $id_usuario, $hashCookie);
    $stmt->execute();
    $res = $stmt->get_result(); 

    if ($res->num_rows === 1) {
        $usuario = $res->fetch_assoc(); 
        session_regenerate_id(true); 

        $_SESSION['id_usuario']   = $usuario['id'];
        $_SESSION['nome_usuario'] = $usuario['nome'];
        $_SESSION['cargo']        = $usuario['cargo'];

        $stmt->close();
        $conn->close();

        header('Location: ../dashboard/index.php');
        exit;
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = validaInput($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    if (empty($email) || empty($senha)) {
        $erro = 'Preencha o e-mail e a senha.';
    } else {
        $conn = conectar();

        $stmt = $conn->prepare("SELECT id, nome, senha, cargo FROM usuarios WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            if (password_verify($senha, $usuario['senha'])) {

                session_regenerate_id(true);

                $_SESSION['id_usuario']   = $usuario['id'];
                $_SESSION['nome_usuario'] = $usuario['nome'];
                $_SESSION['cargo']        = $usuario['cargo'];

                if (isset($_POST['lembrar']) && $_POST['lembrar'] === '1') { 
                    $token     = bin2hex(random_bytes(32)); 
                    $hashCookie = hash('sha256', $token);
                    $expira    = time() + (30 * 24 * 60 * 60);

                    $stmtToken = $conn->prepare("UPDATE usuarios SET lembrar_cookie = ? WHERE id = ?");
                    $stmtToken->bind_param('si', $hashCookie, $usuario['id']);
                    $stmtToken->execute();
                    $stmtToken->close();

                    setcookie('lembrar_cookie', $token,          $expira, '/', '', false, true);
                    setcookie('lembrar_usuario',  $usuario['id'],  $expira, '/', '', false, true);
                }

                $stmt->close();
                $conn->close();

                header('Location: ../dashboard/index.php');
                exit;

            } else {
                $erro = 'E-mail ou senha incorretos.';
            }
        } else {
            $erro = 'E-mail ou senha incorretos.';
        }

        $stmt->close();
        $conn->close();
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Palmeiras FC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-body">

    <div class="auth-container">

        <div class="auth-logo">
            <img src="../assets/img/logo_palmeiras.png" alt="Palmeiras FC">
            <h2>Gerenciador de Tarefas</h2>
            <p class="auth-subtitulo">Palmeiras FC</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="auth-erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="auth-form">

            <div class="auth-campo">
                <label for="email">E-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="seu@email.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                    autocomplete="email"
                >
            </div>

            <div class="auth-campo">
                <label for="senha">Senha</label>
                <input
                    type="password"
                    id="senha"
                    name="senha"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                >
            </div>

            <div class="auth-lembrar">
                <label class="checkbox-label">
                    <input type="checkbox" name="lembrar" value="1">
                    Lembrar-me por 30 dias
                </label>
            </div>

            <button type="submit" class="btn-primario btn-bloco">Entrar</button>

        </form>

    </div>

</body>
</html>
