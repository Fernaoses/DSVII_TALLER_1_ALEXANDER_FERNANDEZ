<?php

function contar_palabras_repetidas($texto)
{
    $texto = "Vamos a pasar el examen";

    $textoLower = strtolower($texto);
    $textoArray = explode(" ", $textoLower);

    for($i = 0; $i < count($textoArray); $i++) {
        $palabra = $textoArray[$i];
        if(array_key_exists($palabra, $resultado)) {
            $resultado[$palabra]++;
        } else {
            $resultado[$palabra] = 1;
        }
    }
    return $resultado;
}

function capitalizar_palabras($texto)
{
    $texto = "Vamos a reprobar el examen";

    $textoArray = explode(" ", $texto);
    for($i = 0; $i < count($textoArray); $i++) {
        $textoArray[$i] = substr($textoArray[$i], 0, 1).strtoupper(substr($textoArray[$i], 1));
    }
    return implode(" ", $textoArray);
}
?>