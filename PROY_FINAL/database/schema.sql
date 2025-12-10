-- Esquema para el sistema de gestión de empleados (MySQL/XAMPP)
SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE DATABASE IF NOT EXISTS proy_final CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE proy_final;

-- Reinicio seguro de tablas respetando dependencias
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS evaluaciones;
DROP TABLE IF EXISTS permisos;
DROP TABLE IF EXISTS nominas;
DROP TABLE IF EXISTS asistencias;
DROP TABLE IF EXISTS empleados;
DROP TABLE IF EXISTS departamentos;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE departamentos (
    id VARCHAR(50) PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    responsable VARCHAR(120) NOT NULL,
    parent_id VARCHAR(50),
    CONSTRAINT fk_departamento_padre FOREIGN KEY (parent_id) REFERENCES departamentos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE empleados (
    id VARCHAR(50) PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    cargo VARCHAR(120) NOT NULL,
    departamento_id VARCHAR(50) NOT NULL,
    salario DECIMAL(10,2) NOT NULL,
    email VARCHAR(180) NOT NULL,
    CONSTRAINT fk_empleado_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE asistencias (
    id VARCHAR(50) PRIMARY KEY,
    empleado_id VARCHAR(50) NOT NULL,
    fecha DATE NOT NULL,
    estado VARCHAR(40) NOT NULL,
    CONSTRAINT fk_asistencia_empleado FOREIGN KEY (empleado_id) REFERENCES empleados(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE nominas (
    id VARCHAR(50) PRIMARY KEY,
    empleado_id VARCHAR(50) NOT NULL,
    mes CHAR(7) NOT NULL,
    salario_base DECIMAL(10,2) NOT NULL,
    bonos DECIMAL(10,2) DEFAULT 0,
    deducciones DECIMAL(10,2) DEFAULT 0,
    CONSTRAINT fk_nomina_empleado FOREIGN KEY (empleado_id) REFERENCES empleados(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE permisos (
    id VARCHAR(50) PRIMARY KEY,
    empleado_id VARCHAR(50) NOT NULL,
    tipo VARCHAR(80) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado VARCHAR(40) NOT NULL,
    CONSTRAINT fk_permiso_empleado FOREIGN KEY (empleado_id) REFERENCES empleados(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE evaluaciones (
    id VARCHAR(50) PRIMARY KEY,
    empleado_id VARCHAR(50) NOT NULL,
    periodo VARCHAR(20) NOT NULL,
    puntaje INT NOT NULL,
    comentarios TEXT,
    CONSTRAINT fk_evaluacion_empleado FOREIGN KEY (empleado_id) REFERENCES empleados(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos de ejemplo
INSERT INTO departamentos (id, nombre, responsable, parent_id) VALUES
('dep_dir', 'Dirección General', 'Laura Méndez', NULL),
('dep_hr', 'Recursos Humanos', 'Carlos Paredes', 'dep_dir'),
('dep_it', 'Tecnología', 'Andrea Ríos', 'dep_dir'),
('dep_fin', 'Finanzas', 'Marcelo Díaz', 'dep_dir');

INSERT INTO empleados (id, nombre, cargo, departamento_id, salario, email) VALUES
('emp_001', 'Ana Torres', 'Analista de Nómina', 'dep_fin', 1200, 'ana.torres@empresa.com'),
('emp_002', 'Jorge Luna', 'Especialista en RRHH', 'dep_hr', 1050, 'jorge.luna@empresa.com'),
('emp_003', 'María Prado', 'Desarrolladora Fullstack', 'dep_it', 1500, 'maria.prado@empresa.com'),
('emp_004', 'Hernán Ortiz', 'Administrador de Sistemas', 'dep_it', 1300, 'hernan.ortiz@empresa.com');

INSERT INTO asistencias (id, empleado_id, fecha, estado) VALUES
('att_001', 'emp_001', CURRENT_DATE, 'Presente'),
('att_002', 'emp_003', CURRENT_DATE, 'Presente');

INSERT INTO nominas (id, empleado_id, mes, salario_base, bonos, deducciones) VALUES
('pay_001', 'emp_001', DATE_FORMAT(CURRENT_DATE, '%Y-%m'), 1200, 120, 60),
('pay_002', 'emp_002', DATE_FORMAT(CURRENT_DATE, '%Y-%m'), 1050, 90, 40);

INSERT INTO permisos (id, empleado_id, tipo, fecha_inicio, fecha_fin, estado) VALUES
('leave_001', 'emp_002', 'Vacaciones', CURRENT_DATE, DATE_ADD(CURRENT_DATE, INTERVAL 5 DAY), 'Aprobado');

INSERT INTO evaluaciones (id, empleado_id, periodo, puntaje, comentarios) VALUES
('eval_001', 'emp_003', '2024 Q4', 92, 'Entrega proyectos a tiempo y lidera revisiones de código.');