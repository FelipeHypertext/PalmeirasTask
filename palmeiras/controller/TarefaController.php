<?php
namespace App\Controller;

use App\Dal\TarefaDao;
use App\Dal\UsuarioDao;
use App\Model\Tarefa;
use App\View\TarefaView;
use App\Util\Functions as Util;
use Exception;

class TarefaController {
    public static ?string $msg = null;

    public static function listar(?int $deletar = null): void {
        $ordem = match($_GET["order"] ?? "") {
            "titulo" => "getTitulo",
            "status" => "getStatus",
            "prazo"  => "getPrazo",
            default  => "getId"
        };

        $tarefas = TarefaDao::listar();
        usort($tarefas, fn($a, $b) => $a->$ordem() <=> $b->$ordem());

        $usuarios = UsuarioDao::listar();
        $mapaUsuarios = [];
        foreach ($usuarios as $u) {
            $mapaUsuarios[$u->getId()] = $u->getNome();
        }

        TarefaView::listar($tarefas, $mapaUsuarios, $deletar);
    }

    public static function cadastrar(): void {
        if ($_SESSION["cargo"] !== "admin") {
            header("Location: ./?p=tarefas");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["titulo"])) {
            $token = $_POST["csrf"] ?? "";
            if (!isset($_SESSION["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $token)) {
                die("Falha no acesso");
            }

            [$titulo, $descricao, $prazo] = array_map(
                [Util::class, "preparaTexto"],
                [$_POST["titulo"] ?? "", $_POST["descricao"] ?? "", $_POST["prazo"] ?? ""]
            );
            $designadoPara = (int) ($_POST["designado_para"] ?? 0);
            $criadoPor     = (int) $_SESSION["id_usuario"];

            try {
                $tarefa = Tarefa::criar(null, $titulo, $descricao, "pendente", $prazo, $criadoPor, $designadoPara);
                TarefaDao::cadastrar($tarefa);
                header("Location: ./?p=tarefas");
                exit;
            } catch (Exception $e) {
                self::$msg = $e->getMessage();
            }
        }

        $usuarios = UsuarioDao::listar();
        TarefaView::formulario(self::$msg, null, $usuarios);
    }

    public static function editar(): void {
        $idUsuario = (int) $_SESSION["id_usuario"];
        $isAdmin   = $_SESSION["cargo"] === "admin";

        $tarefa = null;
        if (isset($_GET["alt"])) {
            $tarefa = TarefaDao::buscarPorId((int) $_GET["alt"]);
        }

        // Membro só acessa tarefa atribuída a ele
        if (!$isAdmin) {
            if ($tarefa === null || $tarefa->getDesignadoPara() !== $idUsuario) {
                header("Location: ./?p=tarefas");
                exit;
            }
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
            $token = $_POST["csrf"] ?? "";
            if (!isset($_SESSION["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $token)) {
                die("Falha no acesso");
            }

            if ($isAdmin) {
                $id            = (int) Util::preparaTexto($_POST["id"]        ?? "");
                $titulo        = Util::preparaTexto($_POST["titulo"]    ?? "");
                $descricao     = Util::preparaTexto($_POST["descricao"] ?? "");
                $prazo         = Util::preparaTexto($_POST["prazo"]     ?? "");
                $status        = Util::preparaTexto($_POST["status"]    ?? "pendente");
                $designadoPara = (int) ($_POST["designado_para"] ?? 0);
                $criadoPor     = (int) ($_POST["criado_por"]     ?? 0);

                try {
                    $tarefa = Tarefa::criar($id, $titulo, $descricao, $status, $prazo, $criadoPor, $designadoPara);
                    TarefaDao::editar($tarefa);
                    header("Location: ./?p=tarefas");
                    exit;
                } catch (Exception $e) {
                    self::$msg = $e->getMessage();
                }
            } else {
                // Membro: só atualiza o status da tarefa dele
                $id           = (int) ($_POST["id"] ?? 0);
                $status       = Util::preparaTexto($_POST["status"] ?? "pendente");
                $tarefaAtual  = TarefaDao::buscarPorId($id);

                if ($tarefaAtual === null || $tarefaAtual->getDesignadoPara() !== $idUsuario) {
                    header("Location: ./?p=tarefas");
                    exit;
                }

                try {
                    $atualizada = Tarefa::criar(
                        $tarefaAtual->getId(),
                        $tarefaAtual->getTitulo(),
                        $tarefaAtual->getDescricao(),
                        $status,
                        $tarefaAtual->getPrazo(),
                        $tarefaAtual->getCriadoPor(),
                        $tarefaAtual->getDesignadoPara()
                    );
                    TarefaDao::editar($atualizada);
                    header("Location: ./?p=tarefas");
                    exit;
                } catch (Exception $e) {
                    self::$msg = $e->getMessage();
                }
            }
        }

        if (!$isAdmin) {
            TarefaView::formularioStatus(self::$msg, $tarefa);
            return;
        }

        $usuarios = UsuarioDao::listar();
        TarefaView::formulario(self::$msg, $tarefa, $usuarios);
    }

    public static function deletar(): void {
        if ($_SESSION["cargo"] !== "admin") {
            header("Location: ./?p=tarefas");
            exit;
        }

        if (isset($_GET["deletar"])) {
            TarefaDao::excluir((int) $_GET["deletar"]);
            header("Location: ./?p=tarefas");
            exit;
        }

        if (isset($_GET["del"])) {
            self::listar((int) $_GET["del"]);
            return;
        }

        header("Location: ./?p=tarefas");
        exit;
    }
}
