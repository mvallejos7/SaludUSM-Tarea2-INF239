USE salud_usm;

-- =========================================================
-- AJUSTE DE DISPONIBILIDAD DE CITAS
--
-- Solo las citas Reservadas (1) y Confirmadas (2)
-- deben bloquear un horario.
--
-- Una cita Cancelada, Atendida o No Asistió conserva
-- su registro histórico, pero no bloquea futuras reservas.
-- =========================================================


-- ---------------------------------------------------------
-- 1. Índices auxiliares para conservar soporte de las FK
-- ---------------------------------------------------------

CREATE INDEX idx_cita_paciente_fk
ON cita (id_paciente);

CREATE INDEX idx_cita_medico_centro_fk
ON cita (id_medico, codigo_centro);


-- ---------------------------------------------------------
-- 2. Detectar y eliminar UNIQUE antiguo del paciente
-- ---------------------------------------------------------

SELECT INDEX_NAME
INTO @idx_paciente
FROM (
    SELECT
        INDEX_NAME,
        GROUP_CONCAT(
            COLUMN_NAME
            ORDER BY SEQ_IN_INDEX
            SEPARATOR ','
        ) AS columnas
    FROM information_schema.statistics
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'cita'
      AND NON_UNIQUE = 0
      AND INDEX_NAME <> 'PRIMARY'
    GROUP BY INDEX_NAME
) indices
WHERE columnas = 'id_paciente,fecha_hora'
LIMIT 1;


SET @sql_paciente =
    IF(
        @idx_paciente IS NULL,
        'SELECT 1',
        CONCAT(
            'ALTER TABLE cita DROP INDEX `',
            @idx_paciente,
            '`'
        )
    );

PREPARE stmt_paciente
FROM @sql_paciente;

EXECUTE stmt_paciente;

DEALLOCATE PREPARE stmt_paciente;


-- ---------------------------------------------------------
-- 3. Detectar y eliminar UNIQUE antiguo del médico
--
-- Se contempla tanto:
-- id_medico + fecha_hora
--
-- como el diseño anterior:
-- id_medico + codigo_centro + fecha_hora
-- ---------------------------------------------------------

SELECT INDEX_NAME
INTO @idx_medico
FROM (
    SELECT
        INDEX_NAME,
        GROUP_CONCAT(
            COLUMN_NAME
            ORDER BY SEQ_IN_INDEX
            SEPARATOR ','
        ) AS columnas
    FROM information_schema.statistics
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'cita'
      AND NON_UNIQUE = 0
      AND INDEX_NAME <> 'PRIMARY'
    GROUP BY INDEX_NAME
) indices
WHERE columnas IN (
    'id_medico,fecha_hora',
    'id_medico,codigo_centro,fecha_hora'
)
LIMIT 1;


SET @sql_medico =
    IF(
        @idx_medico IS NULL,
        'SELECT 1',
        CONCAT(
            'ALTER TABLE cita DROP INDEX `',
            @idx_medico,
            '`'
        )
    );

PREPARE stmt_medico
FROM @sql_medico;

EXECUTE stmt_medico;

DEALLOCATE PREPARE stmt_medico;


-- ---------------------------------------------------------
-- 4. Crear columnas generadas
--
-- Si la cita está Reservada o Confirmada:
-- guarda el ID y participa en la restricción UNIQUE.
--
-- En estados finales:
-- queda NULL y no bloquea el horario.
-- ---------------------------------------------------------

ALTER TABLE cita

ADD COLUMN id_medico_bloqueo INT
GENERATED ALWAYS AS (
    CASE
        WHEN id_estado_cita IN (1, 2)
        THEN id_medico
        ELSE NULL
    END
) STORED,

ADD COLUMN id_paciente_bloqueo INT
GENERATED ALWAYS AS (
    CASE
        WHEN id_estado_cita IN (1, 2)
        THEN id_paciente
        ELSE NULL
    END
) STORED;


-- ---------------------------------------------------------
-- 5. Nuevas restricciones de sobre-agendamiento
-- ---------------------------------------------------------

ALTER TABLE cita

ADD CONSTRAINT uq_cita_medico_horario_activo
UNIQUE (
    id_medico_bloqueo,
    fecha_hora
),

ADD CONSTRAINT uq_cita_paciente_horario_activo
UNIQUE (
    id_paciente_bloqueo,
    fecha_hora
);