<?php
function conectar() {
    $conn = new mysqli("localhost", "root", "", "palmeirasdb", 3306);

    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    return $conn;
}
?>