<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('ADMIN');

// ---------------------------------------------------------
// Actualizar citas vencidas antes de calcular estadísticas
// ---------------------------------------------------------

$stmtActualizar = $pdo->query("
    CALL sp_cancelar_citas_vencidas()
");

$stmtActualizar->closeCursor();

// ---------------------------------------------------------
// Estadísticas por centro
// ---------------------------------------------------------

$stmtCentros = $pdo->query("
    SELECT
        cm.codigo_centro,
        cm.nombre,
        cm.comuna,
        cm.region,

        COUNT(c.id_cita) AS total_citas,

        SUM(
            CASE
                WHEN ec.nombre = 'Reservada'
                THEN 1
                ELSE 0
            END
        ) AS reservadas,

        SUM(
            CASE
                WHEN ec.nombre = 'Confirmada'
                THEN 1
                ELSE 0
            END
        ) AS confirmadas,

        SUM(
            CASE
                WHEN ec.nombre = 'Atendida'
                THEN 1
                ELSE 0
            END
        ) AS atendidas,

        SUM(
            CASE
                WHEN ec.nombre = 'No Asistió'
                THEN 1
                ELSE 0
            END
        ) AS no_asistio,

        SUM(
            CASE
                WHEN ec.nombre = 'Cancelada'
                THEN 1
                ELSE 0
            END
        ) AS canceladas,

        fn_porcentaje_inasistencia(
            cm.codigo_centro
        ) AS porcentaje_inasistencia

    FROM centro_medico cm

    LEFT JOIN cita c
        ON cm.codigo_centro =
           c.codigo_centro

    LEFT JOIN estado_cita ec
        ON c.id_estado_cita =
           ec.id_estado_cita

    GROUP BY
        cm.codigo_centro,
        cm.nombre,
        cm.comuna,
        cm.region

    ORDER BY cm.codigo_centro
");

$estadisticasCentros =
    $stmtCentros->fetchAll();

// ---------------------------------------------------------
// Top 5 diagnósticos más frecuentes
// ---------------------------------------------------------

$stmtTopDiagnosticos = $pdo->query("
    SELECT
        d.codigo_diagnostico,
        d.descripcion,
        COUNT(*) AS cantidad

    FROM atencion_diagnostico ad

    JOIN diagnostico d
        ON ad.codigo_diagnostico =
           d.codigo_diagnostico

    GROUP BY
        d.codigo_diagnostico,
        d.descripcion

    ORDER BY
        cantidad DESC,
        d.codigo_diagnostico ASC

    LIMIT 5
");

$topDiagnosticos =
    $stmtTopDiagnosticos->fetchAll();

// ---------------------------------------------------------
// Totales generales
// ---------------------------------------------------------

$stmtTotales = $pdo->query("
    SELECT

        (
            SELECT COUNT(*)
            FROM paciente
        ) AS pacientes,

        (
            SELECT COUNT(*)
            FROM medico
        ) AS medicos,

        (
            SELECT COUNT(*)
            FROM centro_medico
        ) AS centros,

        (
            SELECT COUNT(*)
            FROM cita
        ) AS citas,

        (
            SELECT COUNT(*)
            FROM atencion
        ) AS atenciones,

        (
            SELECT COUNT(*)
            FROM receta
        ) AS recetas
");

$totales = $stmtTotales->fetch();

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Estadísticas - SaludUSM
    </title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>
        Estadísticas administrativas
    </h2>


    <h3>Resumen general</h3>

    <table border="1" cellpadding="8">

        <thead>

            <tr>
                <th>Pacientes</th>
                <th>Médicos</th>
                <th>Centros</th>
                <th>Citas</th>
                <th>Atenciones</th>
                <th>Recetas</th>
            </tr>

        </thead>

        <tbody>

            <tr>

                <td>
                    <?= htmlspecialchars(
                        $totales['pacientes']
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $totales['medicos']
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $totales['centros']
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $totales['citas']
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $totales['atenciones']
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $totales['recetas']
                    ) ?>
                </td>

            </tr>

        </tbody>

    </table>


    <br><br>

    <hr>


    <h3>
        Estadísticas por centro médico
    </h3>


    <?php if (empty($estadisticasCentros)): ?>

        <p>
            No existen centros médicos registrados.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>

                <tr>
                    <th>Código</th>
                    <th>Centro</th>
                    <th>Comuna</th>
                    <th>Total citas</th>
                    <th>Reservadas</th>
                    <th>Confirmadas</th>
                    <th>Atendidas</th>
                    <th>No Asistió</th>
                    <th>Canceladas</th>
                    <th>% Inasistencia</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach (
                    $estadisticasCentros
                    as $centro
                ): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $centro['codigo_centro']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $centro['nombre']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $centro['comuna']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $centro['total_citas']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $centro['reservadas']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $centro['confirmadas']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $centro['atendidas']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $centro['no_asistio']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $centro['canceladas']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                number_format(
                                    (float)$centro['porcentaje_inasistencia'],
                                    2,
                                    ',',
                                    '.'
                                )
                            ) ?>%
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>


    <br><br>

    <hr>


    <h3>
        Top 5 diagnósticos más frecuentes
    </h3>


    <?php if (empty($topDiagnosticos)): ?>

        <p>
            No existen diagnósticos registrados.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>

                <tr>
                    <th>Posición</th>
                    <th>Código</th>
                    <th>Diagnóstico</th>
                    <th>Cantidad</th>
                </tr>

            </thead>

            <tbody>

                <?php
                $posicion = 1;
                ?>

                <?php foreach (
                    $topDiagnosticos
                    as $diagnostico
                ): ?>

                    <tr>

                        <td>
                            <?= $posicion ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $diagnostico['codigo_diagnostico']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $diagnostico['descripcion']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $diagnostico['cantidad']
                            ) ?>
                        </td>

                    </tr>

                    <?php
                    $posicion++;
                    ?>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>


    <br><br>

    <a href="/admin/index.php">
        Volver al panel administrador
    </a>

    <br><br>

    <a href="/logout.php">
        Cerrar sesión
    </a>

</body>

</html>