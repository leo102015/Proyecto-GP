<?php

require '../vendor/autoload.php'; // Incluir el autoload de Composer para usar PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

// Iniciar sesión para obtener datos del usuario conectado
session_start();

$servername = "localhost";
$username = "root";
$password = "Previus22";
$dbname = "bd_nats";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error al conectar con la base de datos: " . $conn->connect_error);
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    die("Error: Usuario no autenticado.");
}

$id_usuario = $_SESSION['id_usuario']; // Obtener el ID del usuario
$nombre_usuario = $_SESSION['nombre_usuario']; // Obtener el nombre del usuario

// Obtener las áreas desde la base de datos
$sql = "SELECT NOMBRE_AREA FROM AREAS";
$resulta = $conn->query($sql);

// Verificar si la consulta devolvió resultados
if ($resulta->num_rows > 0) {
    // Crear un array con las áreas
    $areas = [];
    while ($row = $resulta->fetch_assoc()) {
        $areas[] = $row["NOMBRE_AREA"];
    }
} else {
    $areas = []; // No hay áreas
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $areaSeleccionada = $_POST['area'] ?? '';
    $estatusSeleccionado = $_POST['estatus'] ?? '';

    // Construcción dinámica de la consulta
    $sql = "SELECT p.ID_PETICION, c.NOMBRE_CIUDADANO, c.TELEFONO_CIUDADANO, 
                   t.NOMBRE, p.FECHA_REGISTRO, p.STATUS, p.FOTO, p.FECHA_FINALIZACION
            FROM PETICIONES p
            INNER JOIN CIUDADANOS c ON p.ID_CIUDADANO = c.ID_CIUDADANO
            INNER JOIN TRABAJADOR t ON p.ID_USUARIO = t.ID_USUARIO
            INNER JOIN AREAS a ON p.ID_AREA = a.ID_AREA
            WHERE t.ID_USUARIO = ?";

    $params = [$id_usuario];
    $types = "s"; // Primer parámetro es ID de usuario (string)

    if (!empty($areaSeleccionada)) {
        $sql .= " AND a.NOMBRE_AREA = ?";
        $params[] = $areaSeleccionada;
        $types .= "s";
    }

    if (!empty($estatusSeleccionado) && $estatusSeleccionado !== 'TODOS') {
        $sql .= " AND p.STATUS = ?";
        $params[] = $estatusSeleccionado;
        $types .= "s";
    } else if (!empty($estatusSeleccionado) && $estatusSeleccionado == 'TODOS') {
    }

    // Preparar y ejecutar consulta
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        die("Error en la consulta: " . $stmt->error);
    }

    $result = $stmt->get_result();

    // Crear archivo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Área seleccionada: ' . ($areaSeleccionada ?: 'Todas'))->mergeCells('A1:H1');
    $sheet->getStyle('A1')->applyFromArray([
        'font' => ['bold' => true, 'size' => 14],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);

    // Encabezados
    $headers = ['ID Petición', 'Nombre Ciudadano', 'Teléfono', 'Trabajador', 'Fecha Registro', 'Status', 'Evidencia', 'Fecha Finalización'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '2', $header);
        $col++;
    }

    $sheet->getStyle('A2:H2')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
    ]);

    // Llenar datos
    $row = 3;
    while ($data = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $data['ID_PETICION']);
        $sheet->setCellValue('B' . $row, $data['NOMBRE_CIUDADANO']);
        $sheet->setCellValue('C' . $row, $data['TELEFONO_CIUDADANO']);
        $sheet->setCellValue('D' . $row, $data['NOMBRE']);
        $sheet->setCellValue('E' . $row, $data['FECHA_REGISTRO']);
        $sheet->setCellValue('F' . $row, $data['STATUS']);
        $sheet->setCellValue('G' . $row, !empty($data['FOTO']) ? 'Sí' : 'No');
        $sheet->setCellValue('H' . $row, $data['FECHA_FINALIZACION'] ?: 'SIN FECHA');
        $row++;
    }

    // Aplicar estilos
    $sheet->getStyle('A3:H' . ($row - 1))->applyFromArray([
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
    ]);

    $sheet->getStyle('A3:A' . ($row - 1))->getFont()->setBold(true);
    foreach (range('A', 'H') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Guardar y descargar archivo
    try {
        $temp_file = tempnam(sys_get_temp_dir(), 'reporte_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($temp_file);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="reporte.xlsx"');
        readfile($temp_file);
    } finally {
        unlink($temp_file);
    }

    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Reporte</title>
    <link rel="stylesheet" href="../estiloBarras.css">
    <link rel="stylesheet" href="../formulario.css">
</head>

<body>
    <?php require("barraUsuario.php"); ?>

    <div class="main-content">
        <?php require("headerUsuario.php"); ?>
        <div class="form-container">
            <h2>Generar Reporte</h2>
            <form method="POST" action="GenerarReporte.php">
                <div class="form-group">
                    <label for="area">Área:</label>
                    <select id="area" name="area" required>
                        <option value="" disabled selected>Seleccione un área</option>
                        <?php
                        foreach ($areas as $area) {
                            echo "<option value='$area'>$area</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="estatus">Estatus:</label>
                    <select id="estatus" name="estatus" required>
                        <option value="" disabled selected>Seleccione un estatus</option>
                        <option value="COMPLETADA">Completada</option>
                        <option value="EN PROCESO">En proceso</option>
                        <option value="NO COMPLETADA">No completada</option>
                        <option value="TODOS">Todos</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Generar</button>
            </form>
        </div>
    </div>
</body>

</html>