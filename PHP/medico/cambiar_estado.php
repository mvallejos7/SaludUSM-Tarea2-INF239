<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('MEDICO');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /medico/agenda.php');
    exit;
}

// ---------------------------------------------------------
// Datos recibidos
// ---------------------------------------------------------

$idCita = $_POST['id_cita'] ?? '';
$nuevoEstado = $_POST['nuevo_estado'] ?? '';
$fecha = $_POST['fecha'] ?? date('Y-m-d');

if (!ctype_digit((string)$idCita)) {

    $_SESSION['mensaje_error'] =
        'La cita seleccionada no es válida.';

    header(
        'Location: /medico/agenda.php?fecha='
        . urlencode($fecha)
    );

    exit;
}

// ---------------------------------------------------------
// Estados permitidos para el médico
// ---------------------------------------------------------

$estadosPermitidos = [
    'Confirmada',
    'Atendida',
    'No Asistió'
];

if (
    !in_array(
        $nuevoEstado,
        $estadosPermitidos,
        true
    )
) {

    $_SESSION['mensaje_error'] =
        'El estado seleccionado no está permitido.';

    header(
        'Location: /medico/agenda.php?fecha='
        . urlencode($fecha)
    );

    exit;
}

// ---------------------------------------------------------
// Obtener médico conectado
// ---------------------------------------------------------

$stmtMedico = $pdo->prepare("
    SELECT id_medico
    FROM medico
    WHERE id_usuario = ?
    LIMIT 1
");

$stmtMedico->execute([
    $_SESSION['id_usuario']
]);

$idMedico = $stmtMedico->fetchColumn();

if (!$idMedico) {

    $_SESSION['mensaje_error'] =
        'No fue posible identificar al médico.';

    header(
        'Location: /medico/agenda.php?fecha='
        . urlencode($fecha)
    );

    exit;
}

// ---------------------------------------------------------
// Comprobar que la cita pertenece al médico
// y que todavía puede cambiar de estado
// ---------------------------------------------------------

$stmtCita = $pdo->prepare("
    SELECT
        c.id_cita,
        ec.nombre AS estado_actual

    FROM cita c

    JOIN estado_cita ec
        ON c.id_estado_cita = ec.id_estado_cita

    WHERE c.id_cita = ?
      AND c.id_medico = ?

    LIMIT 1
");

$stmtCita->execute([
    $idCita,
    $idMedico
]);

$cita = $stmtCita->fetch();

if (!$cita) {

    $_SESSION['mensaje_error'] =
        'La cita no existe o no pertenece al médico conectado.';

    header(
        'Location: /medico/agenda.php?fecha='
        . urlencode($fecha)
    );

    exit;
}

// ---------------------------------------------------------
// No modificar estados finales
// ---------------------------------------------------------

if (
    in_array(
        $cita['estado_actual'],
        [
            'Atendida',
            'No Asistió',
            'Cancelada'
        ],
        true
    )
) {

    $_SESSION['mensaje_error'] =
        'Esta cita ya se encuentra en un estado final.';

    header(
        'Location: /medico/agenda.php?fecha='
        . urlencode($fecha)
    );

    exit;
}

// ---------------------------------------------------------
// Una cita Confirmada no puede volver a Reservada.
// Reservada puede pasar a Confirmada, Atendida o No Asistió.
// Confirmada puede pasar a Atendida o No Asistió.
// ---------------------------------------------------------

if (
    $cita['estado_actual'] === 'Confirmada'
    && $nuevoEstado === 'Confirmada'
) {

    $_SESSION['mensaje_error'] =
        'La cita ya está Confirmada.';

    header(
        'Location: /medico/agenda.php?fecha='
        . urlencode($fecha)
    );

    exit;
}

// ---------------------------------------------------------
// Obtener ID del nuevo estado
// ---------------------------------------------------------

$stmtEstado = $pdo->prepare("
    SELECT id_estado_cita
    FROM estado_cita
    WHERE nombre = ?
    LIMIT 1
");

$stmtEstado->execute([
    $nuevoEstado
]);

$idNuevoEstado = $stmtEstado->fetchColumn();

if (!$idNuevoEstado) {

    $_SESSION['mensaje_error'] =
        'No fue posible encontrar el estado solicitado.';

    header(
        'Location: /medico/agenda.php?fecha='
        . urlencode($fecha)
    );

    exit;
}

// ---------------------------------------------------------
// Actualizar cita
// ---------------------------------------------------------

$stmtActualizar = $pdo->prepare("
    UPDATE cita
    SET id_estado_cita = ?
    WHERE id_cita = ?
      AND id_medico = ?
");

$stmtActualizar->execute([
    $idNuevoEstado,
    $idCita,
    $idMedico
]);

if ($stmtActualizar->rowCount() === 1) {

    $_SESSION['mensaje_exito'] =
        'Estado de la cita actualizado correctamente.';

} else {

    $_SESSION['mensaje_error'] =
        'No fue posible actualizar la cita.';
}

header(
    'Location: /medico/agenda.php?fecha='
    . urlencode($fecha)
);

exit;