<?php
/*
 * TRABALHO PHP — Prof. João Paulo Nunes da Silva
 * 1. João Felipe Mokdse Costa           - RGM: 43492801
 * 2. Victor Schernikau B. B. Vieira     - RGM: 44050496
 * 3. Max Lopes                          - RGM: 42826381
 * 4. Israel Wendell                     - RGM: [RGM do Israel]
 * 5. Eduarda Luiz                       - RGM: 43806414
 * 6. Douglas Menegotti                  - RGM: 37274261
 */

session_start();

if (isset($_SESSION["id_usuario"])) {
    header("Location: dashboard/index.php");
} else {
    header("Location: auth/login.php");
}

exit();
?>