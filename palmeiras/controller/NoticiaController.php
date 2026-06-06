<?php
namespace App\Controller;

use App\Dal\NoticiaDao;
use App\Model\Noticia;
use App\View\NoticiaView;
use App\Util\Functions as Util;
use Exception;

class NoticiaController {
    public static ?string $msg = null;

    public static function listar(): void {
        $noticias = NoticiaDao::listar();
        NoticiaView::listar($noticias);
    }

    public static function ver(): void {
        $id = (int) ($_GET["id"] ?? 0);
        if ($id <= 0) {
            header("Location: ./?p=noticias");
            exit;
        }
        $noticia = NoticiaDao::buscarPorId($id);
        if ($noticia === null) {
            header("Location: ./?p=noticias");
            exit;
        }
        NoticiaView::ver($noticia);
    }

    public static function cadastrar(): void {
        if ($_SESSION["cargo"] !== "admin") {
            header("Location: ./?p=home");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["titulo"])) {
            $titulo   = Util::preparaTexto($_POST["titulo"]   ?? "");
            $conteudo = Util::preparaTexto($_POST["conteudo"] ?? "");
            $criadoPor = (int) $_SESSION["id_usuario"];

            try {
                $noticia = Noticia::criar(null, $titulo, $conteudo, $criadoPor);
                NoticiaDao::cadastrar($noticia);
                header("Location: ./?p=noticias");
                exit;
            } catch (Exception $e) {
                self::$msg = $e->getMessage();
            }
        }

        NoticiaView::formulario(self::$msg, null);
    }

    public static function editar(): void {
        if ($_SESSION["cargo"] !== "admin") {
            header("Location: ./?p=home");
            exit;
        }

        $noticia = null;
        if (isset($_GET["alt"])) {
            $noticia = NoticiaDao::buscarPorId((int) $_GET["alt"]);
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
            $id       = (int) ($_POST["id"] ?? 0);
            $titulo   = Util::preparaTexto($_POST["titulo"]   ?? "");
            $conteudo = Util::preparaTexto($_POST["conteudo"] ?? "");

            try {
                $noticiaAtual = NoticiaDao::buscarPorId($id);
                if ($noticiaAtual === null) {
                    throw new Exception("Notícia não encontrada.");
                }
                $noticia = Noticia::criar($id, $titulo, $conteudo, $noticiaAtual->getCriadoPor());
                NoticiaDao::editar($noticia);
                header("Location: ./?p=noticias");
                exit;
            } catch (Exception $e) {
                self::$msg = $e->getMessage();
            }
        }

        NoticiaView::formulario(self::$msg, $noticia);
    }

    public static function deletar(): void {
        if ($_SESSION["cargo"] !== "admin") {
            header("Location: ./?p=home");
            exit;
        }

        if (isset($_GET["deletar"])) {
            NoticiaDao::excluir((int) $_GET["deletar"]);
            header("Location: ./?p=noticias");
            exit;
        }

        if (isset($_GET["del"])) {
            $id      = (int) $_GET["del"];
            $noticias = NoticiaDao::listar();
            NoticiaView::listar($noticias, $id);
            return;
        }

        header("Location: ./?p=noticias");
        exit;
    }
}
