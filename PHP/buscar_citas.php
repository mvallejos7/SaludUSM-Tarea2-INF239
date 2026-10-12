<?php

require_once __DIR__ . '/includes/auth.php';

// ---------------------------------------------------------
// Actualizar citas vencidas
// ---------------------------------------------------------

$stmtActualizar = $pdo->query("
    CALL sp_cancelar_citas_vencidas()
");

$stmtActualizar->closeCursor();


// ---------------------------------------------------------
// Usuario y rol actual
// ---------------------------------------------------------

$idUsuario = $_SESSION['id_usuario'];
$rol = $_SESSION['rol'];


// ---------------------------------------------------------
// Resolver paciente o médico según el rol
// ---------------------------------------------------------

$idPaciente = null;
$idMedico = null;

if ($rol === 'PACIENTE') {

    $stmtPaciente = $pdo->prepare("
        SELECT id_paciente
        FROM paciente
        WHERE id_usuario = ?
        LIMIT 1
    ");

    $stmtPaciente->execute([
        $idUsuario
    ]);

    $idPaciente = $stmtPaciente->fetchColumn();

    if (!$idPaciente) {
        http_response_code(403);
        exit('No fue posible identificar al paciente.');
    }
}


if ($rol === 'MEDICO') {

    $stmtMedico = $pdo->prepare("
        SELECT id_medico
        FROM medico
        WHERE id_usuario = ?
        LIMIT 1
    ");

    $stmtMedico->execute([
        $idUsuario
    ]);

    $idMedico = $stmtMedico->fetchColumn();

    if (!$idMedico) {
        http_response_code(403);
        exit('No fue posible identificar al médico.');
    }
}


// ---------------------------------------------------------
// Filtros recibidos
// ---------------------------------------------------------

$fechaDesde =
    trim($_GET['fecha_desde'] ?? '');

$fechaHasta =
    trim($_GET['fecha_hasta'] ?? '');

$codigoCentro =
    trim($_GET['codigo_centro'] ?? '');

$region =
    trim($_GET['region'] ?? '');

$idEspecialidad =
    trim($_GET['id_especialidad'] ?? '');

$idMedicoFiltro =
    trim($_GET['id_medico'] ?? '');

$idEstado =
    trim($_GET['id_estado_cita'] ?? '');

$prevision =
    trim($_GET['prevision'] ?? '');


// ---------------------------------------------------------
// Catálogos para los filtros
// ---------------------------------------------------------

$centros = $pdo->query("
    SELECT
        codigo_centro,
        nombre
    FROM centro_medico
    ORDER BY nombre
")->fetchAll();


$regiones = $pdo->query("
    SELECT DISTINCT region
    FROM centro_medico
    ORDER BY region
")->fetchAll(PDO::FETCH_COLUMN);


$especialidades = $pdo->query("
    SELECT
        id_especialidad,
        nombre
    FROM especialidad
    ORDER BY nombre
")->fetchAll();


$medicos = $pdo->query("
    SELECT
        m.id_medico,
        m.nombre_completo
    FROM medico m
    JOIN usuario u
        ON m.id_usuario = u.id_usuario
    WHERE u.activo = 1
    ORDER BY m.nombre_completo
")->fetchAll();


$estados = $pdo->query("
    SELECT
        id_estado_cita,
        nombre
    FROM estado_cita
    ORDER BY id_estado_cita
")->fetchAll();


$previsiones = $pdo->query("
    SELECT nombre
    FROM prevision
    ORDER BY nombre
")->fetchAll(PDO::FETCH_COLUMN);


// ---------------------------------------------------------
// Construcción dinámica de la consulta
// ---------------------------------------------------------

$sql = "
    SELECT
        id_cita,
        fecha_hora,
        paciente,
        prevision,
        medico,
        especialidad,
        centro,
        region,
        estado

    FROM vw_citas_detalle

    WHERE 1 = 1
";

$parametros = [];


// ---------------------------------------------------------
// Restricción según rol
// ---------------------------------------------------------

if ($rol === 'PACIENTE') {

    $sql .= "
        AND id_paciente = ?
    ";

    $parametros[] = $idPaciente;
}


if ($rol === 'MEDICO') {

    $sql .= "
        AND id_medico = ?
    ";

    $parametros[] = $idMedico;
}


// ---------------------------------------------------------
// Filtro rango de fechas
// ---------------------------------------------------------

if ($fechaDesde !== '') {

    $sql .= "
        AND DATE(fecha_hora) >= ?
    ";

    $parametros[] = $fechaDesde;
}


if ($fechaHasta !== '') {

    $sql .= "
        AND DATE(fecha_hora) <= ?
    ";

    $parametros[] = $fechaHasta;
}


// ---------------------------------------------------------
// Filtro centro
// ---------------------------------------------------------

if ($codigoCentro !== '') {

    $sql .= "
        AND codigo_centro = ?
    ";

    $parametros[] = $codigoCentro;
}


// ---------------------------------------------------------
// Filtro región
// ---------------------------------------------------------

if ($region !== '') {

    $sql .= "
        AND region = ?
    ";

    $parametros[] = $region;
}


// ---------------------------------------------------------
// Filtro especialidad
// ---------------------------------------------------------

if ($idEspecialidad !== '') {

    $sql .= "
        AND id_especialidad = ?
    ";

    $parametros[] = $idEspecialidad;
}


// ---------------------------------------------------------
// Filtro médico
// ---------------------------------------------------------

if ($idMedicoFiltro !== '') {

    $sql .= "
        AND id_medico = ?
    ";

    $parametros[] = $idMedicoFiltro;
}


// ---------------------------------------------------------
// Filtro estado
// ---------------------------------------------------------

if ($idEstado !== '') {

    $sql .= "
        AND id_estado_cita = ?
    ";

    $parametros[] = $idEstado;
}


// ---------------------------------------------------------
// Filtro previsión
// ---------------------------------------------------------

if ($prevision !== '') {

    $sql .= "
        AND prevision = ?
    ";

    $parametros[] = $prevision;
}


$sql .= "
    ORDER BY fecha_hora DESC
";


$stmtCitas = $pdo->prepare($sql);

$stmtCitas->execute(
    $parametros
);

$citas = $stmtCitas->fetchAll();

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
        Búsqueda avanzada de citas - SaludUSM
    </title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>
        Búsqueda avanzada de citas
    </h2>


    <form method="GET">

        <label>
            Fecha desde:
        </label>

        <input
            type="date"
            name="fecha_desde"
            value="<?= htmlspecialchars($fechaDesde) ?>"
        >

        <br><br>


        <label>
            Fecha hasta:
        </label>

        <input
            type="date"
            name="fecha_hasta"
            value="<?= htmlspecialchars($fechaHasta) ?>"
        >

        <br><br>


        <label>
            Centro médico:
        </label>

        <select name="codigo_centro">

            <option value="">
                Todos
            </option>

            <?php foreach ($centros as $centro): ?>

                <option
                    value="<?= htmlspecialchars(
                        $centro['codigo_centro']
                    ) ?>"
                    <?= $codigoCentro ===
                        $centro['codigo_centro']
                            ? 'selected'
                            : ''
                    ?>
                >
                    <?= htmlspecialchars(
                        $centro['nombre']
                    ) ?>
                </option>

            <?php endforeach; ?>

        </select>

        <br><br>


        <label>
            Región:
        </label>

        <select name="region">

            <option value="">
                Todas
            </option>

            <?php foreach ($regiones as $regionOpcion): ?>

                <option
                    value="<?= htmlspecialchars(
                        $regionOpcion
                    ) ?>"
                    <?= $region === $regionOpcion
                            ? 'selected'
                            : ''
                    ?>
                >
                    <?= htmlspecialchars(
                        $regionOpcion
                    ) ?>
                </option>

            <?php endforeach; ?>

        </select>

        <br><br>


        <label>
            Especialidad:
        </label>

        <select name="id_especialidad">

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
                    <?= (string)$idEspecialidad ===
                        (string)$especialidad['id_especialidad']
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


        <label>
            Médico:
        </label>

        <select name="id_medico">

            <option value="">
                Todos
            </option>

            <?php foreach ($medicos as $medico): ?>

                <option
                    value="<?= htmlspecialchars(
                        $medico['id_medico']
                    ) ?>"
                    <?= (string)$idMedicoFiltro ===
                        (string)$medico['id_medico']
                            ? 'selected'
                            : ''
                    ?>
                >
                    <?= htmlspecialchars(
                        $medico['nombre_completo']
                    ) ?>
                </option>

            <?php endforeach; ?>

        </select>

        <br><br>


        <label>
            Estado:
        </label>

        <select name="id_estado_cita">

            <option value="">
                Todos
            </option>

            <?php foreach ($estados as $estado): ?>

                <option
                    value="<?= htmlspecialchars(
                        $estado['id_estado_cita']
                    ) ?>"
                    <?= (string)$idEstado ===
                        (string)$estado['id_estado_cita']
                            ? 'selected'
                            : ''
                    ?>
                >
                    <?= htmlspecialchars(
                        $estado['nombre']
                    ) ?>
                </option>

            <?php endforeach; ?>

        </select>

        <br><br>


        <label>
            Previsión:
        </label>

        <select name="prevision">

            <option value="">
                Todas
            </option>

            <?php foreach (
                $previsiones
                as $previsionOpcion
            ): ?>

                <option
                    value="<?= htmlspecialchars(
                        $previsionOpcion
                    ) ?>"
                    <?= $prevision === $previsionOpcion
                            ? 'selected'
                            : ''
                    ?>
                >
                    <?= htmlspecialchars(
                        $previsionOpcion
                    ) ?>
                </option>

            <?php endforeach; ?>

        </select>

        <br><br>


        <button type="submit">
            Aplicar filtros
        </button>


        <a href="/buscar_citas.php">
            Limpiar filtros
        </a>

    </form>


    <hr>


    <h3>
        Resultados
    </h3>


    <p>
        Total encontrado:
        <strong><?= count($citas) ?></strong>
    </p>


    <?php if (empty($citas)): ?>

        <p>
            No se encontraron citas con los filtros seleccionados.
        </p>

    <?php else: ?>

        <table
            border="1"
            cellpadding="8"
        >

            <thead>

                <tr>
                    <th>Fecha y hora</th>
                    <th>Paciente</th>
                    <th>Previsión</th>
                    <th>Médico</th>
                    <th>Especialidad</th>
                    <th>Centro</th>
                    <th>Región</th>
                    <th>Estado</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($citas as $cita): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $cita['fecha_hora']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $cita['paciente']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $cita['prevision']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $cita['medico']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $cita['especialidad']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $cita['centro']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $cita['region']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $cita['estado']
                            ) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>


    <br><br>


    <?php if ($rol === 'PACIENTE'): ?>

        <a href="/paciente/index.php">
            Volver al panel paciente
        </a>

    <?php elseif ($rol === 'MEDICO'): ?>

        <a href="/medico/index.php">
            Volver al panel médico
        </a>

    <?php elseif ($rol === 'ADMIN'): ?>

        <a href="/admin/index.php">
            Volver al panel administrador
        </a>

    <?php endif; ?>


</body>

</html>