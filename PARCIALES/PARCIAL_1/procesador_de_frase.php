<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Análisis de Texto</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Análisis de Texto</h1>
    <?php
    include 'operaciones_cadenas.php';
    $frases = [
        "Hola bienvenido a PHP",
        "Eres mayor de edad",
        "Lista de frutas manzana banana naranja uva pera"
    ];
    ?>
    <table>
        <tr>
            <th>Frase</th>
            <th>Palabras Repetidas</th>
            <th>Frase Capitalizada</th>
        </tr>
        <?php foreach ($frases as $frase): ?>
            <tr>
                <td><?= $frase ?></td>
                <td><?= contar_palabras_repetidas($frase) ?></td>
                <td><?= capitalizar_palabras($frase) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>