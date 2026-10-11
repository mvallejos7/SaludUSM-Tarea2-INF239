<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('PACIENTE');

$errores = [];

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
// Obtener ID de la cita
// ---------------------------------------------------------

$idCita = $_POST['id_cita']
    ?? $_GET['id']
    ?? '';

if (!ctype_digit((string)$idCita)) {

    $_SESSION['mensaje_error'] =
        'La cita seleccionada no es válida.';

    header('Location: /paciente/mis_citas.php');
    exit;
}

// ---------------------------------------------------------
// Obtener cita y comprobar que:
// - pertenece al paciente
// - está en el futuro
// - está Reservada o Confirmada
// ---------------------------------------------------------

$stmtCita = $pdo->prepare("
    SELECT
        c.id_cita,
        c.fecha_hora,
        c.id_medico,
        c.codigo_centro,
        c.id_especialidad,
        ec.nombre AS estado,
        m.nombre_completo AS medico,
        e.nombre AS especialidad,
        cm.nombre AS centro
    FROM cita c

    JOIN estado_cita ec
        ON c.id_estado_cita = ec.id_estado_cita

    JOIN medico m
        ON c.id_medico = m.id_medico

    JOIN especialidad e
        ON c.id_especialidad = e.id_especialidad

    JOIN centro_medico cm
        ON c.codigo_centro = cm.codigo_centro

    WHERE c.id_cita = ?
      AND c.id_paciente = ?
      AND c.fecha_hora > CURRENT_TIMESTAMP
      AND ec.nombre IN ('Reservada', 'Confirmada')

    LIMIT 1
");

$stmtCita->execute([
    $idCita,
    $idPaciente
]);

$cita = $stmtCita->fetch();

if (!$cita) {

    $_SESSION['mensaje_error'] =
        'La cita no puede reagendarse o no te pertenece.';

    header('Location: /paciente/mis_citas.php');
    exit;
}

// ---------------------------------------------------------
// Valores del formulario
// ---------------------------------------------------------

$accion = $_POST['accion'] ?? '';

$fecha = $_POST['fecha']
    ?? date('Y-m-d', strtotime($cita['fecha_hora']));

$hora = $_POST['hora'] ?? '';

$horariosDisponibles = [];

// ---------------------------------------------------------
// Generar horarios disponibles
// ---------------------------------------------------------

if (
    $accion === 'buscar_horarios'
    || $accion === 'reagendar'
) {

    $fechaObjeto = DateTime::createFromFormat(
        'Y-m-d',
        $fecha
    );

    if (
        !$fechaObjeto
        || $fechaObjeto->format('Y-m-d') !== $fecha
    ) {

        $errores[] =
            'La fecha seleccionada no es válida.';

    } elseif ($fecha < date('Y-m-d')) {

        $errores[] =
            'No puedes seleccionar una fecha pasada.';

    } elseif ((int)$fechaObjeto->format('N') > 5) {

        $errores[] =
            'Las citas se agendan de lunes a viernes.';

    } else {

        // Buscar horas ocupadas por el médico
        // o por el paciente.
        //
        // Se excluye la cita que estamos reagendando.

        $stmtOcupados = $pdo->prepare("
            SELECT
                DATE_FORMAT(c.fecha_hora, '%H:%i')

            FROM cita c

            JOIN estado_cita ec
                ON c.id_estado_cita = ec.id_estado_cita

            WHERE DATE(c.fecha_hora) = ?

            AND c.id_cita <> ?

            AND ec.nombre IN (
                    'Reservada',
                    'Confirmada'
            )

            AND (
                    c.id_medico = ?
                    OR c.id_paciente = ?
            )
        ");

        $stmtOcupados->execute([
            $fecha,
            $idCita,
            $cita['id_medico'],
            $idPaciente
        ]);

        $ocupados = $stmtOcupados->fetchAll(
            PDO::FETCH_COLUMN
        );

        // Horario:
        // 08:00 hasta 18:00
        // bloques de 30 minutos.

        $inicio = new DateTime(
            $fecha . ' 08:00:00'
        );

        $fin = new DateTime(
            $fecha . ' 18:00:00'
        );

        $ahora = new DateTime();

        while ($inicio < $fin) {

            $horaActual =
                $inicio->format('H:i');

            if (
                $inicio > $ahora
                && !in_array(
                    $horaActual,
                    $ocupados,
                    true
                )
            ) {

                $horariosDisponibles[] =
                    $horaActual;
            }

            $inicio->modify('+30 minutes');
        }
    }
}

// ---------------------------------------------------------
// Reagendar
// ---------------------------------------------------------

if ($accion === 'reagendar') {

    if ($hora === '') {

        $errores[] =
            'Debes seleccionar un horario.';
    }

    if (
        empty($errores)
        && !in_array(
            $hora,
            $horariosDisponibles,
            true
        )
    ) {

        $errores[] =
            'El horario seleccionado ya no está disponible.';
    }

    if (empty($errores)) {

        $nuevaFechaHora =
            $fecha . ' ' . $hora . ':00';

        try {

            $pdo->beginTransaction();

            // Comprobar nuevamente por seguridad.

            $stmtConflicto = $pdo->prepare("
                SELECT COUNT(*)

                FROM cita c

                JOIN estado_cita ec
                    ON c.id_estado_cita = ec.id_estado_cita

                WHERE c.fecha_hora = ?

                AND c.id_cita <> ?

                AND ec.nombre IN (
                        'Reservada',
                        'Confirmada'
                )

                AND (
                        c.id_medico = ?
                        OR c.id_paciente = ?
                )
            ");

            $stmtConflicto->execute([
                $nuevaFechaHora,
                $idCita,
                $cita['id_medico'],
                $idPaciente
            ]);

            if (
                (int)$stmtConflicto->fetchColumn() > 0
            ) {

                $pdo->rollBack();

                $errores[] =
                    'El horario acaba de ser ocupado. Selecciona otro.';

            } else {

                // Actualizar solo si la cita todavía
                // sigue siendo válida para reagendar.

                $stmtActualizar = $pdo->prepare("
                    UPDATE cita
                    SET fecha_hora = ?
                    WHERE id_cita = ?
                      AND id_paciente = ?
                      AND fecha_hora > CURRENT_TIMESTAMP
                      AND id_estado_cita IN (
                            SELECT id_estado_cita
                            FROM estado_cita
                            WHERE nombre IN (
                                'Reservada',
                                'Confirmada'
                            )
                      )
                ");

                $stmtActualizar->execute([
                    $nuevaFechaHora,
                    $idCita,
                    $idPaciente
                ]);

                if (
                    $stmtActualizar->rowCount() !== 1
                ) {

                    $pdo->rollBack();

                    $errores[] =
                        'La cita ya no puede reagendarse.';

                } else {

                    $pdo->commit();

                    $_SESSION['mensaje_exito'] =
                        'Cita reagendada correctamente.';

                    header(
                        'Location: /paciente/mis_citas.php'
                    );

                    exit;
                }
            }

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errores[] =
                'No fue posible reagendar la cita.';
        }
    }
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

    <title>Reagendar cita - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Reagendar cita</h2>


    <p>
        <strong>Médico:</strong>
        <?= htmlspecialchars($cita['medico']) ?>
    </p>

    <p>
        <strong>Especialidad:</strong>
        <?= htmlspecialchars($cita['especialidad']) ?>
    </p>

    <p>
        <strong>Centro:</strong>
        <?= htmlspecialchars($cita['centro']) ?>
    </p>

    <p>
        <strong>Estado:</strong>
        <?= htmlspecialchars($cita['estado']) ?>
    </p>

    <p>
        <strong>Fecha actual:</strong>

        <?= htmlspecialchars(
            date(
                'd-m-Y H:i',
                strtotime($cita['fecha_hora'])
            )
        ) ?>
    </p>


    <?php if (!empty($errores)): ?>

        <ul>

            <?php foreach ($errores as $error): ?>

                <li>
                    <?= htmlspecialchars($error) ?>
                </li>

            <?php endforeach; ?>

        </ul>

    <?php endif; ?>


    <hr>


    <h3>Seleccionar nueva fecha</h3>


    <form method="POST">

        <input
            type="hidden"
            name="id_cita"
            value="<?= htmlspecialchars($idCita) ?>"
        >

        <label for="fecha">
            Nueva fecha:
        </label>

        <input
            type="date"
            id="fecha"
            name="fecha"
            min="<?= date('Y-m-d') ?>"
            value="<?= htmlspecialchars($fecha) ?>"
            required
        >

        <br><br>

        <button
            type="submit"
            name="accion"
            value="buscar_horarios"
        >
            Buscar horarios
        </button>

    </form>


    <?php if (
        $accion === 'buscar_horarios'
        && empty($errores)
    ): ?>

        <hr>

        <h3>Horarios disponibles</h3>

        <?php if (
            empty($horariosDisponibles)
        ): ?>

            <p>
                No existen horarios disponibles para esta fecha.
            </p>

        <?php else: ?>

            <form method="POST">

                <input
                    type="hidden"
                    name="id_cita"
                    value="<?= htmlspecialchars($idCita) ?>"
                >

                <input
                    type="hidden"
                    name="fecha"
                    value="<?= htmlspecialchars($fecha) ?>"
                >


                <?php foreach (
                    $horariosDisponibles as $horario
                ): ?>

                    <label>

                        <input
                            type="radio"
                            name="hora"
                            value="<?= htmlspecialchars($horario) ?>"
                            required
                        >

                        <?= htmlspecialchars($horario) ?>

                    </label>

                    <br>

                <?php endforeach; ?>


                <br>

                <button
                    type="submit"
                    name="accion"
                    value="reagendar"
                >
                    Confirmar reagendamiento
                </button>

            </form>

        <?php endif; ?>

    <?php endif; ?>


    <br><br>

    <a href="/paciente/mis_citas.php">
        Volver a Mis citas
    </a>

</body>

</html>