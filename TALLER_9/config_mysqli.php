<?php
const DB_SERVER = '127.0.0.1';
const DB_USERNAME = 'root';
const DB_PASSWORD = '';
const DB_NAME = 'taller9_db';

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn === false) {
    die('ERROR: No se pudo conectar. ' . mysqli_connect_error());
}
?>