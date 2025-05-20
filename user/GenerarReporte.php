<?php
require '../vendor/autoload.php'; // Incluir el autoload de Composer para PhpSpreadsheet y Dompdf
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Dompdf\Dompdf;

session_start();

require("../BD.php");

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error al conectar con la base de datos: " . $conn->connect_error);
}

if (!isset($_SESSION['id_usuario'])) {
    die("Error: Usuario no autenticado.");
}

$id_usuario = $_SESSION['id_usuario'];
$nombre_usuario = $_SESSION['nombre_usuario'];

$sql = "SELECT NOMBRE_AREA FROM AREAS";
$resulta = $conn->query($sql);
$areas = $resulta->num_rows > 0 ? $resulta->fetch_all(MYSQLI_ASSOC) : [];

### Mostrar Vista Previa
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['area']) && isset($_GET['estatus'])) {
    $areaSeleccionada = $_GET['area'];
    $estatusSeleccionado = $_GET['estatus'];

    $sql = "SELECT p.ID_PETICION, c.NOMBRE_CIUDADANO, c.TELEFONO_CIUDADANO, 
                   t.NOMBRE, p.FECHA_REGISTRO, p.STATUS, p.FOTO, p.FECHA_FINALIZACION
            FROM PETICIONES p
            INNER JOIN CIUDADANOS c ON p.ID_CIUDADANO = c.ID_CIUDADANO
            INNER JOIN TRABAJADOR t ON p.ID_USUARIO = t.ID_USUARIO
            INNER JOIN AREAS a ON p.ID_AREA = a.ID_AREA
            WHERE t.ID_USUARIO = ?";
    $params = [$id_usuario];
    $types = "s";

    if (!empty($areaSeleccionada)) {
        $sql .= " AND a.NOMBRE_AREA = ?";
        $params[] = $areaSeleccionada;
        $types .= "s";
    }
    if (!empty($estatusSeleccionado) && $estatusSeleccionado !== 'TODOS') {
        $sql .= " AND p.STATUS = ?";
        $params[] = $estatusSeleccionado;
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
}

### Generar Archivo (PDF o Excel)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['formato'])) {
    $areaSeleccionada = $_POST['area'];
    $estatusSeleccionado = $_POST['estatus'];
    $formato = $_POST['formato'];

    $sql = "SELECT p.ID_PETICION, c.NOMBRE_CIUDADANO, c.TELEFONO_CIUDADANO, 
                   t.NOMBRE, p.FECHA_REGISTRO, p.STATUS, p.FOTO, p.FECHA_FINALIZACION
            FROM PETICIONES p
            INNER JOIN CIUDADANOS c ON p.ID_CIUDADANO = c.ID_CIUDADANO
            INNER JOIN TRABAJADOR t ON p.ID_USUARIO = t.ID_USUARIO
            INNER JOIN AREAS a ON p.ID_AREA = a.ID_AREA
            WHERE t.ID_USUARIO = ?";
    $params = [$id_usuario];
    $types = "s";

    if (!empty($areaSeleccionada)) {
        $sql .= " AND a.NOMBRE_AREA = ?";
        $params[] = $areaSeleccionada;
        $types .= "s";
    }
    if (!empty($estatusSeleccionado) && $estatusSeleccionado !== 'TODOS') {
        $sql .= " AND p.STATUS = ?";
        $params[] = $estatusSeleccionado;
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($formato === 'excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Área seleccionada: ' . ($areaSeleccionada ?: 'Todas'))->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

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

        $sheet->getStyle('A3:H' . ($row - 1))->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ]);
        $sheet->getStyle('A3:A' . ($row - 1))->getFont()->setBold(true);
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $temp_file = tempnam(sys_get_temp_dir(), 'reporte_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($temp_file);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="reporte.xlsx"');
        readfile($temp_file);
        unlink($temp_file);
    } elseif ($formato === 'pdf') {
        $dompdf = new Dompdf();
        $html = "<h1>Reporte</h1>";
        $html .= "<table border='1'>";
        $html .= "<tr><th>ID Petición</th><th>Nombre Ciudadano</th><th>Teléfono</th><th>Trabajador</th><th>Fecha Registro</th><th>Status</th><th>Evidencia</th><th>Fecha Finalización</th></tr>";
        while ($data = $result->fetch_assoc()) {
            $html .= "<tr>";
            $html .= "<td>" . htmlspecialchars($data['ID_PETICION']) . "</td>";
            $html .= "<td>" . htmlspecialchars($data['NOMBRE_CIUDADANO']) . "</td>";
            $html .= "<td>" . htmlspecialchars($data['TELEFONO_CIUDADANO']) . "</td>";
            $html .= "<td>" . htmlspecialchars($data['NOMBRE']) . "</td>";
            $html .= "<td>" . htmlspecialchars($data['FECHA_REGISTRO']) . "</td>";
            $html .= "<td>" . htmlspecialchars($data['STATUS']) . "</td>";
            $html .= "<td>" . (!empty($data['FOTO']) ? 'Sí' : 'No') . "</td>";
            $html .= "<td>" . ($data['FECHA_FINALIZACION'] ?: 'SIN FECHA') . "</td>";
            $html .= "</tr>";
        }
        $html .= "</table>";

        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("reporte.pdf", ["Attachment" => true]);
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
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: center; }
        th { background-color: #4F81BD; color: white; }
        .button-group { margin-top: 20px; }
        .btn-submit { padding: 10px 20px; margin-right: 10px; }
    </style>
</head>
<body>
    <?php require("barraUsuario.php"); ?>
    <div class="main-content">
        <?php require("headerUsuario.php"); ?>
        <div class="form-container">
            <h2>Generar Reporte</h2>
            <form method="GET" action="GenerarReporte.php">
                <div class="form-group">
                    <label for="area">Departamento:</label>
                    <select id="area" name="area" required>
                        <option value="" disabled selected>Seleccione un departamento</option>
                        <?php foreach ($areas as $area) { echo "<option value='{$area['NOMBRE_AREA']}'>{$area['NOMBRE_AREA']}</option>"; } ?>
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

            <?php if (isset($result) && $result->num_rows > 0): ?>
                <h3>Vista Previa del Reporte</h3>
                <table>
                    <tr>
                        <th>ID Petición</th><th>Nombre Ciudadano</th><th>Teléfono</th><th>Trabajador</th>
                        <th>Fecha Registro</th><th>Status</th><th>Evidencia</th><th>Fecha Finalización</th>
                    </tr>
                    <?php while ($data = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data['ID_PETICION']); ?></td>
                            <td><?php echo htmlspecialchars($data['NOMBRE_CIUDADANO']); ?></td>
                            <td><?php echo htmlspecialchars($data['TELEFONO_CIUDADANO']); ?></td>
                            <td><?php echo htmlspecialchars($data['NOMBRE']); ?></td>
                            <td><?php echo htmlspecialchars($data['FECHA_REGISTRO']); ?></td>
                            <td><?php echo htmlspecialchars($data['STATUS']); ?></td>
                            <td><?php echo !empty($data['FOTO']) ? 'Sí' : 'No'; ?></td>
                            <td><?php echo $data['FECHA_FINALIZACION'] ?: 'SIN FECHA'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
                <div class="button-group">
                    <form method="POST" action="GenerarReporte.php">
                        <input type="hidden" name="area" value="<?php echo htmlspecialchars($areaSeleccionada); ?>">
                        <input type="hidden" name="estatus" value="<?php echo htmlspecialchars($estatusSeleccionado); ?>">
                        <button type="submit" name="formato" value="pdf" class="btn-submit">Generar PDF</button>
                        <button type="submit" name="formato" value="excel" class="btn-submit">Generar Excel</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>