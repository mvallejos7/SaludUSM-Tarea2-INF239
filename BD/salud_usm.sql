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
    id_paciente INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,
    nombre_completo VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    sexo VARCHAR(5) NOT NULL,
    id_prevision INT NOT NULL,
    telefono_contacto VARCHAR(20) NULL,
    comuna_residencia VARCHAR(60) NOT NULL,

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
) ENGINE=InnoDB;


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

    -- Solo las citas Reservadas (1) y Confirmadas (2)
    -- participan en las restricciones de horario.
    -- Los estados finales generan NULL y no bloquean el horario.
    id_medico_bloqueo INT
        GENERATED ALWAYS AS (
            CASE
                WHEN id_estado_cita IN (1, 2)
                THEN id_medico
                ELSE NULL
            END
        ) STORED,

    id_paciente_bloqueo INT
        GENERATED ALWAYS AS (
            CASE
                WHEN id_estado_cita IN (1, 2)
                THEN id_paciente
                ELSE NULL
            END
        ) STORED,

    CONSTRAINT pk_cita
        PRIMARY KEY (id_cita),

    CONSTRAINT uq_cita_medico_horario_activo
        UNIQUE (id_medico_bloqueo, fecha_hora),

    CONSTRAINT uq_cita_paciente_horario_activo
        UNIQUE (id_paciente_bloqueo, fecha_hora),

    -- Índices auxiliares para soporte de claves foráneas
    KEY idx_cita_paciente_fk (id_paciente),
    KEY idx_cita_medico_centro_fk (id_medico, codigo_centro),
    KEY idx_cita_medico_especialidad_fk (id_medico, id_especialidad),
    KEY idx_cita_estado_fk (id_estado_cita),

    CONSTRAINT fk_cita_paciente
        FOREIGN KEY (id_paciente)
        REFERENCES paciente(id_paciente)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_cita_medico_especialidad
        FOREIGN KEY (id_medico, id_especialidad)
        REFERENCES medico_especialidad(id_medico, id_especialidad)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_cita_medico_centro
        FOREIGN KEY (id_medico, codigo_centro)
        REFERENCES medico_centro(id_medico, codigo_centro)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_cita_estado
        FOREIGN KEY (id_estado_cita)
        REFERENCES estado_cita(id_estado_cita)
        ON UPDATE RESTRICT
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


-- =========================================================
-- DATOS DE PRUEBA
-- =========================================================

-- =========================================================
-- 1. PREVISION
-- =========================================================

INSERT INTO prevision (nombre) VALUES
('Fonasa'),
('Isapre'),
('Particular');


-- =========================================================
-- 2. ESTADOS DE CITA
-- =========================================================

INSERT INTO estado_cita (nombre) VALUES
('Reservada'),
('Confirmada'),
('Atendida'),
('No Asistió'),
('Cancelada');


-- =========================================================
-- 3. ESPECIALIDADES
-- =========================================================

INSERT INTO especialidad (nombre) VALUES
('Cardiología'),
('Pediatría'),
('Traumatología'),
('Medicina General'),
('Dermatología'),
('Neurología'),
('Oftalmología'),
('Gastroenterología');


-- =========================================================
-- 4. CENTROS MEDICOS
-- =========================================================

INSERT INTO centro_medico
(codigo_centro, nombre, comuna, region)
VALUES
('CM001', 'SaludUSM Providencia', 'Providencia', 'Región Metropolitana'),
('CM002', 'SaludUSM Maipú', 'Maipú', 'Región Metropolitana'),
('CM003', 'SaludUSM Viña del Mar', 'Viña del Mar', 'Región de Valparaíso'),
('CM004', 'SaludUSM Valparaíso', 'Valparaíso', 'Región de Valparaíso');

-- =========================================================
-- 5. USUARIOS PACIENTES
-- Password de prueba: SaludUSM2026!
-- =========================================================

