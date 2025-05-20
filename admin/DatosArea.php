<?php
// Conectar a la base de datos
require("../BD.php");

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el ID del area desde la URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener los datos actuales del area
$sql = "SELECT nombre_area, nombre_director, telefono_director, email_director FROM areas WHERE id_area = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$area = $result->fetch_assoc();

// Si no hay area, redirige
if (!$area) {
    echo "Area no encontrado.";
    exit;
}

// Actualizar usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre_area']);
    $nombreD = trim($_POST['nombre_director']);
    $telefono = trim($_POST['telefono_director']);
    $email = trim($_POST['email_director']);

    $errores = [];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El email no tiene un formato válido.";
    }

    if (!preg_match('/^[0-9]{10}$/', $telefono)) {
        $errores[] = "El teléfono debe contener 10 dígitos.";
    }

    $sqlDuplicados = "SELECT id_area FROM areas WHERE (email_director = ? OR telefono_director = ?) AND id_area != ?";
    $stmtDuplicados = $conn->prepare($sqlDuplicados);
    $stmtDuplicados->bind_param("ssi", $email, $telefono, $id);
    $stmtDuplicados->execute();
    $duplicados = $stmtDuplicados->get_result();

    if ($duplicados->num_rows > 0) {
        $errores[] = "El email o teléfono ya están en uso por otro usuario.";
    }

    // Si no hay errores, actualizar
    if (empty($errores)) {
        $sqlUpdate = "UPDATE areas 
                      SET nombre_area = ?, nombre_director = ?, telefono_director = ?, email_director = ?
                      WHERE id_area = ?";

        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ssssi", $nombre, $nombreD, $telefono, $email, $id);

        if ($stmtUpdate->execute()) {
            $mensaje = "Area actualizada correctamente.";
        } else {
            $errores[] = "Error al actualizar el area.";
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
            <h2>Editar Departamento</h2>

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
                    <label for="nombre_area" class="form-label">Nombre del Departamento</label>
                    <input type="text" class="form-control" id="nombre_area" name="nombre_area" value="<?= $area['nombre_area'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="nombre_director" class="form-label">Nombre del Director</label>
                    <input type="text" class="form-control" id="nombre_director" name="nombre_director" value="<?= $area['nombre_director'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="telefono_director" class="form-label">Teléfono del Director</label>
                    <input type="tel" class="form-control" id="telefono_director" name="telefono_director" value="<?= $area['telefono_director'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email_director" class="form-label">Email del Director</label>
                    <input type="email" class="form-control" id="email_director" name="email_director" value="<?= $area['email_director'] ?>" required>
                </div>

                <button type="submit" class="btn btn-guardar">Actualizar</button>
                <a href="EditarAreaI.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php
$conn->close();
?>