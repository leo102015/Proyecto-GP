<?php

// Iniciar sesión para obtener datos del usuario conectado
session_start();

// Conectar a la base de datos
require("../BD.php");

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error al conectar con la base de datos: " . $conn->connect_error);
}

// Verificar si hay un usuario autenticado
if (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];
    $nombre_usuario = $_SESSION['nombre_usuario']; // Obtener el nombre del usuario
} else {
    die("Error: Usuario no autenticado.");
}


// Revisar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los datos del formulario
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $asunto = $_POST['asunto'];
    $nombre_area = $_POST['nombre_area']; // Nombre del área seleccionada
    $fecha_registro = date('Y-m-d'); // Fecha actual
    $status = 'NO COMPLETADA'; // Status por defecto

    // Consultar el ID del área según el nombre
    $sql_area = "SELECT ID_AREA FROM Areas WHERE NOMBRE_AREA = '$nombre_area'";

    $result_area = $conn->query($sql_area);

    if ($result_area && $result_area->num_rows > 0) {
        $row_area = $result_area->fetch_assoc();
        $id_area = $row_area['ID_AREA'];
    } else {
        die("Error: El área seleccionada no existe o no coincide con los registros en la base de datos.");
    }

    // Insertar los datos en la tabla CIUDADANO si no existe
    $sql_ciudadano = "INSERT INTO CIUDADANOS (TELEFONO_CIUDADANO, NOMBRE_CIUDADANO) 
                      VALUES ('$telefono', '$nombre')
                      ON DUPLICATE KEY UPDATE NOMBRE_CIUDADANO = '$nombre'";

    if ($conn->query($sql_ciudadano) === TRUE) {
        // echo "Nuevo ciudadano registrado o actualizado con éxito.<br>";
    } else {
        //echo "Error al registrar ciudadano: " . $sql_ciudadano . "<br>" . $conn->error;
    }

    // Consultar el ID del ciudadano según el telefono
    $sql_ciudadano = "SELECT ID_CIUDADANO FROM Ciudadanos WHERE TELEFONO_CIUDADANO = '$telefono'";
    $result_ciudadano = $conn->query($sql_ciudadano);
    $row_ciudadano = $result_ciudadano->fetch_assoc();
    $id_ciudadano = $row_ciudadano['ID_CIUDADANO'];

    // Insertar los datos en la tabla PETICION
    $sql_peticion = "INSERT INTO PETICIONES (ID_CIUDADANO, ID_USUARIO, ID_AREA, FECHA_REGISTRO, STATUS, ASUNTO) 
                     VALUES ('$id_ciudadano', $id_usuario, $id_area, '$fecha_registro', '$status', '$asunto')";

    if ($conn->query($sql_peticion) === TRUE) {
        echo '<script language="javascript">alert("Petición Creada Correctamente");</script>';
    } else {
        //echo "Error al registrar petición: " . $sql_peticion . "<br>" . $conn->error;
    }
}

// Obtener las áreas desde la base de datos
$sql = "SELECT NOMBRE_AREA FROM Areas";
$result = $conn->query($sql);

$areas = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $areas[] = $row["NOMBRE_AREA"];
    }
}

// Obtener el próximo ID_PETICION de forma segura
$sql = "SELECT ID_PETICION FROM PETICIONES ORDER BY ID_PETICION DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$nextId = 1; // Valor por defecto si no hay registros
if ($row = $result->fetch_assoc()) {
    $nextId = $row['ID_PETICION'] + 1;
}

$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Petición</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../estiloBarras.css">
    <link rel="stylesheet" href="../formulario.css">
    <style>
        div.error {
            border: 2px solid #dc3545;
            /* rojo Bootstrap */
            background-color: #ffe5e5;
        }
    </style>
</head>

<body>
    <?php require("barraUsuario.php"); ?>

    <div class="main-content">
        <?php require("headerUsuario.php"); ?>
        <div class="form-container">
            <h2>CREAR PETICIÓN</h2>

            <form id="form-peticion" action="CrearPeticion.php" method="post" onsubmit="return validarFormulario()">
                <div class="form-group">
                    <label for="nombre">Nombre del ciudadano:</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Escribe el nombre completo" required>
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono del ciudadano:</label>
                    <input type="tel" id="telefono" name="telefono" placeholder="Escribe el número de teléfono" required>
                </div>

                <div class="form-group">
                    <label for="asunto">Asunto:</label>
                    <textarea id="asunto" name="asunto" placeholder="Escribe el asunto de la petición" required></textarea>
                </div>

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

                <div class="form-group">
                    <label for="id">ID Petición:</label>
                    <input type="text" id="id" name="id" value="<?php echo $nextId; ?>" readonly>
                </div>

                <button type="submit" class="btn-submit">Generar</button>
            </form>
        </div>
    </div>

    <script>
        function validarFormulario() {
            let esValido = true;

            esValido &= validarNombreApellido('nombre');
            esValido &= validarTelefono();

            return Boolean(esValido); // Solo permite enviar si todo es válido
        }

        // Validar nombres y apellidos
        function validarNombreApellido(id) {
            const campo = document.getElementById(id);
            const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/; // Solo letras y espacios
            if (!campo.value.trim() || !regex.test(campo.value)) {
                campo.classList.add('error');
                mostrarError(`El campo Nombre debe contener solo letras.`);
                return false;
            } else {
                campo.classList.remove('error');
                return true;
            }
        }

        // Validar teléfono
        function validarTelefono() {
            const telefono = document.getElementById('telefono');
            const regex = /^(722|729|55)\d{7}$/;

            if (!regex.test(telefono.value)) {
                telefono.classList.add('error');
                mostrarError("El número de teléfono debe ser de 10 dígitos y comenzar con 722, 729 o 55.");
                return false;
            }

            telefono.classList.remove('error');
            return true;
        }
        function mostrarError(mensaje) {
            const contenedor = document.getElementById('mensaje-error');
            contenedor.textContent = mensaje;
            contenedor.classList.remove('d-none');
            contenedor.classList.add('show');
            // Opcional: desplazarse al inicio
            contenedor.scrollIntoView({
                behavior: 'smooth'
            });
        }
    </script>
</body>

</html>