<?php
require_once "config_mysqli.php";

/**
 * Libera los conjuntos de resultados pendientes después de ejecutar un procedimiento almacenado.
 */
function limpiarResultadosProcedimiento(mysqli $conn): void
{
    while (mysqli_more_results($conn)) {
        mysqli_next_result($conn);
        if ($resultado = mysqli_store_result($conn)) {
            mysqli_free_result($resultado);
        }
    }
}

// Función para registrar una venta
function registrarVenta($conn, $cliente_id, $producto_id, $cantidad)
{
    $query = "CALL sp_registrar_venta(?, ?, ?, @venta_id)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iii", $cliente_id, $producto_id, $cantidad);

    try {
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }

        mysqli_stmt_close($stmt);
        $stmt = null;
        limpiarResultadosProcedimiento($conn);

        $result = mysqli_query($conn, "SELECT @venta_id as venta_id");
        if (!$result) {
            throw new Exception(mysqli_error($conn));
        }

        $row = mysqli_fetch_assoc($result);
        if ($row) {
            echo "Venta registrada con éxito. ID de venta: " . $row['venta_id'] . "<br>";
        }

        mysqli_free_result($result);
    } catch (Exception $e) {
        echo "Error al registrar la venta: " . $e->getMessage() . "<br>";
    } finally {
        if ($stmt instanceof mysqli_stmt) {
            mysqli_stmt_close($stmt);
        }
        limpiarResultadosProcedimiento($conn);
    }
}

// Función para obtener estadísticas de cliente
function obtenerEstadisticasCliente($conn, $cliente_id)
{
    $query = "CALL sp_estadisticas_cliente(?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $cliente_id);
    $result = null;

    try {
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
            $estadisticas = mysqli_fetch_assoc($result);
            if ($estadisticas) {
                echo "<h3>Estadísticas del Cliente</h3>";
                echo "Nombre: " . htmlspecialchars($estadisticas['nombre']) . "<br>";
                echo "Membresía: " . htmlspecialchars($estadisticas['nivel_membresia']) . "<br>";
                echo "Total compras: " . (int) $estadisticas['total_compras'] . "<br>";
                echo "Total gastado: $" . number_format((float) $estadisticas['total_gastado'], 2) . "<br>";
                echo "Promedio de compra: $" . number_format((float) $estadisticas['promedio_compra'], 2) . "<br>";
                echo "Últimos productos: " . htmlspecialchars($estadisticas['ultimos_productos']) . "<br>";
            } else {
                echo "No se encontraron estadísticas para el cliente especificado.<br>";
            }
        }
    } catch (Exception $e) {
        echo "Error al obtener estadísticas del cliente: " . $e->getMessage() . "<br>";
    } finally {
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        if ($stmt instanceof mysqli_stmt) {
            mysqli_stmt_close($stmt);
        }
        limpiarResultadosProcedimiento($conn);
    }
}

// Función para procesar una devolución de producto
function procesarDevolucion($conn, $venta_id, $detalle_id, $cantidad, $motivo)
{
    $query = "CALL sp_procesar_devolucion(?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iiis", $venta_id, $detalle_id, $cantidad, $motivo);
    $result = null;

    try {
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        if ($result && ($info = mysqli_fetch_assoc($result))) {
            echo "<h3>Devolución procesada</h3>";
            echo "Venta: " . (int) $info['venta_id'] . "<br>";
            echo "Detalle: " . (int) $info['detalle_id'] . "<br>";
            echo "Producto: " . (int) $info['producto_id'] . "<br>";
            echo "Cantidad devuelta: " . (int) $info['cantidad_devuelta'] . "<br>";
            echo "Stock actualizado: " . (int) $info['stock_actual'] . "<br>";
        } else {
            echo "La devolución se procesó correctamente.<br>";
        }
    } catch (Exception $e) {
        echo "Error al procesar la devolución: " . $e->getMessage() . "<br>";
    } finally {
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        if ($stmt instanceof mysqli_stmt) {
            mysqli_stmt_close($stmt);
        }
        limpiarResultadosProcedimiento($conn);
    }
}

