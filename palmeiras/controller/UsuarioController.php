<?php
namespace App\Controller;

use App\Dal\UsuarioDao;
use App\Model\Usuario;
use App\View\UsuarioView;
use App\Util\Functions as Util;
use Exception;

class UsuarioController {
    public static ?string $msg = null;

    public static function listar(): void {
        if ($_SESSION["cargo"] !== "admin") {
            header("Location: ./?p=home");
            exit;
        }

        $ordem = match($_GET["order"] ?? "") {
            "nome"  => "getNome",
            "cargo" => "getCargo",
            default => "getId"
        };

        $usuarios = UsuarioDao::listar();
        usort($usuarios, fn($a, $b) => $a->$ordem() <=> $b->$ordem());
        UsuarioView::listar($usuarios);
    }

    public static function cadastrar(): void {
        if ($_SESSION["cargo"] !== "admin") {
            header("Location: ./?p=home");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["nome"])) {
            $nome           = Util::preparaTexto($_POST["nome"]            ?? "");
            $email          = Util::preparaTexto($_POST["email"]           ?? "");
            $cpf            = Util::preparaTexto($_POST["cpf"]             ?? "");
            $dataNascimento = Util::preparaTexto($_POST["data_nascimento"]  ?? "");
            $cargo          = Util::preparaTexto($_POST["cargo"]           ?? "membro");
            $posicao        = Util::preparaTexto($_POST["posicao"]         ?? "");
            $senha          = $_POST["senha"] ?? "";

            try {
                $senhaHash = password_hash($senha, PASSWORD_ARGON2ID);
                $usuario   = Usuario::criar(null, $nome, $email, $senhaHash, $cpf, $dataNascimento, $cargo, $posicao);
                UsuarioDao::cadastrar($usuario);
                header("Location: ./?p=usuarios");
                exit;
            } catch (Exception $e) {
                self::$msg = $e->getMessage();
            }
        }

        UsuarioView::formulario(self::$msg, null);
    }

    public static function editar(): void {
        if ($_SESSION["cargo"] !== "admin") {
            header("Location: ./?p=home");
            exit;
        }

        $usuario = null;
        if (isset($_GET["alt"])) {
            $usuario = UsuarioDao::buscarPorId((int) $_GET["alt"]);
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
            $id             = (int) ($_POST["id"]              ?? 0);
            $nome           = Util::preparaTexto($_POST["nome"]            ?? "");
            $email          = Util::preparaTexto($_POST["email"]           ?? "");
            $cpf            = Util::preparaTexto($_POST["cpf"]             ?? "");
            $dataNascimento = Util::preparaTexto($_POST["data_nascimento"]  ?? "");
            $cargo          = Util::preparaTexto($_POST["cargo"]           ?? "membro");
            $posicao        = Util::preparaTexto($_POST["posicao"]         ?? "");

            $usuarioAtual = UsuarioDao::buscarPorId($id);
            if ($usuarioAtual === null) {
                self::$msg = "Usuário não encontrado.";
                UsuarioView::formulario(self::$msg, null);
                return;
            }

            try {
                $usuario = Usuario::criar($id, $nome, $email, $usuarioAtual->getSenha(), $cpf, $dataNascimento, $cargo, $posicao);
                UsuarioDao::editar($usuario);
                header("Location: ./?p=usuarios");
                exit;
            } catch (Exception $e) {
                self::$msg = $e->getMessage();
            }
        }

        UsuarioView::formulario(self::$msg, $usuario);
    }

    public static function deletar(): void {
        if ($_SESSION["cargo"] !== "admin") {
            header("Location: ./?p=home");
            exit;
        }

        if (isset($_GET["deletar"])) {
            UsuarioDao::excluir((int) $_GET["deletar"]);
            header("Location: ./?p=usuarios");
            exit;
        }

        if (isset($_GET["del"])) {
            $usuarios = UsuarioDao::listar();
            UsuarioView::listar($usuarios, (int) $_GET["del"]);
            return;
        }

        header("Location: ./?p=usuarios");
        exit;
    }
}
