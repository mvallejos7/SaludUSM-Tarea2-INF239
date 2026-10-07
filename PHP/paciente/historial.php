<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('PACIENTE');

// ---------------------------------------------------------
// Obtener paciente conectado
// ---------------------------------------------------------

$stmtPaciente = $pdo->prepare("
    SELECT id_paciente
    FROM paciente
    WHERE id_usuario = ?
    LIMIT 1
");

$stmtPaciente->execute([
    $_SESSION['id_usuario']
]);

$idPaciente = $stmtPaciente->fetchColumn();

if (!$idPaciente) {
    exit('No fue posible identificar al paciente.');
}

// ---------------------------------------------------------
// Obtener historial clínico
// ---------------------------------------------------------

$stmtHistorial = $pdo->prepare("
    SELECT
        a.id_atencion,
        c.fecha_hora,
        m.nombre_completo AS medico,
        e.nombre AS especialidad,
        cm.nombre AS centro,
        a.motivo_consulta,
        a.observaciones_clinicas,

        GROUP_CONCAT(
            DISTINCT CONCAT(
                d.codigo_diagnostico,
                ' - ',
                d.descripcion
            )
            ORDER BY d.codigo_diagnostico
            SEPARATOR ' | '
        ) AS diagnosticos

    FROM atencion a

    JOIN cita c
        ON a.id_cita = c.id_cita

    JOIN medico m
        ON c.id_medico = m.id_medico

    JOIN especialidad e
        ON c.id_especialidad = e.id_especialidad

    JOIN centro_medico cm
        ON c.codigo_centro = cm.codigo_centro

    LEFT JOIN atencion_diagnostico ad
        ON a.id_atencion = ad.id_atencion

    LEFT JOIN diagnostico d
        ON ad.codigo_diagnostico = d.codigo_diagnostico

    WHERE c.id_paciente = ?

    GROUP BY
        a.id_atencion,
        c.fecha_hora,
        m.nombre_completo,
        e.nombre,
        cm.nombre,
        a.motivo_consulta,
        a.observaciones_clinicas

    ORDER BY c.fecha_hora DESC
");

$stmtHistorial->execute([
    $idPaciente
]);

$historial = $stmtHistorial->fetchAll();

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Historial médico - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Historial médico</h2>

    <p>
        Aquí puedes consultar tus atenciones médicas anteriores.
    </p>


    <?php if (empty($historial)): ?>

        <p>
            No tienes atenciones médicas registradas.
        </p>

    <?php else: ?>

        <?php foreach ($historial as $atencion): ?>

            <hr>

            <h3>
                Atención #<?= htmlspecialchars($atencion['id_atencion']) ?>
            </h3>

            <p>
                <strong>Fecha:</strong>

                <?= htmlspecialchars(
                    date(
                        'd-m-Y H:i',
                        strtotime($atencion['fecha_hora'])
                    )
                ) ?>
            </p>

            <p>
                <strong>Médico:</strong>
                <?= htmlspecialchars($atencion['medico']) ?>
            </p>

            <p>
                <strong>Especialidad:</strong>
                <?= htmlspecialchars($atencion['especialidad']) ?>
            </p>

            <p>
                <strong>Centro:</strong>
                <?= htmlspecialchars($atencion['centro']) ?>
            </p>

            <p>
                <strong>Motivo de consulta:</strong>
                <?= htmlspecialchars($atencion['motivo_consulta']) ?>
            </p>

            <p>
                <strong>Observaciones clínicas:</strong>

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

        <?php endforeach; ?>

    <?php endif; ?>


    <br>

    <a href="/paciente/index.php">
        Volver al panel
    </a>

    <br><br>

    <a href="/logout.php">
        Cerrar sesión
    </a>

</body>

</html>