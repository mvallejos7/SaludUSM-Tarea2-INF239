-- =========================================================
-- SALUDUSM - TAREA 2
-- Base de datos MySQL
-- =========================================================

DROP DATABASE IF EXISTS salud_usm;

CREATE DATABASE salud_usm
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE salud_usm;


-- =========================================================
-- 1. TABLAS DE DOMINIO / CATALOGOS
-- =========================================================

CREATE TABLE prevision (
    id_prevision INT AUTO_INCREMENT,
    nombre VARCHAR(20) NOT NULL,

    CONSTRAINT pk_prevision
        PRIMARY KEY (id_prevision),

    CONSTRAINT uq_prevision_nombre
        UNIQUE (nombre)
) ENGINE = InnoDB;


CREATE TABLE especialidad (
    id_especialidad INT AUTO_INCREMENT,
    nombre VARCHAR(60) NOT NULL,

    CONSTRAINT pk_especialidad
        PRIMARY KEY (id_especialidad),

    CONSTRAINT uq_especialidad_nombre
        UNIQUE (nombre)
) ENGINE = InnoDB;


CREATE TABLE estado_cita (
    id_estado_cita INT AUTO_INCREMENT,
    nombre VARCHAR(20) NOT NULL,

    CONSTRAINT pk_estado_cita
        PRIMARY KEY (id_estado_cita),

    CONSTRAINT uq_estado_cita_nombre
        UNIQUE (nombre)
) ENGINE = InnoDB;


-- =========================================================
-- 2. USUARIOS DEL SISTEMA
-- =========================================================

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT,
    rut VARCHAR(10) NOT NULL,
    email VARCHAR(120) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('PACIENTE', 'MEDICO', 'ADMIN') NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_usuario
        PRIMARY KEY (id_usuario),

    CONSTRAINT uq_usuario_rut
        UNIQUE (rut),

    CONSTRAINT uq_usuario_email
        UNIQUE (email),

    CONSTRAINT chk_usuario_rut
        CHECK (rut REGEXP '^[0-9]{8}-[0-9Kk]$')
) ENGINE = InnoDB;


-- =========================================================
-- 3. CENTROS MEDICOS
-- =========================================================

CREATE TABLE centro_medico (
    codigo_centro VARCHAR(20) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    comuna VARCHAR(60) NOT NULL,
    region VARCHAR(60) NOT NULL,

    CONSTRAINT pk_centro_medico
        PRIMARY KEY (codigo_centro)
) ENGINE = InnoDB;


-- =========================================================
-- 4. PACIENTES
-- =========================================================

