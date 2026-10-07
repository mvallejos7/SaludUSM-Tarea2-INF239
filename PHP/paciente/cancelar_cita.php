<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('PACIENTE');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /paciente/mis_citas.php');
    exit;
}

$idCita = $_POST['id_cita'] ?? '';

if (!ctype_digit((string)$idCita)) {
    $_SESSION['mensaje_error'] = 'La cita seleccionada no es válida.';
    header('Location: /paciente/mis_citas.php');
    exit;
}

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
    $_SESSION['mensaje_error'] = 'No fue posible identificar al paciente.';
    header('Location: /paciente/mis_citas.php');
    exit;
}

// ---------------------------------------------------------
// Obtener ID del estado Cancelada
// ---------------------------------------------------------

$stmtEstado = $pdo->prepare("
    SELECT id_estado_cita
    FROM estado_cita
    WHERE nombre = 'Cancelada'
    LIMIT 1
");

$stmtEstado->execute();

$idCancelada = $stmtEstado->fetchColumn();

if (!$idCancelada) {
    $_SESSION['mensaje_error'] = 'No fue posible encontrar el estado Cancelada.';
    header('Location: /paciente/mis_citas.php');
    exit;
}

// ---------------------------------------------------------
// Cancelar solo si:
// - pertenece al paciente
// - está en el futuro
// - está Reservada o Confirmada
// ---------------------------------------------------------

$stmtCancelar = $pdo->prepare("
    UPDATE cita
    SET id_estado_cita = ?
    WHERE id_cita = ?
      AND id_paciente = ?
      AND fecha_hora > CURRENT_TIMESTAMP
      AND id_estado_cita IN (
            SELECT id_estado_cita
            FROM estado_cita
            WHERE nombre IN ('Reservada', 'Confirmada')
      )
");

$stmtCancelar->execute([
    $idCancelada,
    $idCita,
    $idPaciente
]);

if ($stmtCancelar->rowCount() === 1) {

    $_SESSION['mensaje_exito'] =
        'Cita cancelada correctamente.';

} else {

    $_SESSION['mensaje_error'] =
        'La cita no puede cancelarse o no te pertenece.';
}

header('Location: /paciente/mis_citas.php');
exit;