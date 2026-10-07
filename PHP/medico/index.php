<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('MEDICO');

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Médico - SaludUSM</title>
</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Panel del Médico</h2>

    <p>
        Sesión iniciada como:
        <strong><?= htmlspecialchars($_SESSION['email']) ?></strong>
    </p>

    <ul>
        <li>Mi agenda</li>
        <li>Registrar atención</li>
        <li>Historial de pacientes</li>
        <li>Mi perfil</li>
    </ul>

    <a href="/logout.php">
        Cerrar sesión
    </a>

</body>

</html>