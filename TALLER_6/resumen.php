<?php
$archivoRegistros = __DIR__ . '/data/registros.json';
$registros = [];

if (file_exists($archivoRegistros)) {
    $contenido = file_get_contents($archivoRegistros);
    $registros = json_decode($contenido, true);
    if (!is_array($registros)) {
        $registros = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Registros</title>
</head>
<body>
    <h1>Resumen de Registros</h1>
    <p><a href="formulario.php">Volver al formulario</a></p>

    <?php if (empty($registros)): ?>
        <p>No hay registros almacenados.</p>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Fecha de Nacimiento</th>
                    <th>Edad</th>
                    <th>Sitio Web</th>
                    <th>Género</th>
                    <th>Intereses</th>
                    <th>Comentarios</th>
                    <th>Foto de Perfil</th>
                    <th>Fecha de Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $registro): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($registro['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($registro['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($registro['fecha_nacimiento'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($registro['edad'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php if (!empty($registro['sitio_web'])): ?>
                                <a href="<?php echo htmlspecialchars($registro['sitio_web'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Sitio</a>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($registro['genero'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(is_array($registro['intereses'] ?? []) ? implode(', ', $registro['intereses']) : '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($registro['comentarios'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php if (!empty($registro['foto_perfil'])): ?>
                                <img src="<?php echo htmlspecialchars($registro['foto_perfil'], ENT_QUOTES, 'UTF-8'); ?>" width="80" alt="Foto de perfil">
                            <?php else: ?>
                                Sin foto
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($registro['fecha_registro'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>