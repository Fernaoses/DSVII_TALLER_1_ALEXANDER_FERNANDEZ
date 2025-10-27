<?php
require_once "config_mysqli.php";

// Consulta 1: Productos que nunca se han vendido
// Esta consulta usa un LEFT JOIN con la tabla de detalles de ventas para identificar los productos
// que no poseen registros asociados. COALESCE no es necesario porque filtramos por NULL explícitamente.
$productosNuncaVendidosSql = "
    SELECT p.id, p.nombre, p.precio
    FROM productos AS p
    LEFT JOIN detalle_ventas AS dv ON dv.producto_id = p.id
    WHERE dv.producto_id IS NULL
    ORDER BY p.nombre
";

if ($resultado = mysqli_query($conn, $productosNuncaVendidosSql)) {
    echo "<h3>Productos que nunca se han vendido</h3>";
    if (mysqli_num_rows($resultado) === 0) {
        echo "No hay productos sin ventas registradas.<br>";
    }
    while ($fila = mysqli_fetch_assoc($resultado)) {
        echo "Producto: {$fila['nombre']} (ID: {$fila['id']}) - Precio: ${$fila['precio']}<br>";
    }
    mysqli_free_result($resultado);
} else {
    echo "Error en consulta de productos no vendidos: " . mysqli_error($conn) . "<br>";
}

echo "<hr>";

// Consulta 2: Categorías con conteo de productos y valor total del inventario
// Utilizamos LEFT JOIN para asegurar que las categorías sin productos aparezcan. COALESCE controla
// valores NULL al calcular el total del inventario (precio * stock).
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

if ($resultado = mysqli_query($conn, $categoriasInventarioSql)) {
    echo "<h3>Categorías con inventario resumido</h3>";
    while ($fila = mysqli_fetch_assoc($resultado)) {
        echo "Categoría: {$fila['categoria']} - Productos: {$fila['total_productos']} - Valor inventario: ${$fila['valor_total_inventario']}<br>";
    }
    mysqli_free_result($resultado);
} else {
    echo "Error en consulta de inventario por categoría: " . mysqli_error($conn) . "<br>";
}

echo "<hr>";

// Consulta 3: Clientes que han comprado todos los productos de una categoría específica
// Se apoya en un patrón NOT EXISTS para asegurar que no exista ningún producto de la categoría
// sin una compra asociada al cliente. El parámetro $categoriaId se puede ajustar según la necesidad.
$categoriaId = 1; // Cambiar este valor para evaluar otra categoría
$clientesCategoriaSql = "
    SELECT cl.id, cl.nombre, cl.email
    FROM clientes AS cl
    WHERE NOT EXISTS (
        SELECT 1
        FROM productos AS p
        WHERE p.categoria_id = ?
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

if ($stmt = mysqli_prepare($conn, $clientesCategoriaSql)) {
    mysqli_stmt_bind_param($stmt, "i", $categoriaId);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    echo "<h3>Clientes que han comprado todos los productos de la categoría {$categoriaId}</h3>";
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            echo "Cliente: {$fila['nombre']} (Email: {$fila['email']})<br>";
        }
    } else {
        echo "No hay clientes que cumplan con la condición para la categoría seleccionada.<br>";
    }

    mysqli_free_result($resultado);
    mysqli_stmt_close($stmt);
} else {
    echo "Error al preparar la consulta de clientes por categoría: " . mysqli_error($conn) . "<br>";
}

echo "<hr>";

// Consulta 4: Porcentaje de ventas por producto respecto del total general
// Calcula el monto vendido por producto a partir de los detalles de venta y lo compara con el total general.
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

if ($resultado = mysqli_query($conn, $ventasPorProductoSql)) {
    echo "<h3>Porcentaje de ventas por producto</h3>";
    while ($fila = mysqli_fetch_assoc($resultado)) {
        echo "Producto: {$fila['nombre']} - Ventas: ${$fila['total_producto']} - Participación: {$fila['porcentaje_total']}%<br>";
    }
    mysqli_free_result($resultado);
} else {
    echo "Error en consulta de porcentaje de ventas: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?>