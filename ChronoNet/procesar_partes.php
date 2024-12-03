<?php
// Cargar las clases de PHPExcel
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include 'Conexion.php'; // Aquí debes asegurarte de que $conn es un PDO para SQLite

// Crear un nuevo objeto Spreadsheet (libro de cálculo)
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("src/Registro-jornada.xlsx");

// Obtener la hoja de trabajo activa
$sheet = $spreadsheet->getActiveSheet();

// Obtener los datos del formulario
$usuarioSeleccionado = filter_input(INPUT_POST, 'usuarios', FILTER_SANITIZE_NUMBER_INT);
$mesSeleccionado = filter_input(INPUT_POST, 'meses', FILTER_SANITIZE_STRING);
$anioSeleccionado = filter_input(INPUT_POST, 'anios', FILTER_VALIDATE_INT);



// Validar las entradas
if (!$usuarioSeleccionado || !$mesSeleccionado || !$anioSeleccionado) {
    die("Error: Parámetros inválidos.");
}

// Función para obtener el número de mes
function obtenerNumeroMes($messeleccionado) {
    $meses = [
        'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4,
        'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8,
        'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
    ];
    return $meses[$messeleccionado] ?? null;
}

$numeroMes = obtenerNumeroMes($mesSeleccionado);
if (!$numeroMes) {
    die("Error: Mes inválido.");
}

// Calcular el rango de fechas
$primerDiaMes = new DateTime("$anioSeleccionado-$numeroMes-01");
$ultimoDiaMes = new DateTime($primerDiaMes->format('Y-m-t'));

// Establecer valores fijos en la hoja
$sheet->setCellValue('C3', "Sadyfi S.L");
$sheet->setCellValue('B4', "B13389531");
$sheet->setCellValue('G6', (new DateTime())->format('d/m/Y'));
$sheet->setCellValue('C6', $primerDiaMes->format('d') . ' al ' . $ultimoDiaMes->format('d') . " de $mesSeleccionado de $anioSeleccionado");

// Consultar datos del usuario desde SQLite
$queryUsuarios = "SELECT * FROM TUsuarios WHERE IdUsuario = :id";
$stmt = $conn->prepare($queryUsuarios);
$stmt->execute(['id' => $usuarioSeleccionado]);

$rowUsuarios = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$rowUsuarios) {
    die("Error: Usuario no encontrado.");
}

// Rellenar datos del usuario en la hoja
$nombreCompleto = $rowUsuarios['Nombre'] . " " . $rowUsuarios['Apellidos'];
$sheet->setCellValue('G3', $nombreCompleto);
$sheet->setCellValue('G4', $rowUsuarios['DNI']);

// Función para procesar múltiples registros (entradas y salidas) por día
function procesarRegistrosDiarios($conn, $numeroMes, $anioSeleccionado, $usuarioSeleccionado, $sheet) {
    $queryRegistros = "
        SELECT Tipo, FechaHora, Incidencia
        FROM TRegistro
        WHERE IdUsuario = :id AND YEAR(FechaHora) = :anio AND MONTH(FechaHora) = :mes
        ORDER BY FechaHora ASC";
    $stmt = $conn->prepare($queryRegistros);
    $stmt->execute([
        'id' => $usuarioSeleccionado,
        'anio' => (string)$anioSeleccionado,
        'mes' => sprintf('%02d', $numeroMes)
    ]);

    $registrosPorDia = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $fecha = (new DateTime($row['FechaHora']))->format('Y-m-d');
        $hora = (new DateTime($row['FechaHora']))->format('H:i:s');
        $registrosPorDia[$fecha][] = ['Tipo' => $row['Tipo'], 'Hora' => $hora, 'Incidencia' => $row['Incidencia']];
    }

    $total_horas_mes = 0;

    foreach ($registrosPorDia as $dia => $registros) {
        $filaExcel = (int)(new DateTime($dia))->format('d') + 8; // Ajustar fila en Excel (días inician en la fila 9)
        $horasDia = 0;
        $pausaTotal = 0;

        $pares = []; // Almacenar pares de entradas y salidas
        $pausas = []; // Almacenar pares de pausas y reanudaciones

        foreach ($registros as $registro) {
            if ($registro['Tipo'] == 0) { // Entrada
                $pares[] = ['entrada' => $registro['Hora'], 'salida' => null];
            } elseif ($registro['Tipo'] == 1) { // Salida
                foreach ($pares as &$par) {
                    if ($par['salida'] === null) {
                        $par['salida'] = $registro['Hora'];
                        break;
                    }
                }
            } elseif ($registro['Tipo'] == 2) { // Pausa
                $pausas[] = ['pausa' => $registro['Hora'], 'reanudar' => null];
            } elseif ($registro['Tipo'] == 3) { // Reanudar
                foreach ($pausas as &$pausa) {
                    if ($pausa['reanudar'] === null) {
                        $pausa['reanudar'] = $registro['Hora'];
                        break;
                    }
                }
            }
        }

        // Calcular horas trabajadas y restar pausas
        foreach ($pares as $par) {
            if ($par['entrada'] && $par['salida']) {
                $entrada = DateTime::createFromFormat('H:i:s', $par['entrada']);
                $salida = DateTime::createFromFormat('H:i:s', $par['salida']);
                $horasDia += $salida->getTimestamp() - $entrada->getTimestamp();
            }
        }

        foreach ($pausas as $pausa) {
            if ($pausa['pausa'] && $pausa['reanudar']) {
                $inicioPausa = DateTime::createFromFormat('H:i:s', $pausa['pausa']);
                $finPausa = DateTime::createFromFormat('H:i:s', $pausa['reanudar']);
                $pausaTotal += $finPausa->getTimestamp() - $inicioPausa->getTimestamp();
            }
        }

        $horasDia -= $pausaTotal;

        // Convertir segundos a horas y minutos
        $horas = floor($horasDia / 3600);
        $minutos = floor(($horasDia % 3600) / 60);
        $sheet->setCellValue("G$filaExcel", sprintf('%02d:%02d', $horas, $minutos));

        $total_horas_mes += $horasDia;
    }

    return $total_horas_mes;
}

// Procesar registros y calcular totales
$total_horas_segundos = procesarRegistrosDiarios($conn, $numeroMes, $anioSeleccionado, $usuarioSeleccionado, $sheet);

// Formatear el total de horas en la celda final
$total_horas = floor($total_horas_segundos / 3600);
$total_minutos = floor(($total_horas_segundos % 3600) / 60);
$sheet->setCellValue("C43", sprintf('%02d:%02d', $total_horas, $total_minutos));

// Guardar el archivo
try{
    $filePath = "src/Registro-jornada_$mesSeleccionado-$nombreCompleto.xlsx";
    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);
}catch(\PhpOffice\PhpSpreadsheet\Writer\Exception $e){
    die('Error al guardar el  archivo excel ' . $e->getMessage());
}

// Descargar el archivo
ob_clean();
ob_flush();
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
?>
