<?php
class Empleado {
    protected string $nombre;
    protected int $idEmpleado;
    protected float $salarioBase;

    public function __construct(string $nombre, int $idEmpleado, float $salarioBase) {
        $this->nombre = $nombre;
        $this->idEmpleado = $idEmpleado;
        $this->salarioBase = $salarioBase;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }

    public function getIdEmpleado(): int {
        return $this->idEmpleado;
    }

    public function setIdEmpleado(int $idEmpleado): void {
        $this->idEmpleado = $idEmpleado;
    }

    public function getSalarioBase(): float {
        return $this->salarioBase;
    }

    public function setSalarioBase(float $salarioBase): void {
        $this->salarioBase = $salarioBase;
    }
}
?>