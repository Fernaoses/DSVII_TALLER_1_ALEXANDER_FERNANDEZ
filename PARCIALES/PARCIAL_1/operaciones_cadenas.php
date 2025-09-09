<?php

function contar_palabras_repetidas($texto)
{
    $textoLower = strtolower($texto);
    $textoArray = explode(" ", $textoLower);

    $contador = 0;
    $enPalabra = false;
    $longitud = strlen($texto);
    for ($i = 0; $i < $longitud; $i++) {
        
        $caracter = $texto[$i];
        if ($caracter === ' ' || $caracter === "\n" || $caracter === "\t") {
            if ($enPalabra) {
                $contador++;
                $enPalabra = false;
            }
        } else {
            $enPalabra = true;
        }
    }
    if ($enPalabra) {
        $contador++;
    }
    return $contador;
    // return implode(" ", $textoArray);
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