CREATE TABLE paciente (
    id_paciente INT AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    sexo VARCHAR(5) NOT NULL,
    id_prevision INT NOT NULL,
    telefono_contacto VARCHAR(20),
    comuna_residencia VARCHAR(60) NOT NULL,

    CONSTRAINT pk_paciente
        PRIMARY KEY (id_paciente),

    CONSTRAINT uq_paciente_usuario
        UNIQUE (id_usuario),

    CONSTRAINT chk_paciente_sexo
        CHECK (sexo IN ('M', 'F', 'Otro')),

    CONSTRAINT fk_paciente_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_paciente_prevision
        FOREIGN KEY (id_prevision)
        REFERENCES prevision(id_prevision)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB;


-- =========================================================
-- 5. MEDICOS
-- =========================================================

CREATE TABLE medico (
    id_medico INT AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,

    CONSTRAINT pk_medico
        PRIMARY KEY (id_medico),

    CONSTRAINT uq_medico_usuario
        UNIQUE (id_usuario),

    CONSTRAINT fk_medico_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB;


-- =========================================================
-- 6. RELACION MEDICO - ESPECIALIDAD
-- =========================================================

CREATE TABLE medico_especialidad (
    id_medico INT NOT NULL,
    id_especialidad INT NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,

    CONSTRAINT pk_medico_especialidad
        PRIMARY KEY (id_medico, id_especialidad),

    CONSTRAINT fk_me_medico
        FOREIGN KEY (id_medico)
        REFERENCES medico(id_medico)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_me_especialidad
        FOREIGN KEY (id_especialidad)
        REFERENCES especialidad(id_especialidad)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB;


-- =========================================================
-- 7. RELACION MEDICO - CENTRO
-- =========================================================

CREATE TABLE medico_centro (
    id_medico INT NOT NULL,
    codigo_centro VARCHAR(20) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,

    CONSTRAINT pk_medico_centro
        PRIMARY KEY (id_medico, codigo_centro),

    CONSTRAINT fk_mc_medico
        FOREIGN KEY (id_medico)
        REFERENCES medico(id_medico)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_mc_centro
        FOREIGN KEY (codigo_centro)
        REFERENCES centro_medico(codigo_centro)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB;


-- =========================================================
-- 8. CITAS
-- =========================================================

CREATE TABLE cita (
    id_cita INT AUTO_INCREMENT,
    fecha_hora DATETIME NOT NULL,
    id_paciente INT NOT NULL,
    id_medico INT NOT NULL,
    codigo_centro VARCHAR(20) NOT NULL,
    id_especialidad INT NOT NULL,
    id_estado_cita INT NOT NULL,

    CONSTRAINT pk_cita
        PRIMARY KEY (id_cita),

    CONSTRAINT uq_cita_medico_fecha
        UNIQUE (id_medico, fecha_hora),

    CONSTRAINT uq_cita_paciente_fecha
        UNIQUE (id_paciente, fecha_hora),

    CONSTRAINT fk_cita_paciente
        FOREIGN KEY (id_paciente)
        REFERENCES paciente(id_paciente)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_cita_medico_especialidad
        FOREIGN KEY (id_medico, id_especialidad)
        REFERENCES medico_especialidad(id_medico, id_especialidad)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_cita_medico_centro
        FOREIGN KEY (id_medico, codigo_centro)
        REFERENCES medico_centro(id_medico, codigo_centro)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_cita_estado
        FOREIGN KEY (id_estado_cita)
        REFERENCES estado_cita(id_estado_cita)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB;


-- =========================================================
-- 9. DIAGNOSTICOS
-- =========================================================

CREATE TABLE diagnostico (
    codigo_diagnostico VARCHAR(10) NOT NULL,
    descripcion VARCHAR(200) NOT NULL,

    CONSTRAINT pk_diagnostico
        PRIMARY KEY (codigo_diagnostico)
) ENGINE = InnoDB;


-- =========================================================
-- 10. ATENCIONES
-- =========================================================

CREATE TABLE atencion (
    id_atencion INT AUTO_INCREMENT,
    id_cita INT NOT NULL,
    motivo_consulta VARCHAR(500) NOT NULL,
    observaciones_clinicas VARCHAR(2000),

    CONSTRAINT pk_atencion
        PRIMARY KEY (id_atencion),

    CONSTRAINT uq_atencion_cita
        UNIQUE (id_cita),

    CONSTRAINT fk_atencion_cita
        FOREIGN KEY (id_cita)
        REFERENCES cita(id_cita)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;


-- =========================================================
-- 11. RELACION ATENCION - DIAGNOSTICO
-- =========================================================

CREATE TABLE atencion_diagnostico (
    id_atencion INT NOT NULL,
    codigo_diagnostico VARCHAR(10) NOT NULL,

    CONSTRAINT pk_atencion_diagnostico
        PRIMARY KEY (id_atencion, codigo_diagnostico),

    CONSTRAINT fk_ad_atencion
        FOREIGN KEY (id_atencion)
        REFERENCES atencion(id_atencion)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_ad_diagnostico
        FOREIGN KEY (codigo_diagnostico)
        REFERENCES diagnostico(codigo_diagnostico)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB;


-- =========================================================
-- 12. RECETAS
-- =========================================================

CREATE TABLE receta (
    id_receta INT AUTO_INCREMENT,
    id_atencion INT NOT NULL,
    medicamento VARCHAR(150) NOT NULL,
    dosis VARCHAR(200) NOT NULL,
    dias_tratamiento INT NOT NULL,

    CONSTRAINT pk_receta
        PRIMARY KEY (id_receta),

    CONSTRAINT chk_receta_dias
        CHECK (dias_tratamiento > 0),

    CONSTRAINT fk_receta_atencion
        FOREIGN KEY (id_atencion)
        REFERENCES atencion(id_atencion)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;