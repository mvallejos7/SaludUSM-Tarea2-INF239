<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('MEDICO');

$errores = [];
$mensaje = '';

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
// Obtener ID de cita
// ---------------------------------------------------------

$idCita = $_POST['id_cita']
    ?? $_GET['id_cita']
    ?? '';

if (!ctype_digit((string)$idCita)) {
    exit('La cita seleccionada no es válida.');
}

// ---------------------------------------------------------
// Obtener cita
//
// Debe:
// - pertenecer al médico conectado
// - estar en estado Atendida
// ---------------------------------------------------------

$stmtCita = $pdo->prepare("
    SELECT
        c.id_cita,
        c.fecha_hora,
        c.id_paciente,
        p.nombre_completo AS paciente,
        pr.nombre AS prevision,
        e.nombre AS especialidad,
        cm.nombre AS centro,
        ec.nombre AS estado

    FROM cita c

    JOIN paciente p
        ON c.id_paciente = p.id_paciente

    JOIN prevision pr
        ON p.id_prevision = pr.id_prevision

    JOIN especialidad e
        ON c.id_especialidad = e.id_especialidad

    JOIN centro_medico cm
        ON c.codigo_centro = cm.codigo_centro

    JOIN estado_cita ec
        ON c.id_estado_cita = ec.id_estado_cita

    WHERE c.id_cita = ?
      AND c.id_medico = ?

    LIMIT 1
");

$stmtCita->execute([
    $idCita,
    $idMedico
]);

$cita = $stmtCita->fetch();

if (!$cita) {
    exit('La cita no existe o no pertenece al médico conectado.');
}

if ($cita['estado'] !== 'Atendida') {
    exit('Solo se puede registrar una atención para una cita Atendida.');
}

// ---------------------------------------------------------
// Buscar si ya existe atención
// ---------------------------------------------------------

$stmtAtencion = $pdo->prepare("
    SELECT
        id_atencion,
        motivo_consulta,
        observaciones_clinicas
    FROM atencion
    WHERE id_cita = ?
    LIMIT 1
");

$stmtAtencion->execute([
    $idCita
]);

$atencion = $stmtAtencion->fetch();

// ---------------------------------------------------------
// Valores iniciales
// ---------------------------------------------------------

$motivoConsulta =
    $atencion['motivo_consulta']
    ?? '';

$observaciones =
    $atencion['observaciones_clinicas']
    ?? '';

// ---------------------------------------------------------
// Guardar atención
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $motivoConsulta =
        trim($_POST['motivo_consulta'] ?? '');

    $observaciones =
        trim($_POST['observaciones_clinicas'] ?? '');

    if ($motivoConsulta === '') {
        $errores[] =
            'El motivo de consulta es obligatorio.';
    }

    if (empty($errores)) {

        try {

            // ---------------------------------------------
            // Si ya existe, actualizar
            // ---------------------------------------------

            if ($atencion) {

                $stmtActualizar = $pdo->prepare("
                    UPDATE atencion
                    SET motivo_consulta = ?,
                        observaciones_clinicas = ?
                    WHERE id_atencion = ?
                ");

                $stmtActualizar->execute([
                    $motivoConsulta,
                    $observaciones !== ''
                        ? $observaciones
                        : null,
                    $atencion['id_atencion']
                ]);

                $mensaje =
                    'Atención actualizada correctamente.';

            } else {

                // -----------------------------------------
                // Si no existe, crear
                //
                // El TRIGGER también comprobará que
                // la cita esté Atendida.
                // -----------------------------------------

                $stmtInsertar = $pdo->prepare("
                    INSERT INTO atencion
                    (
                        id_cita,
                        motivo_consulta,
                        observaciones_clinicas
                    )
                    VALUES (?, ?, ?)
                ");

                $stmtInsertar->execute([
                    $idCita,
                    $motivoConsulta,
                    $observaciones !== ''
                        ? $observaciones
                        : null
                ]);

                $mensaje =
                    'Atención registrada correctamente.';

                // Recargar atención recién creada

                $stmtAtencion->execute([
                    $idCita
                ]);

                $atencion = $stmtAtencion->fetch();
            }

        } catch (PDOException $e) {

            $errores[] =
                'No fue posible guardar la atención.';
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

    <title>Atención médica - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Atención médica</h2>


    <p>
        <strong>Cita:</strong>
        #<?= htmlspecialchars($cita['id_cita']) ?>
    </p>

    <p>
        <strong>Fecha:</strong>

        <?= htmlspecialchars(
            date(
                'd-m-Y H:i',
                strtotime($cita['fecha_hora'])
            )
        ) ?>
    </p>

    <p>
        <strong>Paciente:</strong>
        <?= htmlspecialchars($cita['paciente']) ?>
    </p>

    <p>
        <strong>Previsión:</strong>
        <?= htmlspecialchars($cita['prevision']) ?>
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


    <hr>


    <form method="POST">

        <input
            type="hidden"
            name="id_cita"
            value="<?= htmlspecialchars($idCita) ?>"
        >


        <div>

            <label for="motivo_consulta">
                <strong>Motivo de consulta:</strong>
            </label>

            <br>

            <textarea
                id="motivo_consulta"
                name="motivo_consulta"
                rows="4"
                cols="60"
                required
            ><?= htmlspecialchars($motivoConsulta) ?></textarea>

        </div>


        <br>


        <div>

            <label for="observaciones_clinicas">
                <strong>Observaciones clínicas:</strong>
            </label>

            <br>

            <textarea
                id="observaciones_clinicas"
                name="observaciones_clinicas"
                rows="8"
                cols="60"
            ><?= htmlspecialchars($observaciones) ?></textarea>

        </div>


        <br>


        <button type="submit">

            <?php if ($atencion): ?>

                Guardar cambios

            <?php else: ?>

                Registrar atención

            <?php endif; ?>

        </button>

    </form>


    <?php if ($atencion): ?>

        <br><br>

        <a
            href="/medico/diagnosticos.php?id_cita=<?= urlencode($idCita) ?>"
        >
            Gestionar diagnósticos
        </a>

        <br><br>

        <a
            href="/medico/recetas.php?id_cita=<?= urlencode($idCita) ?>"
        >
            Gestionar recetas
        </a>

    <?php endif; ?>


    <br><br>

    <a
        href="/medico/agenda.php?fecha=<?= urlencode(
            date(
                'Y-m-d',
                strtotime($cita['fecha_hora'])
            )
        ) ?>"
    >
        Volver a la agenda
    </a>

</body>

</html>