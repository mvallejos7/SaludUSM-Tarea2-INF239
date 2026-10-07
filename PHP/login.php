<?php

session_start();

require_once __DIR__ . '/config/conexion.php';

if (isset($_SESSION['id_usuario'])) {
    header('Location: /index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $error = 'Debes ingresar correo y contraseña.';

    } else {

        $stmt = $pdo->prepare("
            SELECT
                id_usuario,
                email,
                password_hash,
                rol,
                activo
            FROM usuario
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        $usuario = $stmt->fetch();

        if (
            $usuario &&
            $usuario['activo'] &&
            password_verify($password, $usuario['password_hash'])
        ) {

            session_regenerate_id(true);

            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['rol'] = $usuario['rol'];

            header('Location: /index.php');
            exit;
        }

        $error = 'Correo o contraseña incorrectos.';
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

    <title>Iniciar sesión - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Iniciar sesión</h2>

    <?php if ($error !== ''): ?>

        <p>
            <?= htmlspecialchars($error) ?>
        </p>

    <?php endif; ?>


    <form method="POST">

        <div>

            <label for="email">
                Correo:
            </label>

            <input
                type="email"
                id="email"
                name="email"
                required
            >

        </div>

        <br>


        <div>

            <label for="password">
                Contraseña:
            </label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >

        </div>

        <br>

        <button type="submit">
            Iniciar sesión
        </button>

    </form>


    <br>

    <p>
        ¿No tienes una cuenta?

        <a href="/registro.php">
            Registrarse como paciente
        </a>
    </p>

</body>

</html>