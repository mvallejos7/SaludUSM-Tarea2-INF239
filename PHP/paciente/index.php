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

    <li>
        <a href="/paciente/mis_citas.php">
            Mis citas
        </a>
    </li>

    <li>
        <a href="/paciente/agendar_cita.php">
            Agendar cita
        </a>
    </li>

    <li>
        <a href="/paciente/historial.php">
            Historial médico
        </a>
    </li>

    <li>
        <a href="/paciente/recetas.php">
            Mis recetas
        </a>
    </li>

    <li>
        <a href="/paciente/perfil.php">
            Mi perfil
        </a>
    </li>

    <li>
    <a href="/buscar_medicos.php">
        Buscar médicos
    </a>
</li>

<li>
    <a href="/paciente/eliminar_cuenta.php">
        Eliminar cuenta
    </a>
</li>

</ul>

    <a href="/logout.php">
        Cerrar sesión
    </a>

</body>

</html>