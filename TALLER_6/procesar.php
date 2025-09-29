<?php
session_start();
require_once 'validaciones.php';
require_once 'sanitizacion.php';

function obtenerNombreFuncion(string $prefijo, string $campo): string {
    $segmentos = explode('_', $campo);
    $segmentosCapitalizados = array_map(fn($segmento) => ucfirst($segmento), $segmentos);
    return $prefijo . implode('', $segmentosCapitalizados);
}

function redirigirConErrores(array $errores, array $datos): void {
    $_SESSION['errores'] = $errores;
    $_SESSION['datos'] = $datos;
    header('Location: formulario.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errores = [];
    $datos = [];

    // Procesar y validar cada campo
    $campos = ['nombre', 'email', 'fecha_nacimiento', 'sitio_web', 'genero', 'intereses', 'comentarios'];
    foreach ($campos as $campo) {
        // if (isset($_POST[$campo])) {
        //     $valor = $_POST[$campo];
        //     $valorSanitizado = call_user_func("sanitizar" . ucfirst($campo), $valor);

        $valor = null;
        if ($campo === 'intereses') {
            $valor = $_POST[$campo] ?? [];
        } else {
            $valor = $_POST[$campo] ?? '';
        }

            // $funcionSanitizar = obtenerNombreFuncion('sanitizar', $campo);
            // if (function_exists($funcionSanitizar)) {
            //     $valorSanitizado = call_user_func($funcionSanitizar, $valor);
            // } else {
            //     $errores[] = "No se encontró la función de sanitización para $campo.";
            //     $valorSanitizado = $valor;
            // }

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
    

    // Procesar la foto de perfil

    if (isset($datos['fecha_nacimiento']) && validarFechaNacimiento($datos['fecha_nacimiento'])) {
        $fechaNacimiento = new DateTime($datos['fecha_nacimiento']);
        $datos['edad'] = $fechaNacimiento->diff(new DateTime('today'))->y;
    }

    $uploadsDir = __DIR__ . '/uploads';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }

    $archivoFoto = null;

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {
        if (!validarFotoPerfil($_FILES['foto_perfil'])) {
            $errores[] = "La foto de perfil no es válida.";
        } else {
            // $rutaDestino = 'uploads/' . basename($_FILES['foto_perfil']['name']);
            // if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $rutaDestino)) {
            //     $datos['foto_perfil'] = $rutaDestino;

            $nombreArchivo = basename($_FILES['foto_perfil']['name']);
            $rutaRelativa = 'uploads/' . $nombreArchivo;
            $rutaDestino = $uploadsDir . '/' . $nombreArchivo;

            if (file_exists($rutaDestino)) {
                $errores[] = "Ya existe un archivo con el nombre $nombreArchivo. Por favor elige otro nombre.";
            } else {
                // $errores[] = "Hubo un error al subir la foto de perfil.";
                $archivoFoto = [
                    'tmp' => $_FILES['foto_perfil']['tmp_name'],
                    'ruta_destino' => $rutaDestino,
                    'ruta_relativa' => $rutaRelativa,
                ];
            }
        }
    }

    // Mostrar resultados o errores
    // if (empty($errores)) {
    //     echo "<h2>Datos Recibidos:</h2>";
    //     foreach ($datos as $campo => $valor) {
    //         if ($campo === 'intereses') {
    //             echo "$campo: " . implode(", ", $valor) . "<br>";
    //         } elseif ($campo === 'foto_perfil') {
    //             echo "$campo: <img src='$valor' width='100'><br>";
    //         } else {
    //             echo "$campo: $valor<br>";
    //         }
    //     }
    // } else {
    //     echo "<h2>Errores:</h2>";
    //     foreach ($errores as $error) {
    //         echo "$error<br>";
    //     }
    // }

    // if (empty($errores)) {
    //     echo "<h2>Datos Recibidos:</h2>";
    //     echo "<table border='1'>";
    //     foreach ($datos as $campo => $valor) {
    //         echo "<tr>";
    //         echo "<th>" . ucfirst($campo) . "</th>";
    //         if ($campo === 'intereses') {
    //             echo "<td>" . implode(", ", $valor) . "</td>";
    //         } elseif ($campo === 'foto_perfil') {
    //             echo "<td><img src='$valor' width='100'></td>";
    //         } else {
    //             echo "<td>$valor</td>";
    //         }
    //         echo "</tr>";
    if (!empty($errores)) {
        redirigirConErrores($errores, $datos);
    }

    if ($archivoFoto !== null) {
        if (!move_uploaded_file($archivoFoto['tmp'], $archivoFoto['ruta_destino'])) {
            $errores[] = 'Hubo un error al subir la foto de perfil.';
            redirigirConErrores($errores, $datos);
        }
    //     echo "</table>";
    // } else {
    //     echo "<h2>Errores:</h2>";
    //     echo "<ul>";
    //     foreach ($errores as $error) {
    //         echo "<li>$error</li>";

    $datos['foto_perfil'] = $archivoFoto['ruta_relativa'];
    }

    $directorioDatos = __DIR__ . '/data';
    if (!is_dir($directorioDatos)) {
        mkdir($directorioDatos, 0755, true);
    }

    $archivoRegistros = $directorioDatos . '/registros.json';
    $registros = [];
    if (file_exists($archivoRegistros)) {
        $contenido = file_get_contents($archivoRegistros);
        $registros = json_decode($contenido, true);
        if (!is_array($registros)) {
            $registros = [];
        }
        // echo "</ul>";
    }

    $registro = $datos;
    $registro['fecha_registro'] = (new DateTime())->format(DateTime::ATOM);
    $registros[] = $registro;

    file_put_contents($archivoRegistros, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    unset($_SESSION['datos']);

    echo "<h2>Datos Recibidos:</h2>";
    echo "<table border='1'>";
    foreach ($datos as $campo => $valor) {
        echo '<tr>';
        echo '<th>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $campo)), ENT_QUOTES, 'UTF-8') . '</th>';
        if ($campo === 'intereses') {
            echo '<td>' . htmlspecialchars(implode(', ', $valor), ENT_QUOTES, 'UTF-8') . '</td>';
        } elseif ($campo === 'foto_perfil') {
            $ruta = htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
            echo "<td><img src='{$ruta}' width='100' alt='Foto de perfil'></td>";
        } else {
            echo '<td>' . htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8') . '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    echo "<p><a href='formulario.php'>Volver al formulario</a></p>";
    echo "<p><a href='resumen.php'>Ver resumen de registros</a></p>";

// }
} else {
    // echo "Acceso no permitido.";
    header('Location: formulario.php');
    exit;
}
?>