<?php
session_start();

require("../BD.php");

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error al conectar con la base de datos: " . $conn->connect_error);
}

if (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];
    $nombre_usuario = $_SESSION['nombre_usuario'];
} else {
    die("Error: Usuario no autenticado.");
}

// Manejo de actualización de estatus
$mensaje = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_peticion']) && isset($_POST['estatus'])) {
    $id_peticion = $_POST['id_peticion'];
    $nuevo_estatus = $_POST['estatus'];

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
            $fecha_finalizacion = ($nuevo_estatus === 'COMPLETADA') ? date('Y-m-d') : null;
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

// Paginación y filtros
$estatus_filtro = isset($_GET['estatus_filtro']) ? $_GET['estatus_filtro'] : '';
$orden = isset($_GET['orden']) && $_GET['orden'] === 'DESC' ? 'DESC' : 'ASC';
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$por_pagina = 10;
$offset = ($pagina_actual - 1) * $por_pagina;

// Consulta principal
$sql_peticiones = "SELECT DISTINCT P.ID_PETICION, C.NOMBRE_CIUDADANO, P.FECHA_REGISTRO, P.STATUS, P.FECHA_FINALIZACION, P.ASUNTO, A.NOMBRE_AREA
                    FROM PETICIONES P
                    JOIN CIUDADANOS C ON P.ID_CIUDADANO = C.ID_CIUDADANO
                    JOIN AREAS A ON P.ID_AREA = A.ID_AREA
                    WHERE P.ID_USUARIO = ?";
$params = [$id_usuario];
$types = "s";

if (!empty($estatus_filtro)) {
    $sql_peticiones .= " AND P.STATUS = ?";
    $params[] = $estatus_filtro;
    $types .= "s";
}

$sql_total = $sql_peticiones;
$sql_peticiones .= " ORDER BY P.FECHA_REGISTRO $orden LIMIT ? OFFSET ?";
$params[] = $por_pagina;
$params[] = $offset;
$types .= "ii";

$stmt_peticiones = $conn->prepare($sql_peticiones);
$stmt_peticiones->bind_param($types, ...$params);
$stmt_peticiones->execute();
$result_peticiones = $stmt_peticiones->get_result();

// Calcular total para la paginación
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param(substr($types, 0, strlen($types) - 2), ...array_slice($params, 0, -2));
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_registros = $result_total->num_rows;
$total_paginas = ceil($total_registros / $por_pagina);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
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
                        <option value="COMPLETADA" <?= $estatus_filtro === 'COMPLETADA' ? 'selected' : ''; ?>>Completada
                        </option>
                        <option value="EN PROCESO" <?= $estatus_filtro === 'EN PROCESO' ? 'selected' : ''; ?>>En proceso
                        </option>
                        <option value="NO COMPLETADA" <?= $estatus_filtro === 'NO COMPLETADA' ? 'selected' : ''; ?>>No
                            completada</option>
                    </select>
                    <input type="hidden" name="orden" value="<?= $orden ?>">
                </form>
            </div>

            <form method="POST" action="">
                <table>
                    <thead>
                        <tr>
                            <th>Seleccionar</th>
                            <th>ID Petición</th>
                            <th>Nombre Ciudadano</th>
                            <th>
                                <a class="fil"
                                    href="?<?php echo http_build_query(['estatus_filtro' => $estatus_filtro, 'orden' => $orden === 'ASC' ? 'DESC' : 'ASC']); ?>">
                                    Fecha Registro <?= $orden === 'ASC' ? '↑' : '↓' ?>
                                </a>
                            </th>
                            <th>Asunto</th>
                            <th>Estatus</th>
                            <th>Fecha Finalización</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_peticiones->num_rows > 0): ?>
                            <?php while ($fila = $result_peticiones->fetch_assoc()): ?>
                                <tr>
                                    <td><input type="radio" name="id_peticion" value="<?= $fila['ID_PETICION']; ?>" required>
                                    </td>
                                    <td><?= htmlspecialchars($fila['ID_PETICION']); ?></td>
                                    <td><?= htmlspecialchars($fila['NOMBRE_CIUDADANO']); ?></td>
                                    <td><?= htmlspecialchars($fila['ASUNTO']); ?></td>
                                    <td><?= htmlspecialchars($fila['FECHA_REGISTRO']); ?></td>
                                    <td><?= htmlspecialchars($fila['STATUS']); ?></td>
                                    <td><?= !empty($fila['FECHA_FINALIZACION']) ? htmlspecialchars($fila['FECHA_FINALIZACION']) : 'NO COMPLETADA'; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No hay peticiones con el estatus seleccionado.</td>
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

            <!-- Paginación -->
            <div class="paginacion">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <a href="?<?= http_build_query(['estatus_filtro' => $estatus_filtro, 'orden' => $orden, 'pagina' => $i]); ?>"
                        class="<?= $i == $pagina_actual ? 'pagina-actual' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>

        </div>
    </div>
</body>

</html>