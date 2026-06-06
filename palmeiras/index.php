<?php
declare(strict_types=1);
namespace App;

session_start();
session_regenerate_id(true);

require_once "./Autoload.php";

use App\Controller\AuthController;
use App\Controller\TarefaController;
use App\Controller\UsuarioController;
use App\Controller\NoticiaController;

if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$paginasPublicas = ["home", "noticias", "noticia", "sobre", "login", "registro", "recuperar"];
$autenticado     = isset($_SESSION["id_usuario"]);
$page            = $_GET["p"] ?? "home";

// Usuário não autenticado tentando acessar página privada → redireciona ao login
if (!$autenticado && !in_array($page, $paginasPublicas)) {
    header("Location: ./?p=login");
    exit;
}

// Usuário autenticado tentando acessar login/registro/recuperar → redireciona ao home
if ($autenticado && in_array($page, ["login", "registro", "recuperar"])) {
    header("Location: ./?p=home");
    exit;
}

$titulo = match($page) {
    "home"         => "Home",
    "noticias"     => "Notícias",
    "noticia"      => "Notícia",
    "sobre"        => "Sobre",
    "login"        => "Login",
    "registro"     => "Criar Conta",
    "recuperar"    => "Recuperar Senha",
    "tarefas"      => "Tarefas",
    "cad-tarefa"   => "Nova Tarefa",
    "alt-tarefa"   => "Editar Tarefa",
    "del-tarefa"   => "Excluir Tarefa",
    "usuarios"     => "Jogadores",
    "cad-usuario"  => "Cadastrar Jogador",
    "alt-usuario"  => "Editar Jogador",
    "del-usuario"  => "Excluir Jogador",
    "cad-noticia"  => "Nova Notícia",
    "alt-noticia"  => "Editar Notícia",
    "del-noticia"  => "Excluir Notícia",
    "sair"         => "Sair",
    default        => "ERRO 404"
};
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Palmeiras FC — <?= $titulo ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/style.css">
</head>
<body>
    <header>
        <nav>
            <?php require_once "./menu.php"; ?>
        </nav>
    </header>

    <main>
    <?php
        match($page) {
            "home"        => require_once("./view/home.php"),
            "noticias"    => NoticiaController::listar(),
            "noticia"     => NoticiaController::ver(),
            "sobre"       => require_once("./view/sobre.php"),
            "login"       => AuthController::login(),
            "registro"    => AuthController::registrar(),
            "recuperar"   => AuthController::recuperar(),
            "tarefas"     => TarefaController::listar(),
            "cad-tarefa"  => TarefaController::cadastrar(),
            "alt-tarefa"  => TarefaController::editar(),
            "del-tarefa"  => TarefaController::deletar(),
            "usuarios"    => UsuarioController::listar(),
            "cad-usuario" => UsuarioController::cadastrar(),
            "alt-usuario" => UsuarioController::editar(),
            "del-usuario" => UsuarioController::deletar(),
            "cad-noticia" => NoticiaController::cadastrar(),
            "alt-noticia" => NoticiaController::editar(),
            "del-noticia" => NoticiaController::deletar(),
            "sair"        => AuthController::logout(),
            default       => require_once("./view/404.php"),
        };
    ?>
    </main>

    <footer>
        <small>Sociedade Esportiva Palmeiras &copy; <?= date("Y") ?> &mdash; Gerenciador de Tarefas</small>
    </footer>
</body>
</html>
