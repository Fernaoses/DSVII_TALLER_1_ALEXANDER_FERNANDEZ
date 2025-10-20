<?php
require_once "config_mysqli.php";

if (mysqli_connect_errno()) {
    echo "Error de conexión: " . mysqli_connect_error();
    exit;
}

// 1. Mostrar las últimas 5 publicaciones con el nombre del autor y la fecha de publicación
$sqlUltimasPublicaciones = "SELECT p.titulo, u.nombre AS autor, p.fecha_publicacion
        FROM publicaciones p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.fecha_publicacion DESC
        LIMIT 5";

if ($stmt = $conn->prepare($sqlUltimasPublicaciones)) {
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h3>Últimas 5 publicaciones:</h3>";
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "Título: " . htmlspecialchars($row['titulo']) .
                 ", Autor: " . htmlspecialchars($row['autor']) .
                 ", Fecha: " . htmlspecialchars($row['fecha_publicacion']) . "<br>";
        }
    } else {
        echo "No se encontraron publicaciones.<br>";
    }

    $stmt->close();
} else {
    echo "Error en la preparación de la consulta de últimas publicaciones: " . $conn->error;
}

// 2. Listar los usuarios que no han realizado ninguna publicación
$sqlUsuariosSinPublicaciones = "SELECT u.id, u.nombre
        FROM usuarios u
        LEFT JOIN publicaciones p ON u.id = p.usuario_id
        WHERE p.id IS NULL";

if ($stmt = $conn->prepare($sqlUsuariosSinPublicaciones)) {
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h3>Usuarios sin publicaciones:</h3>";
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "Usuario: " . htmlspecialchars($row['nombre']) . "<br>";
        }
    } else {
        echo "Todos los usuarios tienen publicaciones.<br>";
    }

    $stmt->close();
} else {
    echo "Error en la preparación de la consulta de usuarios sin publicaciones: " . $conn->error;
}

// 3. Calcular el promedio de publicaciones por usuario
$sqlPromedioPublicaciones = "SELECT AVG(publicaciones_por_usuario) AS promedio
        FROM (
            SELECT COUNT(p.id) AS publicaciones_por_usuario
            FROM usuarios u
            LEFT JOIN publicaciones p ON u.id = p.usuario_id
            GROUP BY u.id
        ) AS conteos";

if ($stmt = $conn->prepare($sqlPromedioPublicaciones)) {
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h3>Promedio de publicaciones por usuario:</h3>";
    if ($result && ($row = $result->fetch_assoc())) {
        $promedio = $row['promedio'] !== null ? number_format($row['promedio'], 2) : '0.00';
        echo "Promedio: " . $promedio . "<br>";
    } else {
        echo "No se pudo calcular el promedio.<br>";
    }

    $stmt->close();
} else {
    echo "Error en la preparación de la consulta de promedio de publicaciones: " . $conn->error;
}

// 4. Encontrar la publicación más reciente de cada usuario
$sqlPublicacionRecientePorUsuario = "SELECT u.id, u.nombre, p.titulo, p.fecha_publicacion
        FROM usuarios u
        LEFT JOIN publicaciones p ON p.id = (
            SELECT p2.id
            FROM publicaciones p2
            WHERE p2.usuario_id = u.id
            ORDER BY p2.fecha_publicacion DESC, p2.id DESC
            LIMIT 1
        )
        ORDER BY u.nombre";

if ($stmt = $conn->prepare($sqlPublicacionRecientePorUsuario)) {
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h3>Publicación más reciente por usuario:</h3>";
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "Usuario: " . htmlspecialchars($row['nombre']);
            if ($row['titulo']) {
                echo ", Última publicación: " . htmlspecialchars($row['titulo']) .
                     " (" . htmlspecialchars($row['fecha_publicacion']) . ")";
            } else {
                echo ", Sin publicaciones";
            }
            echo "<br>";
        }
    } else {
        echo "No se encontraron usuarios.<br>";
    }

    $stmt->close();
} else {
    echo "Error en la preparación de la consulta de publicación reciente por usuario: " . $conn->error;
}

mysqli_close($conn);
?>