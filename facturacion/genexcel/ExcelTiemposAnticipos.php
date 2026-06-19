<?php
define('BASE_PATH', __DIR__ . '/..');
require __DIR__ . '/../../vendor/autoload.php';
include '../config/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Consulta para obtener los datos reales de la base de datos
$consulta = "
    SELECT identificacion, fecha, fecha_Retencion, fecha_Soporte, fecha_pago, fecha_ingreso, fecha_salida,
            proveedor, localizador, ValorTotalApagar, estado 
    FROM tbl_anticipos
    WHERE fecha >= '2025-08-26 00:00:00'
";

$resultado = $conn->query($consulta);

$datosAnticipos = [];

while ($fila = $resultado->fetch_assoc()) {
    $datosAnticipos[] = [
        'Identificación' => $fila['identificacion'] ?? 'N/A',
        'Fecha Inicio' => $fila['fecha'] ?? '0000-00-00 00:00:00',
        'Fecha Retención' => $fila['fecha_Retencion'] ?? '0000-00-00 00:00:00',
        'Fecha Carga Soporte' => $fila['fecha_Soporte'] ?? '0000-00-00 00:00:00',
        'Fecha de pago' => $fila['fecha_pago'] ?? '0000-00-00 00:00:00',
        'Fecha de entrada de los pasajeros' => $fila['fecha_ingreso'] ?? '0000-00-00 00:00:00',
        'Fecha de salida de los pasajeros' => $fila['fecha_salida'] ?? '0000-00-00 00:00:00',
        'Proveedor' => $fila['proveedor'] ?? 'N/A',
        'Localizador' => $fila['localizador'] ?? 'N/A',
        'Valor a pagar' => $fila['ValorTotalApagar'] ?? 0,
        'Estado' => $fila['estado'] ?? 'N/A'
    ];
}

// Crear un nuevo archivo Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$encabezados = ['Identificación', 'Fecha Inicio', 'Fecha Retención', 'Fecha Carga Soporte', 'Fecha de pago', 'Fecha de entrada de los pasajeros', 'Fecha de salida de los pasajeros', 'Tiempo Transcurrido', 'Proveedor', 'Localizador', 'Valor a pagar', 'Estado', 'Alerta'];
$sheet->fromArray([$encabezados], NULL, 'A1');

// Procesar los datos y calcular tiempo transcurrido
$fila = 2;
foreach ($datosAnticipos as $anticipo) {
    // Es importante verificar que las fechas no sean el valor por defecto antes de calcular
    $fechaInicio = strtotime($anticipo['Fecha Inicio']) > 0 ? strtotime($anticipo['Fecha Inicio']) : null;
    $fechapago = strtotime($anticipo['Fecha de pago']) > 0 ? strtotime($anticipo['Fecha de pago']) : null;
    
    $tiempoTranscurrido = 'N/A';
    $alerta = '';
    
    // Calcular tiempo transcurrido solo si ambas fechas son válidas
    if ($fechaInicio !== null && $fechapago !== null && $fechapago >= $fechaInicio) {
        $diferenciaSegundos = $fechapago - $fechaInicio;

        $dias = floor($diferenciaSegundos / (60 * 60 * 24));
        $horas = floor(($diferenciaSegundos % (60 * 60 * 24)) / (60 * 60));
        $minutos = floor(($diferenciaSegundos % (60 * 60)) / 60);
        $segundos = $diferenciaSegundos % 60;

        $tiempoTranscurrido = "$dias días, $horas horas, $minutos minutos, $segundos segundos";
        
        // Evaluar alerta basada en tiempo transcurrido
        if ($horas >= 36 || $dias >= 2) {
            $alerta = "Pasaron más de 36 horas sin realizar el anticipo";
        } else {
            $alerta = "Anticipo pagado a tiempo";
        }

    } else {
        // Evaluar alerta para casos de fechas faltantes/inválidas
        if ($anticipo['Fecha Retención'] == "0000-00-00 00:00:00") {
            $alerta = "No se han aplicado retenciones";
        } elseif ($anticipo['Fecha Carga Soporte'] == "0000-00-00 00:00:00") {
            $alerta = "No se ha cargado el soporte";
        } elseif ($anticipo['Fecha de pago'] == "0000-00-00 00:00:00") {
            $alerta = "No se ha realizado el pago"; // Ajustado para ser más preciso
        } else {
            $alerta = "Error o Fechas Inválidas";
        }
    }
    
    // Agregar datos al archivo Excel: **Ajuste clave aquí**
    // Se agregan todos los 11 campos del $anticipo, más las dos columnas calculadas
    $sheet->fromArray([
        [
            $anticipo['Identificación'], 
            $anticipo['Fecha Inicio'], 
            $anticipo['Fecha Retención'], 
            $anticipo['Fecha Carga Soporte'], 
            $anticipo['Fecha de pago'], 
            $anticipo['Fecha de entrada de los pasajeros'], // Nueva columna
            $anticipo['Fecha de salida de los pasajeros'], // Nueva columna
            $tiempoTranscurrido, // Columna calculada
            $anticipo['Proveedor'], 
            $anticipo['Localizador'], 
            $anticipo['Valor a pagar'], 
            $anticipo['Estado'], 
            $alerta // Columna calculada
        ]
    ], NULL, 'A' . $fila);

    $fila++;
}

// Configurar cabeceras para la descarga del archivo
$filename = "Reporte_Anticipos.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>