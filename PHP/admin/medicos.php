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
// Catálogos
// ---------------------------------------------------------

$stmtEspecialidades = $pdo->query("
    SELECT
        id_especialidad,
        nombre
    FROM especialidad
    ORDER BY nombre
");

$especialidades = $stmtEspecialidades->fetchAll();

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
// Validación común
// ---------------------------------------------------------

function validarDatosMedico(
    string $nombre,
    string $rut,
    string $email,
    array $especialidadesSeleccionadas,
    array $centrosSeleccionados,
    string $password = '',
    bool $passwordObligatoria = false
): array {

    $errores = [];

    if ($nombre === '') {
        $errores[] =
            'El nombre completo es obligatorio.';
    }

    if (
        !preg_match(
            '/^[0-9]{8}-[0-9Kk]$/',
            $rut
        )
    ) {
        $errores[] =
            'El RUT debe tener formato XXXXXXXX-X.';
    }

    if (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        $errores[] =
            'El correo electrónico no es válido.';
    }

    $cantidadEspecialidades =
        count($especialidadesSeleccionadas);

    if (
        $cantidadEspecialidades < 1
        || $cantidadEspecialidades > 3
    ) {
        $errores[] =
            'El médico debe tener entre 1 y 3 especialidades.';
    }

    if (
        count($centrosSeleccionados) < 1
    ) {
        $errores[] =
            'El médico debe estar asociado al menos a un centro.';
    }

    if (
        $passwordObligatoria
        || $password !== ''
    ) {

        if (strlen($password) < 8) {

            $errores[] =
                'La contraseña debe tener al menos 8 caracteres.';
        }

        if (
            !preg_match('/[A-Z]/', $password)
        ) {

            $errores[] =
                'La contraseña debe contener una mayúscula.';
        }

        if (
            !preg_match('/[a-z]/', $password)
        ) {

            $errores[] =
                'La contraseña debe contener una minúscula.';
        }

        if (
            !preg_match('/[0-9]/', $password)
        ) {

            $errores[] =
                'La contraseña debe contener un número.';
        }
    }

    return $errores;
}

// ---------------------------------------------------------
// CREAR MÉDICO
// ---------------------------------------------------------

if ($accion === 'crear') {

    $nombre = trim(
        $_POST['nombre_completo'] ?? ''
    );

    $rut = strtoupper(
        trim($_POST['rut'] ?? '')
    );

    $email = strtolower(
        trim($_POST['email'] ?? '')
    );

    $password =
        $_POST['password'] ?? '';

    $especialidadesSeleccionadas =
        $_POST['especialidades'] ?? [];

    $centrosSeleccionados =
        $_POST['centros'] ?? [];

    $errores = validarDatosMedico(
        $nombre,
        $rut,
        $email,
        $especialidadesSeleccionadas,
        $centrosSeleccionados,
        $password,
        true
    );

    if (empty($errores)) {

        try {

            $pdo->beginTransaction();

            // ---------------------------------------------
            // Comprobar RUT y correo únicos
            // ---------------------------------------------

            $stmtExiste = $pdo->prepare("
                SELECT COUNT(*)
                FROM usuario
                WHERE rut = ?
                   OR email = ?
            ");

            $stmtExiste->execute([
                $rut,
                $email
            ]);

            if (
                (int)$stmtExiste->fetchColumn() > 0
            ) {

                throw new Exception(
                    'Ya existe un usuario con ese RUT o correo.'
                );
            }

            // ---------------------------------------------
            // Crear usuario
            // ---------------------------------------------

            $passwordHash =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

            $stmtUsuario = $pdo->prepare("
                INSERT INTO usuario
                (
                    rut,
                    email,
                    password_hash,
                    rol,
                    activo
                )
                VALUES (?, ?, ?, 'MEDICO', 1)
            ");

            $stmtUsuario->execute([
                $rut,
                $email,
                $passwordHash
            ]);

            $idUsuario =
                $pdo->lastInsertId();

            // ---------------------------------------------
            // Crear médico
            // ---------------------------------------------

            $stmtMedico = $pdo->prepare("
                INSERT INTO medico
                (
                    id_usuario,
                    nombre_completo
                )
                VALUES (?, ?)
            ");

            $stmtMedico->execute([
                $idUsuario,
                $nombre
            ]);

            $idMedico =
                $pdo->lastInsertId();

            // ---------------------------------------------
            // Asignar especialidades
            // ---------------------------------------------

            $stmtEspecialidad = $pdo->prepare("
                INSERT INTO medico_especialidad
                (
                    id_medico,
                    id_especialidad,
                    activo
                )
                VALUES (?, ?, 1)
            ");

            foreach (
                $especialidadesSeleccionadas
                as $idEspecialidad
            ) {

                if (
                    !ctype_digit(
                        (string)$idEspecialidad
                    )
                ) {
                    throw new Exception(
                        'Especialidad inválida.'
                    );
                }

                $stmtEspecialidad->execute([
                    $idMedico,
                    $idEspecialidad
                ]);
            }

            // ---------------------------------------------
            // Asignar centros
            // ---------------------------------------------

            $stmtCentro = $pdo->prepare("
                INSERT INTO medico_centro
                (
                    id_medico,
                    codigo_centro,
                    activo
                )
                VALUES (?, ?, 1)
            ");

            foreach (
                $centrosSeleccionados
                as $codigoCentro
            ) {

                $stmtCentro->execute([
                    $idMedico,
                    $codigoCentro
                ]);
            }

            $pdo->commit();

            $_SESSION['mensaje_exito'] =
                'Médico creado correctamente.';

            header(
                'Location: /admin/medicos.php'
            );

            exit;

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errores[] =
                $e->getMessage();
        }
    }
}

// ---------------------------------------------------------
// EDITAR MÉDICO
// ---------------------------------------------------------

if ($accion === 'editar') {

    $idMedico =
        $_POST['id_medico'] ?? '';

    $nombre = trim(
        $_POST['nombre_completo'] ?? ''
    );

    $rut = strtoupper(
        trim($_POST['rut'] ?? '')
    );

    $email = strtolower(
        trim($_POST['email'] ?? '')
    );

    $password =
        $_POST['password'] ?? '';

    $especialidadesSeleccionadas =
        $_POST['especialidades'] ?? [];

    $centrosSeleccionados =
        $_POST['centros'] ?? [];

    if (
        !ctype_digit(
            (string)$idMedico
        )
    ) {

        $errores[] =
            'El médico seleccionado no es válido.';
    }

    $errores = array_merge(
        $errores,
        validarDatosMedico(
            $nombre,
            $rut,
            $email,
            $especialidadesSeleccionadas,
            $centrosSeleccionados,
            $password,
            false
        )
    );

    if (empty($errores)) {

        try {

            $pdo->beginTransaction();

            // ---------------------------------------------
            // Obtener usuario del médico
            // ---------------------------------------------

            $stmtUsuarioMedico =
                $pdo->prepare("
                    SELECT id_usuario
                    FROM medico
                    WHERE id_medico = ?
                    LIMIT 1
                ");

            $stmtUsuarioMedico->execute([
                $idMedico
            ]);

            $idUsuario =
                $stmtUsuarioMedico->fetchColumn();

            if (!$idUsuario) {
                throw new Exception(
                    'No fue posible encontrar el médico.'
                );
            }

            // ---------------------------------------------
            // Verificar RUT/correo duplicados
            // ---------------------------------------------

            $stmtDuplicado =
                $pdo->prepare("
                    SELECT COUNT(*)
                    FROM usuario
                    WHERE
                        (rut = ? OR email = ?)
                        AND id_usuario <> ?
                ");

            $stmtDuplicado->execute([
                $rut,
                $email,
                $idUsuario
            ]);

            if (
                (int)$stmtDuplicado->fetchColumn() > 0
            ) {

                throw new Exception(
                    'Ya existe otro usuario con ese RUT o correo.'
                );
            }


            // ---------------------------------------------
            // Proteger especialidades y centros que tengan
            // citas futuras Reservadas o Confirmadas
            // ---------------------------------------------

            $especialidadesSeleccionadasStr =
                array_map(
                    'strval',
                    $especialidadesSeleccionadas
                );

            $centrosSeleccionadosStr =
                array_map(
                    'strval',
                    $centrosSeleccionados
                );


            $stmtCitasFuturas =
                $pdo->prepare("
                    SELECT DISTINCT
                        c.id_especialidad,
                        e.nombre AS especialidad,
                        c.codigo_centro,
                        cm.nombre AS centro

                    FROM cita c

                    JOIN estado_cita ec
                        ON c.id_estado_cita =
                        ec.id_estado_cita

                    JOIN especialidad e
                        ON c.id_especialidad =
                        e.id_especialidad

                    JOIN centro_medico cm
                        ON c.codigo_centro =
                        cm.codigo_centro

                    WHERE c.id_medico = ?

                    AND c.fecha_hora >
                        CURRENT_TIMESTAMP

                    AND ec.nombre IN (
                        'Reservada',
                        'Confirmada'
                    )
                ");

            $stmtCitasFuturas->execute([
                $idMedico
            ]);

            $citasFuturas =
                $stmtCitasFuturas->fetchAll();


            foreach (
                $citasFuturas
                as $citaFutura
            ) {

                // -----------------------------------------
                // No permitir quitar especialidad usada
                // por una cita futura activa
                // -----------------------------------------

                if (
                    !in_array(
                        (string)$citaFutura['id_especialidad'],
                        $especialidadesSeleccionadasStr,
                        true
                    )
                ) {

                    throw new Exception(
                        'No se puede quitar la especialidad "'
                        . $citaFutura['especialidad']
                        . '" porque el médico tiene citas futuras Reservadas o Confirmadas con esa especialidad.'
                    );
                }


                // -----------------------------------------
                // No permitir quitar centro usado
                // por una cita futura activa
                // -----------------------------------------

                if (
                    !in_array(
                        (string)$citaFutura['codigo_centro'],
                        $centrosSeleccionadosStr,
                        true
                    )
                ) {

                    throw new Exception(
                        'No se puede quitar el centro "'
                        . $citaFutura['centro']
                        . '" porque el médico tiene citas futuras Reservadas o Confirmadas en ese centro.'
                    );
                }
            }


            // ---------------------------------------------
            // Actualizar usuario
            // ---------------------------------------------

            if ($password !== '') {

                $passwordHash =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                $stmtUsuario =
                    $pdo->prepare("
                        UPDATE usuario
                        SET rut = ?,
                            email = ?,
                            password_hash = ?
                        WHERE id_usuario = ?
                    ");

                $stmtUsuario->execute([
                    $rut,
                    $email,
                    $passwordHash,
                    $idUsuario
                ]);

            } else {

                $stmtUsuario =
                    $pdo->prepare("
                        UPDATE usuario
                        SET rut = ?,
                            email = ?
                        WHERE id_usuario = ?
                    ");

                $stmtUsuario->execute([
                    $rut,
                    $email,
                    $idUsuario
                ]);
            }

            // ---------------------------------------------
            // Actualizar médico
            // ---------------------------------------------

            $stmtActualizarMedico =
                $pdo->prepare("
                    UPDATE medico
                    SET nombre_completo = ?
                    WHERE id_medico = ?
                ");

            $stmtActualizarMedico->execute([
                $nombre,
                $idMedico
            ]);

            // ---------------------------------------------
            // Desactivar todas las especialidades
            // ---------------------------------------------

            $stmtDesactivarEspecialidades =
                $pdo->prepare("
                    UPDATE medico_especialidad
                    SET activo = 0
                    WHERE id_medico = ?
                ");

            $stmtDesactivarEspecialidades->execute([
                $idMedico
            ]);

            // ---------------------------------------------
            // Activar/crear seleccionadas
            // ---------------------------------------------

            foreach (
                $especialidadesSeleccionadas
                as $idEspecialidad
            ) {

                if (
                    !ctype_digit(
                        (string)$idEspecialidad
                    )
                ) {
                    throw new Exception(
                        'Especialidad inválida.'
                    );
                }

                $stmtExisteRelacion =
                    $pdo->prepare("
                        SELECT COUNT(*)
                        FROM medico_especialidad
                        WHERE id_medico = ?
                          AND id_especialidad = ?
                    ");

                $stmtExisteRelacion->execute([
                    $idMedico,
                    $idEspecialidad
                ]);

                if (
                    (int)$stmtExisteRelacion->fetchColumn() > 0
                ) {

                    $stmtActivar =
                        $pdo->prepare("
                            UPDATE medico_especialidad
                            SET activo = 1
                            WHERE id_medico = ?
                              AND id_especialidad = ?
                        ");

                    $stmtActivar->execute([
                        $idMedico,
                        $idEspecialidad
                    ]);

                } else {

                    $stmtCrearRelacion =
                        $pdo->prepare("
                            INSERT INTO medico_especialidad
                            (
                                id_medico,
                                id_especialidad,
                                activo
                            )
                            VALUES (?, ?, 1)
                        ");

                    $stmtCrearRelacion->execute([
                        $idMedico,
                        $idEspecialidad
                    ]);
                }
            }

            // ---------------------------------------------
            // Desactivar todos los centros
            // ---------------------------------------------

            $stmtDesactivarCentros =
                $pdo->prepare("
                    UPDATE medico_centro
                    SET activo = 0
                    WHERE id_medico = ?
                ");

            $stmtDesactivarCentros->execute([
                $idMedico
            ]);

            // ---------------------------------------------
            // Activar/crear centros seleccionados
            // ---------------------------------------------

            foreach (
                $centrosSeleccionados
                as $codigoCentro
            ) {

                $stmtExisteCentro =
                    $pdo->prepare("
                        SELECT COUNT(*)
                        FROM medico_centro
                        WHERE id_medico = ?
                          AND codigo_centro = ?
                    ");

                $stmtExisteCentro->execute([
                    $idMedico,
                    $codigoCentro
                ]);

                if (
                    (int)$stmtExisteCentro->fetchColumn() > 0
                ) {

                    $stmtActivarCentro =
                        $pdo->prepare("
                            UPDATE medico_centro
                            SET activo = 1
                            WHERE id_medico = ?
                              AND codigo_centro = ?
                        ");

                    $stmtActivarCentro->execute([
                        $idMedico,
                        $codigoCentro
                    ]);

                } else {

                    $stmtCrearCentro =
                        $pdo->prepare("
                            INSERT INTO medico_centro
                            (
                                id_medico,
                                codigo_centro,
                                activo
                            )
                            VALUES (?, ?, 1)
                        ");

                    $stmtCrearCentro->execute([
                        $idMedico,
                        $codigoCentro
                    ]);
                }
            }

            $pdo->commit();

            $_SESSION['mensaje_exito'] =
                'Médico actualizado correctamente.';

            header(
                'Location: /admin/medicos.php'
            );

            exit;

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errores[] =
                $e->getMessage();
        }
    }
}

// ---------------------------------------------------------
// ACTIVAR / DESACTIVAR MÉDICO
// ---------------------------------------------------------

if (
    $accion === 'desactivar'
    || $accion === 'activar'
) {

    $idMedico =
        $_POST['id_medico'] ?? '';

    if (
        !ctype_digit(
            (string)$idMedico
        )
    ) {

        $_SESSION['mensaje_error'] =
            'El médico seleccionado no es válido.';

    } else {

        try {

            // ---------------------------------------------
            // Obtener usuario
            // ---------------------------------------------

            $stmtUsuario =
                $pdo->prepare("
                    SELECT id_usuario
                    FROM medico
                    WHERE id_medico = ?
                    LIMIT 1
                ");

            $stmtUsuario->execute([
                $idMedico
            ]);

            $idUsuario =
                $stmtUsuario->fetchColumn();

            if (!$idUsuario) {

                throw new Exception(
                    'No fue posible encontrar el médico.'
                );
            }

            // ---------------------------------------------
            // Si vamos a desactivar:
            // comprobar citas futuras activas
            // ---------------------------------------------

            if ($accion === 'desactivar') {

                $stmtFuturas =
                    $pdo->prepare("
                        SELECT COUNT(*)

                        FROM cita c

                        JOIN estado_cita ec
                            ON c.id_estado_cita =
                               ec.id_estado_cita

                        WHERE c.id_medico = ?
                          AND c.fecha_hora >
                              CURRENT_TIMESTAMP
                          AND ec.nombre IN (
                              'Reservada',
                              'Confirmada'
                          )
                    ");

                $stmtFuturas->execute([
                    $idMedico
                ]);

                if (
                    (int)$stmtFuturas->fetchColumn() > 0
                ) {

                    $_SESSION['mensaje_error'] =
                        'No se puede desactivar el médico porque tiene citas futuras Reservadas o Confirmadas.';

                } else {

                    $pdo->beginTransaction();

                    $stmtDesactivar =
                        $pdo->prepare("
                            UPDATE usuario
                            SET activo = 0
                            WHERE id_usuario = ?
                        ");

                    $stmtDesactivar->execute([
                        $idUsuario
                    ]);

                    $stmtME =
                        $pdo->prepare("
                            UPDATE medico_especialidad
                            SET activo = 0
                            WHERE id_medico = ?
                        ");

                    $stmtME->execute([
                        $idMedico
                    ]);

                    $stmtMC =
                        $pdo->prepare("
                            UPDATE medico_centro
                            SET activo = 0
                            WHERE id_medico = ?
                        ");

                    $stmtMC->execute([
                        $idMedico
                    ]);

                    $pdo->commit();

                    $_SESSION['mensaje_exito'] =
                        'Médico desactivado correctamente.';
                }

            } else {

                // -----------------------------------------
                // Reactivar usuario
                // Las asignaciones se administran luego
                // desde Editar.
                // -----------------------------------------

                $stmtActivar =
                    $pdo->prepare("
                        UPDATE usuario
                        SET activo = 1
                        WHERE id_usuario = ?
                    ");

                $stmtActivar->execute([
                    $idUsuario
                ]);

                $_SESSION['mensaje_exito'] =
                    'Médico activado correctamente. Revisa sus centros y especialidades.';
            }

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $_SESSION['mensaje_error'] =
                $e->getMessage();
        }
    }

    header(
        'Location: /admin/medicos.php'
    );

    exit;
}

// ---------------------------------------------------------
// MÉDICO PARA EDITAR
// ---------------------------------------------------------

$medicoEditar = null;
$especialidadesMedico = [];
$centrosMedico = [];

$idEditar =
    $_GET['editar'] ?? '';

if (
    ctype_digit(
        (string)$idEditar
    )
) {

    $stmtEditar = $pdo->prepare("
        SELECT
            m.id_medico,
            m.nombre_completo,
            u.rut,
            u.email,
            u.activo

        FROM medico m

        JOIN usuario u
            ON m.id_usuario = u.id_usuario

        WHERE m.id_medico = ?

        LIMIT 1
    ");

    $stmtEditar->execute([
        $idEditar
    ]);

    $medicoEditar =
        $stmtEditar->fetch();

    if ($medicoEditar) {

        $stmtEM = $pdo->prepare("
            SELECT id_especialidad
            FROM medico_especialidad
            WHERE id_medico = ?
              AND activo = 1
        ");

        $stmtEM->execute([
            $idEditar
        ]);

        $especialidadesMedico =
            $stmtEM->fetchAll(
                PDO::FETCH_COLUMN
            );

        $stmtCM = $pdo->prepare("
            SELECT codigo_centro
            FROM medico_centro
            WHERE id_medico = ?
              AND activo = 1
        ");

        $stmtCM->execute([
            $idEditar
        ]);

        $centrosMedico =
            $stmtCM->fetchAll(
                PDO::FETCH_COLUMN
            );
    }
}

// ---------------------------------------------------------
// LISTAR MÉDICOS
// ---------------------------------------------------------

$stmtMedicos = $pdo->query("
    SELECT
        m.id_medico,
        m.nombre_completo,
        u.rut,
        u.email,
        u.activo,

        GROUP_CONCAT(
            DISTINCT
            CASE
                WHEN me.activo = 1
                THEN e.nombre
            END
            ORDER BY e.nombre
            SEPARATOR ', '
        ) AS especialidades,

        GROUP_CONCAT(
            DISTINCT
            CASE
                WHEN mc.activo = 1
                THEN cm.nombre
            END
            ORDER BY cm.nombre
            SEPARATOR ', '
        ) AS centros

    FROM medico m

    JOIN usuario u
        ON m.id_usuario = u.id_usuario

    LEFT JOIN medico_especialidad me
        ON m.id_medico = me.id_medico

    LEFT JOIN especialidad e
        ON me.id_especialidad =
           e.id_especialidad

    LEFT JOIN medico_centro mc
        ON m.id_medico = mc.id_medico

    LEFT JOIN centro_medico cm
        ON mc.codigo_centro =
           cm.codigo_centro

    GROUP BY
        m.id_medico,
        m.nombre_completo,
        u.rut,
        u.email,
        u.activo

    ORDER BY m.nombre_completo
");

$medicos =
    $stmtMedicos->fetchAll();

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
        Médicos - SaludUSM
    </title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>
        Administración de médicos
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


    <h3>Médicos registrados</h3>


    <table border="1" cellpadding="8">

        <thead>

            <tr>
                <th>Nombre</th>
                <th>RUT</th>
                <th>Correo</th>
                <th>Especialidades</th>
                <th>Centros</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>

        </thead>

        <tbody>

            <?php foreach ($medicos as $medico): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars(
                            $medico['nombre_completo']
                        ) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            $medico['rut']
                        ) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            $medico['email']
                        ) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            $medico['especialidades']
                            ?? 'Sin especialidades activas'
                        ) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            $medico['centros']
                            ?? 'Sin centros activos'
                        ) ?>
                    </td>

                    <td>

                        <?php if ($medico['activo']): ?>

                            Activo

                        <?php else: ?>

                            Inactivo

                        <?php endif; ?>

                    </td>

                    <td>

                        <a
                            href="/admin/medicos.php?editar=<?= urlencode($medico['id_medico']) ?>"
                        >
                            Editar
                        </a>

                        <br><br>


                        <?php if ($medico['activo']): ?>

                            <form
                                method="POST"
                                onsubmit="return confirm('¿Seguro que deseas desactivar este médico?');"
                            >

                                <input
                                    type="hidden"
                                    name="id_medico"
                                    value="<?= htmlspecialchars($medico['id_medico']) ?>"
                                >

                                <button
                                    type="submit"
                                    name="accion"
                                    value="desactivar"
                                >
                                    Desactivar
                                </button>

                            </form>

                        <?php else: ?>

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="id_medico"
                                    value="<?= htmlspecialchars($medico['id_medico']) ?>"
                                >

                                <button
                                    type="submit"
                                    name="accion"
                                    value="activar"
                                >
                                    Activar
                                </button>

                            </form>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>


    <hr>


    <?php if ($medicoEditar): ?>

        <h3>Editar médico</h3>

        <form method="POST">

            <input
                type="hidden"
                name="id_medico"
                value="<?= htmlspecialchars(
                    $medicoEditar['id_medico']
                ) ?>"
            >


            <label>
                Nombre completo:
            </label>

            <input
                type="text"
                name="nombre_completo"
                value="<?= htmlspecialchars(
                    $medicoEditar['nombre_completo']
                ) ?>"
                required
            >

            <br><br>


            <label>RUT:</label>

            <input
                type="text"
                name="rut"
                value="<?= htmlspecialchars(
                    $medicoEditar['rut']
                ) ?>"
                required
            >

            <br><br>


            <label>Correo:</label>

            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars(
                    $medicoEditar['email']
                ) ?>"
                required
            >

            <br><br>


            <label>
                Nueva contraseña:
            </label>

            <input
                type="password"
                name="password"
            >

            <small>
                Déjala vacía para conservar la actual.
            </small>


            <h4>Especialidades</h4>

            <p>
                Selecciona entre 1 y 3.
            </p>

            <?php foreach (
                $especialidades
                as $especialidad
            ): ?>

                <label>

                    <input
                        type="checkbox"
                        name="especialidades[]"
                        value="<?= htmlspecialchars(
                            $especialidad['id_especialidad']
                        ) ?>"
                        <?= in_array(
                            $especialidad['id_especialidad'],
                            $especialidadesMedico
                        )
                            ? 'checked'
                            : ''
                        ?>
                    >

                    <?= htmlspecialchars(
                        $especialidad['nombre']
                    ) ?>

                </label>

                <br>

            <?php endforeach; ?>


            <h4>Centros médicos</h4>

            <?php foreach (
                $centros
                as $centro
            ): ?>

                <label>

                    <input
                        type="checkbox"
                        name="centros[]"
                        value="<?= htmlspecialchars(
                            $centro['codigo_centro']
                        ) ?>"
                        <?= in_array(
                            $centro['codigo_centro'],
                            $centrosMedico
                        )
                            ? 'checked'
                            : ''
                        ?>
                    >

                    <?= htmlspecialchars(
                        $centro['nombre']
                    ) ?>

                    -
                    <?= htmlspecialchars(
                        $centro['comuna']
                    ) ?>

                </label>

                <br>

            <?php endforeach; ?>


            <br>


            <button
                type="submit"
                name="accion"
                value="editar"
            >
                Guardar cambios
            </button>

        </form>


        <br>

        <a href="/admin/medicos.php">
            Cancelar edición
        </a>


    <?php else: ?>


        <h3>Crear nuevo médico</h3>

        <form method="POST">

            <label>
                Nombre completo:
            </label>

            <input
                type="text"
                name="nombre_completo"
                required
            >

            <br><br>


            <label>RUT:</label>

            <input
                type="text"
                name="rut"
                placeholder="12345678-9"
                required
            >

            <br><br>


            <label>Correo:</label>

            <input
                type="email"
                name="email"
                required
            >

            <br><br>


            <label>Contraseña:</label>

            <input
                type="password"
                name="password"
                required
            >


            <h4>Especialidades</h4>

            <p>
                Selecciona entre 1 y 3.
            </p>

            <?php foreach (
                $especialidades
                as $especialidad
            ): ?>

                <label>

                    <input
                        type="checkbox"
                        name="especialidades[]"
                        value="<?= htmlspecialchars(
                            $especialidad['id_especialidad']
                        ) ?>"
                    >

                    <?= htmlspecialchars(
                        $especialidad['nombre']
                    ) ?>

                </label>

                <br>

            <?php endforeach; ?>


            <h4>Centros médicos</h4>

            <p>
                Selecciona al menos uno.
            </p>

            <?php foreach (
                $centros
                as $centro
            ): ?>

                <label>

                    <input
                        type="checkbox"
                        name="centros[]"
                        value="<?= htmlspecialchars(
                            $centro['codigo_centro']
                        ) ?>"
                    >

                    <?= htmlspecialchars(
                        $centro['nombre']
                    ) ?>

                    -
                    <?= htmlspecialchars(
                        $centro['comuna']
                    ) ?>

                </label>

                <br>

            <?php endforeach; ?>


            <br>


            <button
                type="submit"
                name="accion"
                value="crear"
            >
                Crear médico
            </button>

        </form>

    <?php endif; ?>


    <br><br>

    <a href="/admin/index.php">
        Volver al panel administrador
    </a>

</body>

</html>