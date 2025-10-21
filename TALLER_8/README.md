# Sistema de Gestión de Biblioteca

Este proyecto contiene dos implementaciones completas de un sistema de gestión de biblioteca desarrollado en PHP. Se ofrece una versión utilizando **MySQLi** y otra utilizando **PDO**, ambas con consultas preparadas, transacciones y validaciones de datos.

## Características principales

- Gestión de libros: alta, listado con paginación, búsqueda por título/autor/ISBN, actualización y eliminación.
- Gestión de usuarios: registro, listado con paginación, búsqueda por nombre/email, actualización (incluyendo cambio de contraseña) y eliminación.
- Sistema de préstamos: registro de préstamos con control de stock, devoluciones, listado de préstamos activos con filtros y consulta de historial por usuario.
- Uso de transacciones para operaciones críticas (préstamos y devoluciones).
- Validación y sanitización de entradas antes de interactuar con la base de datos.
- Consultas `JOIN` para enriquecer la información mostrada en los listados de préstamos.
- Interfaces web simples para probar todas las operaciones.

## Estructura del proyecto

```
TALLER_8/
├── mysqli/
│   ├── config.php
│   ├── index.php
│   ├── libros.php
│   ├── prestamos.php
│   └── usuarios.php
├── pdo/
│   ├── config.php
│   ├── index.php
│   ├── libros.php
│   ├── prestamos.php
│   └── usuarios.php
└── README.md
```

Cada carpeta (`mysqli/` y `pdo/`) contiene la misma funcionalidad implementada con la librería correspondiente. Los archivos `index.php` ofrecen una interfaz web sencilla para interactuar con el sistema.

## Requisitos previos

- PHP 8.1 o superior con las extensiones `mysqli` y `pdo_mysql` habilitadas.
- Servidor web (por ejemplo Apache o Nginx) o el servidor embebido de PHP.
- MySQL o MariaDB.

## Configuración de la base de datos

1. Cree una base de datos llamada `biblioteca` (o ajuste el nombre en los archivos `config.php`).
2. Ejecute el siguiente script SQL para crear las tablas necesarias:

```sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE libros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    autor VARCHAR(255) NOT NULL,
    isbn VARCHAR(45) NOT NULL UNIQUE,
    anio_publicacion INT NOT NULL,
    cantidad_disponible INT NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE prestamos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libro_id INT NOT NULL,
    usuario_id INT NOT NULL,
    fecha_prestamo DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_limite DATE NULL,
    fecha_devolucion DATETIME NULL,
    devuelto TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_prestamos_libro FOREIGN KEY (libro_id) REFERENCES libros (id) ON DELETE CASCADE,
    CONSTRAINT fk_prestamos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
);
```

3. Ajuste las credenciales de conexión en `mysqli/config.php` y `pdo/config.php` si es necesario.

## Ejecución

### Usando la versión MySQLi

1. Inicie un servidor PHP apuntando al directorio `TALLER_8/mysqli`:
   ```bash
   php -S localhost:8080 -t TALLER_8/mysqli
   ```
2. Abra `http://localhost:8080/index.php` en su navegador.

### Usando la versión PDO

1. Inicie un servidor PHP apuntando al directorio `TALLER_8/pdo`:
   ```bash
   php -S localhost:8080 -t TALLER_8/pdo
   ```
2. Abra `http://localhost:8080/index.php` en su navegador.

## Notas de seguridad

- Las contraseñas de los usuarios se almacenan utilizando `password_hash`. Para la autenticación real se debería implementar un flujo de login.
- Todas las consultas utilizan declaraciones preparadas para prevenir inyección SQL.
- Las interfaces web proporcionadas son básicas y tienen fines demostrativos; pueden ser adaptadas o protegidas con autenticación según las necesidades del proyecto.

## Licencia

Este proyecto se entrega como parte del Taller 8 y puede utilizarse libremente con fines educativos.