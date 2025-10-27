<?php
require_once "config_pdo.php";

function renderTable(array $headers, array $rows, string $emptyMessage): void
{
    if (empty($rows)) {
        echo "<p>{$emptyMessage}</p>";
        return;
    }

    echo "<table border='1'>";
    echo "<tr>";
    foreach ($headers as $header) {
        echo "<th>" . htmlspecialchars($header) . "</th>";
    }
    echo "</tr>";

    foreach ($rows as $row) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>{$value}</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
}

function formatCurrency($value): string
{
    if ($value === null) {
        return "-";
    }
    return '$' . number_format((float) $value, 2, '.', ',');
}

function formatInteger($value): string
{
    if ($value === null) {
        return "-";
    }
    return number_format((int) $value, 0, '.', ',');
}

function mostrarProductosBajoStock(PDO $pdo): void
{
    try {
        $stmt = $pdo->query("SELECT * FROM vista_productos_bajo_stock");
        $rows = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = [
                formatInteger($row['producto_id']),
                htmlspecialchars($row['producto']),
                htmlspecialchars($row['categoria']),
                formatInteger($row['stock_actual']),
                formatInteger($row['total_unidades_vendidas']),
                formatCurrency($row['total_ingresos_generados']),
                $row['ultima_venta'] ? htmlspecialchars($row['ultima_venta']) : 'Sin ventas',
            ];
        }

        echo "<h3>Productos con bajo stock</h3>";
        renderTable(
            ['ID producto', 'Producto', 'Categoría', 'Stock actual', 'Unidades vendidas', 'Ingresos generados', 'Última venta'],
            $rows,
            'No hay productos con bajo stock registrados.'
        );
    } catch (PDOException $e) {
        echo "<p>Error al consultar productos con bajo stock: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

function mostrarHistorialClientes(PDO $pdo): void
{
    try {
        $stmt = $pdo->query("SELECT * FROM vista_historial_clientes");
        $rows = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = [
                formatInteger($row['cliente_id']),
                htmlspecialchars($row['cliente'] ?? 'Sin registrar'),
                htmlspecialchars($row['email'] ?? 'Sin correo'),
                $row['venta_id'] ? formatInteger($row['venta_id']) : '-',
                $row['fecha_venta'] ? htmlspecialchars($row['fecha_venta']) : '-',
                $row['producto'] ? htmlspecialchars($row['producto']) : '-',
                $row['cantidad'] !== null ? formatInteger((int) $row['cantidad']) : '-',
                $row['precio_unitario'] !== null ? formatCurrency((float) $row['precio_unitario']) : '-',
                $row['subtotal_linea'] !== null ? formatCurrency((float) $row['subtotal_linea']) : '-',
                $row['total_venta'] !== null ? formatCurrency((float) $row['total_venta']) : '-',
                $row['total_cliente'] !== null ? formatCurrency((float) $row['total_cliente']) : '-',
            ];
        }

        echo "<h3>Historial completo de clientes</h3>";
        renderTable(
            [
                'ID Cliente',
                'Cliente',
                'Correo',
                'ID Venta',
                'Fecha de venta',
                'Producto',
                'Cantidad',
                'Precio unitario',
                'Subtotal línea',
                'Total venta',
                'Total cliente',
            ],
            $rows,
            'No hay historial disponible para los clientes.'
        );
    } catch (PDOException $e) {
        echo "<p>Error al consultar el historial de clientes: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

function mostrarRendimientoCategorias(PDO $pdo): void
{
    try {
        $stmt = $pdo->query("SELECT * FROM vista_rendimiento_categorias");
        $rows = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = [
                formatInteger($row['categoria_id']),
                htmlspecialchars($row['categoria']),
                formatInteger($row['total_productos']),
                formatInteger($row['unidades_vendidas']),
                formatCurrency($row['ventas_totales']),
                $row['producto_mas_vendido_id'] !== null
                    ? formatInteger((int) $row['producto_mas_vendido_id'])
                    : '-',
                $row['producto_mas_vendido'] ? htmlspecialchars($row['producto_mas_vendido']) : 'Sin ventas',
                $row['unidades_producto_mas_vendido'] !== null
                    ? formatInteger((int) $row['unidades_producto_mas_vendido'])
                    : '-',
                $row['ventas_producto_mas_vendido'] !== null
                    ? formatCurrency((float) $row['ventas_producto_mas_vendido'])
                    : '-',
            ];
        }

        echo "<h3>Métricas de rendimiento por categoría</h3>";
        renderTable(
            [
                'ID categoría',
                'Categoría',
                'Total productos',
                'Unidades vendidas',
                'Ventas totales',
                'ID producto más vendido',
                'Producto más vendido',
                'Unidades producto más vendido',
                'Ventas producto más vendido',
            ],
            $rows,
            'No hay métricas disponibles para las categorías.'
        );
    } catch (PDOException $e) {
        echo "<p>Error al consultar el rendimiento por categoría: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

function mostrarTendenciasMensuales(PDO $pdo): void
{
    try {
        $stmt = $pdo->query("SELECT * FROM vista_tendencias_ventas_mensuales");
        $rows = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = [
                formatInteger($row['anio']),
                formatInteger($row['mes']),
                htmlspecialchars($row['anio_mes']),
                formatCurrency($row['ventas_totales']),
                formatInteger($row['unidades_vendidas']),
                $row['ventas_mes_anterior'] !== null ? formatCurrency((float) $row['ventas_mes_anterior']) : '-',
                $row['variacion_monto'] !== null ? formatCurrency((float) $row['variacion_monto']) : '-',
                $row['variacion_porcentual'] !== null
                    ? number_format((float) $row['variacion_porcentual'], 2, '.', ',') . '%'
                    : '-',
            ];
        }

        echo "<h3>Tendencias de ventas por mes</h3>";
        renderTable(
            [
                'Año',
                'Mes',
                'Periodo',
                'Ventas totales',
                'Unidades vendidas',
                'Ventas mes anterior',
                'Variación monetaria',
                'Variación porcentual',
            ],
            $rows,
            'No hay ventas registradas para calcular tendencias.'
        );
    } catch (PDOException $e) {
        echo "<p>Error al consultar las tendencias de ventas: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

mostrarProductosBajoStock($pdo);
mostrarHistorialClientes($pdo);
mostrarRendimientoCategorias($pdo);
mostrarTendenciasMensuales($pdo);

$pdo = null;
?>