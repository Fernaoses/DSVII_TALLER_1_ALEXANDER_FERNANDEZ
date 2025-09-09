<?php

function calcular_descuento($antiguedad_meses)
{
    if ($antiguedad_meses < 3) {
        return 0;
    } elseif ($antiguedad_meses >= 3 && $antiguedad_meses <= 12) {
        return 0.08;
    } elseif ($antiguedad_meses >= 13 && $antiguedad_meses <= 24) {
        return 0.12; 
    } elseif ($antiguedad_meses > 24) {
        return 0.20;
    }
}

function calcular_seguro_medico($cuota_base)
{
    return $cuota_base * 0.05; 
}   

function calcular_cuota_final($cuota_base, $descuento_porcentaje, $seguro_medico)
{
    $monto_descuento = ($cuota_base * $descuento_porcentaje)/100;
    return $cuota_base - $monto_descuento + $seguro_medico;
}

?>