INSERT INTO usuario
(rut, email, password_hash, rol)
VALUES
('10000000-8', 'paciente01@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000001-6', 'paciente02@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000002-4', 'paciente03@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000003-2', 'paciente04@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000004-0', 'paciente05@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000005-9', 'paciente06@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000006-7', 'paciente07@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000007-5', 'paciente08@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000008-3', 'paciente09@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000009-1', 'paciente10@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),

('10000010-5', 'paciente11@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000011-3', 'paciente12@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000012-1', 'paciente13@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000013-K', 'paciente14@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000014-8', 'paciente15@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000015-6', 'paciente16@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000016-4', 'paciente17@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000017-2', 'paciente18@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000018-0', 'paciente19@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000019-9', 'paciente20@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),

('10000020-2', 'paciente21@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000021-0', 'paciente22@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000022-9', 'paciente23@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000023-7', 'paciente24@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000024-5', 'paciente25@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000025-3', 'paciente26@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000026-1', 'paciente27@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000027-K', 'paciente28@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000028-8', 'paciente29@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000029-6', 'paciente30@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),

('10000030-K', 'paciente31@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000031-8', 'paciente32@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000032-6', 'paciente33@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000033-4', 'paciente34@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000034-2', 'paciente35@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000035-0', 'paciente36@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000036-9', 'paciente37@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000037-7', 'paciente38@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000038-5', 'paciente39@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE'),
('10000039-3', 'paciente40@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'PACIENTE');

-- =========================================================
-- 6. USUARIOS MEDICOS
-- =========================================================

INSERT INTO usuario
(rut, email, password_hash, rol)
VALUES
('20000001-3', 'medico01@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO'),
('20000002-1', 'medico02@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO'),
('20000003-K', 'medico03@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO'),
('20000004-8', 'medico04@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO'),
('20000005-6', 'medico05@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO'),
('20000006-4', 'medico06@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO'),
('20000007-2', 'medico07@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO'),
('20000008-0', 'medico08@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO'),
('20000009-9', 'medico09@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO'),
('20000010-2', 'medico10@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO'),
('20000011-0', 'medico11@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO'),
('20000012-9', 'medico12@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO'),
('20000013-7', 'medico13@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO'),
('20000014-5', 'medico14@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO'),
('20000015-3', 'medico15@saludusm.cl', '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa', 'MEDICO');

-- =========================================================
-- 7. USUARIO ADMINISTRADOR
-- =========================================================

INSERT INTO usuario
(rut, email, password_hash, rol)
VALUES
(
    '30000000-5',
    'admin@saludusm.cl',
    '$2y$12$GFklF/DCTpCEakTgaTQCVeFl3OLca5/ubTVpv0JoMw18BZWxNPQLa',
    'ADMIN'
);

-- =========================================================
-- 8. PACIENTES
-- Los usuarios 1 al 40 corresponden a pacientes
-- =========================================================

INSERT INTO paciente
(id_usuario, nombre_completo, fecha_nacimiento, sexo,
 id_prevision, telefono_contacto, comuna_residencia)
VALUES

(1,  'Nombre01 Apellido01', '1970-01-01', 'F',    2, '900000001', 'Providencia'),
(2,  'Nombre02 Apellido02', '1971-01-01', 'M',    1, '900000002', 'Maipú'),
(3,  'Nombre03 Apellido03', '1972-01-01', 'F',    2, '900000003', 'Viña del Mar'),
(4,  'Nombre04 Apellido04', '1973-01-01', 'M',    3, '900000004', 'Valparaíso'),
(5,  'Nombre05 Apellido05', '1974-01-01', 'F',    2, NULL,        'Santiago'),

(6,  'Nombre06 Apellido06', '1975-01-01', 'M',    1, '900000006', 'Providencia'),
(7,  'Nombre07 Apellido07', '1976-01-01', 'F',    2, '900000007', 'Maipú'),
(8,  'Nombre08 Apellido08', '1977-01-01', 'M',    1, '900000008', 'Viña del Mar'),
(9,  'Nombre09 Apellido09', '1978-01-01', 'F',    2, '900000009', 'Valparaíso'),
(10, 'Nombre10 Apellido10', '1979-01-01', 'M',    3, NULL,        'Santiago'),

(11, 'Nombre11 Apellido11', '1980-01-01', 'F',    2, '900000011', 'Providencia'),
(12, 'Nombre12 Apellido12', '1981-01-01', 'M',    1, '900000012', 'Maipú'),
(13, 'Nombre13 Apellido13', '1982-01-01', 'F',    2, '900000013', 'Viña del Mar'),
(14, 'Nombre14 Apellido14', '1983-01-01', 'M',    1, '900000014', 'Valparaíso'),
(15, 'Nombre15 Apellido15', '1984-01-01', 'F',    3, NULL,        'Santiago'),

(16, 'Nombre16 Apellido16', '1985-01-01', 'M',    2, '900000016', 'Providencia'),
(17, 'Nombre17 Apellido17', '1986-01-01', 'F',    1, '900000017', 'Maipú'),
(18, 'Nombre18 Apellido18', '1987-01-01', 'M',    2, '900000018', 'Viña del Mar'),
(19, 'Nombre19 Apellido19', '1988-01-01', 'F',    1, '900000019', 'Valparaíso'),
(20, 'Nombre20 Apellido20', '1989-01-01', 'M',    3, NULL,        'Santiago'),

(21, 'Nombre21 Apellido21', '1990-01-01', 'F',    2, '900000021', 'Providencia'),
(22, 'Nombre22 Apellido22', '1991-01-01', 'M',    1, '900000022', 'Maipú'),
(23, 'Nombre23 Apellido23', '1992-01-01', 'Otro', 2, '900000023', 'Viña del Mar'),
(24, 'Nombre24 Apellido24', '1993-01-01', 'M',    1, '900000024', 'Valparaíso'),
(25, 'Nombre25 Apellido25', '1994-01-01', 'F',    3, NULL,        'Santiago'),

(26, 'Nombre26 Apellido26', '2000-01-01', 'M',    2, '900000026', 'Providencia'),
(27, 'Nombre27 Apellido27', '2001-01-01', 'F',    1, '900000027', 'Maipú'),
(28, 'Nombre28 Apellido28', '2002-01-01', 'M',    2, '900000028', 'Viña del Mar'),
(29, 'Nombre29 Apellido29', '2003-01-01', 'F',    1, '900000029', 'Valparaíso'),
(30, 'Nombre30 Apellido30', '2004-01-01', 'M',    3, NULL,        'Santiago'),

(31, 'Nombre31 Apellido31', '2005-01-01', 'F',    2, '900000031', 'Providencia'),
(32, 'Nombre32 Apellido32', '2006-01-01', 'M',    1, '900000032', 'Maipú'),
(33, 'Nombre33 Apellido33', '2007-01-01', 'F',    2, '900000033', 'Viña del Mar'),
(34, 'Nombre34 Apellido34', '2008-01-01', 'M',    1, '900000034', 'Valparaíso'),
(35, 'Nombre35 Apellido35', '2009-01-01', 'F',    3, NULL,        'Santiago'),

(36, 'Nombre36 Apellido36', '2010-01-01', 'M',    2, '900000036', 'Providencia'),
(37, 'Nombre37 Apellido37', '2011-01-01', 'F',    1, '900000037', 'Maipú'),
(38, 'Nombre38 Apellido38', '2012-01-01', 'M',    2, '900000038', 'Viña del Mar'),
(39, 'Nombre39 Apellido39', '2013-01-01', 'F',    1, '900000039', 'Valparaíso'),
(40, 'Nombre40 Apellido40', '2014-01-01', 'M',    3, NULL,        'Santiago');

-- =========================================================
-- 9. MEDICOS
-- Los usuarios 41 al 55 corresponden a medicos
-- =========================================================

INSERT INTO medico
(id_usuario, nombre_completo)
VALUES
(41, 'Medico01 Apellido01'),
(42, 'Medico02 Apellido02'),
(43, 'Medico03 Apellido03'),
(44, 'Medico04 Apellido04'),
(45, 'Medico05 Apellido05'),

(46, 'Medico06 Apellido06'),
(47, 'Medico07 Apellido07'),
(48, 'Medico08 Apellido08'),
(49, 'Medico09 Apellido09'),
(50, 'Medico10 Apellido10'),

(51, 'Medico11 Apellido11'),
(52, 'Medico12 Apellido12'),
(53, 'Medico13 Apellido13'),
(54, 'Medico14 Apellido14'),
(55, 'Medico15 Apellido15');

-- =========================================================
-- 10. ESPECIALIDADES DE LOS MEDICOS
-- =========================================================

INSERT INTO medico_especialidad
(id_medico, id_especialidad)
VALUES

-- Medico01: Cardiologia y Medicina General
(1, 1),
(1, 4),

-- Medico02: Pediatria
(2, 2),

-- Medico03: Traumatologia y Medicina General
(3, 3),
(3, 4),

-- Medico04: Medicina General
(4, 4),

-- Medico05: Dermatologia y Medicina General
(5, 5),
(5, 4),

-- Medico06: Neurologia
(6, 6),

-- Medico07: Oftalmologia
(7, 7),

-- Medico08: Gastroenterologia y Medicina General
(8, 8),
(8, 4),

-- Medico09: Cardiologia
(9, 1),

-- Medico10: Pediatria y Medicina General
(10, 2),
(10, 4),

-- Medico11: Traumatologia
(11, 3),

-- Medico12: Dermatologia
(12, 5),

-- Medico13: Neurologia y Medicina General
(13, 6),
(13, 4),

-- Medico14: Oftalmologia y Medicina General
(14, 7),
(14, 4),

-- Medico15: Gastroenterologia
(15, 8);

-- =========================================================
-- 11. CENTROS DONDE ATIENDEN LOS MEDICOS
-- =========================================================

INSERT INTO medico_centro
(id_medico, codigo_centro)
VALUES

(1, 'CM001'),
(1, 'CM003'),

(2, 'CM001'),

(3, 'CM002'),
(3, 'CM004'),

(4, 'CM001'),
(4, 'CM002'),

(5, 'CM003'),

(6, 'CM001'),
(6, 'CM004'),

(7, 'CM002'),

(8, 'CM003'),
(8, 'CM004'),

(9, 'CM001'),

(10, 'CM002'),
(10, 'CM003'),

(11, 'CM004'),

(12, 'CM003'),

(13, 'CM001'),
(13, 'CM004'),

(14, 'CM002'),

(15, 'CM003'),
(15, 'CM004');

-- 9. CITAS MEDICAS

-- Estados:
-- 1 = Reservada
-- 2 = Confirmada
-- 3 = Atendida
-- 4 = No Asistió
-- 5 = Cancelada

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES


-- -------------------------------------------------------------
-- Medico 1
-- Cardiologia (1) / Medicina General (4)
-- Centros: CM001 / CM003
-- -------------------------------------------------------------

('2026-06-01 09:00:00', 1,  1, 'CM001', 1, 3),
('2026-06-01 09:30:00', 2,  1, 'CM003', 4, 3),
('2026-06-01 10:00:00', 3,  1, 'CM001', 1, 3),
('2026-06-01 10:30:00', 4,  1, 'CM003', 4, 3),
('2026-06-01 11:00:00', 5,  1, 'CM001', 1, 3),
('2026-06-01 11:30:00', 6,  1, 'CM003', 4, 3),
('2026-06-01 14:00:00', 7,  1, 'CM001', 1, 3),
('2026-06-01 14:30:00', 8,  1, 'CM003', 4, 4),
('2026-06-01 15:00:00', 9,  1, 'CM001', 1, 4),
('2026-06-01 15:30:00', 10, 1, 'CM003', 4, 4),
('2026-06-01 16:00:00', 11, 1, 'CM001', 1, 4),
('2026-06-01 16:30:00', 12, 1, 'CM003', 4, 4),

('2026-06-02 09:00:00', 13, 1, 'CM001', 1, 4),
('2026-06-02 09:30:00', 14, 1, 'CM003', 4, 4);


-- -------------------------------------------------------------
-- Medico 2
-- Pediatria (2)
-- Centro: CM001
-- 13 citas
-- -------------------------------------------------------------

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

('2026-06-03 09:00:00',  1, 2, 'CM001', 2, 3),
('2026-06-03 09:30:00',  2, 2, 'CM001', 2, 3),
('2026-06-03 10:00:00',  3, 2, 'CM001', 2, 3),
('2026-06-03 10:30:00',  4, 2, 'CM001', 2, 3),
('2026-06-03 11:00:00',  5, 2, 'CM001', 2, 3),
('2026-06-03 11:30:00',  6, 2, 'CM001', 2, 3),
('2026-06-03 14:00:00',  7, 2, 'CM001', 2, 3),
('2026-06-03 14:30:00',  8, 2, 'CM001', 2, 3),

('2026-06-03 15:00:00',  9, 2, 'CM001', 2, 4),
('2026-06-03 15:30:00', 10, 2, 'CM001', 2, 4),

('2026-06-03 16:00:00', 11, 2, 'CM001', 2, 5),

('2026-06-03 16:30:00', 12, 2, 'CM001', 2, 1),

('2026-06-03 17:00:00', 13, 2, 'CM001', 2, 2);


-- -------------------------------------------------------------
-- Medico 3
-- Traumatologia (3) / Medicina General (4)
-- Centros: CM002 / CM004
-- 12 citas
-- -------------------------------------------------------------

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

('2026-06-04 09:00:00', 14, 3, 'CM002', 3, 3),
('2026-06-04 09:30:00', 15, 3, 'CM004', 4, 3),
('2026-06-04 10:00:00', 16, 3, 'CM002', 3, 3),
('2026-06-04 10:30:00', 17, 3, 'CM004', 4, 3),
('2026-06-04 11:00:00', 18, 3, 'CM002', 3, 3),
('2026-06-04 11:30:00', 19, 3, 'CM004', 4, 3),
('2026-06-04 14:00:00', 20, 3, 'CM002', 3, 3),
('2026-06-04 14:30:00', 21, 3, 'CM004', 4, 3),

('2026-06-04 15:00:00', 22, 3, 'CM002', 3, 4),
('2026-06-04 15:30:00', 23, 3, 'CM004', 4, 4),

('2026-06-04 16:00:00', 24, 3, 'CM002', 3, 5),

('2026-06-04 16:30:00', 25, 3, 'CM004', 4, 1);


-- -------------------------------------------------------------
-- Medico 4
-- Medicina General (4)
-- Centros: CM001 / CM002
-- 11 citas

-- IMPORTANTE:
-- Este medico no tendra citas "No Asistio".
-- Esto nos servira posteriormente para la Consulta 7.
-- -------------------------------------------------------------

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

('2026-06-05 09:00:00', 26, 4, 'CM001', 4, 3),
('2026-06-05 09:30:00', 27, 4, 'CM002', 4, 3),
('2026-06-05 10:00:00', 28, 4, 'CM001', 4, 3),
('2026-06-05 10:30:00', 29, 4, 'CM002', 4, 3),
('2026-06-05 11:00:00', 30, 4, 'CM001', 4, 3),
('2026-06-05 11:30:00', 31, 4, 'CM002', 4, 3),
('2026-06-05 14:00:00', 32, 4, 'CM001', 4, 3),
('2026-06-05 14:30:00', 33, 4, 'CM002', 4, 3),

('2026-06-05 15:00:00', 34, 4, 'CM001', 4, 5),

('2026-06-05 15:30:00', 35, 4, 'CM002', 4, 1),

('2026-06-05 16:00:00', 36, 4, 'CM001', 4, 2);


-- -------------------------------------------------------------
-- Medico 5
-- Dermatologia (5) / Medicina General (4)
-- Centro: CM003
-- 10 citas
-- -------------------------------------------------------------

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

('2026-06-08 09:00:00',  1, 5, 'CM003', 5, 3),
('2026-06-08 09:30:00',  2, 5, 'CM003', 5, 3),
('2026-06-08 10:00:00',  3, 5, 'CM003', 4, 3),
('2026-06-08 10:30:00',  4, 5, 'CM003', 5, 3),
('2026-06-08 11:00:00',  5, 5, 'CM003', 4, 3),
('2026-06-08 11:30:00',  6, 5, 'CM003', 5, 3),

('2026-06-08 14:00:00',  7, 5, 'CM003', 5, 4),

('2026-06-08 14:30:00',  8, 5, 'CM003', 4, 5),

('2026-06-08 15:00:00',  9, 5, 'CM003', 5, 1),

('2026-06-08 15:30:00', 10, 5, 'CM003', 4, 2);


-- -------------------------------------------------------------
-- Medico 6
-- Neurologia (6)
-- Centros: CM001 / CM004
-- 9 citas
-- -------------------------------------------------------------

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

('2026-06-09 09:00:00', 11, 6, 'CM001', 6, 3),
('2026-06-09 09:30:00', 12, 6, 'CM004', 6, 3),
('2026-06-09 10:00:00', 13, 6, 'CM001', 6, 3),
('2026-06-09 10:30:00', 14, 6, 'CM004', 6, 3),
('2026-06-09 11:00:00', 15, 6, 'CM001', 6, 3),

('2026-06-09 11:30:00', 16, 6, 'CM004', 6, 4),

('2026-06-09 14:00:00', 17, 6, 'CM001', 6, 5),

('2026-06-09 14:30:00', 18, 6, 'CM004', 6, 1),

('2026-06-09 15:00:00', 19, 6, 'CM001', 6, 2);


-- -------------------------------------------------------------
-- Medico 7
-- Oftalmologia (7)
-- Centro: CM002
-- 8 citas
-- -------------------------------------------------------------

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

('2026-06-10 09:00:00', 20, 7, 'CM002', 7, 3),
('2026-06-10 09:30:00', 21, 7, 'CM002', 7, 3),
('2026-06-10 10:00:00', 22, 7, 'CM002', 7, 3),
('2026-06-10 10:30:00', 23, 7, 'CM002', 7, 3),
('2026-06-10 11:00:00', 24, 7, 'CM002', 7, 3),

('2026-06-10 11:30:00', 25, 7, 'CM002', 7, 5),

('2026-06-10 14:00:00', 26, 7, 'CM002', 7, 1),

('2026-06-10 14:30:00', 27, 7, 'CM002', 7, 2);


-- -------------------------------------------------------------
-- Medico 8
-- Gastroenterologia (8) / Medicina General (4)
-- Centros: CM003 / CM004
-- 7 citas
-- -------------------------------------------------------------

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

('2026-06-11 09:00:00', 28, 8, 'CM003', 8, 3),
('2026-06-11 09:30:00', 29, 8, 'CM004', 4, 3),
('2026-06-11 10:00:00', 30, 8, 'CM003', 8, 3),
('2026-06-11 10:30:00', 31, 8, 'CM004', 4, 3),

('2026-06-11 11:00:00', 32, 8, 'CM003', 8, 5),

('2026-06-11 11:30:00', 33, 8, 'CM004', 4, 1),

('2026-06-11 14:00:00', 34, 8, 'CM003', 8, 2);


-- -------------------------------------------------------------
-- Medico 9
-- Cardiologia (1)
-- Centro: CM001
-- 7 citas
-- -------------------------------------------------------------

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

('2026-06-12 09:00:00', 35, 9, 'CM001', 1, 3),
('2026-06-12 09:30:00', 36, 9, 'CM001', 1, 3),
('2026-06-12 10:00:00', 37, 9, 'CM001', 1, 3),
('2026-06-12 10:30:00', 38, 9, 'CM001', 1, 3),

('2026-06-12 11:00:00', 39, 9, 'CM001', 1, 4),

('2026-06-12 11:30:00', 40, 9, 'CM001', 1, 5),

('2026-06-12 14:00:00', 1, 9, 'CM001', 1, 1);


-- -------------------------------------------------------------
-- Medico 10
-- Pediatria (2) / Medicina General (4)
-- Centros: CM002 / CM003
-- 6 citas
-- -------------------------------------------------------------

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

('2026-06-15 09:00:00',  2, 10, 'CM002', 2, 3),
('2026-06-15 09:30:00',  3, 10, 'CM003', 4, 3),
('2026-06-15 10:00:00',  4, 10, 'CM002', 2, 3),
('2026-06-15 10:30:00',  5, 10, 'CM003', 4, 3),

('2026-06-15 11:00:00',  6, 10, 'CM002', 2, 5),

('2026-06-15 11:30:00',  7, 10, 'CM003', 4, 2);


-- -------------------------------------------------------------
-- Medico 11
-- Traumatologia (3)
-- Centro: CM004
-- 6 citas
-- -------------------------------------------------------------

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

('2026-06-16 09:00:00',  8, 11, 'CM004', 3, 3),
('2026-06-16 09:30:00',  9, 11, 'CM004', 3, 3),
('2026-06-16 10:00:00', 10, 11, 'CM004', 3, 3),

('2026-06-16 10:30:00', 11, 11, 'CM004', 3, 4),

('2026-06-16 11:00:00', 12, 11, 'CM004', 3, 5),

('2026-06-16 11:30:00', 13, 11, 'CM004', 3, 1);


-- -------------------------------------------------------------
-- Medico 12
-- Dermatologia (5)
-- Centro: CM003
-- 5 citas
-- -------------------------------------------------------------

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

('2026-06-17 09:00:00', 14, 12, 'CM003', 5, 3),
('2026-06-17 09:30:00', 15, 12, 'CM003', 5, 3),
('2026-06-17 10:00:00', 16, 12, 'CM003', 5, 3),

('2026-06-17 10:30:00', 17, 12, 'CM003', 5, 4),

('2026-06-17 11:00:00', 18, 12, 'CM003', 5, 2);


-- -------------------------------------------------------------
-- Medico 13
-- Neurologia (6) / Medicina General (4)
-- Centros: CM001 / CM004
-- 4 citas
-- -------------------------------------------------------------

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

('2026-06-18 09:00:00', 19, 13, 'CM001', 6, 3),
('2026-06-18 09:30:00', 20, 13, 'CM004', 4, 3),

('2026-06-18 10:00:00', 21, 13, 'CM001', 6, 5),

('2026-06-18 10:30:00', 22, 13, 'CM004', 4, 1);


-- -------------------------------------------------------------
-- Medico 14
-- Oftalmologia (7) / Medicina General (4)
-- Centro: CM002
-- 4 citas
-- -------------------------------------------------------------

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

('2026-06-19 09:00:00', 23, 14, 'CM002', 7, 3),
('2026-06-19 09:30:00', 24, 14, 'CM002', 4, 3),

('2026-06-19 10:00:00', 25, 14, 'CM002', 7, 4),

('2026-06-19 10:30:00', 26, 14, 'CM002', 4, 2);


-- -------------------------------------------------------------
-- Medico 15
-- Gastroenterologia (8)
-- Centros: CM003 / CM004
-- 4 citas
-- -------------------------------------------------------------

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

('2026-06-22 09:00:00', 27, 15, 'CM003', 8, 3),
('2026-06-22 09:30:00', 28, 15, 'CM004', 8, 3),

('2026-06-22 10:00:00', 29, 15, 'CM003', 8, 5),

('2026-06-22 10:30:00', 30, 15, 'CM004', 8, 2);



-- 10. DIAGNOSTICOS

INSERT INTO diagnostico
(codigo_diagnostico, descripcion)
VALUES

('I10', 'Hipertensión esencial (primaria)'),
('J00', 'Rinofaringitis aguda (resfriado común)'),
('M54.5', 'Dolor lumbar'),
('L30.9', 'Dermatitis no especificada'),
('G43.9', 'Migraña no especificada'),
('H10.9', 'Conjuntivitis no especificada'),
('K29.7', 'Gastritis no especificada'),
('Z00.0', 'Examen médico general'),
('J30.2', 'Rinitis alérgica estacional'),
('M25.5', 'Dolor articular');



-- 11. ATENCIONES

-- Se crea una atencion solamente para las citas
-- que se encuentran en estado Atendida (id_estado_cita = 3)

INSERT INTO atencion
(id_cita, motivo_consulta, observaciones_clinicas)

SELECT
    c.id_cita,

    CASE c.id_especialidad
        WHEN 1 THEN 'Control cardiologico'
        WHEN 2 THEN 'Consulta pediatrica'
        WHEN 3 THEN 'Dolor traumatologico'
        WHEN 4 THEN 'Consulta medicina general'
        WHEN 5 THEN 'Consulta dermatologica'
        WHEN 6 THEN 'Consulta neurologica'
        WHEN 7 THEN 'Consulta oftalmologica'
        WHEN 8 THEN 'Consulta gastroenterologica'
    END AS motivo_consulta,

    'Paciente evaluado durante la consulta.'

FROM cita c
WHERE c.id_estado_cita = 3
ORDER BY c.id_cita;



-- 12. DIAGNOSTICOS DE LAS ATENCIONES

-- Cada atencion recibe al menos un diagnostico.
-- El diagnostico principal se asigna segun la especialidad.

INSERT INTO atencion_diagnostico
(id_atencion, codigo_diagnostico)

SELECT
    a.id_atencion,

    CASE c.id_especialidad
        WHEN 1 THEN 'I10'     -- Hipertensión esencial
        WHEN 2 THEN 'J00'     -- Resfriado común
        WHEN 3 THEN 'M54.5'   -- Dolor lumbar
        WHEN 4 THEN 'Z00.0'   -- Examen médico general
        WHEN 5 THEN 'L30.9'   -- Dermatitis no especificada
        WHEN 6 THEN 'G43.9'   -- Migraña no especificada
        WHEN 7 THEN 'H10.9'   -- Conjuntivitis no especificada
        WHEN 8 THEN 'K29.7'   -- Gastritis no especificada
    END AS codigo_diagnostico

FROM atencion a
JOIN cita c
    ON a.id_cita = c.id_cita
ORDER BY a.id_atencion;



-- DIAGNOSTICOS SECUNDARIOS

INSERT INTO atencion_diagnostico
(id_atencion, codigo_diagnostico)

SELECT
    id_atencion,
    'J30.2'
FROM atencion
WHERE MOD(id_atencion, 6) = 0;



INSERT INTO atencion_diagnostico
(id_atencion, codigo_diagnostico)

SELECT
    id_atencion,
    'M25.5'
FROM atencion
WHERE MOD(id_atencion, 7) = 0;



-- 13. RECETAS

-- Se genera una receta principal para cada atención,
-- según la especialidad de la cita asociada.

INSERT INTO receta
(id_atencion, medicamento, dosis, dias_tratamiento)

SELECT
    a.id_atencion,

    CASE c.id_especialidad
        WHEN 1 THEN 'Losartan'
        WHEN 2 THEN 'Paracetamol'
        WHEN 3 THEN 'Ibuprofeno'
        WHEN 4 THEN 'Paracetamol'
        WHEN 5 THEN 'Loratadina'
        WHEN 6 THEN 'Sumatriptan'
        WHEN 7 THEN 'Lagrimas artificiales'
        WHEN 8 THEN 'Omeprazol'
    END AS medicamento,

    CASE c.id_especialidad
        WHEN 1 THEN '1 comprimido cada 24 horas'
        WHEN 2 THEN '1 comprimido cada 8 horas'
        WHEN 3 THEN '1 comprimido cada 8 horas'
        WHEN 4 THEN '1 comprimido cada 8 horas'
        WHEN 5 THEN '1 comprimido cada 24 horas'
        WHEN 6 THEN '1 comprimido si presenta sintomas'
        WHEN 7 THEN '2 gotas cada 8 horas'
        WHEN 8 THEN '1 capsula cada 24 horas'
    END AS dosis,

    CASE c.id_especialidad
        WHEN 1 THEN 30
        WHEN 2 THEN 5
        WHEN 3 THEN 7
        WHEN 4 THEN 5
        WHEN 5 THEN 10
        WHEN 6 THEN 5
        WHEN 7 THEN 7
        WHEN 8 THEN 14
    END AS dias_tratamiento

FROM atencion a
JOIN cita c
    ON a.id_cita = c.id_cita
ORDER BY a.id_atencion;


-- RECETAS ADICIONALES PARA PEDIATRIA
-- Permiten disponer de al menos 5 medicamentos distintos
-- para realizar el ranking solicitado en la Consulta 8.

-- Ibuprofeno
INSERT INTO receta
(id_atencion, medicamento, dosis, dias_tratamiento)

SELECT
    a.id_atencion,
    'Ibuprofeno',
    '1 comprimido cada 8 horas',
    5
FROM atencion a
JOIN cita c
    ON a.id_cita = c.id_cita
WHERE c.id_especialidad = 2
ORDER BY a.id_atencion
LIMIT 1;


-- Loratadina
INSERT INTO receta
(id_atencion, medicamento, dosis, dias_tratamiento)

SELECT
    a.id_atencion,
    'Loratadina',
    '1 comprimido cada 24 horas',
    7
FROM atencion a
JOIN cita c
    ON a.id_cita = c.id_cita
WHERE c.id_especialidad = 2
ORDER BY a.id_atencion
LIMIT 1 OFFSET 1;


-- Amoxicilina
INSERT INTO receta
(id_atencion, medicamento, dosis, dias_tratamiento)

SELECT
    a.id_atencion,
    'Amoxicilina',
    '1 comprimido cada 8 horas',
    7
FROM atencion a
JOIN cita c
    ON a.id_cita = c.id_cita
WHERE c.id_especialidad = 2
ORDER BY a.id_atencion
LIMIT 1 OFFSET 2;


-- Salbutamol
INSERT INTO receta
(id_atencion, medicamento, dosis, dias_tratamiento)

SELECT
    a.id_atencion,
    'Salbutamol',
    '2 inhalaciones cada 8 horas',
    5
FROM atencion a
JOIN cita c
    ON a.id_cita = c.id_cita
WHERE c.id_especialidad = 2
ORDER BY a.id_atencion
LIMIT 1 OFFSET 3;


-- RECETAS SECUNDARIAS
-- Algunas atenciones reciben además Paracetamol.

INSERT INTO receta
(id_atencion, medicamento, dosis, dias_tratamiento)

SELECT
    id_atencion,
    'Paracetamol',
    '1 comprimido cada 8 horas',
    5
FROM atencion
WHERE MOD(id_atencion, 5) = 0;

-- =========================================================
-- 14. CITAS FUTURAS PARA PRUEBAS DE TAREA 2
-- Permiten probar citas proximas, reprogramacion,
-- cancelacion y reglas de negocio.
-- =========================================================

INSERT INTO cita
(fecha_hora, id_paciente, id_medico, codigo_centro,
 id_especialidad, id_estado_cita)
VALUES

-- Medico 1: dos especialidades y dos centros
('2027-01-12 09:00:00',  1, 1, 'CM001', 1, 1),
('2027-01-12 09:30:00',  2, 1, 'CM003', 4, 2),

-- Medico 2: Pediatria
('2027-01-13 09:00:00',  3, 2, 'CM001', 2, 1),
('2027-01-13 09:30:00',  4, 2, 'CM001', 2, 2),

-- Medico 3: Traumatologia / Medicina General
('2027-01-14 09:00:00',  5, 3, 'CM002', 3, 1),
('2027-01-14 09:30:00',  6, 3, 'CM004', 4, 2),

-- Medico 4: Medicina General
('2027-01-15 09:00:00',  7, 4, 'CM001', 4, 1),
('2027-01-15 09:30:00',  8, 4, 'CM002', 4, 2),

-- Medico 5: Dermatologia / Medicina General
('2027-01-16 09:00:00',  9, 5, 'CM003', 5, 1),
('2027-01-16 09:30:00', 10, 5, 'CM003', 4, 2),

-- Medico 6: Neurologia
('2027-01-19 09:00:00', 11, 6, 'CM001', 6, 1),
('2027-01-19 09:30:00', 12, 6, 'CM004', 6, 2);


-- =========================================================
-- OBJETOS SQL: VIEW, FUNCTION, PROCEDURE Y TRIGGER
-- =========================================================

-- =========================================================
-- VIEW: DETALLE COMPLETO DE LAS CITAS
-- =========================================================

DROP VIEW IF EXISTS vw_citas_detalle;

CREATE VIEW vw_citas_detalle AS

SELECT
    c.id_cita,
    c.fecha_hora,

    p.id_paciente,
    up.rut AS rut_paciente,
    p.nombre_completo AS paciente,
    pr.nombre AS prevision,

    m.id_medico,
    um.rut AS rut_medico,
    m.nombre_completo AS medico,

    e.id_especialidad,
    e.nombre AS especialidad,

    cm.codigo_centro,
    cm.nombre AS centro,
    cm.comuna,
    cm.region,

    ec.id_estado_cita,
    ec.nombre AS estado

FROM cita c

JOIN paciente p
    ON c.id_paciente = p.id_paciente

JOIN usuario up
    ON p.id_usuario = up.id_usuario

JOIN prevision pr
    ON p.id_prevision = pr.id_prevision

JOIN medico m
    ON c.id_medico = m.id_medico

JOIN usuario um
    ON m.id_usuario = um.id_usuario

JOIN especialidad e
    ON c.id_especialidad = e.id_especialidad

JOIN centro_medico cm
    ON c.codigo_centro = cm.codigo_centro

JOIN estado_cita ec
    ON c.id_estado_cita = ec.id_estado_cita;

-- =========================================================
-- FUNCTION: PORCENTAJE DE INASISTENCIA POR CENTRO
-- =========================================================

DROP FUNCTION IF EXISTS fn_porcentaje_inasistencia;

DELIMITER //

CREATE FUNCTION fn_porcentaje_inasistencia(
    p_codigo_centro VARCHAR(20)
)
RETURNS DECIMAL(5,2)
READS SQL DATA

BEGIN

    DECLARE v_total_citas INT DEFAULT 0;
    DECLARE v_no_asistio INT DEFAULT 0;
    DECLARE v_porcentaje DECIMAL(5,2) DEFAULT 0.00;


    -- Cantidad total de citas del centro
    SELECT COUNT(*)
    INTO v_total_citas
    FROM cita
    WHERE codigo_centro = p_codigo_centro;


    -- Cantidad de citas en estado "No Asistió"
    SELECT COUNT(*)
    INTO v_no_asistio
    FROM cita c
    JOIN estado_cita ec
        ON c.id_estado_cita = ec.id_estado_cita
    WHERE c.codigo_centro = p_codigo_centro
      AND ec.nombre = 'No Asistió';


    -- Evita una división por cero
    IF v_total_citas = 0 THEN
        RETURN 0.00;
    END IF;


    SET v_porcentaje =
        ROUND(
            (v_no_asistio * 100.0) / v_total_citas,
            2
        );


    RETURN v_porcentaje;

END//

DELIMITER ;

-- =========================================================
-- PROCEDURE: CANCELAR CITAS VENCIDAS
-- =========================================================

DROP PROCEDURE IF EXISTS sp_cancelar_citas_vencidas;

DELIMITER //

CREATE PROCEDURE sp_cancelar_citas_vencidas()

BEGIN

    DECLARE v_id_cancelada INT;

    -- Obtener el ID correspondiente al estado Cancelada
    SELECT id_estado_cita
    INTO v_id_cancelada
    FROM estado_cita
    WHERE nombre = 'Cancelada';


    -- Cambiar a Cancelada las citas vencidas que todavía
    -- se encuentren Reservadas o Confirmadas
    UPDATE cita
    SET id_estado_cita = v_id_cancelada
    WHERE fecha_hora < CURRENT_TIMESTAMP
      AND id_estado_cita IN (
            SELECT id_estado_cita
            FROM estado_cita
            WHERE nombre IN ('Reservada', 'Confirmada')
      );


    -- Informar cuántas citas fueron modificadas
    SELECT ROW_COUNT() AS citas_canceladas;

END//

DELIMITER ;

-- =========================================================
-- TRIGGER: VALIDAR CREACION DE ATENCION
-- =========================================================

DROP TRIGGER IF EXISTS trg_validar_atencion;

DELIMITER //

CREATE TRIGGER trg_validar_atencion
BEFORE INSERT ON atencion

FOR EACH ROW

BEGIN

    DECLARE v_estado VARCHAR(20);

    -- Obtener el estado de la cita asociada
    SELECT ec.nombre
    INTO v_estado
    FROM cita c
    JOIN estado_cita ec
        ON c.id_estado_cita = ec.id_estado_cita
    WHERE c.id_cita = NEW.id_cita;


    -- Solo se puede crear una atencion
    -- si la cita se encuentra Atendida
    IF v_estado <> 'Atendida' THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT =
            'No se puede registrar una atención porque la cita no está Atendida';

    END IF;

END//

DELIMITER ;

-- =========================================================
-- USUARIO DE CONEXION DE LA APLICACION PHP
-- =========================================================

CREATE USER IF NOT EXISTS 'salud_app'@'localhost'
IDENTIFIED BY 'SaludUSM_DB_2026!';

ALTER USER 'salud_app'@'localhost'
IDENTIFIED BY 'SaludUSM_DB_2026!';

GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE
ON salud_usm.*
TO 'salud_app'@'localhost';

FLUSH PRIVILEGES;

