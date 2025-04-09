<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario</title>
    <link rel="stylesheet" href="../formulario.css">
    <link rel="stylesheet" href="../estiloBarras.css">
</head>

<body>
    <?php require('barraAdmin.php'); ?>
    <div class="main-content">
        <div class="form-container">
            <?php require('headerAdmin.php'); ?>
            <h2>CREAR USUARIO</h2>
            <div class="profile-icon">👤</div>

            <form id="form-usuario" action="CrearUsuario.php" method="post" onsubmit="return validarFormulario()">
                <!-- Campo de nombres -->
                <div class="form-group">
                    <label for="nombre">Nombres:</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Escribe los nombres">
                </div>

                <!-- Campo de apellidos -->
                <div class="form-group">
                    <label for="apellidos">Apellidos:</label>
                    <input type="text" id="apellidos" name="apellidos" placeholder="Escribe los apellidos">
                </div>

                <!-- Campo de correo -->
                <div class="form-group">
                    <label for="correo">Correo electrónico:</label>
                    <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com">
                </div>

                <!-- Campo de teléfono -->
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" placeholder="Escribe el teléfono">
                </div>

                <!-- Campo de contraseña -->
                <div class="form-group">
                    <label for="contraseña">Contraseña:</label>
                    <input type="password" id="contraseña" name="contraseña" placeholder="Escribe la contraseña" oninput="mostrarAyudaContraseña()">
                    <div id="mensaje-contraseña" class="ayuda"></div>
                </div>

                <!-- Botón de envío -->
                <button type="submit" class="btn-submit">Crear</button>
            </form>
        </div>
    </div>

    <script>
        // Validar formulario completo
        function validarFormulario() {
            let esValido = true;

            esValido &= validarNombreApellido('nombre');
            esValido &= validarNombreApellido('apellidos');
            esValido &= validarCorreo();
            esValido &= validarTelefono();
            esValido &= validarContraseña();

            return Boolean(esValido); // Solo permite enviar si todo es válido
        }

        // Validar nombres y apellidos
        function validarNombreApellido(id) {
            const campo = document.getElementById(id);
            const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/; // Solo letras y espacios
            if (!campo.value.trim() || !regex.test(campo.value)) {
                campo.classList.add('error');
                alert(`El campo "${id}" debe contener solo letras y no puede estar vacío.`);
                return false;
            } else {
                campo.classList.remove('error');
                return true;
            }
        }

        // Validar correo
        function validarCorreo() {
            const correo = document.getElementById('correo');
            const dominiosPermitidos = ["hotmail.com", "gmail.com", "yahoo.com"];
            const regex = /^[a-zA-Z0-9._%+-]+@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/;

            if (!regex.test(correo.value)) {
                correo.classList.add('error');
                alert("Por favor, ingresa un correo electrónico válido.");
                return false;
            }

            const dominio = correo.value.split('@')[1];
            if (!dominiosPermitidos.includes(dominio)) {
                correo.classList.add('error');
                alert(`El dominio "${dominio}" no está permitido. Usa dominios como: ${dominiosPermitidos.join(", ")}.`);
                return false;
            }

            correo.classList.remove('error');
            return true;
        }

        // Validar teléfono
        function validarTelefono() {
            const telefono = document.getElementById('telefono');
            const regex = /^(722|729|55)\d{7}$/;

            if (!regex.test(telefono.value)) {
                telefono.classList.add('error');
                alert("El número de teléfono debe ser de 10 dígitos y comenzar con 722, 729 o 55.");
                return false;
            }

            telefono.classList.remove('error');
            return true;
        }

        // Validar contraseña
        function validarContraseña() {
            const contraseña = document.getElementById('contraseña');
            const mensajeContraseña = document.getElementById('mensaje-contraseña');
            const regex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/;

            if (!regex.test(contraseña.value)) {
                contraseña.classList.add('error');
                mensajeContraseña.classList.add('ayuda-error');
                mensajeContraseña.textContent = "La contraseña debe tener al menos 8 caracteres, incluir una mayúscula, una minúscula y un número.";
                return false;
            }

            contraseña.classList.remove('error');
            mensajeContraseña.classList.remove('ayuda-error');
            mensajeContraseña.classList.add('ayuda-segura');
            mensajeContraseña.textContent = "Contraseña válida.";
            return true;
        }
        // Mostrar ayuda dinámica para la contraseña
        function mostrarAyudaContraseña() {
            validarContraseña();
        }
    </script>
</body>

</html>