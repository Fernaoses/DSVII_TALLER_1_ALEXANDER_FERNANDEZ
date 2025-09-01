<?php
$nombre = "Alexander";
$edad = 27;
$correo = "alexander.fernandez@utp.ac.pa";
$telefono = "123-456-789";

define("OCUPACION", "Estudiante");

$mensaje1 = "Hola, mi nombre es " . $nombre . " y tengo $edad años. Mi correo es $correo y mi teléfono es" . $telefono . " y soy un " . OCUPACION . ".";

print $mensaje1 . "<br>";
echo "<br>";

var_dump($nombre);
echo "<br>";
var_dump($edad);
echo "<br>";
var_dump($correo);
echo "<br>";
var_dump($telefono);
echo "<br>";
var_dump(OCUPACION);
echo "<br>";
?>