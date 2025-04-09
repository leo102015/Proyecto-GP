<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servername = "localhost";
    $username = "root";
    $password = "Previus22";
    $dbname = "bd_nats";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Error al conectar con la base de datos.']));
    }

    $nombre_area = $conn->real_escape_string($_POST['nombre_area']);

    $sql = "SELECT NOMBRE_DIRECTOR, TELEFONO_DIRECTOR, EMAIL_DIRECTOR FROM Areas WHERE NOMBRE_AREA = '$nombre_area'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'nombre' => $row['NOMBRE_DIRECTOR'],
            'telefono' => $row['TELEFONO_DIRECTOR'],
            'correo' => $row['EMAIL_DIRECTOR']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron datos para el área seleccionada.']);
    }

    $conn->close();
}
