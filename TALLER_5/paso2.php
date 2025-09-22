<?php
// 1. Crear un arreglo asociativo de productos con su inventario
$inventario = [
    "laptop" => ["cantidad" => 50, "precio" => 800],
    "smartphone" => ["cantidad" => 100, "precio" => 500],
    "tablet" => ["cantidad" => 30, "precio" => 300],
    "smartwatch" => ["cantidad" => 25, "precio" => 150]
];

// 2. Función para mostrar el inventario
function mostrarInventario($inv) {
    foreach ($inv as $producto => $info) {
        echo "$producto: {$info['cantidad']} unidades, Precio: ${$info['precio']}\n";
    }
}

// 3. Mostrar inventario inicial
echo "Inventario inicial:\n";
mostrarInventario($inventario);

// 4. Función para actualizar el inventario
function actualizarInventario(&$inv, $producto, $cantidad, $precio = null) {
    if (!isset($inv[$producto])) {
        $inv[$producto] = ["cantidad" => $cantidad, "precio" => $precio];
    } else {
        $inv[$producto]["cantidad"] += $cantidad;
        if ($precio !== null) {
            $inv[$producto]["precio"] = $precio;
        }
    }
}

// 5. Actualizar inventario
actualizarInventario($inventario, "laptop", -5);  // Venta de 5 laptops
actualizarInventario($inventario, "smartphone", 50, 450);  // Nuevo lote de smartphones con precio actualizado
actualizarInventario($inventario, "auriculares", 100, 50);  // Nuevo producto

// 6. Mostrar inventario actualizado
echo "\nInventario actualizado:\n";
mostrarInventario($inventario);

// 7. Función para calcular el valor total del inventario
function valorTotalInventario($inv) {
    $total = 0;
    foreach ($inv as $producto => $info) {
        $total += $info['cantidad'] * $info['precio'];
    }
    return $total;
}

// 8. Mostrar valor total del inventario
echo "\nValor total del inventario: $" . valorTotalInventario($inventario) . "\n";

// TAREA: Crea una función que encuentre y retorne el producto con el mayor valor total en inventario
// (cantidad * precio). Muestra el resultado.
function productoConMayorValorTotal($inv) {
    if (empty($inv)) {
        return null;
    }

    $productoMayor = null;
    $valorMayor = -INF;

    foreach ($inv as $producto => $info) {
        $valorTotal = $info['cantidad'] * $info['precio'];
        if ($valorTotal > $valorMayor) {
            $valorMayor = $valorTotal;
            $productoMayor = [
                'producto' => $producto,
                'cantidad' => $info['cantidad'],
                'precio' => $info['precio'],
                'valor_total' => $valorTotal
            ];
        }
    }

    return $productoMayor;
}

$productoMayorValor = productoConMayorValorTotal($inventario);

if ($productoMayorValor !== null) {
    echo "\nProducto con mayor valor total en inventario: {$productoMayorValor['producto']}\n";
    echo "Cantidad: {$productoMayorValor['cantidad']} unidades\n";
    echo "Precio unitario: $ {$productoMayorValor['precio']}\n";
    echo "Valor total: $ {$productoMayorValor['valor_total']}\n";
} else {
    echo "\nNo hay productos en el inventario.\n";
}

?>