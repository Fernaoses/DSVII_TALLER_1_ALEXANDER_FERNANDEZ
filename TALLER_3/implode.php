
<?php
// Ejemplo de uso de implode()
$frutas = ["Manzana", "Naranja", "Plátano", "Uva"];
$frase = implode(", ", $frutas);

echo "Array de frutas:</br>";
print_r($frutas);
echo "Frase creada: $frase</br>";

// Ejercicio: Crea un array con los nombres de 5 países que te gustaría visitar
// y usa implode() para convertirlo en una cadena separada por guiones (-)
$paises = ["Japón", "Canadá", "Australia", "Italia", "Brasil"]; // Reemplaza esto con tu array de países
$listaPaises = implode("-", $paises);

echo "</br>Mi lista de países para visitar: $listaPaises</br>";

// Bonus: Usa implode() con un array asociativo
$persona = [
    "nombre" => "Juan",
    "edad" => 30,
    "ciudad" => "Madrid"
];
$infoPersona = implode(" | ", $persona);

echo "</br>Información de la persona: $infoPersona</br>";
?>
      
