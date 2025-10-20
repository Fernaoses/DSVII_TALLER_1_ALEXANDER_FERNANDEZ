<?php
require_once "config_mysqli.php";

try {
    if (mysqli_connect_errno()) {
        throw new Exception("Error de conexión: " . mysqli_connect_error());
    }

    // 1. Mostrar las últimas 5 publicaciones con el nombre del autor y la fecha de publicación
    $sqlUltimasPublicaciones = "SELECT p.titulo, u.nombre AS autor, p.fecha_publicacion
            FROM publicaciones p
            INNER JOIN usuarios u ON p.usuario_id = u.id
            ORDER BY p.fecha_publicacion DESC
            LIMIT 5";

    $stmt = $conn->prepare($sqlUltimasPublicaciones);
    if(!$stmt){
        throw new Exception("Error en la preparación de la consulta de últimas publicaciones: " . $conn->error);
    }
    if(!$stmt->execute()){
        throw new Exception("Error en la consulta de últimas publicaciones: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if(!$result){
        throw new Exception("Error al obtener los resultados de últimas publicaciones: " . $stmt->error);
    }

    echo "<h3>Últimas 5 publicaciones:</h3>";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "Título: " . htmlspecialchars($row['titulo']) .
                 ", Autor: " . htmlspecialchars($row['autor']) .
                 ", Fecha: " . htmlspecialchars($row['fecha_publicacion']) . "<br>";
        }
    } else {
        echo "No se encontraron publicaciones.<br>";
    }
    $stmt->close();

    // 2. Listar los usuarios que no han realizado ninguna publicación
    $sqlUsuariosSinPublicaciones = "SELECT u.id, u.nombre
            FROM usuarios u
            LEFT JOIN publicaciones p ON u.id = p.usuario_id
            WHERE p.id IS NULL";

    $stmt = $conn->prepare($sqlUsuariosSinPublicaciones);
    if(!$stmt){
        throw new Exception("Error en la preparación de la consulta de usuarios sin publicaciones: " . $conn->error);
    }
    if(!$stmt->execute()){
        throw new Exception("Error en la consulta de usuarios sin publicaciones: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if(!$result){
        throw new Exception("Error al obtener los resultados de usuarios sin publicaciones: " . $stmt->error);
    }

    echo "<h3>Usuarios sin publicaciones:</h3>";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "Usuario: " . htmlspecialchars($row['nombre']) . "<br>";
        }
    } else {
        echo "Todos los usuarios tienen publicaciones.<br>";
    }
    $stmt->close();

    // 3. Calcular el promedio de publicaciones por usuario
    $sqlPromedioPublicaciones = "SELECT AVG(publicaciones_por_usuario) AS promedio
            FROM (
                SELECT COUNT(p.id) AS publicaciones_por_usuario
                FROM usuarios u
                LEFT JOIN publicaciones p ON u.id = p.usuario_id
                GROUP BY u.id
            ) AS conteos";

    $stmt = $conn->prepare($sqlPromedioPublicaciones);
    if(!$stmt){
        throw new Exception("Error en la preparación de la consulta de promedio de publicaciones: " . $conn->error);
    }
    if(!$stmt->execute()){
        throw new Exception("Error en la consulta de promedio de publicaciones: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if(!$result){
        throw new Exception("Error al obtener los resultados del promedio de publicaciones: " . $stmt->error);
    }

    echo "<h3>Promedio de publicaciones por usuario:</h3>";
    if ($row = $result->fetch_assoc()) {
        $promedio = $row['promedio'] !== null ? number_format($row['promedio'], 2) : '0.00';
        echo "Promedio: " . $promedio . "<br>";
    } else {
        echo "No se pudo calcular el promedio.<br>";
    }
    $stmt->close();

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

    $stmt = $conn->prepare($sqlPublicacionRecientePorUsuario);
    if(!$stmt){
        throw new Exception("Error en la preparación de la consulta de publicación reciente por usuario: " . $conn->error);
    }
    if(!$stmt->execute()){
        throw new Exception("Error en la consulta de publicación reciente por usuario: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if(!$result){
        throw new Exception("Error al obtener los resultados de publicación reciente por usuario: " . $stmt->error);
    }

    echo "<h3>Publicación más reciente por usuario:</h3>";
    if ($result->num_rows > 0) {
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
} catch (Exception $e) {
    echo "Se produjo un error: " . $e->getMessage();
} finally {
    mysqli_close($conn);
}

?>