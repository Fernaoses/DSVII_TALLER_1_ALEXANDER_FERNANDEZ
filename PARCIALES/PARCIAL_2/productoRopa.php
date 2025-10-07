<!-- hereda de productos.json y crear atributo talla (cadena: 'XS', 'S', 'M', 'L', 'XL', 'XXL') -->
<?php

require_once 'productos.json';

class ProductoRopa extends Producto {
    public $talla;

    public function __construct($datos) {
        parent::__construct($datos);
        if (isset($datos['talla'])) {
            $this->talla = $datos['talla'];
        }
    }
}
?>