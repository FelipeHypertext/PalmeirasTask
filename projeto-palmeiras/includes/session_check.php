<?php
/*Ferramentas de validação de credencial*/

/*Ferramenta Geral: Analisa se o usuário já se logou alguma vez no site (para voltar diretamente caso ele não dê logout)*/
function verificarSessao(): void {
    if (!isset($_SESSION['id_usuario'])) {
        header('Location: ../auth/login.php');
        exit;
    }
}

/*Ferramenta específica: Analisa se o usuário é um administrador, puxando do banco de dados*/
function verificarAdmin(): void {
    verificarSessao();
    if ($_SESSION['cargo'] !== 'admin') {
        header('Location: ../dashboard/index.php');
        exit;
    }
}
    
/*Ferramenta de formatação de dados (Proteção contra XSS)*/
function validaInput(string $dados): string {
    $dados = trim($dados);
    $dados = htmlspecialchars($dados);
    return $dados;
}
?>