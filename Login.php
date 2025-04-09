<?php

session_start(); // Iniciar la sesión

$servername = "localhost";
$username = "root";
$password = "Previus22";
//$password = "";
$dbname = "bd_nats";

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}


// Recibir datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validar que los datos no estén vacíos
    if (!empty($email) && !empty($password)) {
        // Usar consultas preparadas para evitar SQL Injection
        $stmt = $conn->prepare("SELECT * FROM trabajador WHERE EMAIL = ? LIMIT 1");
        $stmt->bind_param("s", $email); // 's' indica que es un string
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Verificar la contraseña
            if ($user['CONTRASENA'] === $password) {
                // Después de verificar que el usuario y la contraseña son correctos
                $_SESSION['nombre_usuario'] = $user['NOMBRE']; // Obtener el nombre del usuario
                $_SESSION['id_usuario'] = $user['ID_USUARIO']; // Obtener el ID del usuario

                if ($user['ADMIN'] == 1) {
                    $_SESSION['login_exitoso'] = "¡Has ingresado correctamente como administrador!";
                    header("Location: ./admin/AdministradorInicioI.php"); // Redirige al administrador
                } else {
                    $_SESSION['login_exitoso'] = "¡Has ingresado correctamente!";
                    header("Location: ./user/UsuarioInicio.php"); // Redirige al trabajador
                }
                exit(); // Asegúrate de que no haya más código después de header()
            } else {
                $_SESSION['error_login'] = "Contraseña incorrecta";
            }
        } else {
            $_SESSION['error_login'] = "Correo no encontrado";
        }
        $stmt->close();
    } else {
        $_SESSION['error_login'] = "Por favor completa todos los campos";
    }
    header("Location: Login.html"); // Redirige al login si hubo algún error
    exit();
}

$conn->close();
