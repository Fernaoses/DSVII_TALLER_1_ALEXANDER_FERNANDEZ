<?php
require_once 'Empleado.php';
require_once 'Evaluable.php';

class Empresa {
    /** @var Empleado[] */
    private array $empleados = [];

    public function agregarEmpleado(Empleado $empleado): void {
        $this->empleados[] = $empleado;
    }

    /**
     * @return Empleado[]
     */
    public function listarEmpleados(): array {
        return $this->empleados;
    }

    public function calcularNominaTotal(): float {
        $total = 0;
        foreach ($this->empleados as $empleado) {
            $total += $empleado->getSalarioBase();
        }
        return $total;
    }

    /**
     * Realiza evaluaciones de desempeño para todos los empleados que implementen Evaluable.
     *
     * @return array<string, float> Resultados de la evaluación por nombre de empleado
     */
    public function evaluarEmpleados(): array {
        $resultados = [];
        foreach ($this->empleados as $empleado) {
            if ($empleado instanceof Evaluable) {
                $resultados[$empleado->getNombre()] = $empleado->evaluarDesempenio();
            }
        }
        return $resultados;
    }
}
?>