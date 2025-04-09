<?php
$servername = "localhost";
$username = "root";
$password = "Previus22";
//$password = "";
$dbname = "bd_nats";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error al conectar con la base de datos: " . $conn->connect_error);
}

// Obtener datos del formulario
$nombre_area = $_POST['nombre_area'];
$nombre_director = $_POST['nombre_director'];
$telefono_director = $_POST['telefono_director'];
$email_director = $_POST['email_director'];

// Preparar y ejecutar la consulta
$sql = "INSERT INTO areas (NOMBRE_AREA, NOMBRE_DIRECTOR, TELEFONO_DIRECTOR, EMAIL_DIRECTOR)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ssss", $nombre_area, $nombre_director, $telefono_director, $email_director);

    if ($stmt->execute()) {
        echo '<script language="javascript">alert("Area Agregada Correctamente");</script>';

        header("refresh:1;url=AdministradorInicioI.php");
        exit();
    } else {
        echo "<h3>Error al agregar el área:</h3> " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "<h3>Error en la preparación de la consulta:</h3> " . $conn->error;
}

// Cerrar conexión
$conn->close();
