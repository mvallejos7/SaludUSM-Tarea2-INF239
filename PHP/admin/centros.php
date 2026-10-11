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
// CREAR CENTRO
// ---------------------------------------------------------

if ($accion === 'crear') {

    $codigo = strtoupper(
        trim($_POST['codigo_centro'] ?? '')
    );

    $nombre = trim(
        $_POST['nombre'] ?? ''
    );

    $comuna = trim(
        $_POST['comuna'] ?? ''
    );

    $region = trim(
        $_POST['region'] ?? ''
    );

    if ($codigo === '') {
        $errores[] =
            'El código del centro es obligatorio.';
    }

    if ($nombre === '') {
        $errores[] =
            'El nombre del centro es obligatorio.';
    }

    if ($comuna === '') {
        $errores[] =
            'La comuna es obligatoria.';
    }

    if ($region === '') {
        $errores[] =
            'La región es obligatoria.';
    }

    if (empty($errores)) {

        try {

            $stmtExiste = $pdo->prepare("
                SELECT COUNT(*)
                FROM centro_medico
                WHERE codigo_centro = ?
            ");

            $stmtExiste->execute([
                $codigo
            ]);

            if (
                (int)$stmtExiste->fetchColumn() > 0
            ) {

                $errores[] =
                    'Ya existe un centro con ese código.';

            } else {

                $stmtCrear = $pdo->prepare("
                    INSERT INTO centro_medico
                    (
                        codigo_centro,
                        nombre,
                        comuna,
                        region
                    )
                    VALUES (?, ?, ?, ?)
                ");

                $stmtCrear->execute([
                    $codigo,
                    $nombre,
                    $comuna,
                    $region
                ]);

                $_SESSION['mensaje_exito'] =
                    'Centro médico creado correctamente.';

                header(
                    'Location: /admin/centros.php'
                );

                exit;
            }

        } catch (PDOException $e) {

            $errores[] =
                'No fue posible crear el centro médico.';
        }
    }
}

// ---------------------------------------------------------
// EDITAR CENTRO
// ---------------------------------------------------------

if ($accion === 'editar') {

    $codigo = strtoupper(
        trim($_POST['codigo_centro'] ?? '')
    );

    $nombre = trim(
        $_POST['nombre'] ?? ''
    );

    $comuna = trim(
        $_POST['comuna'] ?? ''
    );

    $region = trim(
        $_POST['region'] ?? ''
    );

    if ($codigo === '') {
        $errores[] =
            'El centro seleccionado no es válido.';
    }

    if ($nombre === '') {
        $errores[] =
            'El nombre del centro es obligatorio.';
    }

    if ($comuna === '') {
        $errores[] =
            'La comuna es obligatoria.';
    }

    if ($region === '') {
        $errores[] =
            'La región es obligatoria.';
    }

    if (empty($errores)) {

        try {

            $stmtEditar = $pdo->prepare("
                UPDATE centro_medico
                SET nombre = ?,
                    comuna = ?,
                    region = ?
                WHERE codigo_centro = ?
            ");

            $stmtEditar->execute([
                $nombre,
                $comuna,
                $region,
                $codigo
            ]);

            $_SESSION['mensaje_exito'] =
                'Centro médico actualizado correctamente.';

            header(
                'Location: /admin/centros.php'
            );

            exit;

        } catch (PDOException $e) {

            $errores[] =
                'No fue posible actualizar el centro médico.';
        }
    }
}

// ---------------------------------------------------------
// ELIMINAR CENTRO
// ---------------------------------------------------------

if ($accion === 'eliminar') {

    $codigo = strtoupper(
        trim($_POST['codigo_centro'] ?? '')
    );

    try {

        // -------------------------------------------------
        // Verificar citas asociadas
        // -------------------------------------------------

        $stmtCitas = $pdo->prepare("
            SELECT COUNT(*)
            FROM cita
            WHERE codigo_centro = ?
        ");

        $stmtCitas->execute([
            $codigo
        ]);

        $cantidadCitas =
            (int)$stmtCitas->fetchColumn();

        // -------------------------------------------------
        // Verificar médicos asociados
        // -------------------------------------------------

        $stmtMedicos = $pdo->prepare("
            SELECT COUNT(*)
            FROM medico_centro
            WHERE codigo_centro = ?
        ");

        $stmtMedicos->execute([
            $codigo
        ]);

        $cantidadMedicos =
            (int)$stmtMedicos->fetchColumn();

        if ($cantidadCitas > 0) {

            $_SESSION['mensaje_error'] =
                'No se puede eliminar el centro porque tiene citas registradas.';

        } elseif ($cantidadMedicos > 0) {

            $_SESSION['mensaje_error'] =
                'No se puede eliminar el centro porque tiene médicos asociados.';

        } else {

            $stmtEliminar = $pdo->prepare("
                DELETE FROM centro_medico
                WHERE codigo_centro = ?
            ");

            $stmtEliminar->execute([
                $codigo
            ]);

            if ($stmtEliminar->rowCount() === 1) {

                $_SESSION['mensaje_exito'] =
                    'Centro médico eliminado correctamente.';

            } else {

                $_SESSION['mensaje_error'] =
                    'No fue posible encontrar el centro.';
            }
        }

    } catch (PDOException $e) {

        $_SESSION['mensaje_error'] =
            'No fue posible eliminar el centro médico.';
    }

    header(
        'Location: /admin/centros.php'
    );

    exit;
}

// ---------------------------------------------------------
// CENTRO SELECCIONADO PARA EDITAR
// ---------------------------------------------------------

$centroEditar = null;

$codigoEditar =
    strtoupper(
        trim($_GET['editar'] ?? '')
    );

if ($codigoEditar !== '') {

    $stmtCentro = $pdo->prepare("
        SELECT
            codigo_centro,
            nombre,
            comuna,
            region
        FROM centro_medico
        WHERE codigo_centro = ?
        LIMIT 1
    ");

    $stmtCentro->execute([
        $codigoEditar
    ]);

    $centroEditar =
        $stmtCentro->fetch();
}

// ---------------------------------------------------------
// LISTAR CENTROS
// ---------------------------------------------------------

$stmtCentros = $pdo->query("
    SELECT
        cm.codigo_centro,
        cm.nombre,
        cm.comuna,
        cm.region,

        (
            SELECT COUNT(*)
            FROM medico_centro mc
            WHERE mc.codigo_centro =
                  cm.codigo_centro
        ) AS cantidad_medicos,

        (
            SELECT COUNT(*)
            FROM cita c
            WHERE c.codigo_centro =
                  cm.codigo_centro
        ) AS cantidad_citas

    FROM centro_medico cm

    ORDER BY cm.codigo_centro
");

$centros = $stmtCentros->fetchAll();

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Centros médicos - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Administración de centros médicos</h2>


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


    <h3>Centros registrados</h3>


    <?php if (empty($centros)): ?>

        <p>
            No existen centros médicos registrados.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>

                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Comuna</th>
                    <th>Región</th>
                    <th>Médicos</th>
                    <th>Citas</th>
                    <th>Acciones</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($centros as $centro): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $centro['codigo_centro']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $centro['nombre']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $centro['comuna']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $centro['region']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $centro['cantidad_medicos']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $centro['cantidad_citas']
                            ) ?>
                        </td>

                        <td>

                            <a
                                href="/admin/centros.php?editar=<?= urlencode($centro['codigo_centro']) ?>"
                            >
                                Editar
                            </a>

                            <br><br>

                            <form
                                method="POST"
                                onsubmit="return confirm('¿Seguro que deseas eliminar este centro médico?');"
                            >

                                <input
                                    type="hidden"
                                    name="codigo_centro"
                                    value="<?= htmlspecialchars($centro['codigo_centro']) ?>"
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


    <?php if ($centroEditar): ?>

        <h3>Editar centro médico</h3>

        <form method="POST">

            <input
                type="hidden"
                name="codigo_centro"
                value="<?= htmlspecialchars(
                    $centroEditar['codigo_centro']
                ) ?>"
            >


            <p>
                <strong>Código:</strong>

                <?= htmlspecialchars(
                    $centroEditar['codigo_centro']
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
                    $centroEditar['nombre']
                ) ?>"
                required
            >

            <br><br>


            <label for="comuna">
                Comuna:
            </label>

            <input
                type="text"
                id="comuna"
                name="comuna"
                value="<?= htmlspecialchars(
                    $centroEditar['comuna']
                ) ?>"
                required
            >

            <br><br>


            <label for="region">
                Región:
            </label>

            <input
                type="text"
                id="region"
                name="region"
                value="<?= htmlspecialchars(
                    $centroEditar['region']
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

        <a href="/admin/centros.php">
            Cancelar edición
        </a>


    <?php else: ?>


        <h3>Crear nuevo centro</h3>

        <form method="POST">

            <label for="codigo_centro">
                Código:
            </label>

            <input
                type="text"
                id="codigo_centro"
                name="codigo_centro"
                placeholder="Ejemplo: CM005"
                required
            >

            <br><br>


            <label for="nombre">
                Nombre:
            </label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                required
            >

            <br><br>


            <label for="comuna">
                Comuna:
            </label>

            <input
                type="text"
                id="comuna"
                name="comuna"
                required
            >

            <br><br>


            <label for="region">
                Región:
            </label>

            <input
                type="text"
                id="region"
                name="region"
                required
            >

            <br><br>


            <button
                type="submit"
                name="accion"
                value="crear"
            >
                Crear centro
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