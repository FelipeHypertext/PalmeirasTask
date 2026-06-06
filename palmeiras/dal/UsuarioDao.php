<?php
namespace App\Dal;

use App\Dal\Conn;
use App\Model\Usuario;
use PDO;
use PDOException;
use Exception;

abstract class UsuarioDao {
    public static function cadastrar(Usuario $usuario): int {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare(
                "INSERT INTO usuarios (nome, email, senha, cpf, data_nascimento, cargo, posicao)
                 VALUES (:nome, :email, :senha, :cpf, :dataNascimento, :cargo, :posicao)"
            );
            $sql->bindValue(":nome",           $usuario->getNome(),           PDO::PARAM_STR);
            $sql->bindValue(":email",          $usuario->getEmail(),          PDO::PARAM_STR);
            $sql->bindValue(":senha",          $usuario->getSenha(),          PDO::PARAM_STR);
            $sql->bindValue(":cpf",            $usuario->getCpf(),            PDO::PARAM_STR);
            $sql->bindValue(":dataNascimento", $usuario->getDataNascimento(), PDO::PARAM_STR);
            $sql->bindValue(":cargo",          $usuario->getCargo(),          PDO::PARAM_STR);
            $sql->bindValue(":posicao",        $usuario->getPosicao(),        PDO::PARAM_STR);
            $sql->execute();
            return (int) $pdo->lastInsertId();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function listar(): array {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare("SELECT * FROM usuarios ORDER BY nome");
            $sql->execute();
            $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            $usuarios = [];
            foreach ($res as $d) {
                $usuarios[] = self::montar($d);
            }
            return $usuarios;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function buscarPorId(int $id): ?Usuario {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
            $sql->execute([$id]);
            $d = $sql->fetch(PDO::FETCH_ASSOC);
            if (!$d) return null;
            return self::montar($d);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function buscarPorEmail(string $email): ?Usuario {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $sql->execute([$email]);
            $d = $sql->fetch(PDO::FETCH_ASSOC);
            if (!$d) return null;
            return self::montar($d);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function buscarPorToken(string $hashToken): ?Usuario {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare("SELECT * FROM usuarios WHERE token_lembrar = ?");
            $sql->execute([$hashToken]);
            $d = $sql->fetch(PDO::FETCH_ASSOC);
            if (!$d) return null;
            return self::montar($d);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function atualizarToken(int $id, ?string $hashToken): void {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare("UPDATE usuarios SET token_lembrar = ? WHERE id = ?");
            $sql->execute([$hashToken, $id]);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function atualizarSenha(int $id, string $senhaHash): void {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $sql->execute([$senhaHash, $id]);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function editar(Usuario $usuario): void {
        try {
            $pdo = Conn::getConn();
            $sql = $pdo->prepare(
                "UPDATE usuarios SET nome = ?, email = ?, cpf = ?, data_nascimento = ?, cargo = ?, posicao = ? WHERE id = ?"
            );
            $sql->execute([
                $usuario->getNome(),
                $usuario->getEmail(),
                $usuario->getCpf(),
                $usuario->getDataNascimento(),
                $usuario->getCargo(),
                $usuario->getPosicao(),
                $usuario->getId()
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
            $sql = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $sql->execute([$id]);
            if ($sql->rowCount() !== 1) {
                throw new Exception("Erro ao excluir usuário.");
            }
        } catch (PDOException $e) {
            throw $e;
        }
    }

    private static function montar(array $d): Usuario {
        return Usuario::criar(
            (int) $d["id"],
            $d["nome"],
            $d["email"],
            $d["senha"],
            $d["cpf"],
            $d["data_nascimento"],
            $d["cargo"],
            $d["posicao"] ?? "",
            $d["token_lembrar"] ?? null
        );
    }
}
