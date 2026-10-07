<?php

session_start();

require_once __DIR__ . '/config/conexion.php';

if (isset($_SESSION['id_usuario'])) {
    header('Location: /index.php');
    exit;
}

$errores = [];
$mensaje = '';

$nombre = '';
$rut = '';
$email = '';
$fecha_nacimiento = '';
$sexo = '';
$id_prevision = '';
$telefono = '';
$comuna = '';

$stmtPrevisiones = $pdo->query("
    SELECT id_prevision, nombre
    FROM prevision
    ORDER BY nombre
");

$previsiones = $stmtPrevisiones->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $rut = strtoupper(trim($_POST['rut'] ?? ''));
    $email = strtolower(trim($_POST['email'] ?? ''));
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $sexo = $_POST['sexo'] ?? '';
    $id_prevision = $_POST['id_prevision'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $comuna = trim($_POST['comuna'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirmacion = $_POST['password_confirmacion'] ?? '';

    // -----------------------------------------------------
    // Validaciones
    // -----------------------------------------------------

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }

    if (!preg_match('/^[0-9]{8}-[0-9K]$/', $rut)) {
        $errores[] = 'El RUT debe tener el formato XXXXXXXX-X.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    }

    if ($fecha_nacimiento === '') {
        $errores[] = 'La fecha de nacimiento es obligatoria.';
    } elseif ($fecha_nacimiento > date('Y-m-d')) {
        $errores[] = 'La fecha de nacimiento no puede ser futura.';
    }

    if (!in_array($sexo, ['M', 'F', 'Otro'], true)) {
        $errores[] = 'Debes seleccionar un sexo válido.';
    }

    if (!ctype_digit((string)$id_prevision)) {
        $errores[] = 'Debes seleccionar una previsión.';
    }

    if ($comuna === '') {
        $errores[] = 'La comuna es obligatoria.';
    }

    if (strlen($password) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errores[] = 'La contraseña debe contener al menos una mayúscula.';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errores[] = 'La contraseña debe contener al menos una minúscula.';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errores[] = 'La contraseña debe contener al menos un número.';
    }

    if ($password !== $password_confirmacion) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    // -----------------------------------------------------
    // Verificar RUT y correo únicos
    // -----------------------------------------------------

    if (empty($errores)) {

        $stmt = $pdo->prepare("
            SELECT id_usuario
            FROM usuario
            WHERE rut = ? OR email = ?
            LIMIT 1
        ");

        $stmt->execute([$rut, $email]);

        if ($stmt->fetch()) {
            $errores[] = 'El RUT o correo electrónico ya está registrado.';
        }
    }

    // -----------------------------------------------------
    // Registrar usuario y paciente
    // -----------------------------------------------------

    if (empty($errores)) {

        try {

            $pdo->beginTransaction();

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmtUsuario = $pdo->prepare("
                INSERT INTO usuario
                (
                    rut,
                    email,
                    password_hash,
                    rol,
                    activo
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    'PACIENTE',
                    TRUE
                )
            ");

            $stmtUsuario->execute([
                $rut,
                $email,
                $passwordHash
            ]);

            $idUsuario = $pdo->lastInsertId();

            $stmtPaciente = $pdo->prepare("
                INSERT INTO paciente
                (
                    id_usuario,
                    nombre_completo,
                    fecha_nacimiento,
                    sexo,
                    id_prevision,
                    telefono_contacto,
                    comuna_residencia
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmtPaciente->execute([
                $idUsuario,
                $nombre,
                $fecha_nacimiento,
                $sexo,
                $id_prevision,
                $telefono !== '' ? $telefono : null,
                $comuna
            ]);

            $pdo->commit();

            $mensaje = 'Registro realizado correctamente. Ya puedes iniciar sesión.';

            $nombre = '';
            $rut = '';
            $email = '';
            $fecha_nacimiento = '';
            $sexo = '';
            $id_prevision = '';
            $telefono = '';
            $comuna = '';

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errores[] = 'No fue posible completar el registro.';
        }
    }
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

    <title>Registro - SaludUSM</title>

</head>

<body>

    <h1>SaludUSM</h1>

    <h2>Registro de paciente</h2>

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
                value="<?= htmlspecialchars($nombre) ?>"
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
                placeholder="12345678-9"
                maxlength="10"
                value="<?= htmlspecialchars($rut) ?>"
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
                value="<?= htmlspecialchars($email) ?>"
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
                value="<?= htmlspecialchars($fecha_nacimiento) ?>"
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

                <option value="">
                    Seleccione
                </option>

                <option
                    value="M"
                    <?= $sexo === 'M' ? 'selected' : '' ?>
                >
                    Masculino
                </option>

                <option
                    value="F"
                    <?= $sexo === 'F' ? 'selected' : '' ?>
                >
                    Femenino
                </option>

                <option
                    value="Otro"
                    <?= $sexo === 'Otro' ? 'selected' : '' ?>
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

                <option value="">
                    Seleccione
                </option>

                <?php foreach ($previsiones as $prevision): ?>

                    <option
                        value="<?= $prevision['id_prevision'] ?>"
                        <?= (string)$id_prevision === (string)$prevision['id_prevision'] ? 'selected' : '' ?>
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
                value="<?= htmlspecialchars($telefono) ?>"
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
                value="<?= htmlspecialchars($comuna) ?>"
                required
            >

        </div>

        <br>


        <div>

            <label for="password">
                Contraseña:
            </label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >

        </div>

        <p>
            Mínimo 8 caracteres, una mayúscula,
            una minúscula y un número.
        </p>


        <div>

            <label for="password_confirmacion">
                Confirmar contraseña:
            </label>

            <input
                type="password"
                id="password_confirmacion"
                name="password_confirmacion"
                required
            >

        </div>

        <br>


        <button type="submit">
            Registrarse
        </button>

    </form>

    <br>

    <a href="/login.php">
        Ya tengo una cuenta
    </a>

</body>

</html>