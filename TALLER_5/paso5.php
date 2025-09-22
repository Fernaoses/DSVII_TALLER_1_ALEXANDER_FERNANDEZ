<?php
// 1. Crear un string JSON con datos de una tienda en línea
$jsonDatos = '
{
    "tienda": "ElectroTech",
    "productos": [
        {"id": 1, "nombre": "Laptop Gamer", "precio": 1200, "categorias": ["electrónica", "computadoras"]},
        {"id": 2, "nombre": "Smartphone 5G", "precio": 800, "categorias": ["electrónica", "celulares"]},
        {"id": 3, "nombre": "Auriculares Bluetooth", "precio": 150, "categorias": ["electrónica", "accesorios"]},
        {"id": 4, "nombre": "Smart TV 4K", "precio": 700, "categorias": ["electrónica", "televisores"]},
        {"id": 5, "nombre": "Tablet", "precio": 300, "categorias": ["electrónica", "computadoras"]}
    ],
    "clientes": [
        {"id": 101, "nombre": "Ana López", "email": "ana@example.com"},
        {"id": 102, "nombre": "Carlos Gómez", "email": "carlos@example.com"},
        {"id": 103, "nombre": "María Rodríguez", "email": "maria@example.com"}
    ]
}
';

// 2. Convertir el JSON a un arreglo asociativo de PHP
$tiendaData = json_decode($jsonDatos, true);

// 3. Función para imprimir los productos
function imprimirProductos($productos) {
    foreach ($productos as $producto) {
        echo "{$producto['nombre']} - \$" . number_format($producto['precio'], 2) . " - Categorías: " . implode(", ", $producto['categorias']) . "\n";
    }
}

echo "Productos de {$tiendaData['tienda']}:\n";
imprimirProductos($tiendaData['productos']);

// 4. Calcular el valor total del inventario
$valorTotal = array_reduce($tiendaData['productos'], function($total, $producto) {
    return $total + $producto['precio'];
}, 0);

echo "\nValor total del inventario: \$" . number_format($valorTotal, 2) . "\n";

// 5. Encontrar el producto más caro
$productoMasCaro = array_reduce($tiendaData['productos'], function($max, $producto) {
    return ($producto['precio'] > $max['precio']) ? $producto : $max;
}, $tiendaData['productos'][0]);

echo "\nProducto más caro: {$productoMasCaro['nombre']} (\$" . number_format($productoMasCaro['precio'], 2) . ")\n";

// 6. Filtrar productos por categoría
function filtrarPorCategoria($productos, $categoria) {
    return array_filter($productos, function($producto) use ($categoria) {
        return in_array($categoria, $producto['categorias']);
    });
}

$productosDeComputadoras = filtrarPorCategoria($tiendaData['productos'], "computadoras");
echo "\nProductos en la categoría 'computadoras':\n";
imprimirProductos($productosDeComputadoras);

// 7. Agregar un nuevo producto
$nuevoProducto = [
    "id" => 6,
    "nombre" => "Smartwatch",
    "precio" => 250,
    "categorias" => ["electrónica", "accesorios", "wearables"]
];
$tiendaData['productos'][] = $nuevoProducto;

// 8. Convertir el arreglo actualizado de vuelta a JSON
$jsonActualizado = json_encode($tiendaData, JSON_PRETTY_PRINT);
echo "\nDatos actualizados de la tienda (JSON):\n$jsonActualizado\n";

// TAREA: Implementa una función que genere un resumen de ventas
// Crea un arreglo de ventas (producto_id, cliente_id, cantidad, fecha)
// y genera un informe que muestre:
// - Total de ventas
// - Producto más vendido
// - Cliente que más ha comprado
$ventas = [
    ["producto_id" => 1, "cliente_id" => 101, "cantidad" => 2, "fecha" => "2024-06-15"],
    ["producto_id" => 3, "cliente_id" => 102, "cantidad" => 3, "fecha" => "2024-06-18"],
    ["producto_id" => 2, "cliente_id" => 103, "cantidad" => 1, "fecha" => "2024-07-02"],
    ["producto_id" => 4, "cliente_id" => 101, "cantidad" => 1, "fecha" => "2024-07-03"],
    ["producto_id" => 1, "cliente_id" => 103, "cantidad" => 1, "fecha" => "2024-07-05"],
    ["producto_id" => 5, "cliente_id" => 102, "cantidad" => 2, "fecha" => "2024-07-06"]
];

function generarResumenVentas($ventas, $productos, $clientes) {
    $indiceProductos = [];
    foreach ($productos as $producto) {
        $indiceProductos[$producto['id']] = $producto;
    }

    $indiceClientes = [];
    foreach ($clientes as $cliente) {
        $indiceClientes[$cliente['id']] = $cliente;
    }

    $totalVentas = 0;
    $ventasPorProducto = [];
    $ventasPorCliente = [];

    foreach ($ventas as $venta) {
        $productoId = $venta['producto_id'];
        $clienteId = $venta['cliente_id'];

        if (!isset($indiceProductos[$productoId]) || !isset($indiceClientes[$clienteId])) {
            continue;
        }

        $cantidad = $venta['cantidad'];
        $montoVenta = $indiceProductos[$productoId]['precio'] * $cantidad;

        $totalVentas += $montoVenta;
        $ventasPorProducto[$productoId] = ($ventasPorProducto[$productoId] ?? 0) + $cantidad;
        $ventasPorCliente[$clienteId] = ($ventasPorCliente[$clienteId] ?? 0) + $montoVenta;
    }

    $productoMasVendidoId = null;
    $maxCantidadVendida = 0;
    foreach ($ventasPorProducto as $productoId => $cantidadVendida) {
        if ($cantidadVendida > $maxCantidadVendida) {
            $maxCantidadVendida = $cantidadVendida;
            $productoMasVendidoId = $productoId;
        }
    }

    $clienteMasCompradorId = null;
    $maxMontoComprado = 0;
    foreach ($ventasPorCliente as $clienteId => $montoComprado) {
        if ($montoComprado > $maxMontoComprado) {
            $maxMontoComprado = $montoComprado;
            $clienteMasCompradorId = $clienteId;
        }
    }

    return [
        'totalVentas' => $totalVentas,
        'productoMasVendido' => $productoMasVendidoId ? [
            'id' => $productoMasVendidoId,
            'nombre' => $indiceProductos[$productoMasVendidoId]['nombre'],
            'cantidad' => $maxCantidadVendida
        ] : null,
        'clienteMasComprador' => $clienteMasCompradorId ? [
            'id' => $clienteMasCompradorId,
            'nombre' => $indiceClientes[$clienteMasCompradorId]['nombre'],
            'email' => $indiceClientes[$clienteMasCompradorId]['email'],
            'monto' => $maxMontoComprado
        ] : null
    ];
}

$resumenVentas = generarResumenVentas($ventas, $tiendaData['productos'], $tiendaData['clientes']);

echo "\nResumen de ventas:\n";
echo "Total de ventas: \$" . number_format($resumenVentas['totalVentas'], 2) . "\n";

if ($resumenVentas['productoMasVendido']) {
    $producto = $resumenVentas['productoMasVendido'];
    echo "Producto más vendido: {$producto['nombre']} ({$producto['cantidad']} unidades)\n";
}

if ($resumenVentas['clienteMasComprador']) {
    $cliente = $resumenVentas['clienteMasComprador'];
    echo "Cliente que más ha comprado: {$cliente['nombre']} (\$" . number_format($cliente['monto'], 2) . " en compras)\n";
}

?>