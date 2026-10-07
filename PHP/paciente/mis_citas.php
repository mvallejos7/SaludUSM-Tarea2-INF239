<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('PACIENTE');

// ---------------------------------------------------------
// Actualizar automáticamente citas vencidas
// ---------------------------------------------------------

$stmtActualizar = $pdo->query("
    CALL sp_cancelar_citas_vencidas()
");

$stmtActualizar->closeCursor();

// ---------------------------------------------------------
// Obtener id_paciente del usuario conectado
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

$paciente = $stmtPaciente->fetch();

if (!$paciente) {
    exit('No fue posible encontrar al paciente.');
}

$idPaciente = $paciente['id_paciente'];

// ---------------------------------------------------------
// Próximas citas
// ---------------------------------------------------------

$stmtProximas = $pdo->prepare("
    SELECT
        id_cita,
        fecha_hora,
        medico,
        especialidad,
        centro,
        comuna,
        estado
    FROM vw_citas_detalle
    WHERE id_paciente = ?
      AND fecha_hora >= CURRENT_TIMESTAMP
      AND estado IN ('Reservada', 'Confirmada')
    ORDER BY fecha_hora ASC
");

$stmtProximas->execute([
    $idPaciente
]);

$proximas = $stmtProximas->fetchAll();

// ---------------------------------------------------------
// Historial y otras citas
// ---------------------------------------------------------

$stmtHistorial = $pdo->prepare("
    SELECT
        id_cita,
        fecha_hora,
        medico,
        especialidad,
        centro,
        comuna,
        estado
    FROM vw_citas_detalle
    WHERE id_paciente = ?
      AND NOT (
            fecha_hora >= CURRENT_TIMESTAMP
            AND estado IN ('Reservada', 'Confirmada')
      )
    ORDER BY fecha_hora DESC
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

    <title>Mis citas - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Mis citas</h2>


    <h3>Próximas citas</h3>

    <?php if (empty($proximas)): ?>

        <p>
            No tienes próximas citas.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Fecha y hora</th>
                    <th>Médico</th>
                    <th>Especialidad</th>
                    <th>Centro</th>
                    <th>Comuna</th>
                    <th>Estado</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($proximas as $cita): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($cita['id_cita']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                date(
                                    'd-m-Y H:i',
                                    strtotime($cita['fecha_hora'])
                                )
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cita['medico']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cita['especialidad']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cita['centro']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cita['comuna']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cita['estado']) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>


    <br>

    <h3>Historial de citas</h3>

    <?php if (empty($historial)): ?>

        <p>
            No tienes citas en el historial.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Fecha y hora</th>
                    <th>Médico</th>
                    <th>Especialidad</th>
                    <th>Centro</th>
                    <th>Comuna</th>
                    <th>Estado</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($historial as $cita): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($cita['id_cita']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                date(
                                    'd-m-Y H:i',
                                    strtotime($cita['fecha_hora'])
                                )
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cita['medico']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cita['especialidad']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cita['centro']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cita['comuna']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cita['estado']) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

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