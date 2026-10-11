<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('MEDICO');

// ---------------------------------------------------------
// Obtener médico conectado
// ---------------------------------------------------------

$stmtMedico = $pdo->prepare("
    SELECT
        id_medico,
        nombre_completo
    FROM medico
    WHERE id_usuario = ?
    LIMIT 1
");

$stmtMedico->execute([
    $_SESSION['id_usuario']
]);

$medico = $stmtMedico->fetch();

if (!$medico) {
    exit('No fue posible identificar al médico.');
}

$idMedico = $medico['id_medico'];

// ---------------------------------------------------------
// Obtener pacientes atendidos por este médico
// ---------------------------------------------------------

$stmtPacientes = $pdo->prepare("
    SELECT DISTINCT
        p.id_paciente,
        p.nombre_completo,
        u.rut,
        pr.nombre AS prevision

    FROM atencion a

    JOIN cita c
        ON a.id_cita = c.id_cita

    JOIN paciente p
        ON c.id_paciente = p.id_paciente

    JOIN usuario u
        ON p.id_usuario = u.id_usuario

    JOIN prevision pr
        ON p.id_prevision = pr.id_prevision

    WHERE c.id_medico = ?

    ORDER BY p.nombre_completo
");

$stmtPacientes->execute([
    $idMedico
]);

$pacientes = $stmtPacientes->fetchAll();

// ---------------------------------------------------------
// Paciente seleccionado
// ---------------------------------------------------------

$idPaciente = $_GET['id_paciente'] ?? '';

$pacienteSeleccionado = null;
$historial = [];

if ($idPaciente !== '') {

    if (!ctype_digit((string)$idPaciente)) {
        exit('El paciente seleccionado no es válido.');
    }

    // -----------------------------------------------------
    // Verificar que sea realmente paciente del médico
    // -----------------------------------------------------

    $stmtAutorizacion = $pdo->prepare("
        SELECT
            p.id_paciente,
            p.nombre_completo,
            u.rut,
            pr.nombre AS prevision

        FROM paciente p

        JOIN usuario u
            ON p.id_usuario = u.id_usuario

        JOIN prevision pr
            ON p.id_prevision = pr.id_prevision

        WHERE p.id_paciente = ?

          AND EXISTS (
                SELECT 1

                FROM atencion a

                JOIN cita c
                    ON a.id_cita = c.id_cita

                WHERE c.id_paciente = p.id_paciente
                  AND c.id_medico = ?
          )

        LIMIT 1
    ");

    $stmtAutorizacion->execute([
        $idPaciente,
        $idMedico
    ]);

    $pacienteSeleccionado =
        $stmtAutorizacion->fetch();

    if (!$pacienteSeleccionado) {
        exit(
            'No tienes autorización para consultar el historial de este paciente.'
        );
    }

    // -----------------------------------------------------
    // Historial del paciente atendido por este médico
    // -----------------------------------------------------

    $stmtHistorial = $pdo->prepare("
        SELECT
            a.id_atencion,
            c.fecha_hora,

            e.nombre AS especialidad,

            m.nombre_completo AS medico,

            cm.nombre AS centro,

            a.motivo_consulta,
            a.observaciones_clinicas,

            (
                SELECT GROUP_CONCAT(
                    DISTINCT CONCAT(
                        d.codigo_diagnostico,
                        ' - ',
                        d.descripcion
                    )
                    ORDER BY d.codigo_diagnostico
                    SEPARATOR ' | '
                )

                FROM atencion_diagnostico ad

                JOIN diagnostico d
                    ON ad.codigo_diagnostico =
                    d.codigo_diagnostico

                WHERE ad.id_atencion =
                    a.id_atencion
            ) AS diagnosticos,

            (
                SELECT GROUP_CONCAT(
                    CONCAT(
                        r.medicamento,
                        ' - ',
                        r.dosis,
                        ' - ',
                        r.dias_tratamiento,
                        ' días'
                    )
                    ORDER BY r.id_receta
                    SEPARATOR ' | '
                )

                FROM receta r

                WHERE r.id_atencion =
                    a.id_atencion
            ) AS recetas

        FROM atencion a

        JOIN cita c
            ON a.id_cita = c.id_cita

        JOIN especialidad e
            ON c.id_especialidad =
            e.id_especialidad

        JOIN medico m
            ON c.id_medico =
            m.id_medico

        JOIN centro_medico cm
            ON c.codigo_centro =
            cm.codigo_centro

        WHERE c.id_paciente = ?

        ORDER BY c.fecha_hora DESC
    ");

    $stmtHistorial->execute([
        $idPaciente
    ]);

    $historial = $stmtHistorial->fetchAll();
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Historial de pacientes - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Historial de pacientes</h2>

    <p>
        <strong>Médico:</strong>
        <?= htmlspecialchars($medico['nombre_completo']) ?>
    </p>


    <h3>Pacientes atendidos</h3>


    <?php if (empty($pacientes)): ?>

        <p>
            Todavía no has atendido pacientes.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>

                <tr>
                    <th>Paciente</th>
                    <th>RUT</th>
                    <th>Previsión</th>
                    <th>Acción</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($pacientes as $paciente): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $paciente['nombre_completo']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $paciente['rut']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $paciente['prevision']
                            ) ?>
                        </td>

                        <td>

                            <a
                                href="/medico/historial_pacientes.php?id_paciente=<?= urlencode($paciente['id_paciente']) ?>"
                            >
                                Ver historial
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>


    <?php if ($pacienteSeleccionado): ?>

        <hr>

        <h3>
            Historial de
            <?= htmlspecialchars(
                $pacienteSeleccionado['nombre_completo']
            ) ?>
        </h3>

        <p>
            <strong>RUT:</strong>
            <?= htmlspecialchars(
                $pacienteSeleccionado['rut']
            ) ?>
        </p>

        <p>
            <strong>Previsión:</strong>
            <?= htmlspecialchars(
                $pacienteSeleccionado['prevision']
            ) ?>
        </p>


        <?php if (empty($historial)): ?>

            <p>
                No existen atenciones registradas.
            </p>

        <?php else: ?>

            <?php foreach ($historial as $atencion): ?>

                <hr>

                <h4>
                    Atención
                    #<?= htmlspecialchars(
                        $atencion['id_atencion']
                    ) ?>
                </h4>

                <p>
                    <strong>Fecha:</strong>

                    <?= htmlspecialchars(
                        date(
                            'd-m-Y H:i',
                            strtotime(
                                $atencion['fecha_hora']
                            )
                        )
                    ) ?>
                </p>

                <p>
                    <strong>Especialidad:</strong>
                    <?= htmlspecialchars(
                        $atencion['especialidad']
                    ) ?>
                </p>

                <p>
                    <strong>Médico:</strong>
                    <?= htmlspecialchars(
                        $atencion['medico']
                    ) ?>
                </p>

                <p>
                    <strong>Centro:</strong>
                    <?= htmlspecialchars(
                        $atencion['centro']
                    ) ?>
                </p>

                <p>
                    <strong>Motivo:</strong>
                    <?= htmlspecialchars(
                        $atencion['motivo_consulta']
                    ) ?>
                </p>

                <p>
                    <strong>Observaciones:</strong>

                    <?= htmlspecialchars(
                        $atencion['observaciones_clinicas']
                        ?? 'Sin observaciones'
                    ) ?>
                </p>

                <p>
                    <strong>Diagnósticos:</strong>

                    <?= htmlspecialchars(
                        $atencion['diagnosticos']
                        ?? 'Sin diagnósticos registrados'
                    ) ?>
                </p>

                <p>
                    <strong>Recetas:</strong>

                    <?= htmlspecialchars(
                        $atencion['recetas']
                        ?? 'Sin recetas registradas'
                    ) ?>
                </p>

            <?php endforeach; ?>

        <?php endif; ?>

    <?php endif; ?>


    <br><br>

    <a href="/medico/index.php">
        Volver al panel
    </a>

    <br><br>

    <a href="/logout.php">
        Cerrar sesión
    </a>

</body>

</html>