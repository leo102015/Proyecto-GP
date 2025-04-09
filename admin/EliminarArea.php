<?php
require("../BD.php");

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM areas WHERE id_area = $id";

    if ($conn->query($sql) === TRUE) {
        echo "El área ha sido eliminado correctamente.";
    } else {
        echo "Error al eliminar el usuario: " . $conn->error;
    }
}

$conn->close();
