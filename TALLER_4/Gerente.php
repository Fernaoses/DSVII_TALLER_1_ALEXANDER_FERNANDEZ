<?php
require_once 'Empleado.php';
require_once 'Evaluable.php';

class Gerente extends Empleado implements Evaluable {
    private string $departamento;

    public function __construct(string $nombre, int $idEmpleado, float $salarioBase, string $departamento) {
        parent::__construct($nombre, $idEmpleado, $salarioBase);
        $this->departamento = $departamento;
    }

    public function getDepartamento(): string {
        return $this->departamento;
    }

    public function setDepartamento(string $departamento): void {
        $this->departamento = $departamento;
    }

    public function asignarBono(float $monto): void {
        $this->salarioBase += $monto;
    }

    public function evaluarDesempenio(): float {
        // Lógica simple de evaluación para gerentes
        return 90.0;
    }
}
?>