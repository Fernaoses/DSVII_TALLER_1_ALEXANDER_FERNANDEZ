<?php
session_start();
$errores = $_SESSION['errores'] ?? [];
$datos = $_SESSION['datos'] ?? [];
unset($_SESSION['errores'], $_SESSION['datos']);

$interesesSeleccionados = $datos['intereses'] ?? [];
if (!is_array($interesesSeleccionados)) {
    $interesesSeleccionados = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Registro Avanzado</title>
</head>
<body>
    <h2>Formulario de Registro Avanzado</h2>

    <?php if (!empty($errores)): ?>
        <div style="color: red;">
            <h3>Se encontraron errores:</h3>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="procesar.php" method="POST" enctype="multipart/form-data">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($datos['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($datos['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($datos['fecha_nacimiento'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

        <label for="sitio_web">Sitio Web:</label>
        <input type="url" id="sitio_web" name="sitio_web" value="<?php echo htmlspecialchars($datos['sitio_web'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br><br>

        <label for="genero">Género:</label>
        <select id="genero" name="genero">
            <?php $generoSeleccionado = $datos['genero'] ?? ''; ?>
            <option value="masculino" <?php echo $generoSeleccionado === 'masculino' ? 'selected' : ''; ?>>Masculino</option>
            <option value="femenino" <?php echo $generoSeleccionado === 'femenino' ? 'selected' : ''; ?>>Femenino</option>
            <option value="otro" <?php echo $generoSeleccionado === 'otro' ? 'selected' : ''; ?>>Otro</option>
        </select><br><br>

        <label>Intereses:</label><br>
        <input type="checkbox" id="deportes" name="intereses[]" value="deportes" <?php echo in_array('deportes', $interesesSeleccionados, true) ? 'checked' : ''; ?>>
        <label for="deportes">Deportes</label><br>
        <input type="checkbox" id="musica" name="intereses[]" value="musica" <?php echo in_array('musica', $interesesSeleccionados, true) ? 'checked' : ''; ?>>
        <label for="musica">Música</label><br>
        <input type="checkbox" id="lectura" name="intereses[]" value="lectura" <?php echo in_array('lectura', $interesesSeleccionados, true) ? 'checked' : ''; ?>>
        <label for="lectura">Lectura</label><br><br>

        <label for="comentarios">Comentarios:</label><br>
        <textarea id="comentarios" name="comentarios" rows="4" cols="50"><?php echo htmlspecialchars($datos['comentarios'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea><br><br>

        <label for="foto_perfil">Foto de Perfil:</label>
        <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*"><br><br>

        <input type="submit" value="Enviar">
        <input type="reset" value="Limpiar">
    </form>

    <p><a href="resumen.php">Ver resumen de registros</a></p>
</body>
</html>