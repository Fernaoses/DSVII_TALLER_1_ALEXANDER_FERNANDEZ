<?php
require_once __DIR__ . '/../database.php';

$pdo = get_connection();

$stmt = $pdo->query('SELECT * FROM productos ORDER BY fecha_registro DESC');
$productos = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mini Biblioteca Personal</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f4; }
        header { background: #0d6efd; color: #fff; padding: 1rem 2rem; }
        main { padding: 2rem; }
        a.button { display: inline-block; padding: 0.5rem 1rem; background: #198754; color: #fff; text-decoration: none; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: #fff; }
        th, td { padding: 0.75rem; border: 1px solid #ddd; text-align: left; }
        th { background: #e9ecef; }
        .actions a { margin-right: 0.5rem; }
    </style>
</head>
<body>
<header>
    <h1>Listado de productos</h1>
</header>
<main>
    <a class="button" href="crear.php">Registrar producto</a>

        <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Fecha de registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($productos)): ?>
                <tr><td colspan="7">No hay productos registrados.</td></tr>
            <?php endif; ?>
            <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?= htmlspecialchars($producto['id']) ?></td>
                    <td><?= htmlspecialchars($producto['nombre']) ?></td>
                    <td><?= htmlspecialchars($producto['categoria']) ?></td>
                    <td>$<?= number_format((float)$producto['precio'], 2) ?></td>
                    <td><?= htmlspecialchars($producto['cantidad']) ?></td>
                    <td><?= htmlspecialchars($producto['fecha_registro']) ?></td>
                    <td class="actions">
                        <a href="editar.php?id=<?= urlencode((string)$producto['id']) ?>">Editar</a>
                        <a href="eliminar.php?id=<?= urlencode((string)$producto['id']) ?>" onclick="return confirm('¿Deseas eliminar este producto?');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>