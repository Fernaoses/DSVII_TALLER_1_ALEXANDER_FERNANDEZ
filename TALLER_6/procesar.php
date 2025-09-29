<?php
require_once 'validaciones.php';
require_once 'sanitizacion.php';

function obtenerNombreFuncion(string $prefijo, string $campo): string {
    $segmentos = explode('_', $campo);
    $segmentosCapitalizados = array_map(fn($segmento) => ucfirst($segmento), $segmentos);
    return $prefijo . implode('', $segmentosCapitalizados);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errores = [];
    $datos = [];

    // Procesar y validar cada campo
    $campos = ['nombre', 'email', 'edad', 'sitio_web', 'genero', 'intereses', 'comentarios'];
    foreach ($campos as $campo) {
        if (isset($_POST[$campo])) {
            $valor = $_POST[$campo];
            // $valorSanitizado = call_user_func("sanitizar" . ucfirst($campo), $valor);

            $funcionSanitizar = obtenerNombreFuncion('sanitizar', $campo);
            if (function_exists($funcionSanitizar)) {
                $valorSanitizado = call_user_func($funcionSanitizar, $valor);
            } else {
                $errores[] = "No se encontró la función de sanitización para $campo.";
                $valorSanitizado = $valor;
            }

            $datos[$campo] = $valorSanitizado;

            // if (!call_user_func("validar" . ucfirst($campo), $valorSanitizado)) {
            //     $errores[] = "El campo $campo no es válido.";
            $funcionValidar = obtenerNombreFuncion('validar', $campo);
            if (function_exists($funcionValidar)) {
                if (!call_user_func($funcionValidar, $valorSanitizado)) {
                    $errores[] = "El campo $campo no es válido.";
                }
            } else {
                $errores[] = "No se encontró la función de validación para $campo.";
            }
        }
    }

    // Procesar la foto de perfil
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {
        if (!validarFotoPerfil($_FILES['foto_perfil'])) {
            $errores[] = "La foto de perfil no es válida.";
        } else {
            $rutaDestino = 'uploads/' . basename($_FILES['foto_perfil']['name']);
            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $rutaDestino)) {
                $datos['foto_perfil'] = $rutaDestino;
            } else {
                $errores[] = "Hubo un error al subir la foto de perfil.";
            }
        }
    }

    // Mostrar resultados o errores
    if (empty($errores)) {
        echo "<h2>Datos Recibidos:</h2>";
        foreach ($datos as $campo => $valor) {
            if ($campo === 'intereses') {
                echo "$campo: " . implode(", ", $valor) . "<br>";
            } elseif ($campo === 'foto_perfil') {
                echo "$campo: <img src='$valor' width='100'><br>";
            } else {
                echo "$campo: $valor<br>";
            }
        }
    } else {
        echo "<h2>Errores:</h2>";
        foreach ($errores as $error) {
            echo "$error<br>";
        }
    }
} else {
    echo "Acceso no permitido.";
}
?>