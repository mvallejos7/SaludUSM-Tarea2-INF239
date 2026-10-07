<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('PACIENTE');

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Paciente - SaludUSM</title>
</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Panel del Paciente</h2>

    <p>
        Sesión iniciada como:
        <strong><?= htmlspecialchars($_SESSION['email']) ?></strong>
    </p>

    <ul>
        <li>Mis citas</li>
        <li>Agendar cita</li>
        <li>Historial médico</li>
        <li>Mis recetas</li>
        <li>Mi perfil</li>
    </ul>

    <a href="/logout.php">
        Cerrar sesión
    </a>

</body>

</html>