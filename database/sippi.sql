CREATE DATABASE sippi_ideice CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sippi_ideice;

-- Tablas principales (jerarquía PEI-POA)
CREATE TABLE ejes (
    id_eje INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    anio_inicio INT NOT NULL,
    anio_fin INT NOT NULL
);

CREATE TABLE resultados (
    id_resultado INT AUTO_INCREMENT PRIMARY KEY,
    id_eje INT NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    FOREIGN KEY (id_eje) REFERENCES ejes(id_eje)
);

CREATE TABLE productos_estrategicos (
    id_prod_estr INT AUTO_INCREMENT PRIMARY KEY,
    id_resultado INT NOT NULL,
    nombre VARCHAR(250) NOT NULL,
    indicador VARCHAR(300),
    linea_base VARCHAR(100),
    meta VARCHAR(100),
    medio_verificacion VARCHAR(300),
    FOREIGN KEY (id_resultado) REFERENCES resultados(id_resultado)
);

CREATE TABLE areas (
    id_area INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL
);

CREATE TABLE productos_poa (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_prod_estr INT NOT NULL,
    id_area INT NOT NULL,
    nombre VARCHAR(250) NOT NULL,
    anio INT NOT NULL,
    FOREIGN KEY (id_prod_estr) REFERENCES productos_estrategicos(id_prod_estr),
    FOREIGN KEY (id_area) REFERENCES areas(id_area)
);

CREATE TABLE actividades (
    id_actividad INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    nombre VARCHAR(300) NOT NULL,
    responsable_id INT NOT NULL,
    estado ENUM('Iniciado','En proceso','Logrado') DEFAULT 'Iniciado',
    anio INT NOT NULL,
    FOREIGN KEY (id_producto) REFERENCES productos_poa(id_producto)
);

CREATE TABLE tareas (
    id_tarea INT AUTO_INCREMENT PRIMARY KEY,
    id_actividad INT NOT NULL,
    trimestre ENUM('Q1','Q2','Q3','Q4') NOT NULL,
    avance DECIMAL(5,2) DEFAULT 0,
    estado ENUM('Iniciado','En proceso','Logrado') DEFAULT 'Iniciado',
    FOREIGN KEY (id_actividad) REFERENCES actividades(id_actividad)
);

-- Evidencias y aprobaciones
CREATE TABLE evidencias (
    id_evidencia INT AUTO_INCREMENT PRIMARY KEY,
    id_actividad INT NULL,
    id_tarea INT NULL,
    medio_verificacion VARCHAR(300) NOT NULL,
    archivo_uri VARCHAR(500) NOT NULL,
    estado ENUM('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
    hash_sha256 CHAR(64) NOT NULL,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_actividad) REFERENCES actividades(id_actividad),
    FOREIGN KEY (id_tarea) REFERENCES tareas(id_tarea)
);

CREATE TABLE aprobaciones (
    id_aprobacion INT AUTO_INCREMENT PRIMARY KEY,
    id_actividad INT NOT NULL,
    aprobador_id INT NOT NULL,
    resultado ENUM('aprobado','rechazado') NOT NULL,
    comentario TEXT,
    tiempo_solucion INT, -- en días
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_actividad) REFERENCES actividades(id_actividad)
);

-- Presupuesto y PACC
CREATE TABLE partidas (
    id_partida INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL,
    descripcion VARCHAR(200) NOT NULL
);

CREATE TABLE presupuesto_actividad (
    id_pres INT AUTO_INCREMENT PRIMARY KEY,
    id_actividad INT NOT NULL,
    id_partida INT NOT NULL,
    monto_plan DECIMAL(14,2) NOT NULL,
    monto_ejec DECIMAL(14,2) DEFAULT 0,
    FOREIGN KEY (id_actividad) REFERENCES actividades(id_actividad),
    FOREIGN KEY (id_partida) REFERENCES partidas(id_partida)
);

CREATE TABLE renglones_pacc (
    id_renglon INT AUTO_INCREMENT PRIMARY KEY,
    id_actividad INT NOT NULL,
    id_partida INT NOT NULL,
    tipo_proceso VARCHAR(50) NOT NULL,
    plan DECIMAL(14,2) NOT NULL,
    adjudicado DECIMAL(14,2) DEFAULT 0,
    estado VARCHAR(50) DEFAULT 'planificado',
    FOREIGN KEY (id_actividad) REFERENCES actividades(id_actividad),
    FOREIGN KEY (id_partida) REFERENCES partidas(id_partida)
);

CREATE TABLE hitos_pacc (
    id_hito INT AUTO_INCREMENT PRIMARY KEY,
    id_renglon INT NOT NULL,
    tipo_hito VARCHAR(50) NOT NULL,
    fecha_programada DATE NOT NULL,
    fecha_real DATE NULL,
    FOREIGN KEY (id_renglon) REFERENCES renglones_pacc(id_renglon)
);

-- Seguridad y configuración
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    correo VARCHAR(100),
    id_area INT NULL,
    id_rol INT NOT NULL,
    estado ENUM('activo','inactivo') DEFAULT 'activo'
);

CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL -- Administrador, Planificación, Encargado, Responsable, Auditor
);

CREATE TABLE bitacora (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    entidad VARCHAR(50) NOT NULL,
    antes TEXT,
    despues TEXT,
    ip VARCHAR(45),
    hash CHAR(64)
);

CREATE TABLE configuracion (
    id_config INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT NOT NULL,
    scope VARCHAR(20) -- general, financiero, pacc
);