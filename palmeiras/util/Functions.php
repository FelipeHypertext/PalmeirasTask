<?php
namespace App\Util;

class Functions {
    public static function preparaTexto(string $texto): string {
        return trim(htmlentities($texto));
    }
}