// Función para aplicar descuentos por historial de compras
function aplicarDescuentoHistorial($conn, $cliente_id, $venta_id)
{
    $query = "CALL sp_aplicar_descuento_historial(?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $cliente_id, $venta_id);
    $result = null;

    try {
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        if ($result && ($datos = mysqli_fetch_assoc($result))) {
            echo "<h3>Descuento aplicado</h3>";
            echo "Cliente: " . (int) $datos['cliente_id'] . "<br>";
            echo "Venta: " . (int) $datos['venta_id'] . "<br>";
            echo "Historial acumulado: $" . number_format((float) $datos['total_historial'], 2) . "<br>";
            echo "Descuento aplicado: " . number_format((float) $datos['porcentaje_descuento'] * 100, 2) . "%<br>";
            echo "Total original: $" . number_format((float) $datos['total_original'], 2) . "<br>";
            echo "Total con descuento: $" . number_format((float) $datos['total_con_descuento'], 2) . "<br>";
        } else {
            echo "La venta no requiere descuento adicional.<br>";
        }
    } catch (Exception $e) {
        echo "Error al aplicar el descuento: " . $e->getMessage() . "<br>";
    } finally {
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        if ($stmt instanceof mysqli_stmt) {
            mysqli_stmt_close($stmt);
        }
        limpiarResultadosProcedimiento($conn);
    }
}

// Función para generar reporte de bajo stock
function reporteBajoStock($conn, $umbral, $objetivo)
{
    $query = "CALL sp_reporte_bajo_stock(?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $umbral, $objetivo);
    $result = null;

    try {
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            echo "<h3>Productos con bajo stock</h3>";
            echo "<table border='1' cellpadding='4' cellspacing='0'>";
            echo "<tr><th>ID</th><th>Producto</th><th>Stock actual</th><th>Sugerido</th><th>Umbral recomendado</th></tr>";
            while ($fila = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . (int) $fila['producto_id'] . "</td>";
                echo "<td>" . htmlspecialchars($fila['producto']) . "</td>";
                echo "<td>" . (int) $fila['stock_actual'] . "</td>";
                echo "<td>" . (int) $fila['sugerido_reposicion'] . "</td>";
                echo "<td>" . (isset($fila['umbral_reorden']) ? (int) $fila['umbral_reorden'] : '-') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No hay productos por debajo del umbral especificado.<br>";
        }
    } catch (Exception $e) {
        echo "Error al generar el reporte de stock: " . $e->getMessage() . "<br>";
    } finally {
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        if ($stmt instanceof mysqli_stmt) {
            mysqli_stmt_close($stmt);
        }
        limpiarResultadosProcedimiento($conn);
    }
}

// Función para calcular comisiones por ventas
function calcularComisiones($conn, $empleado_id, $fecha_inicio, $fecha_fin, $tasa_monto, $tasa_cantidad)
{
    $query = "CALL sp_calcular_comisiones(?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "issdd", $empleado_id, $fecha_inicio, $fecha_fin, $tasa_monto, $tasa_cantidad);
    $result = null;

    try {
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        if ($result && ($resumen = mysqli_fetch_assoc($result))) {
            echo "<h3>Comisiones calculadas</h3>";
            echo "Empleado: " . (int) $resumen['empleado_id'] . "<br>";
            echo "Ventas realizadas: " . (int) $resumen['total_ventas'] . "<br>";
            echo "Monto total: $" . number_format((float) $resumen['monto_total'], 2) . "<br>";
            echo "Unidades vendidas: " . (int) $resumen['unidades_vendidas'] . "<br>";
            echo "Comisión por monto: $" . number_format((float) $resumen['comision_por_monto'], 2) . "<br>";
            echo "Comisión por cantidad: $" . number_format((float) $resumen['comision_por_cantidad'], 2) . "<br>";
            echo "Comisión total: $" . number_format((float) $resumen['comision_total'], 2) . "<br>";
        } else {
            echo "No se encontraron ventas en el período indicado.<br>";
        }
    } catch (Exception $e) {
        echo "Error al calcular las comisiones: " . $e->getMessage() . "<br>";
    } finally {
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        if ($stmt instanceof mysqli_stmt) {
            mysqli_stmt_close($stmt);
        }
        limpiarResultadosProcedimiento($conn);
    }
}

