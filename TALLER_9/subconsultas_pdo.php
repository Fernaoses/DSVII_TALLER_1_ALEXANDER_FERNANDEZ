<?php
require_once "config_pdo.php";

try {
    // Consulta 1: Productos que nunca se han vendido
    $productosNuncaVendidosSql = "
        SELECT p.id, p.nombre, p.precio
        FROM productos AS p
        LEFT JOIN detalle_ventas AS dv ON dv.producto_id = p.id
        WHERE dv.producto_id IS NULL
        ORDER BY p.nombre
    ";

    $stmt = $pdo->query($productosNuncaVendidosSql);
    echo "<h3>Productos que nunca se han vendido</h3>";
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($productos)) {
        echo "No hay productos sin ventas registradas.<br>";
    }
    foreach ($productos as $producto) {
        echo "Producto: {$producto['nombre']} (ID: {$producto['id']}) - Precio: ${$producto['precio']}<br>";
    }

    echo "<hr>";

    // Consulta 2: Categorías con número de productos y valor de inventario
    $categoriasInventarioSql = "
        SELECT
            c.id,
            c.nombre AS categoria,
            COUNT(p.id) AS total_productos,
            COALESCE(SUM(p.precio * COALESCE(p.stock, 0)), 0) AS valor_total_inventario
        FROM categorias AS c
        LEFT JOIN productos AS p ON p.categoria_id = c.id
        GROUP BY c.id, c.nombre
        ORDER BY c.nombre
    ";

    $stmt = $pdo->query($categoriasInventarioSql);
    echo "<h3>Categorías con inventario resumido</h3>";
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $categoria) {
        echo "Categoría: {$categoria['categoria']} - Productos: {$categoria['total_productos']} - Valor inventario: ${$categoria['valor_total_inventario']}<br>";
    }

    echo "<hr>";

    // Consulta 3: Clientes que han comprado todos los productos de una categoría específica
    $categoriaId = 1; // Ajustar según la categoría que se desee evaluar
    $clientesCategoriaSql = "
        SELECT cl.id, cl.nombre, cl.email
        FROM clientes AS cl
        WHERE NOT EXISTS (
            SELECT 1
            FROM productos AS p
            WHERE p.categoria_id = :categoria
              AND NOT EXISTS (
                  SELECT 1
                  FROM detalle_ventas AS dv
                  INNER JOIN ventas AS v ON v.id = dv.venta_id
                  WHERE dv.producto_id = p.id
                    AND v.cliente_id = cl.id
              )
        )
        ORDER BY cl.nombre
    ";

    $stmt = $pdo->prepare($clientesCategoriaSql);
    $stmt->execute([':categoria' => $categoriaId]);

    echo "<h3>Clientes que han comprado todos los productos de la categoría {$categoriaId}</h3>";
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($clientes)) {
        echo "No hay clientes que cumplan con la condición para la categoría seleccionada.<br>";
    }
    foreach ($clientes as $cliente) {
        echo "Cliente: {$cliente['nombre']} (Email: {$cliente['email']})<br>";
    }

    echo "<hr>";

    // Consulta 4: Porcentaje de ventas por producto respecto al total
    $ventasPorProductoSql = "
        SELECT
            p.id,
            p.nombre,
            COALESCE(SUM(dv.cantidad * dv.precio_unitario), 0) AS total_producto,
            CASE
                WHEN totales.total_general > 0 THEN ROUND((COALESCE(SUM(dv.cantidad * dv.precio_unitario), 0) / totales.total_general) * 100, 2)
                ELSE 0
            END AS porcentaje_total
        FROM productos AS p
        LEFT JOIN detalle_ventas AS dv ON dv.producto_id = p.id
        CROSS JOIN (
            SELECT COALESCE(SUM(dv2.cantidad * dv2.precio_unitario), 0) AS total_general
            FROM detalle_ventas AS dv2
        ) AS totales
        GROUP BY p.id, p.nombre, totales.total_general
        ORDER BY porcentaje_total DESC, p.nombre
    ";

    $stmt = $pdo->query($ventasPorProductoSql);
    echo "<h3>Porcentaje de ventas por producto</h3>";
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $venta) {
        echo "Producto: {$venta['nombre']} - Ventas: ${$venta['total_producto']} - Participación: {$venta['porcentaje_total']}%<br>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$pdo = null;
?>