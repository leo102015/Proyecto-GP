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
    <title>Agregar Departamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../formulario.css">
    <link rel="stylesheet" href="../estiloBarras.css">
    <style>
        div.error {
            border: 2px solid #dc3545;
            /* rojo Bootstrap */
            background-color: #ffe5e5;
        }
    </style>
</head>

<body>
    <?php require("barraAdmin.php"); ?>
    <div class="main-content">
        <?php require("headerAdmin.php"); ?>
        <div class="form-container">
            <h2>CREAR DEPARTAMENTO</h2>
            <div class="profile-icon">🏢</div>

            <div id="mensaje-error" class="alert alert-danger d-none mt-2 mb-2" role="alert"></div>

            <form id="form-area" action="CrearDepartamento.php" method="post" onsubmit="return validarFormulario()">
                <!-- Campo Nombre del Área -->
                <div class="form-group">
                    <label for="nombre_area">Nombre del Departamento:</label>
                    <input type="text" id="nombre_area" name="nombre_area" placeholder="Escribe el nombre del departamento" required>
                </div>

                <!-- Campo Nombre del Director -->
                <div class="form-group">
                    <label for="nombre_director">Nombre del Director:</label>
                    <input type="text" id="nombre_director" name="nombre_director" placeholder="Escribe el nombre del director" required>
                </div>

                <!-- Campo Teléfono del Director -->
                <div class="form-group">
                    <label for="telefono_director">Teléfono del Director:</label>
                    <input type="tel" id="telefono_director" name="telefono_director" placeholder="Escribe el teléfono del director" required>
                </div>

                <!-- Campo Email del Director -->
                <div class="form-group">
                    <label for="email_director">Email del Director:</label>
                    <input type="email" id="email_director" name="email_director" placeholder="correo@ejemplo.com" required>
                </div>

                <!-- Botón de envío -->
                <button type="submit" class="btn-submit">Agregar</button>
            </form>
        </div>
    </div>

    <script>
        // Validar formulario completo
        function validarFormulario() {
            let esValido = true;

            esValido &= validarNombreApellido('nombre_area');
            esValido &= validarNombreApellido('nombre_director');
            esValido &= validarTelefono('telefono_director');
            esValido &= validarCorreo('email_director');

            return Boolean(esValido); // Solo permite enviar si todo es válido
        }

        // Validar nombres y apellidos
        function validarNombreApellido(id) {
            const campo = document.getElementById(id);
            const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/; // Solo letras y espacios
            if (!campo.value.trim() || !regex.test(campo.value)) {
                campo.classList.add('error');
                mostrarError(`El campo "${id}" debe contener solo letras`);
                return false;
            } else {
                campo.classList.remove('error');
                return true;
            }
        }

        // Validar correo
        function validarCorreo(id) {
            const correo = document.getElementById(id);
            const dominiosPermitidos = ["hotmail.com", "gmail.com", "yahoo.com"];
            const regex = /^[a-zA-Z0-9._%+-]+@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/;

            if (!regex.test(correo.value)) {
                correo.classList.add('error');
                mostrarError("Por favor, ingresa un correo electrónico válido.");
                return false;
            }

            const dominio = correo.value.split('@')[1];
            if (!dominiosPermitidos.includes(dominio)) {
                correo.classList.add('error');
                mostrarError(`El dominio "${dominio}" no está permitido. Usa dominios como: ${dominiosPermitidos.join(", ")}.`);
                return false;
            }

            correo.classList.remove('error');
            return true;
        }

        // Validar teléfono
        function validarTelefono(id) {
            const telefono = document.getElementById(id);
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