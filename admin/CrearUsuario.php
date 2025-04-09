<?php
require("../BD.php");

//Conexión bd_nats
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida:" . $conn->connect_error);
}


//Recibir datos en formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellidos'];
    $email = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $contrasena = $_POST['contraseña'];

    $sql = "INSERT INTO trabajador(NOMBRE, TELEFONO, EMAIL, CONTRASENA, APELLIDO ) VALUES('$nombre', '$telefono', '$email', '$contrasena', '$apellido')";


    if ($conn->query($sql) === TRUE) {
        echo '<script language="javascript">alert("Usuario Creado Correctamente");</script>';

        header("refresh:1;url=AdministradorInicioI.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
