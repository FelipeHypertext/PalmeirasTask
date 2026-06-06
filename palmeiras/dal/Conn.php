<?php
namespace App\Dal;

use PDO;
use PDOException;
use Exception;

abstract class Conn {
    private static ?PDO $conn = null;
    private static string $host = "localhost:3307";
    private static string $dbname = "palmeirasdb";
    private static string $user = "root";
    private static string $password = "";

    public static function getConn(): PDO {
        if (self::$conn === null) {
            try {
                self::$conn = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$dbname,
                    self::$user,
                    self::$password
                );
            } catch (PDOException $e) {
                throw new Exception("Erro ao conectar ao banco de dados: " . $e->getMessage(), 1);
            }
        }
        return self::$conn;
    }
}
