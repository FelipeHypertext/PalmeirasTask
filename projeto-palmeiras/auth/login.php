<?php

session_start();
require_once '../config/db.php';
require_once '../includes/session_check.php';

// SE O USUÁRIO JÁ ESTIVER LOGADO, VAI DIRETO PARA A DASHBOARD
if (isset($_SESSION['id_usuario'])) {
    header('Location: ../dashboard/index.php');
    exit;
}


// INICIALIZANDO A VARIAVEL PARA QUE A PÁGINA FUNCIONE SE O USUÁRIO NÃO ENVIOU O FORMULÁRIO
$erro = '';

// VERIFICA O COOKIE LEMBRAR-ME ANTES DE INICIAR O FORMULÁRIO
if (isset($_COOKIE['lembrar_cookie']) && isset($_COOKIE['lembrar_usuario'])) {
    $conn      = conectar(); // CONEXÃO COM O BANCO DE DADOS
    $id_usuario    = (int) $_COOKIE['lembrar_usuario'];
    $hashCookie = hash('sha256', $_COOKIE['lembrar_cookie']); // AQUI É GERADO O HASH USANDO sha256 PARA COMPARAR COM O QUE FOI SALVO NO BANCO DE DADOS (lembrar_cookie)



    /* Usuário preenche o formulário e marca "lembrar-me"
    PHP salva o id e o token nos cookies do navegador
    PHP salva o hash do token no banco
    
    Visita seguinte → usuário abre o site sem preencher nada
    navegador envia os cookies automaticamente
    PHP lê: $_COOKIE['lembrar_usuario'] e $_COOKIE['lembrar_cookie']
    busca no banco se existe usuário com aquele id e aquele hash
    se encontrar → loga sem precisar digitar nada */
    $stmt = $conn->prepare("SELECT id, nome, cargo FROM usuarios WHERE id = ? AND lembrar_cookie = ?"); // BUSCA NO BANCO UM USUARIO COM O MESMO ID E O MESMO lembrar_cookie. O ? É SUBSTITUIDO PELAS INFORMAÇÕES SALVAS NO COOKIE
    $stmt->bind_param('is', $id_usuario, $hashCookie); // AQUI É SUBSTITUIDO O ? DA QUERY USANDO OS COOKIES QUE FORAM SALVOS NO NAVEGADOR E DEFINIDOS, ali em cima são preparados, aqui são substituidos e abaixo são exxecutados
    $stmt->execute();
    $res = $stmt->get_result(); // PEGA O RESULTADO DA QUERY E SALVA AQUI

    // VERIFICA SE ENCONTROU UM RESULTADO
    if ($res->num_rows === 1) {
        $usuario = $res->fetch_assoc(); // TRANSFORMA EM ARRAY
        session_regenerate_id(true); // SEGURANÇA, TROCA O ID DA SESSÃO

        // SESSIONS
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

// FORMULÁRIO POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = validaInput($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? ''; // Não sanitizar antes do password_verify

    // VALIDAÇÃO SE ESTÁ VAZIO
    if (empty($email) || empty($senha)) {
        $erro = 'Preencha o e-mail e a senha.';
    } else {
        $conn = conectar();

        //VERIFICA SE O EMAIL E A SENHA ESTÃO NO BANCO DE DADOS (PELO EMAIL)
        $stmt = $conn->prepare("SELECT id, nome, senha, cargo FROM usuarios WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            // VERIFICA SE A SENHA DIGITADA É A MESMA DO BANCO DE DADOS
            if (password_verify($senha, $usuario['senha'])) {

                // SEGURANÇA, TROCA O ID DA SESSÃO
                session_regenerate_id(true);

                // SESSIONS
                $_SESSION['id_usuario']   = $usuario['id'];
                $_SESSION['nome_usuario'] = $usuario['nome'];
                $_SESSION['cargo']        = $usuario['cargo'];

                // COOKIE LEMBRAR-ME
                if (isset($_POST['lembrar']) && $_POST['lembrar'] === '1') { // VERIFICA SE O CHECKBOX FOI MARCADO (POR ISSO VALOR === 1)
                    $token     = bin2hex(random_bytes(32));  // 64 chars hex
                    $hashCookie = hash('sha256', $token);
                    $expira    = time() + (30 * 24 * 60 * 60); // 30 dias × 24 horas × 60 minutos × 60 segundos

                    // SALVA O HASH COOKIE NO BANCO
                    $stmtToken = $conn->prepare("UPDATE usuarios SET lembrar_cookie = ? WHERE id = ?");
                    $stmtToken->bind_param('si', $hashCookie, $usuario['id']);
                    $stmtToken->execute();
                    $stmtToken->close();

                    // HttpOnly = TRUE PROTEGE O COOKIE DE JS MALICIOSO
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
            // MESMA MENSAGEM PARA NÃO REVELAR SE O EMAIL OU A SENHA EXISTEM NO BANCO
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
