<?php

require_once __DIR__ . '/config/conexion.php';

try {
    $stmtCitas = $pdo->query("SELECT COUNT(*) AS total FROM cita");
    $citas = $stmtCitas->fetch()['total'];

    $stmtAtenciones = $pdo->query("SELECT COUNT(*) AS total FROM atencion");
    $atenciones = $stmtAtenciones->fetch()['total'];

    echo "<h1>Conexión correcta</h1>";
    echo "<p>Citas: $citas</p>";
    echo "<p>Atenciones: $atenciones</p>";

} catch (PDOException $e) {
    echo "Error al consultar la base de datos.";
}