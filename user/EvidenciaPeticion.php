<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "Previus22";
$dbname = "bd_nats";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error al conectar con la base de datos: " . $conn->connect_error);
}

// Asegurar autenticación del usuario
if (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];
    $nombre_usuario = $_SESSION['nombre_usuario'];
} else {
    die("Error: Usuario no autenticado.");
}

// Obtener peticiones completadas
$sql_peticiones = "SELECT ID_PETICION, NOMBRE_CIUDADANO, FECHA_REGISTRO, FECHA_FINALIZACION
                    FROM PETICIONES 
                    NATURAL JOIN CIUDADANOS
                    WHERE STATUS = 'COMPLETADA'";
$result_peticiones = $conn->query($sql_peticiones);

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_peticion = $_POST['id_peticion'];
    $archivo = $_FILES['archivo'];

    if ($archivo['error'] == UPLOAD_ERR_OK) {
        $nombreDirectorio = 'evidencias/';
        if (!file_exists($nombreDirectorio)) {
            mkdir($nombreDirectorio, 0777, true);
        }

        $ultimoArchivo = 0;
        $files = scandir($nombreDirectorio);
        foreach ($files as $file) {
            if (preg_match('/^evidencia_(\d+)\./', $file, $matches)) {
                $ultimoArchivo = max($ultimoArchivo, intval($matches[1]));
            }
        }
        $nuevoIdArchivo = $ultimoArchivo + 1;

        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = "evidencia_" . $nuevoIdArchivo . "." . $extension;
        $rutaArchivo = $nombreDirectorio . $nombreArchivo;

        if (move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
            $sql = "UPDATE PETICIONES SET FOTO = '$rutaArchivo' WHERE ID_PETICION = $id_peticion";
            if ($conn->query($sql) === TRUE) {
                echo '<script>alert("Evidencia subida correctamente.");</script>';
            } else {
                echo '<script>alert("Error al actualizar la base de datos: ' . $conn->error . '");</script>';
            }
        } else {
            echo '<script>alert("Error al mover el archivo.");</script>';
        }
    } else {
        echo '<script>alert("Error al subir el archivo. Por favor, selecciona una imagen.");</script>';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Evidencia de Petición</title>
    <link rel="stylesheet" href="../estiloBarras.css">
    <link rel="stylesheet" href="../formulario.css">
    <link rel="stylesheet" href="EvidenciaPeticion.css">
</head>

<body>
    <?php require("barraUsuario.php"); ?>

    <div class="main-content">
        <?php require("headerUsuario.php"); ?>
        <div class="form-container">
            <h2>Subir Evidencia de Petición</h2>
            <form id="form-evidencia" action="EvidenciaPeticion.php" method="post" enctype="multipart/form-data" onsubmit="return validarFormulario()">

                <label>Selecciona una petición completada:</label>
                <table>
                    <thead>
                        <tr>
                            <th>Seleccionar</th>
                            <th>ID Petición</th>
                            <th>Nombre Ciudadano</th>
                            <th>Fecha Registro</th>
                            <th>Fecha Finalización</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_peticiones->num_rows > 0): ?>
                            <?php while ($fila = $result_peticiones->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <input type="radio" name="id_peticion" value="<?php echo $fila['ID_PETICION']; ?>" required>
                                    </td>
                                    <td><?php echo htmlspecialchars($fila['ID_PETICION']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['NOMBRE_CIUDADANO']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['FECHA_REGISTRO']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['FECHA_FINALIZACION']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No hay peticiones completadas disponibles.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="form-group">
                    <label for="archivo">Selecciona archivo:</label>
                    <input type="file" id="archivo" name="archivo" accept="image/*" required>
                </div>
                <button type="submit" class="btn-submit">Subir Evidencia</button>
            </form>
        </div>
    </div>

    <script>
        function buscarPeticion() {
            const idPeticion = document.getElementById('id_peticion').value;
            if (idPeticion.trim() !== "") {
                console.log("Buscando la petición con ID:", idPeticion);
            }
        }

        // Validar formulario
        function validarFormulario() {
            const archivo = document.getElementById('archivo');
            if (!archivo.files[0]) {
                alert("Por favor, selecciona una imagen.");
                return false;
            }

            const extensionesPermitidas = ["image/jpeg", "image/png", "image/jpg"];
            if (!extensionesPermitidas.includes(archivo.files[0].type)) {
                alert("El archivo debe ser una imagen en formato JPG o PNG.");
                return false;
            }

            return true;
        }
    </script>
</body>

</html>