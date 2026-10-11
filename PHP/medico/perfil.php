<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('MEDICO');

$errores = [];
$mensajeExito = '';

$idUsuario = $_SESSION['id_usuario'];


// ---------------------------------------------------------
// Obtener datos actuales del médico
// ---------------------------------------------------------

$stmtMedico = $pdo->prepare("
    SELECT
        m.id_medico,
        m.nombre_completo,
        u.rut,
        u.email

    FROM medico m

    JOIN usuario u
        ON m.id_usuario = u.id_usuario

    WHERE u.id_usuario = ?

    LIMIT 1
");

$stmtMedico->execute([
    $idUsuario
]);

$medico = $stmtMedico->fetch();

if (!$medico) {
    http_response_code(404);
    exit('No fue posible encontrar los datos del médico.');
}


// ---------------------------------------------------------
// Guardar cambios
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre =
        trim($_POST['nombre_completo'] ?? '');

    $rut =
        strtoupper(
            trim($_POST['rut'] ?? '')
        );

    $email =
        strtolower(
            trim($_POST['email'] ?? '')
        );

    $password =
        $_POST['password'] ?? '';


    // -----------------------------------------------------
    // Validaciones
    // -----------------------------------------------------

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


    if ($password !== '') {

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


    // -----------------------------------------------------
    // Guardar si no existen errores
    // -----------------------------------------------------

    if (empty($errores)) {

        try {

            $pdo->beginTransaction();


            // -------------------------------------------------
            // RUT y correo deben seguir siendo únicos
            // -------------------------------------------------

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


            // -------------------------------------------------
            // Actualizar usuario
            // -------------------------------------------------

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


            // -------------------------------------------------
            // Actualizar nombre del médico
            // -------------------------------------------------

            $stmtActualizarMedico =
                $pdo->prepare("
                    UPDATE medico

                    SET nombre_completo = ?

                    WHERE id_medico = ?
                ");

            $stmtActualizarMedico->execute([
                $nombre,
                $medico['id_medico']
            ]);


            $pdo->commit();


            // Actualizar correo de la sesión
            $_SESSION['email'] = $email;

            $mensajeExito =
                'Perfil actualizado correctamente.';


            // Datos actualizados para volver a mostrarlos
            $medico['nombre_completo'] = $nombre;
            $medico['rut'] = $rut;
            $medico['email'] = $email;


        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errores[] =
                $e->getMessage();
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
        Mi perfil - Médico - SaludUSM
    </title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Mi perfil</h2>


    <?php if ($mensajeExito !== ''): ?>

        <p>
            <?= htmlspecialchars($mensajeExito) ?>
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


    <form method="POST">

        <label>
            Nombre completo:
        </label>

        <input
            type="text"
            name="nombre_completo"
            value="<?= htmlspecialchars(
                $medico['nombre_completo']
            ) ?>"
            required
        >

        <br><br>


        <label>
            RUT:
        </label>

        <input
            type="text"
            name="rut"
            value="<?= htmlspecialchars(
                $medico['rut']
            ) ?>"
            required
        >

        <br><br>


        <label>
            Correo:
        </label>

        <input
            type="email"
            name="email"
            value="<?= htmlspecialchars(
                $medico['email']
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
            Déjala vacía para mantener la contraseña actual.
        </small>

        <br><br>


        <button type="submit">
            Guardar cambios
        </button>

    </form>


    <br>

    <a href="/medico/index.php">
        Volver al panel médico
    </a>

</body>

</html>