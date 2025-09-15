<?php
require_once 'Empleado.php';
require_once 'Evaluable.php';

class Desarrollador extends Empleado implements Evaluable {
    private string $lenguajePrincipal;
    private string $nivelExperiencia;

    public function __construct(string $nombre, int $idEmpleado, float $salarioBase, string $lenguajePrincipal, string $nivelExperiencia) {
        parent::__construct($nombre, $idEmpleado, $salarioBase);
        $this->lenguajePrincipal = $lenguajePrincipal;
        $this->nivelExperiencia = $nivelExperiencia;
    }

    public function getLenguajePrincipal(): string {
        return $this->lenguajePrincipal;
    }

    public function setLenguajePrincipal(string $lenguajePrincipal): void {
        $this->lenguajePrincipal = $lenguajePrincipal;
    }

    public function getNivelExperiencia(): string {
        return $this->nivelExperiencia;
    }

    public function setNivelExperiencia(string $nivelExperiencia): void {
        $this->nivelExperiencia = $nivelExperiencia;
    }

    public function evaluarDesempenio(): float {
        switch (strtolower($this->nivelExperiencia)) {
            case 'junior':
                return 70.0;
            case 'senior':
                return 95.0;
            default:
                return 85.0; // intermedio u otros
        }
    }
}
?>