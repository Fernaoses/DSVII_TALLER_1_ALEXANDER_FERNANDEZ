<?php
require_once 'Gerente.php';
require_once 'Desarrollador.php';
require_once 'Empresa.php';

$empresa = new Empresa();

$gerente = new Gerente("Ana Pérez", 1, 5000, "Ventas");
$gerente->asignarBono(500);

$desarrollador = new Desarrollador("Luis Gómez", 2, 4000, "PHP", "senior");

$empresa->agregarEmpleado($gerente);
$empresa->agregarEmpleado($desarrollador);

echo "Listado de empleados:\n";
foreach ($empresa->listarEmpleados() as $empleado) {
    echo "- {$empleado->getNombre()} (Salario: {$empleado->getSalarioBase()})\n";
}

echo "\nNómina total: " . $empresa->calcularNominaTotal() . "\n";

echo "\nEvaluaciones de desempeño:\n";
foreach ($empresa->evaluarEmpleados() as $nombre => $resultado) {
    echo "- $nombre: $resultado\n";
}
?>