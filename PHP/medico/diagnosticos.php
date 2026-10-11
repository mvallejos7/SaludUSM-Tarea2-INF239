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
// Obtener atención y comprobar propiedad del médico
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

// ---------------------------------------------------------
// Procesar acciones
// ---------------------------------------------------------

$accion = $_POST['accion'] ?? '';

if (
    $accion === 'agregar'
    || $accion === 'editar'
) {

    $codigo = strtoupper(
        trim($_POST['codigo_diagnostico'] ?? '')
    );

    $descripcion = trim(
        $_POST['descripcion'] ?? ''
    );

    // Formato CIE-10 básico:
    // I10
    // J45.9
    // E11.9

    if (
        !preg_match(
            '/^[A-Z][0-9]{2}(?:\.[0-9A-Z]{1,4})?$/',
            $codigo
        )
    ) {
        $errores[] =
            'El código debe tener un formato CIE-10 válido, por ejemplo I10 o J45.9.';
    }

    if ($descripcion === '') {
        $errores[] =
            'La descripción del diagnóstico es obligatoria.';
    }
}

// ---------------------------------------------------------
// AGREGAR diagnóstico
// ---------------------------------------------------------

if (
    $accion === 'agregar'
    && empty($errores)
) {

    try {

        $pdo->beginTransaction();

        // Verificar si el diagnóstico ya existe en catálogo

        $stmtDiagnostico = $pdo->prepare("
            SELECT codigo_diagnostico
            FROM diagnostico
            WHERE codigo_diagnostico = ?
            LIMIT 1
        ");

        $stmtDiagnostico->execute([
            $codigo
        ]);

        $diagnosticoExiste =
            $stmtDiagnostico->fetchColumn();

        // Si no existe, crear en catálogo

        if (!$diagnosticoExiste) {

            $stmtCrearDiagnostico = $pdo->prepare("
                INSERT INTO diagnostico
                (
                    codigo_diagnostico,
                    descripcion
                )
                VALUES (?, ?)
            ");

            $stmtCrearDiagnostico->execute([
                $codigo,
                $descripcion
            ]);
        }

        // Comprobar si ya está asociado a esta atención

        $stmtExisteRelacion = $pdo->prepare("
            SELECT COUNT(*)
            FROM atencion_diagnostico
            WHERE id_atencion = ?
              AND codigo_diagnostico = ?
        ");

        $stmtExisteRelacion->execute([
            $idAtencion,
            $codigo
        ]);

        if (
            (int)$stmtExisteRelacion->fetchColumn() > 0
        ) {

            $pdo->rollBack();

            $_SESSION['mensaje_error'] =
                'Ese diagnóstico ya está asociado a la atención.';

        } else {

            $stmtRelacion = $pdo->prepare("
                INSERT INTO atencion_diagnostico
                (
                    id_atencion,
                    codigo_diagnostico
                )
                VALUES (?, ?)
            ");

            $stmtRelacion->execute([
                $idAtencion,
                $codigo
            ]);

            $pdo->commit();

            $_SESSION['mensaje_exito'] =
                'Diagnóstico agregado correctamente.';
        }

    } catch (PDOException $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['mensaje_error'] =
            'No fue posible agregar el diagnóstico.';
    }

    header(
        'Location: /medico/diagnosticos.php?id_cita='
        . urlencode($idCita)
    );

    exit;
}

// ---------------------------------------------------------
// EDITAR diagnóstico
// ---------------------------------------------------------

if (
    $accion === 'editar'
    && empty($errores)
) {

    $codigoAnterior = strtoupper(
        trim($_POST['codigo_anterior'] ?? '')
    );

    try {

        $pdo->beginTransaction();

        // Verificar que el diagnóstico anterior
        // pertenece a esta atención.

        $stmtRelacionAnterior = $pdo->prepare("
            SELECT COUNT(*)
            FROM atencion_diagnostico
            WHERE id_atencion = ?
              AND codigo_diagnostico = ?
        ");

        $stmtRelacionAnterior->execute([
            $idAtencion,
            $codigoAnterior
        ]);

        if (
            (int)$stmtRelacionAnterior->fetchColumn() === 0
        ) {
            throw new Exception(
                'El diagnóstico no pertenece a esta atención.'
            );
        }

        // Ver si el nuevo código existe

        $stmtExisteNuevo = $pdo->prepare("
            SELECT codigo_diagnostico
            FROM diagnostico
            WHERE codigo_diagnostico = ?
            LIMIT 1
        ");

        $stmtExisteNuevo->execute([
            $codigo
        ]);

        $nuevoExiste =
            $stmtExisteNuevo->fetchColumn();

        if (!$nuevoExiste) {

            // Crear nuevo diagnóstico

            $stmtCrear = $pdo->prepare("
                INSERT INTO diagnostico
                (
                    codigo_diagnostico,
                    descripcion
                )
                VALUES (?, ?)
            ");

            $stmtCrear->execute([
                $codigo,
                $descripcion
            ]);

        } else {

            // Actualizar descripción del catálogo

            $stmtActualizarDescripcion = $pdo->prepare("
                UPDATE diagnostico
                SET descripcion = ?
                WHERE codigo_diagnostico = ?
            ");

            $stmtActualizarDescripcion->execute([
                $descripcion,
                $codigo
            ]);
        }

        // Si cambió el código

        if ($codigo !== $codigoAnterior) {

            $stmtYaRelacionado = $pdo->prepare("
                SELECT COUNT(*)
                FROM atencion_diagnostico
                WHERE id_atencion = ?
                  AND codigo_diagnostico = ?
            ");

            $stmtYaRelacionado->execute([
                $idAtencion,
                $codigo
            ]);

            if (
                (int)$stmtYaRelacionado->fetchColumn() > 0
            ) {
                throw new Exception(
                    'El nuevo diagnóstico ya está asociado.'
                );
            }

            // Eliminar relación anterior

            $stmtEliminarAnterior = $pdo->prepare("
                DELETE FROM atencion_diagnostico
                WHERE id_atencion = ?
                  AND codigo_diagnostico = ?
            ");

            $stmtEliminarAnterior->execute([
                $idAtencion,
                $codigoAnterior
            ]);

            // Crear relación nueva

            $stmtNuevaRelacion = $pdo->prepare("
                INSERT INTO atencion_diagnostico
                (
                    id_atencion,
                    codigo_diagnostico
                )
                VALUES (?, ?)
            ");

            $stmtNuevaRelacion->execute([
                $idAtencion,
                $codigo
            ]);
        }

        $pdo->commit();

        $_SESSION['mensaje_exito'] =
            'Diagnóstico actualizado correctamente.';

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['mensaje_error'] =
            'No fue posible actualizar el diagnóstico.';
    }

    header(
        'Location: /medico/diagnosticos.php?id_cita='
        . urlencode($idCita)
    );

    exit;
}

// ---------------------------------------------------------
// ELIMINAR diagnóstico
// ---------------------------------------------------------

if ($accion === 'eliminar') {

    $codigoEliminar = strtoupper(
        trim($_POST['codigo_diagnostico'] ?? '')
    );

    try {

        // No permitir dejar una atención sin diagnóstico

        $stmtCantidad = $pdo->prepare("
            SELECT COUNT(*)
            FROM atencion_diagnostico
            WHERE id_atencion = ?
        ");

        $stmtCantidad->execute([
            $idAtencion
        ]);

        $cantidadDiagnosticos =
            (int)$stmtCantidad->fetchColumn();

        if ($cantidadDiagnosticos <= 1) {

            $_SESSION['mensaje_error'] =
                'La atención debe conservar al menos un diagnóstico.';

        } else {

            $stmtEliminar = $pdo->prepare("
                DELETE FROM atencion_diagnostico
                WHERE id_atencion = ?
                  AND codigo_diagnostico = ?
            ");

            $stmtEliminar->execute([
                $idAtencion,
                $codigoEliminar
            ]);

            if ($stmtEliminar->rowCount() === 1) {

                $_SESSION['mensaje_exito'] =
                    'Diagnóstico eliminado de la atención.';

            } else {

                $_SESSION['mensaje_error'] =
                    'No fue posible encontrar el diagnóstico.';
            }
        }

    } catch (PDOException $e) {

        $_SESSION['mensaje_error'] =
            'No fue posible eliminar el diagnóstico.';
    }

    header(
        'Location: /medico/diagnosticos.php?id_cita='
        . urlencode($idCita)
    );

    exit;
}

// ---------------------------------------------------------
// Obtener diagnósticos actuales
// ---------------------------------------------------------

$stmtDiagnosticos = $pdo->prepare("
    SELECT
        d.codigo_diagnostico,
        d.descripcion

    FROM atencion_diagnostico ad

    JOIN diagnostico d
        ON ad.codigo_diagnostico =
           d.codigo_diagnostico

    WHERE ad.id_atencion = ?

    ORDER BY d.codigo_diagnostico
");

$stmtDiagnosticos->execute([
    $idAtencion
]);

$diagnosticos =
    $stmtDiagnosticos->fetchAll();

// ---------------------------------------------------------
// Diagnóstico que se desea editar
// ---------------------------------------------------------

$diagnosticoEditar = null;

$codigoEditar =
    $_GET['editar'] ?? '';

if ($codigoEditar !== '') {

    $stmtEditar = $pdo->prepare("
        SELECT
            d.codigo_diagnostico,
            d.descripcion

        FROM atencion_diagnostico ad

        JOIN diagnostico d
            ON ad.codigo_diagnostico =
               d.codigo_diagnostico

        WHERE ad.id_atencion = ?
          AND d.codigo_diagnostico = ?

        LIMIT 1
    ");

    $stmtEditar->execute([
        $idAtencion,
        $codigoEditar
    ]);

    $diagnosticoEditar =
        $stmtEditar->fetch();
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

    <title>Diagnósticos - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Diagnósticos de la atención</h2>


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


    <h3>Diagnósticos registrados</h3>


    <?php if (empty($diagnosticos)): ?>

        <p>
            Esta atención todavía no tiene diagnósticos.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>

                <tr>
                    <th>Código CIE-10</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($diagnosticos as $diagnostico): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $diagnostico['codigo_diagnostico']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $diagnostico['descripcion']
                            ) ?>
                        </td>

                        <td>

                            <a
                                href="/medico/diagnosticos.php?id_cita=<?= urlencode($idCita) ?>&editar=<?= urlencode($diagnostico['codigo_diagnostico']) ?>"
                            >
                                Editar
                            </a>

                            <br><br>

                            <form
                                method="POST"
                                onsubmit="return confirm('¿Seguro que deseas eliminar este diagnóstico de la atención?');"
                            >

                                <input
                                    type="hidden"
                                    name="id_cita"
                                    value="<?= htmlspecialchars($idCita) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="codigo_diagnostico"
                                    value="<?= htmlspecialchars($diagnostico['codigo_diagnostico']) ?>"
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


    <?php if ($diagnosticoEditar): ?>

        <h3>Editar diagnóstico</h3>

        <form method="POST">

            <input
                type="hidden"
                name="id_cita"
                value="<?= htmlspecialchars($idCita) ?>"
            >

            <input
                type="hidden"
                name="codigo_anterior"
                value="<?= htmlspecialchars(
                    $diagnosticoEditar['codigo_diagnostico']
                ) ?>"
            >


            <label for="codigo_diagnostico">
                Código CIE-10:
            </label>

            <input
                type="text"
                id="codigo_diagnostico"
                name="codigo_diagnostico"
                maxlength="10"
                value="<?= htmlspecialchars(
                    $diagnosticoEditar['codigo_diagnostico']
                ) ?>"
                required
            >

            <br><br>


            <label for="descripcion">
                Descripción:
            </label>

            <input
                type="text"
                id="descripcion"
                name="descripcion"
                size="60"
                value="<?= htmlspecialchars(
                    $diagnosticoEditar['descripcion']
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


        <h3>Agregar diagnóstico</h3>

        <form method="POST">

            <input
                type="hidden"
                name="id_cita"
                value="<?= htmlspecialchars($idCita) ?>"
            >


            <label for="codigo_diagnostico">
                Código CIE-10:
            </label>

            <input
                type="text"
                id="codigo_diagnostico"
                name="codigo_diagnostico"
                maxlength="10"
                placeholder="Ejemplo: I10"
                required
            >

            <br><br>


            <label for="descripcion">
                Descripción:
            </label>

            <input
                type="text"
                id="descripcion"
                name="descripcion"
                size="60"
                required
            >

            <br><br>


            <button
                type="submit"
                name="accion"
                value="agregar"
            >
                Agregar diagnóstico
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