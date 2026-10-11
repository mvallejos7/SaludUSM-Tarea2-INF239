<?php

require_once __DIR__ . '/config/conexion.php';

// ---------------------------------------------------------
// Obtener filtros
// ---------------------------------------------------------

$nombre = trim(
    $_GET['nombre'] ?? ''
);

$idEspecialidad =
    $_GET['id_especialidad'] ?? '';

$codigoCentro = trim(
    $_GET['codigo_centro'] ?? ''
);

$comuna = trim(
    $_GET['comuna'] ?? ''
);

// ---------------------------------------------------------
// Catálogo de especialidades
// ---------------------------------------------------------

$stmtEspecialidades = $pdo->query("
    SELECT
        id_especialidad,
        nombre
    FROM especialidad
    ORDER BY nombre
");

$especialidades =
    $stmtEspecialidades->fetchAll();

// ---------------------------------------------------------
// Catálogo de centros
// ---------------------------------------------------------

$stmtCentros = $pdo->query("
    SELECT
        codigo_centro,
        nombre,
        comuna
    FROM centro_medico
    ORDER BY nombre
");

$centros =
    $stmtCentros->fetchAll();

// ---------------------------------------------------------
// Construir búsqueda dinámica
// ---------------------------------------------------------

$sql = "
    SELECT
        m.id_medico,
        m.nombre_completo,
        u.email,

        GROUP_CONCAT(
            DISTINCT e.nombre
            ORDER BY e.nombre
            SEPARATOR ', '
        ) AS especialidades,

        GROUP_CONCAT(
            DISTINCT CONCAT(
                cm.nombre,
                ' - ',
                cm.comuna
            )
            ORDER BY cm.nombre
            SEPARATOR ' | '
        ) AS centros

    FROM medico m

    JOIN usuario u
        ON m.id_usuario = u.id_usuario

    LEFT JOIN medico_especialidad me
        ON m.id_medico = me.id_medico
       AND me.activo = 1

    LEFT JOIN especialidad e
        ON me.id_especialidad =
           e.id_especialidad

    LEFT JOIN medico_centro mc
        ON m.id_medico = mc.id_medico
       AND mc.activo = 1

    LEFT JOIN centro_medico cm
        ON mc.codigo_centro =
           cm.codigo_centro

    WHERE u.activo = 1
      AND u.rol = 'MEDICO'
";

// ---------------------------------------------------------
// Filtro por nombre o especialidad
// ---------------------------------------------------------

if ($nombre !== '') {

    $sql .= "
        AND (
            m.nombre_completo LIKE ?

            OR EXISTS (
                SELECT 1

                FROM medico_especialidad me_nombre

                JOIN especialidad e_nombre
                    ON me_nombre.id_especialidad =
                       e_nombre.id_especialidad

                WHERE me_nombre.id_medico =
                      m.id_medico

                  AND me_nombre.activo = 1

                  AND e_nombre.nombre LIKE ?
            )
        )
    ";

    $parametros[] =
        '%' . $nombre . '%';

    $parametros[] =
        '%' . $nombre . '%';
}

// ---------------------------------------------------------
// Filtro por especialidad
// ---------------------------------------------------------

if (
    $idEspecialidad !== ''
    && ctype_digit(
        (string)$idEspecialidad
    )
) {

    $sql .= "
        AND EXISTS (
            SELECT 1
            FROM medico_especialidad me2
            WHERE me2.id_medico = m.id_medico
              AND me2.id_especialidad = ?
              AND me2.activo = 1
        )
    ";

    $parametros[] =
        $idEspecialidad;
}

// ---------------------------------------------------------
// Filtro por centro
// ---------------------------------------------------------

if ($codigoCentro !== '') {

    $sql .= "
        AND EXISTS (
            SELECT 1
            FROM medico_centro mc2
            WHERE mc2.id_medico = m.id_medico
              AND mc2.codigo_centro = ?
              AND mc2.activo = 1
        )
    ";

    $parametros[] =
        $codigoCentro;
}

// ---------------------------------------------------------
// Filtro por comuna
// ---------------------------------------------------------

if ($comuna !== '') {

    $sql .= "
        AND EXISTS (
            SELECT 1

            FROM medico_centro mc3

            JOIN centro_medico cm3
                ON mc3.codigo_centro =
                   cm3.codigo_centro

            WHERE mc3.id_medico = m.id_medico
              AND mc3.activo = 1
              AND cm3.comuna LIKE ?
        )
    ";

    $parametros[] =
        '%' . $comuna . '%';
}

// ---------------------------------------------------------
// Agrupar resultados
// ---------------------------------------------------------

$sql .= "
    GROUP BY
        m.id_medico,
        m.nombre_completo,
        u.email

    ORDER BY m.nombre_completo
";

$stmtMedicos = $pdo->prepare($sql);

$stmtMedicos->execute(
    $parametros
);

$medicos =
    $stmtMedicos->fetchAll();

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
        Buscar médicos - SaludUSM
    </title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>
        Buscar médicos
    </h2>

    <p>
        Puedes combinar uno o más filtros.
    </p>


    <form method="GET">

        <label for="nombre">
            Nombre o especialidad:
        </label>

        <input
            type="text"
            id="nombre"
            name="nombre"
            value="<?= htmlspecialchars($nombre) ?>"
            placeholder="Ej: Medico01 o Cardio"
        >

        <br><br>


        <label for="id_especialidad">
            Especialidad:
        </label>

        <select
            id="id_especialidad"
            name="id_especialidad"
        >

            <option value="">
                Todas
            </option>

            <?php foreach (
                $especialidades
                as $especialidad
            ): ?>

                <option
                    value="<?= htmlspecialchars(
                        $especialidad['id_especialidad']
                    ) ?>"
                    <?= (
                        (string)$idEspecialidad
                        ===
                        (string)$especialidad['id_especialidad']
                    )
                        ? 'selected'
                        : ''
                    ?>
                >

                    <?= htmlspecialchars(
                        $especialidad['nombre']
                    ) ?>

                </option>

            <?php endforeach; ?>

        </select>


        <br><br>


        <label for="codigo_centro">
            Centro médico:
        </label>

        <select
            id="codigo_centro"
            name="codigo_centro"
        >

            <option value="">
                Todos
            </option>

            <?php foreach (
                $centros
                as $centro
            ): ?>

                <option
                    value="<?= htmlspecialchars(
                        $centro['codigo_centro']
                    ) ?>"
                    <?= (
                        $codigoCentro
                        ===
                        $centro['codigo_centro']
                    )
                        ? 'selected'
                        : ''
                    ?>
                >

                    <?= htmlspecialchars(
                        $centro['nombre']
                    ) ?>

                    -
                    <?= htmlspecialchars(
                        $centro['comuna']
                    ) ?>

                </option>

            <?php endforeach; ?>

        </select>


        <br><br>


        <label for="comuna">
            Comuna:
        </label>

        <input
            type="text"
            id="comuna"
            name="comuna"
            value="<?= htmlspecialchars($comuna) ?>"
            placeholder="Ejemplo: Providencia"
        >


        <br><br>


        <button type="submit">
            Buscar
        </button>

        <a href="/buscar_medicos.php">
            Limpiar filtros
        </a>

    </form>


    <hr>


    <h3>
        Resultados
    </h3>


    <?php if (empty($medicos)): ?>

        <p>
            No se encontraron médicos con los filtros seleccionados.
        </p>

    <?php else: ?>

        <p>
            Médicos encontrados:
            <?= count($medicos) ?>
        </p>

        <table
            border="1"
            cellpadding="8"
        >

            <thead>

                <tr>
                    <th>Médico</th>
                    <th>Especialidades</th>
                    <th>Centros</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach (
                    $medicos
                    as $medico
                ): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $medico['nombre_completo']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $medico['especialidades']
                                ?? 'Sin especialidades activas'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $medico['centros']
                                ?? 'Sin centros activos'
                            ) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>


    <br><br>

    <a href="/login.php">
        Ir al inicio de sesión
    </a>

</body>

</html>