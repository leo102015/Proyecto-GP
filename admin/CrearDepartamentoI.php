<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Área</title>
    <link rel="stylesheet" href="../formulario.css">
    <link rel="stylesheet" href="../estiloBarras.css">
</head>

<body>
    <?php require("barraAdmin.php"); ?>
    <div class="main-content">
        <?php require("headerAdmin.php"); ?>
        <div class="form-container">
            <h2>CREAR DEPARTAMENTO</h2>
            <div class="profile-icon">🏢</div>

            <form id="form-area" action="CrearDepartamento.php" method="post" onsubmit="return validarFormulario()">
                <!-- Campo Nombre del Área -->
                <div class="form-group">
                    <label for="nombre_area">Nombre del Área:</label>
                    <input type="text" id="nombre_area" name="nombre_area" placeholder="Escribe el nombre del área" required>
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
                alert(`El campo "${id}" debe contener solo letras y no puede estar vacío.`);
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
        function validarTelefono(id) {
            const telefono = document.getElementById(id);
            const regex = /^(722|729|55)\d{7}$/;

            if (!regex.test(telefono.value)) {
                telefono.classList.add('error');
                alert("El número de teléfono debe ser de 10 dígitos y comenzar con 722, 729 o 55.");
                return false;
            }

            telefono.classList.remove('error');
            return true;
        }
    </script>
</body>

</html>