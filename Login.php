<?php
session_start();

header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "Previus22";
$dbname = "bd_nats";

// Conexión
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM trabajador WHERE EMAIL = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['CONTRASENA'] === $password) {
                $_SESSION['nombre_usuario'] = $user['NOMBRE'];
                $_SESSION['id_usuario'] = $user['ID_USUARIO'];

                $redirect = ($user['ADMIN'] == 1) ? './admin/AdministradorInicioI.php' : './user/UsuarioInicio.php';

                echo json_encode(['success' => true, 'redirect' => $redirect]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Correo no encontrado.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Completa todos los campos.']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
$conn->close();
