<?php

session_start(); // Inicia la sesión

$servername = "localhost";
$username = "root";
$password = "Previus22";
//$password = "";
$dbname = "bd_nats";

//Conexión bd_nats
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida:" . $conn->connect_error);
}

if (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario']; // Obtener el ID del usuario
    $nombre_usuario = $_SESSION['nombre_usuario']; // Obtener el nombre del usuario
} else {
    die("Error: Usuario no autenticado.");
}

if (!isset($_SESSION['login_exitoso'])) {
    header("Location: Login.html"); // Si no está autenticado, redirige al login
    exit(); // Asegúrate de que no siga ejecutándose el código después de la redirección
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../carousel.css">
    <link rel="stylesheet" href="../estiloBarras.css">

</head>

<body>
    <?php require("barraUsuario.php"); ?>

    <div class="main-content">
        <?php require("headerUsuario.php"); ?>
        <div id="content">
            <h2>Bienvenido al panel de usuario</h2>
            <p>Aquí puedes gestionar tus peticiones, generar reportes y consultar áreas.</p>
            <?php require("../carousel.php"); ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>