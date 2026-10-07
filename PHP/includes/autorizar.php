<?php

function exigirRol(string $rol): void
{
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== $rol) {
        http_response_code(403);

        echo '<h1>403 - Acceso denegado</h1>';
        echo '<p>No tienes permisos para acceder a esta sección.</p>';
        echo '<p><a href="/index.php">Volver al inicio</a></p>';

        exit;
    }
}