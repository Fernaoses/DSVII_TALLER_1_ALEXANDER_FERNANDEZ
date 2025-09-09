<?php

function contar_palabras_repetidas($texto)
{
    $textoLower = strtolower($texto);
    $textoArray = explode(" ", $textoLower);

    for($i = 0; $i < count($textoArray); $i++) {
        $textoArray[$i] = trim($textoArray[$i], ",.?!;:\"'()[]{}");

    }
    return $textoArray;
}

function capitalizar_palabras($texto)
{
    $textoArray = explode(" ", $texto);
    for($i = 0; $i < count($textoArray); $i++) {
        $textoArray[$i] = substr($textoArray[$i], 0, 0).strtoupper($textoArray[$i]);
    }
    return implode(" ", $textoArray);
}
?>