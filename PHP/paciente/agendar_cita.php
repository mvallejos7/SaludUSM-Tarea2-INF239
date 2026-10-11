<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('PACIENTE');

$errores = [];
$mensaje = '';

$idEspecialidad = $_POST['id_especialidad'] ?? '';
$codigoCentro = $_POST['codigo_centro'] ?? '';
$idMedico = $_POST['id_medico'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$hora = $_POST['hora'] ?? '';
$accion = $_POST['accion'] ?? '';

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

$paciente = $stmtPaciente->fetch();

if (!$paciente) {
    exit('No fue posible encontrar al paciente.');
}

$idPaciente = $paciente['id_paciente'];

// ---------------------------------------------------------
// Obtener especialidades disponibles
// ---------------------------------------------------------

$stmtEspecialidades = $pdo->query("
    SELECT DISTINCT
        e.id_especialidad,
        e.nombre
    FROM especialidad e
    JOIN medico_especialidad me
        ON e.id_especialidad = me.id_especialidad
    WHERE me.activo = TRUE
    ORDER BY e.nombre
");

$especialidades = $stmtEspecialidades->fetchAll();

// ---------------------------------------------------------
// Obtener centros disponibles
// ---------------------------------------------------------

$stmtCentros = $pdo->query("
    SELECT
        codigo_centro,
        nombre,
        comuna
    FROM centro_medico
    ORDER BY nombre
");

$centros = $stmtCentros->fetchAll();

// ---------------------------------------------------------
// Obtener médicos según especialidad + centro
// ---------------------------------------------------------

$medicos = [];

if (
    ctype_digit((string)$idEspecialidad) &&
    $codigoCentro !== ''
) {

    $stmtMedicos = $pdo->prepare("
        SELECT DISTINCT
            m.id_medico,
            m.nombre_completo
        FROM medico m

        JOIN usuario u
            ON m.id_usuario = u.id_usuario

        JOIN medico_especialidad me
            ON m.id_medico = me.id_medico

        JOIN medico_centro mc
            ON m.id_medico = mc.id_medico

        WHERE me.id_especialidad = ?
          AND mc.codigo_centro = ?
          AND me.activo = TRUE
          AND mc.activo = TRUE
          AND u.activo = TRUE

        ORDER BY m.nombre_completo
    ");

    $stmtMedicos->execute([
        $idEspecialidad,
        $codigoCentro
    ]);

    $medicos = $stmtMedicos->fetchAll();
}

// ---------------------------------------------------------
// Función para validar relación médico-especialidad-centro
// ---------------------------------------------------------

function medicoValido(
    PDO $pdo,
    int $idMedico,
    int $idEspecialidad,
    string $codigoCentro
): bool {

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM medico m

        JOIN usuario u
            ON m.id_usuario = u.id_usuario

        JOIN medico_especialidad me
            ON m.id_medico = me.id_medico

        JOIN medico_centro mc
            ON m.id_medico = mc.id_medico

        WHERE m.id_medico = ?
          AND me.id_especialidad = ?
          AND mc.codigo_centro = ?
          AND me.activo = TRUE
          AND mc.activo = TRUE
          AND u.activo = TRUE
    ");

    $stmt->execute([
        $idMedico,
        $idEspecialidad,
        $codigoCentro
    ]);

    return (int)$stmt->fetchColumn() > 0;
}

// ---------------------------------------------------------
// Generar horarios disponibles
// ---------------------------------------------------------

$horariosDisponibles = [];

if (
    ctype_digit((string)$idEspecialidad) &&
    ctype_digit((string)$idMedico) &&
    $codigoCentro !== '' &&
    $fecha !== ''
) {

    $fechaObjeto = DateTime::createFromFormat('Y-m-d', $fecha);

    if (
        !$fechaObjeto ||
        $fechaObjeto->format('Y-m-d') !== $fecha
    ) {

        $errores[] = 'La fecha seleccionada no es válida.';

    } elseif ($fecha < date('Y-m-d')) {

        $errores[] = 'No puedes agendar una cita en una fecha pasada.';

    } elseif ((int)$fechaObjeto->format('N') > 5) {

        $errores[] = 'Las citas se agendan de lunes a viernes.';

    } elseif (
        !medicoValido(
            $pdo,
            (int)$idMedico,
            (int)$idEspecialidad,
            $codigoCentro
        )
    ) {

        $errores[] = 'El médico seleccionado no corresponde a la especialidad y centro indicados.';

    } else {

        // Obtener horarios que ya están ocupados
        // por el médico o por el paciente.

        $stmtOcupados = $pdo->prepare("
            SELECT
                DATE_FORMAT(c.fecha_hora, '%H:%i')

            FROM cita c

            JOIN estado_cita ec
                ON c.id_estado_cita = ec.id_estado_cita

            WHERE DATE(c.fecha_hora) = ?

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
            $idMedico,
            $idPaciente
        ]);

        $ocupados = $stmtOcupados->fetchAll(
            PDO::FETCH_COLUMN
        );

        // Bloques de 30 minutos:
        // 08:00, 08:30, ..., 17:30

        $inicio = new DateTime(
            $fecha . ' 08:00:00'
        );

        $fin = new DateTime(
            $fecha . ' 18:00:00'
        );

        $ahora = new DateTime();

        while ($inicio < $fin) {

            $horaActual = $inicio->format('H:i');

            if (
                $inicio > $ahora &&
                !in_array(
                    $horaActual,
                    $ocupados,
                    true
                )
            ) {

                $horariosDisponibles[] = $horaActual;
            }

            $inicio->modify('+30 minutes');
        }
    }
}

// ---------------------------------------------------------
// Crear la cita
// ---------------------------------------------------------

if ($accion === 'crear_cita') {

    if (
        !ctype_digit((string)$idEspecialidad) ||
        !ctype_digit((string)$idMedico) ||
        $codigoCentro === '' ||
        $fecha === '' ||
        $hora === ''
    ) {

        $errores[] = 'Faltan datos para crear la cita.';
    }

    if (
        empty($errores) &&
        !medicoValido(
            $pdo,
            (int)$idMedico,
            (int)$idEspecialidad,
            $codigoCentro
        )
    ) {

        $errores[] = 'El médico ya no está disponible para esa especialidad o centro.';
    }

    if (
        empty($errores) &&
        !in_array(
            $hora,
            $horariosDisponibles,
            true
        )
    ) {

        $errores[] = 'El horario seleccionado ya no está disponible.';
    }

    if (empty($errores)) {

        $fechaHora = $fecha . ' ' . $hora . ':00';

        try {

            $pdo->beginTransaction();

            // Volver a comprobar que médico y paciente
            // no tengan una cita en ese mismo horario.

            $stmtConflicto = $pdo->prepare("
                SELECT COUNT(*)

                FROM cita c

                JOIN estado_cita ec
                    ON c.id_estado_cita = ec.id_estado_cita

                WHERE c.fecha_hora = ?

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
                $fechaHora,
                $idMedico,
                $idPaciente
            ]);

            if ((int)$stmtConflicto->fetchColumn() > 0) {

                $pdo->rollBack();

                $errores[] =
                    'El horario acaba de ser ocupado. Selecciona otro.';

            } else {

                // Obtener dinámicamente el estado Reservada.

                $stmtEstado = $pdo->prepare("
                    SELECT id_estado_cita
                    FROM estado_cita
                    WHERE nombre = 'Reservada'
                    LIMIT 1
                ");

                $stmtEstado->execute();

                $idEstadoReservada =
                    $stmtEstado->fetchColumn();

                if (!$idEstadoReservada) {
                    throw new Exception(
                        'No existe el estado Reservada.'
                    );
                }

                $stmtInsertar = $pdo->prepare("
                    INSERT INTO cita
                    (
                        fecha_hora,
                        id_paciente,
                        id_medico,
                        codigo_centro,
                        id_especialidad,
                        id_estado_cita
                    )
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmtInsertar->execute([
                    $fechaHora,
                    $idPaciente,
                    $idMedico,
                    $codigoCentro,
                    $idEspecialidad,
                    $idEstadoReservada
                ]);

                $pdo->commit();

                $mensaje =
                    'Cita agendada correctamente.';

                $hora = '';

                // Volver a calcular los bloques para que
                // el recién reservado desaparezca.

                $stmtOcupados = $pdo->prepare("
                    SELECT
                        DATE_FORMAT(fecha_hora, '%H:%i')
                    FROM cita
                    WHERE DATE(fecha_hora) = ?
                      AND (
                            id_medico = ?
                            OR id_paciente = ?
                      )
                ");

                $stmtOcupados->execute([
                    $fecha,
                    $idMedico,
                    $idPaciente
                ]);

                $ocupados = $stmtOcupados->fetchAll(
                    PDO::FETCH_COLUMN
                );

                $horariosDisponibles = [];

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
                        $inicio > $ahora &&
                        !in_array(
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

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errores[] =
                'No fue posible agendar la cita.';
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

    <title>Agendar cita - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Agendar cita</h2>


    <?php if ($mensaje !== ''): ?>

        <p>
            <?= htmlspecialchars($mensaje) ?>
        </p>

    <?php endif; ?>


    <?php if (!empty($errores)): ?>

        <ul>

            <?php foreach ($errores as $error): ?>

                <li>
                    <?= htmlspecialchars($error) ?>
                </li>

            <?php endforeach; ?>

        </ul>

    <?php endif; ?>


    <!-- =================================================
         PASO 1: ESPECIALIDAD Y CENTRO
         ================================================= -->

    <h3>1. Selecciona especialidad y centro</h3>

    <form method="POST">

        <label for="id_especialidad">
            Especialidad:
        </label>

        <select
            id="id_especialidad"
            name="id_especialidad"
            required
        >

            <option value="">
                Seleccione
            </option>

            <?php foreach ($especialidades as $especialidad): ?>

                <option
                    value="<?= $especialidad['id_especialidad'] ?>"
                    <?= (string)$idEspecialidad === (string)$especialidad['id_especialidad']
                        ? 'selected'
                        : '' ?>
                >
                    <?= htmlspecialchars($especialidad['nombre']) ?>
                </option>

            <?php endforeach; ?>

        </select>


        <br><br>


        <label for="codigo_centro">
            Centro médico:
        </label>

        <select
            id="codigo_centro"
            name="codigo_centro"
            required
        >

            <option value="">
                Seleccione
            </option>

            <?php foreach ($centros as $centro): ?>

                <option
                    value="<?= htmlspecialchars($centro['codigo_centro']) ?>"
                    <?= $codigoCentro === $centro['codigo_centro']
                        ? 'selected'
                        : '' ?>
                >

                    <?= htmlspecialchars(
                        $centro['nombre']
                        . ' - '
                        . $centro['comuna']
                    ) ?>

                </option>

            <?php endforeach; ?>

        </select>

        <br><br>

        <button
            type="submit"
            name="accion"
            value="cargar_medicos"
        >
            Buscar médicos
        </button>

    </form>


    <!-- =================================================
         PASO 2: MEDICO Y FECHA
         ================================================= -->

    <?php if (
        $idEspecialidad !== '' &&
        $codigoCentro !== ''
    ): ?>

        <hr>

        <h3>2. Selecciona médico y fecha</h3>

        <?php if (empty($medicos)): ?>

            <p>
                No existen médicos disponibles para
                esa especialidad y centro.
            </p>

        <?php else: ?>

            <form method="POST">

                <input
                    type="hidden"
                    name="id_especialidad"
                    value="<?= htmlspecialchars($idEspecialidad) ?>"
                >

                <input
                    type="hidden"
                    name="codigo_centro"
                    value="<?= htmlspecialchars($codigoCentro) ?>"
                >


                <label for="id_medico">
                    Médico:
                </label>

                <select
                    id="id_medico"
                    name="id_medico"
                    required
                >

                    <option value="">
                        Seleccione
                    </option>

                    <?php foreach ($medicos as $medico): ?>

                        <option
                            value="<?= $medico['id_medico'] ?>"
                            <?= (string)$idMedico === (string)$medico['id_medico']
                                ? 'selected'
                                : '' ?>
                        >

                            <?= htmlspecialchars(
                                $medico['nombre_completo']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>


                <br><br>


                <label for="fecha">
                    Fecha:
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

        <?php endif; ?>

    <?php endif; ?>


    <!-- =================================================
         PASO 3: HORARIO
         ================================================= -->

    <?php if (
        $idMedico !== '' &&
        $fecha !== '' &&
        empty($errores)
    ): ?>

        <hr>

        <h3>3. Horarios disponibles</h3>

        <?php if (empty($horariosDisponibles)): ?>

            <p>
                No existen horarios disponibles
                para esta fecha.
            </p>

        <?php else: ?>

            <form method="POST">

                <input
                    type="hidden"
                    name="id_especialidad"
                    value="<?= htmlspecialchars($idEspecialidad) ?>"
                >

                <input
                    type="hidden"
                    name="codigo_centro"
                    value="<?= htmlspecialchars($codigoCentro) ?>"
                >

                <input
                    type="hidden"
                    name="id_medico"
                    value="<?= htmlspecialchars($idMedico) ?>"
                >

                <input
                    type="hidden"
                    name="fecha"
                    value="<?= htmlspecialchars($fecha) ?>"
                >


                <?php foreach ($horariosDisponibles as $horario): ?>

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
                    value="crear_cita"
                >
                    Agendar cita
                </button>

            </form>

        <?php endif; ?>

    <?php endif; ?>


    <br><br>

    <a href="/paciente/mis_citas.php">
        Mis citas
    </a>

    <br><br>

    <a href="/paciente/index.php">
        Volver al panel
    </a>

</body>

</html>