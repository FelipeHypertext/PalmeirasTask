<?php
namespace App\Dal;

use App\Dal\Conn;
use App\Model\Noticia;
use PDO;
use PDOException;
use Exception;

abstract class NoticiaDao {
    public static function cadastrar(Noticia $noticia): int {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare(
                "INSERT INTO noticias (titulo, conteudo, criado_por) VALUES (:titulo, :conteudo, :criadoPor)"
            );
            $sql->bindValue(":titulo",    $noticia->getTitulo(),   PDO::PARAM_STR);
            $sql->bindValue(":conteudo",  $noticia->getConteudo(), PDO::PARAM_STR);
            $sql->bindValue(":criadoPor", $noticia->getCriadoPor(), PDO::PARAM_INT);
            $sql->execute();
            return (int) $pdo->lastInsertId();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function listar(int $limite = 0): array {
        try {
            $pdo = Conn::getConn();
            $limiteSql = $limite > 0 ? " LIMIT " . (int)$limite : "";
            $sql = $pdo->prepare("SELECT * FROM noticias ORDER BY id DESC" . $limiteSql);
            $sql->execute();
            $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            $noticias = [];
            foreach ($res as $d) {
                $noticias[] = Noticia::criar(
                    (int) $d["id"],
                    $d["titulo"],
                    $d["conteudo"],
                    (int) $d["criado_por"],
                    $d["criado_em"] ?? ""
                );
            }
            return $noticias;
        } catch (PDOException $e) {
            throw new PDOException("Erro ao listar noticias ".$e->getMessage());        
        }
    }

    public static function buscarPorId(int $id): ?Noticia {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
            $sql->execute([$id]);
            $d = $sql->fetch(PDO::FETCH_ASSOC);
            if (!$d) return null;
            return Noticia::criar(
                (int) $d["id"],
                $d["titulo"],
                $d["conteudo"],
                (int) $d["criado_por"],
                $d["criado_em"] ?? ""
            );
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function editar(Noticia $noticia): void {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare("UPDATE noticias SET titulo = ?, conteudo = ? WHERE id = ?");
            $sql->execute([
                $noticia->getTitulo(),
                $noticia->getConteudo(),
                $noticia->getId()
            ]);
            if ($sql->rowCount() !== 1) {
                throw new Exception("Nenhum registro foi alterado.");
            }
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function excluir(int $id): void {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare("DELETE FROM noticias WHERE id = ?");
            $sql->execute([$id]);
            if ($sql->rowCount() !== 1) {
                throw new Exception("Erro ao excluir notícia.");
            }
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
