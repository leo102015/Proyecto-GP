<?php
session_start(); // Inicia la sesión
if (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario']; // Obtener el ID del usuario
    $nombre_usuario = $_SESSION['nombre_usuario']; // Obtener el nombre del usuario
} else {
    die("Error: Usuario no autenticado.");
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../carousel.css">
    <link rel="stylesheet" href="../estiloBarras.css">
    <style>
        .submenu {
            display: none;
            margin-left: 20px;
            /* Para que los submenús estén indentados */
        }
    </style>
</head>

<body>
    <?php require("barraAdmin.php"); ?>

    <div class="main-content">
        <?php require("headerAdmin.php"); ?>
        <div id="content">
            <h2>Bienvenido al panel de administración</h2>
            <p>Aquí puedes gestionar a los Usuarios y Departamentos del sistema.</p>
            <?php require("../carousel.php"); ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>