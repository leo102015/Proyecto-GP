<?php
require("../BD.php");

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$registrosPorPagina = 10;  // Cantidad de usuarios por página
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

$totalSql = "SELECT COUNT(*) AS total FROM areas";
$totalResult = $conn->query($totalSql);
$totalUsuarios = $totalResult->fetch_assoc()['total'];
$totalPaginas = ceil($totalUsuarios / $registrosPorPagina);

$sql = "SELECT id_area, nombre_area, nombre_director, telefono_director, email_director FROM areas";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Areas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../estiloBarras.css">
    <link rel="stylesheet" href="estiloCrud.css">
</head>

<body>

    <?php require("barraAdmin.php"); ?>

    <div class="main-content">
        <?php require("headerAdmin.php"); ?>

        <div class="table-container">
            <h2 class="mb-4">Gestión de Áreas</h2>

            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre de Área</th>
                        <th>Nombre de Director</th>
                        <th>Teléfono del Director</th>
                        <th>Email del Director</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr id="area-<?= $row['id_area'] ?>">
                                <td><?= $row['id_area'] ?></td>
                                <td><?= $row['nombre_area'] ?></td>
                                <td><?= $row['nombre_director'] ?></td>
                                <td><?= $row['telefono_director'] ?></td>
                                <td><?= $row['email_director'] ?></td>
                                <td>
                                    <a href="DatosArea.php?id=<?= $row['id_area'] ?>" class="btn btn-editar">Editar</a>
                                    <button class="btn btn-eliminar"
                                        onclick="confirmarEliminacion(<?= $row['id_area'] ?>, '<?= $row['nombre_area'] ?>', '<?= $row['nombre_director'] ?>', '<?= $row['telefono_director'] ?>', '<?= $row['email_director'] ?>')">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No hay áreas registrados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <nav>
                <ul class="pagination">
                    <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?pagina=<?= max(1, $paginaActual - 1) ?>">Anterior</a>
                    </li>

                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?= ($i == $paginaActual) ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?pagina=<?= min($totalPaginas, $paginaActual + 1) ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <script>
        // Función para confirmar la eliminación
        function confirmarEliminacion(id, nombre_area, nombre_director, telefono_director, email_director) {
            if (confirm(`¿Estás seguro de que deseas eliminar a:\n\nArea: ${nombre_area} \nDirector: ${nombre_director}\nTeléfono: ${telefono_director}\nEmail: ${email_director}?`)) {
                // Realizar la eliminación con AJAX
                fetch(`EliminarArea.php?id=${id}`, {
                        method: 'GET'
                    })
                    .then(response => response.text())
                    .then(data => {
                        alert(data);
                        document.getElementById(`area-${id}`).remove(); // Elimina la fila
                    })
                    .catch(error => console.error('Error:', error));
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php
$conn->close();
?>