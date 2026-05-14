<?php
function conectar() {
    $conn = new mysqli("127.0.0.1", "root", "", "palmeirasdb", 3307);

    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    return $conn;
}
?>