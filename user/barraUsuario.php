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
    <a href="UsuarioInicio.php" onclick="cargarPagina('UsuarioInicio.php')">Inicio</a>

    <a href="#" class="peticiones" onclick="toggleSubmenu('peticiones')">► Peticiones</a>
    <div class="submenu" id="submenu-peticiones">
        <a href="CrearPeticion.php" onclick="cargarPagina('CrearPeticion.php')">Crear petición</a>
        <a href="EstatusPeticion.php" onclick="cargarPagina('EstatusPeticion.php')">Estatus peticion</a>
        <a href="EvidenciaPeticion.php" onclick="cargarPagina('EvidenciaPeticion.php')">Evidencia de petición</a>
    </div>

    <a href="GenerarReporte.php" onclick="cargarPagina('GenerarReporte.php')">► Generar reporte</a>

    <a href="ConsultarArea.php" onclick="cargarPagina('ConsultarArea.php')">► Consultar Departamento</a>
</div>
<script>
    function toggleSubmenu(menu) {
        const submenuPeticiones = document.getElementById('submenu-peticiones');

        submenuPeticiones.style.display = 'none';

        if (menu === 'peticiones') {
            submenuPeticiones.style.display = submenuPeticiones.style.display === 'block' ? 'none' : 'block';
        }
    }

    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('hidden'); // Alterna la clase 'hidden'
    }
</script>