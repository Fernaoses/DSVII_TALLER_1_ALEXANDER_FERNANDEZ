CREATE DATABASE IF NOT EXISTS techparts_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techparts_db;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    categoria VARCHAR(80) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    cantidad INT NOT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT NOW()
);