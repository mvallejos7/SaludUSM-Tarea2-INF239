<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('ADMIN');

$errores = [];

$mensajeExito = $_SESSION['mensaje_exito'] ?? '';
$mensajeError = $_SESSION['mensaje_error'] ?? '';

unset($_SESSION['mensaje_exito']);
unset($_SESSION['mensaje_error']);

$accion = $_POST['accion'] ?? '';

// ---------------------------------------------------------
// CREAR ESPECIALIDAD
// ---------------------------------------------------------

if ($accion === 'crear') {

    $nombre = trim(
        $_POST['nombre'] ?? ''
    );

    if ($nombre === '') {

        $errores[] =
            'El nombre de la especialidad es obligatorio.';
    }

    if (empty($errores)) {

        try {

            $stmtExiste = $pdo->prepare("
                SELECT COUNT(*)
                FROM especialidad
                WHERE LOWER(nombre) = LOWER(?)
            ");

            $stmtExiste->execute([
                $nombre
            ]);

            if (
                (int)$stmtExiste->fetchColumn() > 0
            ) {

                $errores[] =
                    'Ya existe una especialidad con ese nombre.';

            } else {

                $stmtCrear = $pdo->prepare("
                    INSERT INTO especialidad
                    (
                        nombre
                    )
                    VALUES (?)
                ");

                $stmtCrear->execute([
                    $nombre
                ]);

                $_SESSION['mensaje_exito'] =
                    'Especialidad creada correctamente.';

                header(
                    'Location: /admin/especialidades.php'
                );

                exit;
            }

        } catch (PDOException $e) {

            $errores[] =
                'No fue posible crear la especialidad.';
        }
    }
}

// ---------------------------------------------------------
// EDITAR ESPECIALIDAD
// ---------------------------------------------------------

if ($accion === 'editar') {

    $idEspecialidad =
        $_POST['id_especialidad'] ?? '';

    $nombre = trim(
        $_POST['nombre'] ?? ''
    );

    if (
        !ctype_digit(
            (string)$idEspecialidad
        )
    ) {

        $errores[] =
            'La especialidad seleccionada no es válida.';
    }

    if ($nombre === '') {

        $errores[] =
            'El nombre de la especialidad es obligatorio.';
    }

    if (empty($errores)) {

        try {

            // Comprobar que no exista otra especialidad
            // con el mismo nombre.

            $stmtExiste = $pdo->prepare("
                SELECT COUNT(*)
                FROM especialidad
                WHERE LOWER(nombre) = LOWER(?)
                  AND id_especialidad <> ?
            ");

            $stmtExiste->execute([
                $nombre,
                $idEspecialidad
            ]);

            if (
                (int)$stmtExiste->fetchColumn() > 0
            ) {

                $errores[] =
                    'Ya existe otra especialidad con ese nombre.';

            } else {

                $stmtEditar = $pdo->prepare("
                    UPDATE especialidad
                    SET nombre = ?
                    WHERE id_especialidad = ?
                ");

                $stmtEditar->execute([
                    $nombre,
                    $idEspecialidad
                ]);

                $_SESSION['mensaje_exito'] =
                    'Especialidad actualizada correctamente.';

                header(
                    'Location: /admin/especialidades.php'
                );

                exit;
            }

        } catch (PDOException $e) {

            $errores[] =
                'No fue posible actualizar la especialidad.';
        }
    }
}

// ---------------------------------------------------------
// ELIMINAR ESPECIALIDAD
// ---------------------------------------------------------

if ($accion === 'eliminar') {

    $idEspecialidad =
        $_POST['id_especialidad'] ?? '';

    if (
        !ctype_digit(
            (string)$idEspecialidad
        )
    ) {

        $_SESSION['mensaje_error'] =
            'La especialidad seleccionada no es válida.';

    } else {

        try {

            // -------------------------------------------------
            // Verificar médicos asociados
            // -------------------------------------------------

            $stmtMedicos = $pdo->prepare("
                SELECT COUNT(*)
                FROM medico_especialidad
                WHERE id_especialidad = ?
            ");

            $stmtMedicos->execute([
                $idEspecialidad
            ]);

            $cantidadMedicos =
                (int)$stmtMedicos->fetchColumn();

            // -------------------------------------------------
            // Verificar citas asociadas
            // -------------------------------------------------

            $stmtCitas = $pdo->prepare("
                SELECT COUNT(*)
                FROM cita
                WHERE id_especialidad = ?
            ");

            $stmtCitas->execute([
                $idEspecialidad
            ]);

            $cantidadCitas =
                (int)$stmtCitas->fetchColumn();

            if ($cantidadCitas > 0) {

                $_SESSION['mensaje_error'] =
                    'No se puede eliminar la especialidad porque tiene citas registradas.';

            } elseif ($cantidadMedicos > 0) {

                $_SESSION['mensaje_error'] =
                    'No se puede eliminar la especialidad porque tiene médicos asociados.';

            } else {

                $stmtEliminar = $pdo->prepare("
                    DELETE FROM especialidad
                    WHERE id_especialidad = ?
                ");

                $stmtEliminar->execute([
                    $idEspecialidad
                ]);

                if (
                    $stmtEliminar->rowCount() === 1
                ) {

                    $_SESSION['mensaje_exito'] =
                        'Especialidad eliminada correctamente.';

                } else {

                    $_SESSION['mensaje_error'] =
                        'No fue posible encontrar la especialidad.';
                }
            }

        } catch (PDOException $e) {

            $_SESSION['mensaje_error'] =
                'No fue posible eliminar la especialidad.';
        }
    }

    header(
        'Location: /admin/especialidades.php'
    );

    exit;
}

// ---------------------------------------------------------
// ESPECIALIDAD SELECCIONADA PARA EDITAR
// ---------------------------------------------------------

$especialidadEditar = null;

$idEditar =
    $_GET['editar'] ?? '';

if (
    ctype_digit(
        (string)$idEditar
    )
) {

    $stmtEditar = $pdo->prepare("
        SELECT
            id_especialidad,
            nombre
        FROM especialidad
        WHERE id_especialidad = ?
        LIMIT 1
    ");

    $stmtEditar->execute([
        $idEditar
    ]);

    $especialidadEditar =
        $stmtEditar->fetch();
}

// ---------------------------------------------------------
// LISTAR ESPECIALIDADES
// ---------------------------------------------------------

$stmtEspecialidades = $pdo->query("
    SELECT
        e.id_especialidad,
        e.nombre,

        (
            SELECT COUNT(*)
            FROM medico_especialidad me
            WHERE me.id_especialidad =
                  e.id_especialidad
        ) AS cantidad_medicos,

        (
            SELECT COUNT(*)
            FROM cita c
            WHERE c.id_especialidad =
                  e.id_especialidad
        ) AS cantidad_citas

    FROM especialidad e

    ORDER BY e.nombre
");

$especialidades =
    $stmtEspecialidades->fetchAll();

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Especialidades - SaludUSM
    </title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>
        Administración de especialidades
    </h2>


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


    <h3>
        Especialidades registradas
    </h3>


    <?php if (empty($especialidades)): ?>

        <p>
            No existen especialidades registradas.
        </p>

    <?php else: ?>

        <table
            border="1"
            cellpadding="8"
        >

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Especialidad</th>
                    <th>Médicos</th>
                    <th>Citas</th>
                    <th>Acciones</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach (
                    $especialidades as $especialidad
                ): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $especialidad['id_especialidad']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $especialidad['nombre']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $especialidad['cantidad_medicos']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $especialidad['cantidad_citas']
                            ) ?>
                        </td>

                        <td>

                            <a
                                href="/admin/especialidades.php?editar=<?= urlencode($especialidad['id_especialidad']) ?>"
                            >
                                Editar
                            </a>

                            <br><br>

                            <form
                                method="POST"
                                onsubmit="return confirm('¿Seguro que deseas eliminar esta especialidad?');"
                            >

                                <input
                                    type="hidden"
                                    name="id_especialidad"
                                    value="<?= htmlspecialchars($especialidad['id_especialidad']) ?>"
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


    <?php if ($especialidadEditar): ?>

        <h3>
            Editar especialidad
        </h3>

        <form method="POST">

            <input
                type="hidden"
                name="id_especialidad"
                value="<?= htmlspecialchars(
                    $especialidadEditar['id_especialidad']
                ) ?>"
            >


            <p>
                <strong>ID:</strong>

                <?= htmlspecialchars(
                    $especialidadEditar['id_especialidad']
                ) ?>
            </p>


            <label for="nombre">
                Nombre:
            </label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                value="<?= htmlspecialchars(
                    $especialidadEditar['nombre']
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

        <br>

        <a href="/admin/especialidades.php">
            Cancelar edición
        </a>


    <?php else: ?>


        <h3>
            Crear nueva especialidad
        </h3>

        <form method="POST">

            <label for="nombre">
                Nombre:
            </label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                placeholder="Ejemplo: Nutrición"
                required
            >

            <br><br>


            <button
                type="submit"
                name="accion"
                value="crear"
            >
                Crear especialidad
            </button>

        </form>

    <?php endif; ?>


    <br><br>

    <a href="/admin/index.php">
        Volver al panel administrador
    </a>

    <br><br>

    <a href="/logout.php">
        Cerrar sesión
    </a>

</body>

</html>