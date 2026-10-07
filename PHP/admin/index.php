<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('ADMIN');

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Administrador - SaludUSM</title>
</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Panel del Administrador</h2>

    <p>
        Sesión iniciada como:
        <strong><?= htmlspecialchars($_SESSION['email']) ?></strong>
    </p>

    <ul>
        <li>Centros médicos</li>
        <li>Médicos</li>
        <li>Especialidades</li>
        <li>Asignaciones médico-centro</li>
        <li>Estadísticas</li>
    </ul>

    <a href="/logout.php">
        Cerrar sesión
    </a>

</body>

</html>