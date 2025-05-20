<?php

session_start();

require("../BD.php");

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error al conectar con la base de datos: " . $conn->connect_error);
}

if (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario']; // Obtener el ID del usuario
    $nombre_usuario = $_SESSION['nombre_usuario']; // Obtener el nombre del usuario
} else {
    die("Error: Usuario no autenticado.");
}

$sql = "SELECT NOMBRE_AREA FROM Areas";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Crear un array con las áreas
    $areas = [];
    while ($row = $result->fetch_assoc()) {
        $areas[] = $row["NOMBRE_AREA"];
    }
} else {
    $areas = []; // No hay áreas
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Área</title>
    <link rel="stylesheet" href="../estiloBarras.css">
    <link rel="stylesheet" href="ConsultarArea.css">
</head>

<body>
    <?php require("barraUsuario.php"); ?>

    <div class="main-content">
        <?php require("headerUsuario.php"); ?>
        <div class="form-container">
            <h2>Consultar Departamento</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="area">Departamento:</label>
                    <select id="area" name="nombre_area" required>
                        <option value="" disabled selected>Seleccione un Departamento</option>
                        <?php
                        foreach ($areas as $area) {
                            echo "<option value='$area'>$area</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="button" id="consultar" class="btn-submit">Consultar</button>
            </form>

            <div class="results-container">
                <div class="form-group">
                    <label for="director-name">Nombre del director:</label>
                    <input type="text" id="NOMBRE_DIRECTOR" readonly>
                </div>
                <div class="form-group">
                    <label for="director-phone">Teléfono del director:</label>
                    <input type="text" id="TELEFONO_DIRECTOR" readonly>
                </div>
                <div class="form-group">
                    <label for="director-email">Correo del director:</label>
                    <input type="text" id="EMAIL_DIRECTOR" readonly>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('consultar').addEventListener('click', function() {
            const area = document.getElementById('area').value;

            if (!area) {
                alert("Por favor selecciona un departamento.");
                return;
            }

            fetch('ObtenerDatosArea.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'nombre_area=' + encodeURIComponent(area)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('NOMBRE_DIRECTOR').value = data.nombre;
                        document.getElementById('TELEFONO_DIRECTOR').value = data.telefono;
                        document.getElementById('EMAIL_DIRECTOR').value = data.correo;
                    } else {
                        alert("No se encontraron datos para el área seleccionada.");
                    }
                })
                .catch(error => {
                    console.error("Error al obtener los datos:", error);
                    alert("Hubo un problema al consultar los datos.");
                });
        });
    </script>
</body>

</html>