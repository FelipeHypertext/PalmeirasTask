<?php
namespace App\Dal;

use App\Dal\Conn;
use App\Model\Tarefa;
use PDO;
use PDOException;
use Exception;

abstract class TarefaDao {
    public static function cadastrar(Tarefa $tarefa): int {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare(
                "INSERT INTO tarefas (titulo, descricao, status, prazo, criado_por, designado_para)
                 VALUES (:titulo, :descricao, :status, :prazo, :criadoPor, :designadoPara)"
            );
            $sql->bindValue(":titulo",       $tarefa->getTitulo(),       PDO::PARAM_STR);
            $sql->bindValue(":descricao",    $tarefa->getDescricao(),    PDO::PARAM_STR);
            $sql->bindValue(":status",       $tarefa->getStatus(),       PDO::PARAM_STR);
            $sql->bindValue(":prazo",        $tarefa->getPrazo(),        PDO::PARAM_STR);
            $sql->bindValue(":criadoPor",    $tarefa->getCriadoPor(),    PDO::PARAM_INT);
            $sql->bindValue(":designadoPara",$tarefa->getDesignadoPara(),PDO::PARAM_INT);
            $sql->execute();
            return (int) $pdo->lastInsertId();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function listar(): array {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare("SELECT * FROM tarefas");
            $sql->execute();
            $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            $tarefas = [];
            foreach ($res as $dados) {
                $tarefas[] = Tarefa::criar(
                    (int) $dados["id"],
                    $dados["titulo"],
                    $dados["descricao"] ?? "",
                    $dados["status"],
                    $dados["prazo"],
                    (int) $dados["criado_por"],
                    (int) $dados["designado_para"]
                );
            }
            return $tarefas;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function buscarPorId(int $id): ?Tarefa {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare("SELECT * FROM tarefas WHERE id = ?");
            $sql->execute([$id]);
            $dados = $sql->fetch(PDO::FETCH_ASSOC);
            if (!$dados) return null;
            return Tarefa::criar(
                (int) $dados["id"],
                $dados["titulo"],
                $dados["descricao"] ?? "",
                $dados["status"],
                $dados["prazo"],
                (int) $dados["criado_por"],
                (int) $dados["designado_para"]
            );
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function editar(Tarefa $tarefa): void {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare(
                "UPDATE tarefas SET titulo = ?, descricao = ?, status = ?, prazo = ?, designado_para = ? WHERE id = ?"
            );
            $sql->execute([
                $tarefa->getTitulo(),
                $tarefa->getDescricao(),
                $tarefa->getStatus(),
                $tarefa->getPrazo(),
                $tarefa->getDesignadoPara(),
                $tarefa->getId()
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
            $sql = $pdo->prepare("DELETE FROM tarefas WHERE id = ?");
            $sql->execute([$id]);
            if ($sql->rowCount() !== 1) {
                throw new Exception("Erro ao excluir tarefa.");
            }
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
