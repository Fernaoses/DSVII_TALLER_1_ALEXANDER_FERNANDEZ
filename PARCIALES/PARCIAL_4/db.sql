CREATE DATABASE IF NOT EXISTS biblioteca_personal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE biblioteca_personal;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    google_id VARCHAR(255) NOT NULL,
    fecha_registro DATETIME NOT NULL,
    UNIQUE KEY unique_google_id (google_id)
);

CREATE TABLE IF NOT EXISTS libros_guardados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    google_books_id VARCHAR(255) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    autor VARCHAR(255) NOT NULL,
    imagen_portada VARCHAR(500) DEFAULT NULL,
    reseña_personal TEXT,
    fecha_guardado DATETIME NOT NULL,
    CONSTRAINT fk_libros_usuario FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);