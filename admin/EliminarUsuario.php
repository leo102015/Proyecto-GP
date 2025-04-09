<?php
$servername = "localhost";
$username = "root";
$password = "Previus22";
$dbname = "bd_nats";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM trabajador WHERE id_usuario = $id";

    if ($conn->query($sql) === TRUE) {
        echo "El usuario ha sido eliminado correctamente.";
    } else {
        echo "Error al eliminar el usuario: " . $conn->error;
    }
}

$conn->close();
