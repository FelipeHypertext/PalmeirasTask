<?php

session_start();
require_once '../config/db.php';

// REMOVE O LEMBRAR_COOKIE DO BANCO DE DADOS CASO ELE EXISTA
if (isset($_COOKIE['lembrar_usuario'])) {
    $conn   = conectar();
    $userId = (int) $_COOKIE['lembrar_usuario'];

    $stmt = $conn->prepare("UPDATE usuarios SET lembrar_cookie = NULL WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// LIMPA COOKIE DE LEMBRAR ME
setcookie('lembrar_cookie', '', time() - 3600, '/');
setcookie('lembrar_usuario',  '', time() - 3600, '/');

// DESTRÓI A SESSÃO
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

// REDICIONAR AO LOGIN
header('Location: login.php');
exit;
?>
