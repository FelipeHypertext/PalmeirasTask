<?php
    session_start();
    require_once("../config/db.php");
    require_once("../includes/session_check.php");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador Palmeiras</title>
    <!--Css aqui-->
</head>
<body>
    <?php
        require_once("../includes/header.php");
    ?>
    <h2>Dashboard</h2>

    <?php
        require_once("../includes/footer.php");
    ?>
</body>
</html>