$salida = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    ob_start();

    switch ($accion) {
        case 'registrar_venta':
            $clienteId = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
            $productoId = filter_input(INPUT_POST, 'producto_id', FILTER_VALIDATE_INT);
            $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);

            if ($clienteId === null || $clienteId === false ||
                $productoId === null || $productoId === false ||
                $cantidad === null || $cantidad === false || $cantidad <= 0) {
                echo "<p class='error'>Por favor ingresa datos válidos para la venta.</p>";
            } else {
                registrarVenta($conn, $clienteId, $productoId, $cantidad);
            }
            break;

        case 'estadisticas_cliente':
            $clienteId = filter_input(INPUT_POST, 'cliente_id_estadisticas', FILTER_VALIDATE_INT);
            if ($clienteId === null || $clienteId === false) {
                echo "<p class='error'>Debes proporcionar un ID de cliente válido.</p>";
            } else {
                obtenerEstadisticasCliente($conn, $clienteId);
            }
            break;

        case 'procesar_devolucion':
            $ventaId = filter_input(INPUT_POST, 'venta_id', FILTER_VALIDATE_INT);
            $detalleId = filter_input(INPUT_POST, 'detalle_id', FILTER_VALIDATE_INT);
            $cantidad = filter_input(INPUT_POST, 'cantidad_devuelta', FILTER_VALIDATE_INT);
            $motivo = trim($_POST['motivo'] ?? '');

            if ($ventaId === null || $ventaId === false ||
                $detalleId === null || $detalleId === false ||
                $cantidad === null || $cantidad === false || $cantidad <= 0 ||
                $motivo === '') {
                echo "<p class='error'>Todos los campos de la devolución son obligatorios y deben ser válidos.</p>";
            } else {
                procesarDevolucion($conn, $ventaId, $detalleId, $cantidad, $motivo);
            }
            break;

        case 'aplicar_descuento':
            $clienteId = filter_input(INPUT_POST, 'cliente_descuento', FILTER_VALIDATE_INT);
            $ventaId = filter_input(INPUT_POST, 'venta_descuento', FILTER_VALIDATE_INT);

            if ($clienteId === null || $clienteId === false ||
                $ventaId === null || $ventaId === false) {
                echo "<p class='error'>Los datos para aplicar el descuento no son válidos.</p>";
            } else {
                aplicarDescuentoHistorial($conn, $clienteId, $ventaId);
            }
            break;

        case 'reporte_stock':
            $umbral = filter_input(INPUT_POST, 'umbral', FILTER_VALIDATE_INT);
            $objetivo = filter_input(INPUT_POST, 'objetivo', FILTER_VALIDATE_INT);

            if ($umbral === null || $umbral === false || $umbral < 0 ||
                $objetivo === null || $objetivo === false || $objetivo <= 0) {
                echo "<p class='error'>Indica valores numéricos válidos para el umbral y la reposición.</p>";
            } else {
                reporteBajoStock($conn, $umbral, $objetivo);
            }
            break;

        case 'calcular_comisiones':
            $empleadoId = filter_input(INPUT_POST, 'empleado_id', FILTER_VALIDATE_INT);
            $fechaInicio = $_POST['fecha_inicio'] ?? '';
            $fechaFin = $_POST['fecha_fin'] ?? '';
            $tasaMonto = filter_input(INPUT_POST, 'tasa_monto', FILTER_VALIDATE_FLOAT);
            $tasaCantidad = filter_input(INPUT_POST, 'tasa_cantidad', FILTER_VALIDATE_FLOAT);

            if ($empleadoId === null || $empleadoId === false ||
                $fechaInicio === '' || $fechaFin === '' ||
                $tasaMonto === null || $tasaMonto === false || $tasaMonto < 0 ||
                $tasaCantidad === null || $tasaCantidad === false || $tasaCantidad < 0) {
                echo "<p class='error'>Verifica los datos para calcular la comisión.</p>";
            } else {
                calcularComisiones($conn, $empleadoId, $fechaInicio, $fechaFin, $tasaMonto, $tasaCantidad);
            }
            break;

        default:
            echo "<p class='error'>Acción no reconocida.</p>";
            break;
    }

    $salida = trim(ob_get_clean());
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Procedimientos almacenados - MySQLi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        h1 { margin-bottom: 0.5rem; }
        form { border: 1px solid #ccc; padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; }
        label { display: block; margin-top: 0.5rem; }
        input, textarea { width: 100%; padding: 0.5rem; margin-top: 0.25rem; }
        button { margin-top: 0.75rem; padding: 0.5rem 1rem; }
        .resultado { background: #f9f9f9; border: 1px solid #ddd; padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; }
        .error { color: #b30000; font-weight: bold; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; }
    </style>
</head>
<body>
    <h1>Procedimientos almacenados (MySQLi)</h1>
    <p>Completa los formularios para ejecutar los procedimientos almacenados del Taller 9.</p>

    <?php if ($salida !== ''): ?>
        <section class="resultado">
            <?php echo $salida; ?>
        </section>
    <?php endif; ?>

    <section class="grid">
        <form method="post">
            <h2>Registrar venta</h2>
            <input type="hidden" name="accion" value="registrar_venta">
            <label for="cliente_id">ID Cliente</label>
            <input type="number" name="cliente_id" id="cliente_id" min="1" required>
            <label for="producto_id">ID Producto</label>
            <input type="number" name="producto_id" id="producto_id" min="1" required>
            <label for="cantidad">Cantidad</label>
            <input type="number" name="cantidad" id="cantidad" min="1" required>
            <button type="submit">Registrar</button>
        </form>

        <form method="post">
            <h2>Estadísticas de cliente</h2>
            <input type="hidden" name="accion" value="estadisticas_cliente">
            <label for="cliente_id_estadisticas">ID Cliente</label>
            <input type="number" name="cliente_id_estadisticas" id="cliente_id_estadisticas" min="1" required>
            <button type="submit">Consultar</button>
        </form>

        <form method="post">
            <h2>Procesar devolución</h2>
            <input type="hidden" name="accion" value="procesar_devolucion">
            <label for="venta_id">ID Venta</label>
            <input type="number" name="venta_id" id="venta_id" min="1" required>
            <label for="detalle_id">ID Detalle</label>
            <input type="number" name="detalle_id" id="detalle_id" min="1" required>
            <label for="cantidad_devuelta">Cantidad a devolver</label>
            <input type="number" name="cantidad_devuelta" id="cantidad_devuelta" min="1" required>
            <label for="motivo">Motivo</label>
            <textarea name="motivo" id="motivo" rows="3" required></textarea>
            <button type="submit">Procesar devolución</button>
        </form>

        <form method="post">
            <h2>Aplicar descuento</h2>
            <input type="hidden" name="accion" value="aplicar_descuento">
            <label for="cliente_descuento">ID Cliente</label>
            <input type="number" name="cliente_descuento" id="cliente_descuento" min="1" required>
            <label for="venta_descuento">ID Venta</label>
            <input type="number" name="venta_descuento" id="venta_descuento" min="1" required>
            <button type="submit">Aplicar descuento</button>
        </form>

        <form method="post">
            <h2>Reporte bajo stock</h2>
            <input type="hidden" name="accion" value="reporte_stock">
            <label for="umbral">Umbral (stock mínimo)</label>
            <input type="number" name="umbral" id="umbral" min="0" required>
            <label for="objetivo">Cantidad sugerida</label>
            <input type="number" name="objetivo" id="objetivo" min="1" required>
            <button type="submit">Generar reporte</button>
        </form>

        <form method="post">
            <h2>Calcular comisiones</h2>
            <input type="hidden" name="accion" value="calcular_comisiones">
            <label for="empleado_id">ID Empleado</label>
            <input type="number" name="empleado_id" id="empleado_id" min="1" required>
            <label for="fecha_inicio">Fecha inicio</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" required>
            <label for="fecha_fin">Fecha fin</label>
            <input type="date" name="fecha_fin" id="fecha_fin" required>
            <label for="tasa_monto">Tasa por monto (%)</label>
            <input type="number" name="tasa_monto" id="tasa_monto" min="0" step="0.01" required>
            <label for="tasa_cantidad">Tasa por cantidad ($)</label>
            <input type="number" name="tasa_cantidad" id="tasa_cantidad" min="0" step="0.01" required>
            <button type="submit">Calcular</button>
        </form>
    </section>

    <p>Los formularios anteriores ejecutan los procedimientos almacenados creados en <code>procedimientos.sql</code>. Asegúrate de tener datos de prueba en la base de datos antes de usarlos.</p>
</body>
</html>
<?php
mysqli_close($conn);
?>