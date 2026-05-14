<?php
function verificarSessao(): void {
    if (!isset($_SESSION['id_usuario'])) {
        header('Location: ../auth/login.php');
        exit;
    }
}

function verificarAdmin(): void {
    verificarSessao();
    if ($_SESSION['cargo'] !== 'admin') {
        header('Location: ../dashboard/index.php');
        exit;
    }
}
    
function validaInput(string $dados): string {
    $dados = trim($dados);
    $dados = htmlspecialchars($dados);
    return $dados;
}
?>