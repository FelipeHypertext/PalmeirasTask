<?php
namespace App\Controller;

use App\Dal\UsuarioDao;
use App\Model\Usuario;
use App\View\AuthView;
use App\Util\Functions as Util;
use Exception;

class AuthController {
    public static ?string $msg = null;

    // ──────────────────────────────────────────────
    // LOGIN
    // ──────────────────────────────────────────────
    public static function login(): void {
        // Verifica cookie "lembrar-me" antes de processar formulário
        if (!isset($_SESSION["id_usuario"]) && isset($_COOKIE["palmeiras_token"])) {
            $hashToken = hash("sha256", $_COOKIE["palmeiras_token"]);
            $usuario   = UsuarioDao::buscarPorToken($hashToken);
            if ($usuario !== null) {
                session_regenerate_id(true);
                $_SESSION["id_usuario"]   = $usuario->getId();
                $_SESSION["nome_usuario"] = $usuario->getNome();
                $_SESSION["cargo"]        = $usuario->getCargo();
                header("Location: ./");
                exit;
            }
            // Token inválido: limpa cookie
            setcookie("palmeiras_token", "", time() - 3600, "/");
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["email"])) {
            $token = $_POST["csrf"] ?? "";
            if (!isset($_SESSION["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $token)) {
                die("Falha no acesso");
            }

            $email = Util::preparaTexto($_POST["email"] ?? "");
            $senha = $_POST["senha"] ?? "";

            try {
                $usuario = UsuarioDao::buscarPorEmail($email);
                if ($usuario !== null && password_verify($senha, $usuario->getSenha())) {
                    session_regenerate_id(true);
                    $_SESSION["id_usuario"]   = $usuario->getId();
                    $_SESSION["nome_usuario"] = $usuario->getNome();
                    $_SESSION["cargo"]        = $usuario->getCargo();

                    // Cookie "lembrar-me" por 30 dias
                    if (isset($_POST["lembrar"]) && $_POST["lembrar"] === "1") {
                        $tokenRaw  = bin2hex(random_bytes(32));
                        $hashToken = hash("sha256", $tokenRaw);
                        UsuarioDao::atualizarToken($usuario->getId(), $hashToken);
                        $expira = time() + (30 * 24 * 60 * 60);
                        setcookie("palmeiras_token", $tokenRaw, $expira, "/", "", false, true);
                    }

                    header("Location: ./");
                    exit;
                }
                self::$msg = "E-mail ou senha incorretos.";
            } catch (Exception $e) {
                self::$msg = "Erro ao efetuar login.";
            }
        }

        AuthView::formularioLogin(self::$msg);
    }

    // ──────────────────────────────────────────────
    // REGISTRO
    // ──────────────────────────────────────────────
    public static function registrar(): void {
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["nome"])) {
            $token = $_POST["csrf"] ?? "";
            if (!isset($_SESSION["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $token)) {
                die("Falha no acesso");
            }

            $nome           = Util::preparaTexto($_POST["nome"]           ?? "");
            $email          = Util::preparaTexto($_POST["email"]          ?? "");
            $cpf            = Util::preparaTexto($_POST["cpf"]            ?? "");
            $dataNascimento = Util::preparaTexto($_POST["data_nascimento"] ?? "");
            $senha          = $_POST["senha"]   ?? "";
            $confirma       = $_POST["confirma"] ?? "";

            if ($senha !== $confirma) {
                self::$msg = "As senhas não coincidem.";
            } elseif (strlen($senha) < 6) {
                self::$msg = "A senha deve ter pelo menos 6 caracteres.";
            } else {
                try {
                    $senhaHash = password_hash($senha, PASSWORD_ARGON2ID);
                    $usuario   = Usuario::criar(null, $nome, $email, $senhaHash, $cpf, $dataNascimento, "membro", "");
                    UsuarioDao::cadastrar($usuario);
                    header("Location: ./?p=login&cadastro=ok");
                    exit;
                } catch (Exception $e) {
                    self::$msg = $e->getMessage();
                }
            }
        }

        AuthView::formularioRegistro(self::$msg);
    }

    // ──────────────────────────────────────────────
    // RECUPERAÇÃO DE SENHA
    // ──────────────────────────────────────────────
    public static function recuperar(): void {
        // Etapa 2: usuário foi validado (id na sessão temporária) e define nova senha
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["nova_senha"])) {
            $token = $_POST["csrf"] ?? "";
            if (!isset($_SESSION["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $token)) {
                die("Falha no acesso");
            }

            $id       = (int) ($_SESSION["recuperar_id"] ?? 0);
            $nova     = $_POST["nova_senha"]     ?? "";
            $confirma = $_POST["confirma_senha"] ?? "";

            if ($id <= 0) {
                self::$msg = "Sessão expirada. Tente novamente.";
                AuthView::formularioRecuperar(self::$msg, false);
                return;
            }

            if ($nova !== $confirma) {
                self::$msg = "As senhas não coincidem.";
                AuthView::formularioRecuperar(self::$msg, true);
                return;
            }

            if (strlen($nova) < 6) {
                self::$msg = "A senha deve ter pelo menos 6 caracteres.";
                AuthView::formularioRecuperar(self::$msg, true);
                return;
            }

            try {
                UsuarioDao::atualizarSenha($id, password_hash($nova, PASSWORD_ARGON2ID));
                unset($_SESSION["recuperar_id"]);
                header("Location: ./?p=login&recuperou=ok");
                exit;
            } catch (Exception $e) {
                self::$msg = "Erro ao atualizar senha.";
                AuthView::formularioRecuperar(self::$msg, true);
                return;
            }
        }

        // Etapa 1: valida e-mail + CPF + data de nascimento
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["email"])) {
            $token = $_POST["csrf"] ?? "";
            if (!isset($_SESSION["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $token)) {
                die("Falha no acesso");
            }

            $email = Util::preparaTexto($_POST["email"] ?? "");
            $cpf   = preg_replace('/\D/', '', $_POST["cpf"] ?? "");
            $data  = Util::preparaTexto($_POST["data_nascimento"] ?? "");

            try {
                $usuario = UsuarioDao::buscarPorEmail($email);
                if ($usuario !== null && $usuario->getCpf() === $cpf && $usuario->getDataNascimento() === $data) {
                    $_SESSION["recuperar_id"] = $usuario->getId();
                    AuthView::formularioRecuperar(null, true);
                    return;
                }
                self::$msg = "Dados não conferem com o cadastro.";
            } catch (Exception $e) {
                self::$msg = "Erro ao verificar dados.";
            }
        }

        AuthView::formularioRecuperar(self::$msg, false);
    }

    // ──────────────────────────────────────────────
    // LOGOUT
    // ──────────────────────────────────────────────
    public static function logout(): void {
        if (isset($_SESSION["id_usuario"])) {
            UsuarioDao::atualizarToken((int) $_SESSION["id_usuario"], null);
        }

        setcookie("palmeiras_token", "", time() - 3600, "/");

        $_SESSION = [];
        session_destroy();
        header("Location: ./");
        exit;
    }
}
