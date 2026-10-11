<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('ADMIN');

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Administrador - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Panel administrador</h2>

    <p>
        Sesión:
        <?= htmlspecialchars($_SESSION['email']) ?>
    </p>


    <ul>

        <li>
            <a href="/admin/centros.php">
                Centros médicos
            </a>
        </li>

        <li>
            <a href="/admin/medicos.php">
                Médicos
            </a>
        </li>

        <li>
            <a href="/admin/especialidades.php">
                Especialidades
            </a>
        </li>

        <li>
            <a href="/admin/estadisticas.php">
                Estadísticas
            </a>
        </li>

    </ul>


    <br>

    <a href="/logout.php">
        Cerrar sesión
    </a>

</body>

</html>