<?php
require_once __DIR__ . '/../database.php';

$pdo = get_connection();
$errores = [];
$nombre = '';
$categoria = '';
$precio = '';
$cantidad = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $cantidad = trim($_POST['cantidad'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if ($categoria === '') {
        $errores[] = 'La categoría es obligatoria.';
    }

    if ($precio === '' || !is_numeric($precio) || (float)$precio < 0) {
        $errores[] = 'El precio debe ser un número mayor o igual a 0.';
    }

    $cantidadValida = filter_var($cantidad, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($cantidad === '' || $cantidadValida === false) {
        $errores[] = 'La cantidad debe ser un número entero mayor o igual a 0.';
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare('INSERT INTO productos (nombre, categoria, precio, cantidad) VALUES (:nombre, :categoria, :precio, :cantidad)');
        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':categoria', $categoria);
        $stmt->bindValue(':precio', $precio);
        $stmt->bindValue(':cantidad', $cantidadValida, PDO::PARAM_INT);
        $stmt->execute();

        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar producto - TechParts</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        header { background: #0d6efd; color: #fff; padding: 1rem 2rem; }
        main { padding: 2rem; }
        form { background: #fff; padding: 1.5rem; border-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); max-width: 600px; }
        label { display: block; margin-top: 1rem; }
        input { width: 100%; padding: 0.5rem; margin-top: 0.25rem; }
        button { margin-top: 1rem; padding: 0.5rem 1rem; background: #198754; color: #fff; border: none; border-radius: 4px; }
        a { color: #0d6efd; text-decoration: none; }
        .errores { background: #f8d7da; color: #842029; padding: 0.75rem; border-radius: 4px; }
    </style>
</head>
<body>
<header>
    <h1>Registrar producto</h1>
</header>
<main>
    <p><a href="index.php">← Volver al listado</a></p>

    <?php if ($errores): ?>
        <div class="errores">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <label>Nombre
            <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
        </label>
        <label>Categoría
            <input type="text" name="categoria" value="<?= htmlspecialchars($categoria) ?>" required>
        </label>
        <label>Precio
            <input type="number" name="precio" value="<?= htmlspecialchars($precio) ?>" step="0.01" min="0" required>
        </label>
        <label>Cantidad
            <input type="number" name="cantidad" value="<?= htmlspecialchars($cantidad) ?>" min="0" required>
        </label>
        <button type="submit">Guardar</button>
    </form>
</main>
</body>
</html>