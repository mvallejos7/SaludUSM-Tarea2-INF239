USE salud_usm;

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