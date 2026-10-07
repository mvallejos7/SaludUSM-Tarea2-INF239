<?php

require_once __DIR__ . '/includes/auth.php';

switch ($_SESSION['rol']) {

    case 'PACIENTE':
        header('Location: /paciente/index.php');
        exit;

    case 'MEDICO':
        header('Location: /medico/index.php');
        exit;

    case 'ADMIN':
        header('Location: /admin/index.php');
        exit;

    default:
        http_response_code(403);
        exit('Rol de usuario no válido.');
}