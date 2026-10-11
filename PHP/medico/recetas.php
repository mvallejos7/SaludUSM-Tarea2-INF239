<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('MEDICO');

$errores = [];

$mensajeExito = $_SESSION['mensaje_exito'] ?? '';
$mensajeError = $_SESSION['mensaje_error'] ?? '';

unset($_SESSION['mensaje_exito']);
unset($_SESSION['mensaje_error']);

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
// Obtener cita
// ---------------------------------------------------------

$idCita = $_POST['id_cita']
    ?? $_GET['id_cita']
    ?? '';

if (!ctype_digit((string)$idCita)) {
    exit('La cita seleccionada no es válida.');
}

// ---------------------------------------------------------
// Obtener atención y comprobar propiedad
// ---------------------------------------------------------

$stmtAtencion = $pdo->prepare("
    SELECT
        a.id_atencion,
        a.id_cita,
        a.motivo_consulta,
        c.fecha_hora,
        p.nombre_completo AS paciente,
        e.nombre AS especialidad,
        cm.nombre AS centro

    FROM atencion a

    JOIN cita c
        ON a.id_cita = c.id_cita

    JOIN paciente p
        ON c.id_paciente = p.id_paciente

    JOIN especialidad e
        ON c.id_especialidad = e.id_especialidad

    JOIN centro_medico cm
        ON c.codigo_centro = cm.codigo_centro

    WHERE a.id_cita = ?
      AND c.id_medico = ?

    LIMIT 1
");

$stmtAtencion->execute([
    $idCita,
    $idMedico
]);

$atencion = $stmtAtencion->fetch();

if (!$atencion) {
    exit('La atención no existe o no pertenece al médico conectado.');
}

$idAtencion = $atencion['id_atencion'];

$accion = $_POST['accion'] ?? '';

// ---------------------------------------------------------
// AGREGAR receta
// ---------------------------------------------------------

if ($accion === 'agregar') {

    $medicamento = trim(
        $_POST['medicamento'] ?? ''
    );

    $dosis = trim(
        $_POST['dosis'] ?? ''
    );

    $diasTratamiento =
        $_POST['dias_tratamiento'] ?? '';

    if ($medicamento === '') {
        $errores[] =
            'El medicamento es obligatorio.';
    }

    if ($dosis === '') {
        $errores[] =
            'La dosis es obligatoria.';
    }

    if (
        !ctype_digit((string)$diasTratamiento)
        || (int)$diasTratamiento <= 0
    ) {
        $errores[] =
            'Los días de tratamiento deben ser mayores que cero.';
    }

    if (empty($errores)) {

        try {

            $stmtAgregar = $pdo->prepare("
                INSERT INTO receta
                (
                    id_atencion,
                    medicamento,
                    dosis,
                    dias_tratamiento
                )
                VALUES (?, ?, ?, ?)
            ");

            $stmtAgregar->execute([
                $idAtencion,
                $medicamento,
                $dosis,
                (int)$diasTratamiento
            ]);

            $_SESSION['mensaje_exito'] =
                'Receta agregada correctamente.';

        } catch (PDOException $e) {

            $_SESSION['mensaje_error'] =
                'No fue posible agregar la receta.';
        }

        header(
            'Location: /medico/recetas.php?id_cita='
            . urlencode($idCita)
        );

        exit;
    }
}

// ---------------------------------------------------------
// EDITAR receta
// ---------------------------------------------------------

if ($accion === 'editar') {

    $idReceta = $_POST['id_receta'] ?? '';

    $medicamento = trim(
        $_POST['medicamento'] ?? ''
    );

    $dosis = trim(
        $_POST['dosis'] ?? ''
    );

    $diasTratamiento =
        $_POST['dias_tratamiento'] ?? '';

    if (!ctype_digit((string)$idReceta)) {
        $errores[] =
            'La receta seleccionada no es válida.';
    }

    if ($medicamento === '') {
        $errores[] =
            'El medicamento es obligatorio.';
    }

    if ($dosis === '') {
        $errores[] =
            'La dosis es obligatoria.';
    }

    if (
        !ctype_digit((string)$diasTratamiento)
        || (int)$diasTratamiento <= 0
    ) {
        $errores[] =
            'Los días de tratamiento deben ser mayores que cero.';
    }

    if (empty($errores)) {

        try {

            $stmtEditar = $pdo->prepare("
                UPDATE receta
                SET medicamento = ?,
                    dosis = ?,
                    dias_tratamiento = ?
                WHERE id_receta = ?
                  AND id_atencion = ?
            ");

            $stmtEditar->execute([
                $medicamento,
                $dosis,
                (int)$diasTratamiento,
                $idReceta,
                $idAtencion
            ]);

            if ($stmtEditar->rowCount() >= 0) {

                $_SESSION['mensaje_exito'] =
                    'Receta actualizada correctamente.';
            }

        } catch (PDOException $e) {

            $_SESSION['mensaje_error'] =
                'No fue posible actualizar la receta.';
        }

        header(
            'Location: /medico/recetas.php?id_cita='
            . urlencode($idCita)
        );

        exit;
    }
}

// ---------------------------------------------------------
// ELIMINAR receta
// ---------------------------------------------------------

if ($accion === 'eliminar') {

    $idReceta = $_POST['id_receta'] ?? '';

    if (!ctype_digit((string)$idReceta)) {

        $_SESSION['mensaje_error'] =
            'La receta seleccionada no es válida.';

    } else {

        try {

            $stmtEliminar = $pdo->prepare("
                DELETE FROM receta
                WHERE id_receta = ?
                  AND id_atencion = ?
            ");

            $stmtEliminar->execute([
                $idReceta,
                $idAtencion
            ]);

            if ($stmtEliminar->rowCount() === 1) {

                $_SESSION['mensaje_exito'] =
                    'Receta eliminada correctamente.';

            } else {

                $_SESSION['mensaje_error'] =
                    'No fue posible encontrar la receta.';
            }

        } catch (PDOException $e) {

            $_SESSION['mensaje_error'] =
                'No fue posible eliminar la receta.';
        }
    }

    header(
        'Location: /medico/recetas.php?id_cita='
        . urlencode($idCita)
    );

    exit;
}

// ---------------------------------------------------------
// Obtener recetas actuales
// ---------------------------------------------------------

$stmtRecetas = $pdo->prepare("
    SELECT
        id_receta,
        medicamento,
        dosis,
        dias_tratamiento
    FROM receta
    WHERE id_atencion = ?
    ORDER BY id_receta
");

$stmtRecetas->execute([
    $idAtencion
]);

$recetas = $stmtRecetas->fetchAll();

// ---------------------------------------------------------
// Receta seleccionada para editar
// ---------------------------------------------------------

$recetaEditar = null;

$idEditar = $_GET['editar'] ?? '';

if (ctype_digit((string)$idEditar)) {

    $stmtRecetaEditar = $pdo->prepare("
        SELECT
            id_receta,
            medicamento,
            dosis,
            dias_tratamiento
        FROM receta
        WHERE id_receta = ?
          AND id_atencion = ?
        LIMIT 1
    ");

    $stmtRecetaEditar->execute([
        $idEditar,
        $idAtencion
    ]);

    $recetaEditar =
        $stmtRecetaEditar->fetch();
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

    <title>Recetas - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Recetas de la atención</h2>


    <p>
        <strong>Paciente:</strong>
        <?= htmlspecialchars($atencion['paciente']) ?>
    </p>

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
        <strong>Especialidad:</strong>
        <?= htmlspecialchars($atencion['especialidad']) ?>
    </p>

    <p>
        <strong>Motivo:</strong>
        <?= htmlspecialchars($atencion['motivo_consulta']) ?>
    </p>


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


    <h3>Recetas registradas</h3>


    <?php if (empty($recetas)): ?>

        <p>
            Esta atención todavía no tiene recetas.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>

                <tr>
                    <th>Medicamento</th>
                    <th>Dosis</th>
                    <th>Días</th>
                    <th>Acciones</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($recetas as $receta): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $receta['medicamento']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $receta['dosis']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $receta['dias_tratamiento']
                            ) ?>
                        </td>

                        <td>

                            <a
                                href="/medico/recetas.php?id_cita=<?= urlencode($idCita) ?>&editar=<?= urlencode($receta['id_receta']) ?>"
                            >
                                Editar
                            </a>

                            <br><br>

                            <form
                                method="POST"
                                onsubmit="return confirm('¿Seguro que deseas eliminar esta receta?');"
                            >

                                <input
                                    type="hidden"
                                    name="id_cita"
                                    value="<?= htmlspecialchars($idCita) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id_receta"
                                    value="<?= htmlspecialchars(
                                        $receta['id_receta']
                                    ) ?>"
                                >

                                <button
                                    type="submit"
                                    name="accion"
                                    value="eliminar"
                                >
                                    Eliminar
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>


    <hr>


    <?php if ($recetaEditar): ?>

        <h3>Editar receta</h3>

        <form method="POST">

            <input
                type="hidden"
                name="id_cita"
                value="<?= htmlspecialchars($idCita) ?>"
            >

            <input
                type="hidden"
                name="id_receta"
                value="<?= htmlspecialchars(
                    $recetaEditar['id_receta']
                ) ?>"
            >


            <label for="medicamento">
                Medicamento:
            </label>

            <input
                type="text"
                id="medicamento"
                name="medicamento"
                size="40"
                value="<?= htmlspecialchars(
                    $recetaEditar['medicamento']
                ) ?>"
                required
            >

            <br><br>


            <label for="dosis">
                Dosis:
            </label>

            <input
                type="text"
                id="dosis"
                name="dosis"
                size="60"
                value="<?= htmlspecialchars(
                    $recetaEditar['dosis']
                ) ?>"
                required
            >

            <br><br>


            <label for="dias_tratamiento">
                Días de tratamiento:
            </label>

            <input
                type="number"
                id="dias_tratamiento"
                name="dias_tratamiento"
                min="1"
                value="<?= htmlspecialchars(
                    $recetaEditar['dias_tratamiento']
                ) ?>"
                required
            >

            <br><br>


            <button
                type="submit"
                name="accion"
                value="editar"
            >
                Guardar cambios
            </button>

        </form>


    <?php else: ?>


        <h3>Agregar receta</h3>

        <form method="POST">

            <input
                type="hidden"
                name="id_cita"
                value="<?= htmlspecialchars($idCita) ?>"
            >


            <label for="medicamento">
                Medicamento:
            </label>

            <input
                type="text"
                id="medicamento"
                name="medicamento"
                size="40"
                required
            >

            <br><br>


            <label for="dosis">
                Dosis:
            </label>

            <input
                type="text"
                id="dosis"
                name="dosis"
                size="60"
                placeholder="Ejemplo: 1 comprimido cada 24 horas"
                required
            >

            <br><br>


            <label for="dias_tratamiento">
                Días de tratamiento:
            </label>

            <input
                type="number"
                id="dias_tratamiento"
                name="dias_tratamiento"
                min="1"
                required
            >

            <br><br>


            <button
                type="submit"
                name="accion"
                value="agregar"
            >
                Agregar receta
            </button>

        </form>

    <?php endif; ?>


    <br><br>

    <a
        href="/medico/atencion.php?id_cita=<?= urlencode($idCita) ?>"
    >
        Volver a la atención
    </a>

</body>

</html>