<?php
// Conectar a la base de datos
require("../BD.php");

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el ID del usuario desde la URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener los datos actuales del usuario
$sql = "SELECT nombre, apellido, email, telefono, contrasena FROM trabajador WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Si no hay usuario, redirige
if (!$usuario) {
    echo "Usuario no encontrado.";
    exit;
}

// Actualizar usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $contraseña = trim($_POST['contraseña']);

    $errores = [];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El email no tiene un formato válido.";
    }

    if (!preg_match('/^[0-9]{10}$/', $telefono)) {
        $errores[] = "El teléfono debe contener 10 dígitos.";
    }

    if (!empty($contraseña)) {
        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $contraseña)) {
            $errores[] = "La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números y símbolos.";
        }
    }

    $sqlDuplicados = "SELECT id_usuario FROM trabajador WHERE (email = ? OR telefono = ?) AND id_usuario != ?";
    $stmtDuplicados = $conn->prepare($sqlDuplicados);
    $stmtDuplicados->bind_param("ssi", $email, $telefono, $id);
    $stmtDuplicados->execute();
    $duplicados = $stmtDuplicados->get_result();

    if ($duplicados->num_rows > 0) {
        $errores[] = "El email o teléfono ya están en uso por otro usuario.";
    }

    // Si no hay errores, actualizar
    if (empty($errores)) {
        $sqlUpdate = "UPDATE trabajador 
                      SET nombre = ?, apellido = ?, email = ?, telefono = ?, contrasena = ?
                      WHERE id_usuario = ?";

        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("sssssi", $nombre, $apellido, $email, $telefono, $contraseña, $id);

        if ($stmtUpdate->execute()) {
            $mensaje = "Usuario actualizado correctamente.";
        } else {
            $errores[] = "Error al actualizar el usuario.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../estiloBarras.css">
    <link rel="stylesheet" href="estiloEditar.css">
</head>

<body>

    <?php require("barraAdmin.php"); ?>

    <div class="main-content">
        <?php require("headerAdmin.php"); ?>

        <div class="container">
            <h2>Editar Usuario</h2>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif (!empty($mensaje)): ?>
                <div class="alert alert-success"><?= $mensaje ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= $usuario['nombre'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" value="<?= $usuario['apellido'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= $usuario['email'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" value="<?= $usuario['telefono'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="contraseña" class="form-label">Nueva Contraseña (opcional)</label>
                    <input type="password" class="form-control" id="contraseña" name="contraseña">
                    <small class="form-text text-muted">Déjalo en blanco si no deseas cambiar la contraseña.</small>
                </div>

                <button type="submit" class="btn btn-guardar">Actualizar</button>
                <a href="EditarUsuarioI.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php
$conn->close();
?>