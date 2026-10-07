<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('PACIENTE');

// ---------------------------------------------------------
// Obtener paciente conectado
// ---------------------------------------------------------

$stmtPaciente = $pdo->prepare("
    SELECT id_paciente
    FROM paciente
    WHERE id_usuario = ?
    LIMIT 1
");

$stmtPaciente->execute([
    $_SESSION['id_usuario']
]);

$idPaciente = $stmtPaciente->fetchColumn();

if (!$idPaciente) {
    exit('No fue posible identificar al paciente.');
}

// ---------------------------------------------------------
// Obtener recetas del paciente
// ---------------------------------------------------------

$stmtRecetas = $pdo->prepare("
    SELECT
        r.id_receta,
        c.fecha_hora,
        m.nombre_completo AS medico,
        e.nombre AS especialidad,
        cm.nombre AS centro,
        r.medicamento,
        r.dosis,
        r.dias_tratamiento

    FROM receta r

    JOIN atencion a
        ON r.id_atencion = a.id_atencion

    JOIN cita c
        ON a.id_cita = c.id_cita

    JOIN medico m
        ON c.id_medico = m.id_medico

    JOIN especialidad e
        ON c.id_especialidad = e.id_especialidad

    JOIN centro_medico cm
        ON c.codigo_centro = cm.codigo_centro

    WHERE c.id_paciente = ?

    ORDER BY
        c.fecha_hora DESC,
        r.id_receta ASC
");

$stmtRecetas->execute([
    $idPaciente
]);

$recetas = $stmtRecetas->fetchAll();

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Mis recetas - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Mis recetas</h2>

    <p>
        Aquí puedes consultar tus medicamentos prescritos.
    </p>


    <?php if (empty($recetas)): ?>

        <p>
            No tienes recetas registradas.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>

                <tr>
                    <th>Fecha</th>
                    <th>Médico</th>
                    <th>Especialidad</th>
                    <th>Centro</th>
                    <th>Medicamento</th>
                    <th>Dosis</th>
                    <th>Días</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($recetas as $receta): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                date(
                                    'd-m-Y H:i',
                                    strtotime($receta['fecha_hora'])
                                )
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($receta['medico']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($receta['especialidad']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($receta['centro']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($receta['medicamento']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($receta['dosis']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($receta['dias_tratamiento']) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>


    <br>

    <a href="/paciente/index.php">
        Volver al panel
    </a>

    <br><br>

    <a href="/logout.php">
        Cerrar sesión
    </a>

</body>

</html>