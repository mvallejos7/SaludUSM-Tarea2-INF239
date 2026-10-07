<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('MEDICO');

$mensajeExito = $_SESSION['mensaje_exito'] ?? '';
$mensajeError = $_SESSION['mensaje_error'] ?? '';

unset($_SESSION['mensaje_exito']);
unset($_SESSION['mensaje_error']);

// ---------------------------------------------------------
// Actualizar citas vencidas
// ---------------------------------------------------------

$stmtActualizar = $pdo->query("
    CALL sp_cancelar_citas_vencidas()
");

$stmtActualizar->closeCursor();

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
// Fecha seleccionada
// Por defecto: hoy
// ---------------------------------------------------------

$fecha = $_GET['fecha'] ?? date('Y-m-d');

$fechaObjeto = DateTime::createFromFormat(
    'Y-m-d',
    $fecha
);

if (
    !$fechaObjeto
    || $fechaObjeto->format('Y-m-d') !== $fecha
) {
    $fecha = date('Y-m-d');
}

// ---------------------------------------------------------
// Obtener agenda del médico
// ---------------------------------------------------------

$stmtAgenda = $pdo->prepare("
    SELECT
        id_cita,
        fecha_hora,
        paciente,
        prevision,
        especialidad,
        centro,
        comuna,
        estado
    FROM vw_citas_detalle
    WHERE id_medico = ?
      AND DATE(fecha_hora) = ?
    ORDER BY fecha_hora ASC
");

$stmtAgenda->execute([
    $idMedico,
    $fecha
]);

$citas = $stmtAgenda->fetchAll();

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Mi agenda - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Mi agenda</h2>


    <?php if ($mensajeExito !== ''): ?>

        <p>
            <?= htmlspecialchars($mensajeExito) ?>
        </p>

    <?php endif; ?>


    <?php if ($mensajeError !== ''): ?>

        <p>
            <?= htmlspecialchars($mensajeError) ?>
        </p>

    <?php endif; ?>


    <p>
        <strong>Médico:</strong>
        <?= htmlspecialchars($medico['nombre_completo']) ?>
    </p>


    <form method="GET">

        <label for="fecha">
            Seleccionar día:
        </label>

        <input
            type="date"
            id="fecha"
            name="fecha"
            value="<?= htmlspecialchars($fecha) ?>"
            required
        >

        <button type="submit">
            Ver agenda
        </button>

    </form>


    <br>


    <h3>
        Citas del
        <?= htmlspecialchars(
            date(
                'd-m-Y',
                strtotime($fecha)
            )
        ) ?>
    </h3>


    <?php if (empty($citas)): ?>

        <p>
            No tienes citas para este día.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>

                <tr>
                    <th>Hora</th>
                    <th>Paciente</th>
                    <th>Previsión</th>
                    <th>Especialidad</th>
                    <th>Centro</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($citas as $cita): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                date(
                                    'H:i',
                                    strtotime($cita['fecha_hora'])
                                )
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cita['paciente']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cita['prevision']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cita['especialidad']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $cita['centro']
                                . ' - '
                                . $cita['comuna']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cita['estado']) ?>
                        </td>

                        <td>

                            <?php if ($cita['estado'] === 'Reservada'): ?>

                                <form
                                    method="POST"
                                    action="/medico/cambiar_estado.php"
                                >

                                    <input
                                        type="hidden"
                                        name="id_cita"
                                        value="<?= htmlspecialchars($cita['id_cita']) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="fecha"
                                        value="<?= htmlspecialchars($fecha) ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="nuevo_estado"
                                        value="Confirmada"
                                    >
                                        Confirmar
                                    </button>

                                    <button
                                        type="submit"
                                        name="nuevo_estado"
                                        value="Atendida"
                                    >
                                        Marcar Atendida
                                    </button>

                                    <button
                                        type="submit"
                                        name="nuevo_estado"
                                        value="No Asistió"
                                    >
                                        No Asistió
                                    </button>

                                </form>


                            <?php elseif ($cita['estado'] === 'Confirmada'): ?>

                                <form
                                    method="POST"
                                    action="/medico/cambiar_estado.php"
                                >

                                    <input
                                        type="hidden"
                                        name="id_cita"
                                        value="<?= htmlspecialchars($cita['id_cita']) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="fecha"
                                        value="<?= htmlspecialchars($fecha) ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="nuevo_estado"
                                        value="Atendida"
                                    >
                                        Marcar Atendida
                                    </button>

                                    <button
                                        type="submit"
                                        name="nuevo_estado"
                                        value="No Asistió"
                                    >
                                        No Asistió
                                    </button>

                                </form>


                            <?php elseif ($cita['estado'] === 'Atendida'): ?>

                                <a
                                    href="/medico/atencion.php?id_cita=<?= urlencode($cita['id_cita']) ?>"
                                >
                                    Registrar / ver atención
                                </a>


                            <?php else: ?>

                                Estado final

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

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