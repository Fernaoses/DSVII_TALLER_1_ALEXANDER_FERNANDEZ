<?php
require_once "config_pdo.php";

try {
    // 1. Mostrar las últimas 5 publicaciones con el nombre del autor y la fecha de publicación
    $sqlUltimasPublicaciones = "SELECT p.titulo, u.nombre AS autor, p.fecha_publicacion
            FROM publicaciones p
            INNER JOIN usuarios u ON p.usuario_id = u.id
            ORDER BY p.fecha_publicacion DESC
            LIMIT 5";

    $stmt = $pdo->prepare($sqlUltimasPublicaciones);
    $stmt->execute();

    echo "<h3>Últimas 5 publicaciones:</h3>";
    $publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($publicaciones) {
        foreach ($publicaciones as $row) {
            echo "Título: " . htmlspecialchars($row['titulo']) .
                 ", Autor: " . htmlspecialchars($row['autor']) .
                 ", Fecha: " . htmlspecialchars($row['fecha_publicacion']) . "<br>";
        }
    } else {
        echo "No se encontraron publicaciones.<br>";
    }

    // 2. Listar los usuarios que no han realizado ninguna publicación
    $sqlUsuariosSinPublicaciones = "SELECT u.id, u.nombre
            FROM usuarios u
            LEFT JOIN publicaciones p ON u.id = p.usuario_id
            WHERE p.id IS NULL";

    $stmt = $pdo->prepare($sqlUsuariosSinPublicaciones);
    $stmt->execute();

    echo "<h3>Usuarios sin publicaciones:</h3>";
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($usuarios) {
        foreach ($usuarios as $row) {
            echo "Usuario: " . htmlspecialchars($row['nombre']) . "<br>";
        }
    } else {
        echo "Todos los usuarios tienen publicaciones.<br>";
    }

    // 3. Calcular el promedio de publicaciones por usuario
    $sqlPromedioPublicaciones = "SELECT AVG(publicaciones_por_usuario) AS promedio
            FROM (
                SELECT COUNT(p.id) AS publicaciones_por_usuario
                FROM usuarios u
                LEFT JOIN publicaciones p ON u.id = p.usuario_id
                GROUP BY u.id
            ) AS conteos";

    $stmt = $pdo->prepare($sqlPromedioPublicaciones);
    $stmt->execute();

    echo "<h3>Promedio de publicaciones por usuario:</h3>";
    $promedio = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($promedio && $promedio['promedio'] !== null) {
        echo "Promedio: " . number_format($promedio['promedio'], 2) . "<br>";
    } else {
        echo "No se pudo calcular el promedio.<br>";
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

    $stmt = $pdo->prepare($sqlPublicacionRecientePorUsuario);
    $stmt->execute();

    echo "<h3>Publicación más reciente por usuario:</h3>";
    $recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($recientes) {
        foreach ($recientes as $row) {
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

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$pdo = null;
?>