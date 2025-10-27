<?php
$dsn = 'mysql:host=127.0.0.1;dbname=taller9_db;charset=utf8mb4';
$usuario = 'root';
$contrasena = '';
$opciones = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = new PDO($dsn, $usuario, $contrasena, $opciones);
?>