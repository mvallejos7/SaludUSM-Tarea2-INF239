<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/autorizar.php';

exigirRol('PACIENTE');

$errores = [];
$mensaje = '';

$idUsuario = $_SESSION['id_usuario'];

// ---------------------------------------------------------
// Obtener previsiones
// ---------------------------------------------------------

$stmtPrevisiones = $pdo->query("
    SELECT id_prevision, nombre
    FROM prevision
    ORDER BY nombre
");

$previsiones = $stmtPrevisiones->fetchAll();

// ---------------------------------------------------------
// Procesar actualización
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $rut = strtoupper(trim($_POST['rut'] ?? ''));
    $email = strtolower(trim($_POST['email'] ?? ''));
    $fechaNacimiento = $_POST['fecha_nacimiento'] ?? '';
    $sexo = $_POST['sexo'] ?? '';
    $idPrevision = $_POST['id_prevision'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $comuna = trim($_POST['comuna'] ?? '');

    // Validaciones

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }

    if (!preg_match('/^[0-9]{8}-[0-9K]$/', $rut)) {
        $errores[] = 'El RUT debe tener el formato XXXXXXXX-X.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    }

    if ($fechaNacimiento === '') {
        $errores[] = 'La fecha de nacimiento es obligatoria.';
    } elseif ($fechaNacimiento > date('Y-m-d')) {
        $errores[] = 'La fecha de nacimiento no puede ser futura.';
    }

    if (!in_array($sexo, ['M', 'F', 'Otro'], true)) {
        $errores[] = 'Debes seleccionar un sexo válido.';
    }

    if (!ctype_digit((string)$idPrevision)) {
        $errores[] = 'Debes seleccionar una previsión.';
    }

    if ($comuna === '') {
        $errores[] = 'La comuna es obligatoria.';
    }

    // -----------------------------------------------------
    // Verificar RUT y correo únicos, excepto los propios
    // -----------------------------------------------------

    if (empty($errores)) {

        $stmtDuplicado = $pdo->prepare("
            SELECT id_usuario
            FROM usuario
            WHERE (rut = ? OR email = ?)
              AND id_usuario <> ?
            LIMIT 1
        ");

        $stmtDuplicado->execute([
            $rut,
            $email,
            $idUsuario
        ]);

        if ($stmtDuplicado->fetch()) {
            $errores[] = 'El RUT o correo electrónico ya pertenece a otro usuario.';
        }
    }

    // -----------------------------------------------------
    // Actualizar
    // -----------------------------------------------------

    if (empty($errores)) {

        try {

            $pdo->beginTransaction();

            $stmtUsuario = $pdo->prepare("
                UPDATE usuario
                SET rut = ?,
                    email = ?
                WHERE id_usuario = ?
            ");

            $stmtUsuario->execute([
                $rut,
                $email,
                $idUsuario
            ]);

            $stmtPaciente = $pdo->prepare("
                UPDATE paciente
                SET nombre_completo = ?,
                    fecha_nacimiento = ?,
                    sexo = ?,
                    id_prevision = ?,
                    telefono_contacto = ?,
                    comuna_residencia = ?
                WHERE id_usuario = ?
            ");

            $stmtPaciente->execute([
                $nombre,
                $fechaNacimiento,
                $sexo,
                $idPrevision,
                $telefono !== '' ? $telefono : null,
                $comuna,
                $idUsuario
            ]);

            $pdo->commit();

            $_SESSION['email'] = $email;

            $mensaje = 'Datos actualizados correctamente.';

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errores[] = 'No fue posible actualizar los datos.';
        }
    }
}

// ---------------------------------------------------------
// Obtener datos actuales del paciente
// ---------------------------------------------------------

$stmtPaciente = $pdo->prepare("
    SELECT
        u.rut,
        u.email,
        p.nombre_completo,
        p.fecha_nacimiento,
        p.sexo,
        p.id_prevision,
        p.telefono_contacto,
        p.comuna_residencia
    FROM usuario u
    JOIN paciente p
        ON u.id_usuario = p.id_usuario
    WHERE u.id_usuario = ?
    LIMIT 1
");

$stmtPaciente->execute([$idUsuario]);

$paciente = $stmtPaciente->fetch();

if (!$paciente) {
    exit('No fue posible encontrar los datos del paciente.');
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Mi perfil - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Mi perfil</h2>

    <?php if ($mensaje !== ''): ?>

        <p>
            <?= htmlspecialchars($mensaje) ?>
        </p>

    <?php endif; ?>


    <?php if (!empty($errores)): ?>

        <ul>

            <?php foreach ($errores as $error): ?>

                <li>
                    <?= htmlspecialchars($error) ?>
                </li>

            <?php endforeach; ?>

        </ul>

    <?php endif; ?>


    <form method="POST">

        <div>

            <label for="nombre">
                Nombre completo:
            </label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                value="<?= htmlspecialchars($paciente['nombre_completo']) ?>"
                required
            >

        </div>

        <br>


        <div>

            <label for="rut">
                RUT:
            </label>

            <input
                type="text"
                id="rut"
                name="rut"
                maxlength="10"
                value="<?= htmlspecialchars($paciente['rut']) ?>"
                required
            >

        </div>

        <br>


        <div>

            <label for="email">
                Correo:
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($paciente['email']) ?>"
                required
            >

        </div>

        <br>


        <div>

            <label for="fecha_nacimiento">
                Fecha de nacimiento:
            </label>

            <input
                type="date"
                id="fecha_nacimiento"
                name="fecha_nacimiento"
                value="<?= htmlspecialchars($paciente['fecha_nacimiento']) ?>"
                required
            >

        </div>

        <br>


        <div>

            <label for="sexo">
                Sexo:
            </label>

            <select
                id="sexo"
                name="sexo"
                required
            >

                <option
                    value="M"
                    <?= $paciente['sexo'] === 'M' ? 'selected' : '' ?>
                >
                    Masculino
                </option>

                <option
                    value="F"
                    <?= $paciente['sexo'] === 'F' ? 'selected' : '' ?>
                >
                    Femenino
                </option>

                <option
                    value="Otro"
                    <?= $paciente['sexo'] === 'Otro' ? 'selected' : '' ?>
                >
                    Otro
                </option>

            </select>

        </div>

        <br>


        <div>

            <label for="id_prevision">
                Previsión:
            </label>

            <select
                id="id_prevision"
                name="id_prevision"
                required
            >

                <?php foreach ($previsiones as $prevision): ?>

                    <option
                        value="<?= $prevision['id_prevision'] ?>"
                        <?= (string)$paciente['id_prevision'] === (string)$prevision['id_prevision'] ? 'selected' : '' ?>
                    >

                        <?= htmlspecialchars($prevision['nombre']) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <br>


        <div>

            <label for="telefono">
                Teléfono:
            </label>

            <input
                type="text"
                id="telefono"
                name="telefono"
                value="<?= htmlspecialchars($paciente['telefono_contacto'] ?? '') ?>"
            >

        </div>

        <br>


        <div>

            <label for="comuna">
                Comuna:
            </label>

            <input
                type="text"
                id="comuna"
                name="comuna"
                value="<?= htmlspecialchars($paciente['comuna_residencia']) ?>"
                required
            >

        </div>

        <br>


        <button type="submit">
            Guardar cambios
        </button>

    </form>

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