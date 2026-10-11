<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('PACIENTE');

$errores = [];
$cuentaEliminada = false;
$citasCanceladas = 0;

// ---------------------------------------------------------
// Obtener paciente y usuario conectado
// ---------------------------------------------------------

$stmtPaciente = $pdo->prepare("
    SELECT
        p.id_paciente,
        p.nombre_completo,
        u.id_usuario,
        u.email,
        u.password_hash

    FROM paciente p

    JOIN usuario u
        ON p.id_usuario = u.id_usuario

    WHERE p.id_usuario = ?

    LIMIT 1
");

$stmtPaciente->execute([
    $_SESSION['id_usuario']
]);

$paciente = $stmtPaciente->fetch();

if (!$paciente) {
    exit('No fue posible identificar al paciente.');
}

// ---------------------------------------------------------
// Procesar eliminación/desactivación
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password =
        $_POST['password'] ?? '';

    $confirmacion = trim(
        $_POST['confirmacion'] ?? ''
    );

    if ($password === '') {

        $errores[] =
            'Debes ingresar tu contraseña actual.';
    }

    if ($confirmacion !== 'ELIMINAR') {

        $errores[] =
            'Debes escribir ELIMINAR para confirmar.';
    }

    if (
        empty($errores)
        && !password_verify(
            $password,
            $paciente['password_hash']
        )
    ) {

        $errores[] =
            'La contraseña ingresada es incorrecta.';
    }

    if (empty($errores)) {

        try {

            $pdo->beginTransaction();

            // ---------------------------------------------
            // Obtener estados necesarios
            // ---------------------------------------------

            $stmtEstados = $pdo->query("
                SELECT
                    nombre,
                    id_estado_cita
                FROM estado_cita
                WHERE nombre IN (
                    'Reservada',
                    'Confirmada',
                    'Cancelada'
                )
            ");

            $estados = [];

            foreach ($stmtEstados->fetchAll() as $estado) {

                $estados[$estado['nombre']] =
                    $estado['id_estado_cita'];
            }

            if (
                !isset(
                    $estados['Reservada'],
                    $estados['Confirmada'],
                    $estados['Cancelada']
                )
            ) {

                throw new Exception(
                    'No fue posible encontrar los estados de cita necesarios.'
                );
            }

            // ---------------------------------------------
            // Cancelar citas futuras activas
            // ---------------------------------------------

            $stmtCancelar = $pdo->prepare("
                UPDATE cita

                SET id_estado_cita = ?

                WHERE id_paciente = ?
                  AND fecha_hora > CURRENT_TIMESTAMP
                  AND id_estado_cita IN (?, ?)
            ");

            $stmtCancelar->execute([
                $estados['Cancelada'],
                $paciente['id_paciente'],
                $estados['Reservada'],
                $estados['Confirmada']
            ]);

            $citasCanceladas =
                $stmtCancelar->rowCount();

            // ---------------------------------------------
            // Desactivar usuario
            // ---------------------------------------------

            $stmtDesactivar = $pdo->prepare("
                UPDATE usuario

                SET activo = 0

                WHERE id_usuario = ?
                  AND rol = 'PACIENTE'
            ");

            $stmtDesactivar->execute([
                $paciente['id_usuario']
            ]);

            if (
                $stmtDesactivar->rowCount() !== 1
            ) {

                throw new Exception(
                    'No fue posible desactivar la cuenta.'
                );
            }

            $pdo->commit();

            // ---------------------------------------------
            // Cerrar sesión
            // ---------------------------------------------

            $_SESSION = [];

            if (
                ini_get('session.use_cookies')
            ) {

                $parametros =
                    session_get_cookie_params();

                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $parametros['path'],
                    $parametros['domain'],
                    $parametros['secure'],
                    $parametros['httponly']
                );
            }

            session_destroy();

            $cuentaEliminada = true;

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errores[] =
                'No fue posible eliminar la cuenta.';
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

    <title>
        Eliminar cuenta - SaludUSM
    </title>

</head>

<body>

    <h1>SaludUSM</h1>


    <?php if ($cuentaEliminada): ?>

        <h2>
            Cuenta eliminada
        </h2>

        <p>
            Tu cuenta ha sido desactivada correctamente.
        </p>

        <p>
            Citas futuras canceladas:
            <?= htmlspecialchars($citasCanceladas) ?>
        </p>

        <p>
            Tu historial médico se mantiene registrado.
        </p>

        <a href="/login.php">
            Volver al inicio de sesión
        </a>


    <?php else: ?>


        <h2>
            Eliminar cuenta
        </h2>

        <p>
            <strong>
                Esta acción desactivará tu cuenta.
            </strong>
        </p>

        <p>
            Al continuar:
        </p>

        <ul>

            <li>
                No podrás volver a iniciar sesión.
            </li>

            <li>
                Tus citas futuras Reservadas o
                Confirmadas serán canceladas.
            </li>

            <li>
                Tus atenciones e historial médico
                se conservarán.
            </li>

        </ul>


        <?php if (!empty($errores)): ?>

            <ul>

                <?php foreach ($errores as $error): ?>

                    <li>
                        <?= htmlspecialchars($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        <?php endif; ?>


        <form
            method="POST"
            onsubmit="return confirm('Esta acción desactivará tu cuenta. ¿Deseas continuar?');"
        >

            <label for="password">
                Contraseña actual:
            </label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <br><br>


            <label for="confirmacion">
                Escribe ELIMINAR:
            </label>

            <input
                type="text"
                id="confirmacion"
                name="confirmacion"
                required
            >

            <br><br>


            <button type="submit">
                Eliminar mi cuenta
            </button>

        </form>


        <br><br>

        <a href="/paciente/index.php">
            Cancelar y volver al panel
        </a>

    <?php endif; ?>

</body>

</html>