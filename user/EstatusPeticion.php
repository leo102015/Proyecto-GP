<?php
// Iniciar sesión para obtener datos del usuario conectado
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

// Asegúrate de que el usuario esté autenticado
if (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];
    $nombre_usuario = $_SESSION['nombre_usuario'];
} else {
    die("Error: Usuario no autenticado.");
}

// Manejar actualización del estatus
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_peticion']) && isset($_POST['estatus'])) {
    $id_peticion = $_POST['id_peticion'];
    $nuevo_estatus = $_POST['estatus'];

    // Consultar el estatus actual de la petición seleccionada
    $sql_check = "SELECT STATUS FROM PETICIONES WHERE ID_PETICION = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_peticion);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $fila = $result_check->fetch_assoc();
        $estatus_actual = $fila['STATUS'];

        if ($estatus_actual === 'COMPLETADA') {
            $mensaje = "No se puede actualizar el estatus porque la petición ya está COMPLETADA.";
        } else {
            // Determinar fecha de finalización solo si la petición se marca como COMPLETADA
            $fecha_finalizacion = ($nuevo_estatus === 'COMPLETADA') ? date('Y-m-d') : null;

            // **Aquí aseguramos que solo se actualiza la petición seleccionada**
            $sql_update = "UPDATE PETICIONES SET STATUS = ?, FECHA_FINALIZACION = ? WHERE ID_PETICION = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssi", $nuevo_estatus, $fecha_finalizacion, $id_peticion);

            if ($stmt_update->execute()) {
                $mensaje = "Estatus actualizado correctamente.";
            } else {
                $mensaje = "Error al actualizar: " . $stmt_update->error;
            }
            $stmt_update->close();
        }
    } else {
        $mensaje = "No se encontró la petición con el ID proporcionado.";
    }
    $stmt_check->close();
}

// Obtener el estatus seleccionado (si existe)
$estatus_filtro = isset($_GET['estatus_filtro']) ? $_GET['estatus_filtro'] : '';

// Consultar las peticiones según el estatus seleccionado
$sql_peticiones = "SELECT DISTINCT P.ID_PETICION, C.NOMBRE_CIUDADANO, P.FECHA_REGISTRO, P.STATUS, P.FECHA_FINALIZACION, P.ASUNTO, A.NOMBRE_AREA
                    FROM PETICIONES P
                    JOIN CIUDADANOS C ON P.ID_CIUDADANO = C.ID_CIUDADANO
                    JOIN AREAS A ON P.ID_AREA = A.ID_AREA        
                   WHERE P.ID_USUARIO = ?";
if (!empty($estatus_filtro)) {
    $sql_peticiones .= " AND p.STATUS = ?";
}

$stmt_peticiones = $conn->prepare($sql_peticiones);
if (!empty($estatus_filtro)) {
    $stmt_peticiones->bind_param("ss", $id_usuario, $estatus_filtro);
} else {
    $stmt_peticiones->bind_param("s", $id_usuario);
}
$stmt_peticiones->execute();
$result_peticiones = $stmt_peticiones->get_result();

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatus de Petición</title>
    <link rel="stylesheet" href="../estiloBarras.css">
    <link rel="stylesheet" href="../formulario.css">
    <link rel="stylesheet" href="EstatusPeticion.css">
</head>

<body>
    <?php require("barraUsuario.php"); ?>

    <div class="main-content">
        <?php require("headerUsuario.php"); ?>

        <div class="form-container">
            <h2>Estatus de Petición</h2>

            <?php if (!empty($mensaje)): ?>
                <p class="mensaje"><?php echo htmlspecialchars($mensaje); ?></p>
            <?php endif; ?>

            <div class="filtro-estatus">
                <form method="GET" action="">
                    <label for="estatus_filtro">Filtrar por estatus:</label>
                    <select id="estatus_filtro" name="estatus_filtro" onchange="this.form.submit()">
                        <option value="">Seleccionar un estatus</option>
                        <option value="COMPLETADA" <?php echo ($estatus_filtro === 'COMPLETADA') ? 'selected' : ''; ?>>Completada</option>
                        <option value="EN PROCESO" <?php echo ($estatus_filtro === 'EN PROCESO') ? 'selected' : ''; ?>>En proceso</option>
                        <option value="NO COMPLETADA" <?php echo ($estatus_filtro === 'NO COMPLETADA') ? 'selected' : ''; ?>>No completada</option>
                    </select>
                </form>
            </div>

            <form method="POST" action="">
                <table>
                    <thead>
                        <tr>
                            <th>Seleccionar</th>
                            <th>ID Petición</th>
                            <th>Nombre Ciudadano</th>
                            <th>Fecha Registro</th>
                            <th>Estatus</th>
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
                                    <td><?php echo htmlspecialchars($fila['STATUS']); ?></td>
                                    <td><?php echo !empty($fila['FECHA_FINALIZACION']) ? htmlspecialchars($fila['FECHA_FINALIZACION']) : 'NO COMPLETADA'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No hay peticiones con el estatus seleccionado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="seleccionar-estatus">
                    <label for="estatus">Seleccionar estatus:</label>
                    <select id="estatus" name="estatus" required>
                        <option value="">Selecciona un estatus</option>
                        <option value="COMPLETADA">Completada</option>
                        <option value="EN PROCESO">En proceso</option>
                        <option value="NO COMPLETADA">No completada</option>
                    </select>
                    <button type="submit" class="btn-submit">Actualizar Estatus</button>
                </div>
            </form>

        </div>
    </div>
</body>

</html>