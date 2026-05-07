<?php

/*Para evitar aquele erro visual do VSCODE usarei uma função que retorna a conexão*/
function conectar() {
    $conn = new mysqli("localhost", "root", "", "palmeirasdb");

    /*Caso dê algum erro na conexão*/
    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }
    /*Configura a codificação*/
    $conn->set_charset("utf8");
    return $conn;
}

/*Use require_once"config/db.php" para acessar o banco de dados e $conn = conectar()*/
/*Como dessa maneira $conn é um objeto, é necessário chamar os metodos $conn->metodo. Para uma pesquisa por exemplo: $conn->query()*/

?>