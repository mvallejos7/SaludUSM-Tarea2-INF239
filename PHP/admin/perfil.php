<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('ADMIN');

$errores = [];
$mensajeExito = '';

$idUsuario = $_SESSION['id_usuario'];


// ---------------------------------------------------------
// Obtener datos actuales
// ---------------------------------------------------------

$stmtUsuario = $pdo->prepare("
    SELECT
        rut,
        email,
        rol

    FROM usuario

    WHERE id_usuario = ?

    LIMIT 1
");

$stmtUsuario->execute([
    $idUsuario
]);

$usuario = $stmtUsuario->fetch();

if (!$usuario) {
    http_response_code(404);
    exit('No fue posible encontrar los datos del usuario.');
}


// ---------------------------------------------------------
// Guardar cambios
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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


    if (empty($errores)) {

        try {

            $pdo->beginTransaction();


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


            if ($password !== '') {

                $passwordHash =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                $stmtActualizar =
                    $pdo->prepare("
                        UPDATE usuario

                        SET rut = ?,
                            email = ?,
                            password_hash = ?

                        WHERE id_usuario = ?
                    ");

                $stmtActualizar->execute([
                    $rut,
                    $email,
                    $passwordHash,
                    $idUsuario
                ]);

            } else {

                $stmtActualizar =
                    $pdo->prepare("
                        UPDATE usuario

                        SET rut = ?,
                            email = ?

                        WHERE id_usuario = ?
                    ");

                $stmtActualizar->execute([
                    $rut,
                    $email,
                    $idUsuario
                ]);
            }


            $pdo->commit();

            $_SESSION['email'] = $email;

            $mensajeExito =
                'Perfil actualizado correctamente.';

            $usuario['rut'] = $rut;
            $usuario['email'] = $email;


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
        Mi perfil - Administrador - SaludUSM
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


    <p>
        Rol:
        <strong>
            <?= htmlspecialchars($usuario['rol']) ?>
        </strong>
    </p>


    <form method="POST">

        <label>
            RUT:
        </label>

        <input
            type="text"
            name="rut"
            value="<?= htmlspecialchars(
                $usuario['rut']
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
                $usuario['email']
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

    <a href="/admin/index.php">
        Volver al panel administrador
    </a>

</body>

</html>