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

// Paginación y ordenamiento
$orden = isset($_GET['orden']) && $_GET['orden'] === 'DESC' ? 'DESC' : 'ASC';
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$por_pagina = 10;
$offset = ($pagina_actual - 1) * $por_pagina;

// Consulta para obtener el total de peticiones completadas del usuario activo
$sql_total = "SELECT COUNT(*) as total FROM PETICIONES WHERE ID_USUARIO = ? AND STATUS = 'COMPLETADA'";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("i", $id_usuario); // "i" indica que es un integer
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$fila_total = $result_total->fetch_assoc();
$total_registros = $fila_total['total'];
$total_paginas = ceil($total_registros / $por_pagina);

// Consulta principal con filtro por ID_USUARIO, paginación y ordenamiento
$sql_peticiones = "SELECT P.ID_PETICION, C.NOMBRE_CIUDADANO, C.TELEFONO_CIUDADANO, P.FECHA_REGISTRO, P.FECHA_FINALIZACION, P.ASUNTO
                   FROM PETICIONES P
                   JOIN CIUDADANOS C ON P.ID_CIUDADANO = C.ID_CIUDADANO
                   WHERE P.ID_USUARIO = ? AND P.STATUS = 'COMPLETADA'
                   ORDER BY P.FECHA_REGISTRO ASC LIMIT ? OFFSET ?";
$stmt_peticiones = $conn->prepare($sql_peticiones);
$stmt_peticiones->bind_param("iii", $id_usuario, $por_pagina, $offset);
$stmt_peticiones->execute();
$result_peticiones = $stmt_peticiones->get_result();

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
            $sql = "UPDATE PETICIONES SET FOTO = ? WHERE ID_PETICION = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $rutaArchivo, $id_peticion);
            if ($stmt->execute()) {
                echo '<script>alert("Evidencia subida correctamente.");</script>';
            } else {
                echo '<script>alert("Error al actualizar la base de datos: ' . $stmt->error . '");</script>';
            }
            $stmt->close();
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
            <form id="form-evidencia" action="EvidenciaPeticion.php" method="post" enctype="multipart/form-data"
                onsubmit="return validarFormulario()">

                <label>Selecciona una petición completada:</label>
                <table>
                    <thead>
                        <tr>
                            <th>Seleccionar</th>
                            <th>ID Petición</th>
                            <th>Nombre Ciudadano</th>
                            <th>Teléfono Ciudadano</th> <!-- Nueva columna -->
                            <th>
                                <a class="fil"
                                    href="?<?php echo http_build_query(['orden' => $orden === 'ASC' ? 'DESC' : 'ASC', 'pagina' => $pagina_actual]); ?>">
                                    Fecha Registro <?= $orden === 'ASC' ? '↑' : '↓' ?>
                                </a>
                            </th>
                            <th>Asunto</th>
                            <th>Fecha Finalización</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_peticiones->num_rows > 0): ?>
                            <?php while ($fila = $result_peticiones->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <input type="radio" name="id_peticion" value="<?php echo $fila['ID_PETICION']; ?>"
                                            required>
                                    </td>
                                    <td><?php echo htmlspecialchars($fila['ID_PETICION']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['NOMBRE_CIUDADANO']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['TELEFONO_CIUDADANO']); ?></td> <!-- Mostrar teléfono -->
                                    <td><?php echo htmlspecialchars($fila['FECHA_REGISTRO']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['ASUNTO']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['FECHA_FINALIZACION']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No hay peticiones completadas disponibles.</td> <!-- Ajustar colspan a 7 -->
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div class="paginacion">
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <a href="?<?php echo http_build_query(['orden' => $orden, 'pagina' => $i]); ?>"
                            class="<?php echo $i == $pagina_actual ? 'pagina-actual' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>

                <div class="form-group">
                    <label for="archivo">Selecciona archivo:</label>
                    <input type="file" id="archivo" name="archivo" accept="image/*" required>
                </div>
                <button type="submit" class="btn-submit">Subir Evidencia</button>
            </form>
        </div>
    </div>

    <script>
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