<?php
// Archivo: clases.php

require_once 'productos.json';
require_once 'inventariable.php';

class Producto {
    public $id;
    public $nombre;
    public $descripcion;
    public $estado;
    public $stock;
    public $fechaIngreso;
    public $categoria;

    public function __construct($datos) {
        foreach ($datos as $clave => $valor) {
            if (property_exists($this, $clave)) {
                $this->$clave = $valor;
            }
        }
    }
}

class GestorInventario {
    private $items = [];
    private $rutaArchivo = 'productos.json';

    public function obtenerTodos() {
        if (empty($this->items)) {
            $this->cargarDesdeArchivo();
        }
        return $this->items;
    }

    // Modificar para que cree instancias en las clases hijas correspondientes
    private function cargarDesdeArchivo() {
        if (!file_exists($this->rutaArchivo)) {
            return;
        }
        
        $jsonContenido = file_get_contents($this->rutaArchivo);
        $arrayDatos = json_decode($jsonContenido, true);
        
        if ($arrayDatos === null) {
            return;
        }
        
        foreach ($arrayDatos as $datos) {
            $this->items[] = new Producto($datos);
        }
    }

    private function persistirEnArchivo() {
        $arrayParaGuardar = array_map(function($item) {
            return get_object_vars($item);
        }, $this->items);
        
        file_put_contents(
            $this->rutaArchivo, 
            json_encode($arrayParaGuardar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public function obtenerMaximoId() {
        if (empty($this->items)) {
            return 0;
        }
        
        $ids = array_map(function($item) {
            return $item->id;
        }, $this->items);
        
        return max($ids);
    }

    // Metodo agregar producto
    public function agregar($nuevoProducto) {
        $nuevoId = $this->obtenerMaximoId() + 1;
        $datos['id'] = $nuevoId;
        $nuevoProducto = new Producto($datos);
        $this->items[] = $nuevoProducto;
        $this->persistirEnArchivo();
        return $nuevoProducto;
    }

    // Metodo eliminar producto
    public function eliminar($idProducto) {
        $this->items = array_filter($this->items, function($item) use ($idProducto) {
            return $item->id != $idProducto;
        });
        $this->persistirEnArchivo();
    }

    // Metodo actualizar producto
    public function actualizar($productoActualizado) {   
        foreach ($this->items as &$item) {
            if ($item->id == $productoActualizado->id) {
                $item = $productoActualizado;
                break;
            }
        }
        $this->persistirEnArchivo();
    }

    // Metodo cambiar estado
    public function cambiarEstado($idProducto, $nuevoEstado) {
        foreach ($this->items as &$item) {
            if ($item->id == $idProducto) {
                $item->estado = $nuevoEstado;
                break;
            }
        }
        $this->persistirEnArchivo();
    }

    // Metodo filtrar por estado
    public function filtrarPorEstado($estadoBuscado) {
        return array_filter($this->obtenerTodos(), function($item) use ($estadoBuscado) {
            return $item->estado === $estadoBuscado;
        });
    }

    // Metodo obtener por id
    public function obtenerPorId($idBuscado) {
        foreach ($this->obtenerTodos() as $item) {
            if ($item->id == $idBuscado) {
                return $item;
            }
        }
        return null;
    }
}

// Clase de producto electronico
class productoElectronico extends Producto implements Inventariable {
    public $garantiaMeses;

    public function __construct($id, $nombre, $stock, $garantiaMeses) {
        parent::__construct($id, $nombre, $stock);
        $this->garantiaMeses = $garantiaMeses;
    }

    public function obtenerInformacionInventario() {
        return array_merge(parent::obtenerInformacionInventario(), [
            'garantiaMeses' => $this->garantiaMeses
        ]);
    }
}

// Clase de producto ropa
class productoRopa extends Producto implements Inventariable {
    public $talla;

    public function __construct($id, $nombre, $stock, $talla) {
        parent::__construct($id, $nombre, $stock);
        $this->talla = $talla;
    }

    public function obtenerInformacionInventario() {
        return array_merge(parent::obtenerInformacionInventario(), [
            'talla' => $this->talla
        ]);
    }
}  

// Clase de producto alimento
class productoAlimento extends Producto implements Inventariable {
    public $fechaCaducidad;

    public function __construct($id, $nombre, $stock, $fechaCaducidad) {
        parent::__construct($id, $nombre, $stock);
        $this->fechaCaducidad = $fechaCaducidad;
    }

    public function obtenerInformacionInventario() {
        return array_merge(parent::obtenerInformacionInventario(), [
            'fechaCaducidad' => $this->fechaCaducidad
        ]);
    }
}