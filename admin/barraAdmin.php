    <header>
        <nav class="navbar navbar_home navbar-expand-ig p-0 fixed-top transparent rounded-bottom-2">
            <div class="container-fluid">
                <button class="toggle-sidebar-btn" onclick="toggleSidebar()">☰</button>
                <a class="navbar-brand">
                    <img src="../src/img/logo-administracion-blanco.png" class="logo-administracion">
                </a>
            </div>
        </nav>
    </header>
    <div class="sidebar rounded-end-4">
        <!-- Menú de Inicio -->
        <a href="AdministradorInicioI.php" onclick="cargarPagina('AdministradorInicioI.php')">Inicio</a>

        <!-- Menú de Creación -->
        <a href="#" class="creaciones" onclick="toggleSubmenu('creaciones')">▼ Creación</a>
        <div class="submenu" id="submenu-creaciones">
            <a href="CrearUsuarioI.php" onclick="cargarPagina('CrearUsuarioI.php')">Crear Usuario</a>
            <a href="CrearDepartamentoI.php" onclick="cargarPagina('CrearDepartamentoI.php')">Crear Departamento</a>
        </div>

        <!-- Menú de Edición -->
        <a href="#" class="ediciones" onclick="toggleSubmenu('ediciones')">▼ Edición</a>
        <div class="submenu" id="submenu-ediciones">
            <a href="EditarUsuarioI.php" onclick="cargarPagina('EditarUsuarioI.php')">Editar Usuario</a>
            <a href="EditarAreaI.php" onclick="cargarPagina('EditarAreaI.php')">Editar Departamento</a>
        </div>
    </div>

    <script>
        function toggleSubmenu(menu) {
            const submenuCreaciones = document.getElementById('submenu-creaciones');
            const submenuEdiciones = document.getElementById('submenu-ediciones');

            // Si el submenú que quieres abrir ya está visible, lo ocultamos
            if (menu === 'creaciones') {
                submenuCreaciones.style.display = submenuCreaciones.style.display === 'block' ? 'none' : 'block';
            } else if (menu === 'ediciones') {
                submenuEdiciones.style.display = submenuEdiciones.style.display === 'block' ? 'none' : 'block';
            }
            // Los demás submenús se cierran si no han sido seleccionados
            if (menu !== 'creaciones') {
                submenuCreaciones.style.display = 'none';
            }
            if (menu !== 'ediciones') {
                submenuEdiciones.style.display = 'none';
            }
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('hidden'); // Alterna la clase 'hidden'
        }
    </script>