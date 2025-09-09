<?php
include 'funciones_gimnasio.php';

$membresias = [
    'basico' => 80,
    'premium' => 120,
    'vip' => 180,
    'familia' => 250,
    'corporativo' => 300
];

$miembros = [
    'Ana' => ['membresia' => 'basico', 'antiguedad_meses' => 2],
    'Luis' => ['membresia' => 'premium', 'antiguedad_meses' => 5],
    'Marta' => ['membresia' => 'vip', 'antiguedad_meses' => 15],
    'Carlos' => ['membresia' => 'familia', 'antiguedad_meses' => 30],
    'Sofia' => ['membresia' => 'corporativo', 'antiguedad_meses' => 12]
];

$subtotal = 0;
foreach ($miembros as $membresia => $datos) {
    $precio = $membresias[$datos['membresia']];
    $subtotal += $precio;
}

$descuento = calcular_descuento($subtotal);
$total = $subtotal - $descuento;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen de Membresias</title>
</head>
<body>
    <h1>Resumen de Membresias</h1>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Miembros</th>
            <th>Membresia</th>
            <th>Antigüedad (meses)</th>
            <th>Descuento aplicado</th>
            <th>Seguro medico</th>
            <th>Cuota final</th>
        </tr>
        <?php foreach ($miembros as $membresia => $datos): ?>
            <?php if ($datos['antiguedad_meses'] > 0): ?>
                <tr>
                    <td><?php echo $membresia; ?></td>
                    <td><?php echo $datos['membresia']; ?></td>
                    <td><?php echo $datos['antiguedad_meses']; ?></td>
                    <td><?php echo calcular_descuento($datos['antiguedad_meses']) * 100; ?>%</td>
                    <td><?php echo calcular_seguro_medico($membresias[$datos['membresia']]); ?></td>
                    <td><?php echo calcular_cuota_final($membresias[$datos['membresia']], calcular_descuento($datos['antiguedad_meses']) * 100, calcular_seguro_medico($membresias[$datos['membresia']])); ?></td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>    
    <p>Subtotal: <?php echo $subtotal; ?></p>
    <p>Descuento: <?php echo $descuento; ?></p>
    <p>Total a pagar: <?php echo $total; ?></p>
</body>
</html>


<!-- Para poder abrir este archivo, usa la siguiente URL:
http://localhost/TALLERES/PARCIALES/PARCIAL_1/gestionar_membresias.php -->