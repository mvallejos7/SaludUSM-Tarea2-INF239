<?php

require_once __DIR__ . '/includes/auth.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SaludUSM</title>
</head>

<body>

    <h1>SaludUSM</h1>

    <p>
        Sesión iniciada correctamente.
    </p>

    <p>
        <strong>Correo:</strong>
        <?= htmlspecialchars($_SESSION['email']) ?>
    </p>

    <p>
        <strong>Rol:</strong>
        <?= htmlspecialchars($_SESSION['rol']) ?>
    </p>

    <a href="/logout.php">
        Cerrar sesión
    </a>

</body>
</html>