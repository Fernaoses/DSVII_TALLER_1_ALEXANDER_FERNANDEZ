<?php
// Configuración de conexión a la base de datos usando mysqli orientado a objetos.
// Ajusta estas variables según tu entorno local de XAMPP.

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'biblioteca_personal');

function get_db_connection(): mysqli
{
    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($connection->connect_error) {
        throw new RuntimeException('Error de conexión: ' . $connection->connect_error);
    }

    // Aseguramos soporte UTF-8 para almacenar títulos y reseñas.
    $connection->set_charset('utf8mb4');

    return $connection;
}