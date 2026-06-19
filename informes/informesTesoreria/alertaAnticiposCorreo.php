<?php
// ==================== BOOT / INCLUDES ====================
include "../../facturacion/config/conexion.php";
// Si quieres protegerlo por sesión, también puedes incluir seguridad:
// include "../../seguridad.php";

// PHPMailer
require '../../PHPMailer/Exception.php';
require '../../PHPMailer/PHPMailer.php';
require '../../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==================== CONFIG BÁSICA ====================

// Fecha mínima a analizar (igual que en tu informe)
$FECHA_CORTE = '2025-08-26 00:00:00';

// Si tienes esta columna, usaremos alerta_notificada = 0
$USAR_FLAG_NOTIFICADA = true;  // ponlo en false si aún no creas la columna

// Correos destino
$DESTINATARIOS = [
    'director.sistemas@panamericanaviajes.com',
    'calidad@panamericanadeviajes.com',
];

// ==================== 1. OBTENER ANTICIPOS INCUMPLIDOS ====================

// Clasificamos igual que en el informe:
$condIncumplimiento = "
    (fecha_Retencion = '0000-00-00 00:00:00'
     OR fecha_pago    = '0000-00-00 00:00:00'
     OR (fecha <> '0000-00-00 00:00:00'
         AND fecha_pago <> '0000-00-00 00:00:00'
         AND TIMESTAMPDIFF(HOUR, fecha, fecha_pago) >= 36)
    )
";

// Filtro de notificados si existe la columna
$extraFlag = $USAR_FLAG_NOTIFICADA ? "AND (alerta_notificada = 0)" : "";

// Consulta principal
$sql = "
    SELECT *
    FROM tbl_anticipos
    WHERE fecha >= ?
      AND $condIncumplimiento
      $extraFlag
    ORDER BY fecha DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("❌ Error preparando consulta: " . $conn->error);
}
$stmt->bind_param("s", $FECHA_CORTE);
$stmt->execute();
$result = $stmt->get_result();

$anticipos = [];
while ($row = $result->fetch_assoc()) {
    // Clasificamos el tipo de alerta como en el informe
    $fechaInicio    = $row['fecha'];
    $fechaRetencion = $row['fecha_Retencion'];
    $fecha_Soporte  = $row['fecha_Soporte'];
    $fechaFin       = $row['fecha_pago'];

    $dias = $horas = $minutos = $segundos = 0;
    $tiempoTxt = "";

    if ($fechaInicio !== "0000-00-00 00:00:00" && $fechaFin !== "0000-00-00 00:00:00") {
        $diff     = strtotime($fechaFin) - strtotime($fechaInicio);
        $dias     = floor($diff / (60 * 60 * 24));
        $horas    = floor(($diff % (60 * 60 * 24)) / (60 * 60));
        $minutos  = floor(($diff % (60 * 60)) / 60);
        $segundos = $diff % 60;
        $tiempoTxt = "Días: $dias / Horas: $horas / Minutos: $minutos";
    }

    if ($fechaRetencion == "0000-00-00 00:00:00") {
        $alerta = "No se han aplicado retenciones";
    } elseif ($fechaFin == "0000-00-00 00:00:00") {
        $alerta = "No se ha cargado el soporte";
    } elseif ($dias >= 2 || $horas >= 36) {
        $alerta = "Pasaron más de 36 horas sin realizar el anticipo";
    } else {
        // en teoría no debería entrar aquí porque la consulta ya filtra incumplidos
        $alerta = "Anticipo pagado a tiempo";
    }

    $row['tipo_alerta']   = $alerta;
    $row['tiempo_txt']    = $tiempoTxt;
    $row['valor_formato'] = number_format($row['ValorTotalApagar'], 0, ",", ".");

    $anticipos[] = $row;
}
$stmt->close();

// Si no hay nada que notificar, salimos silenciosamente
if (count($anticipos) === 0) {
    // Puedes hacer echo si quieres ver algo cuando lo llames manualmente
    // echo "No hay anticipos incumplidos para notificar.\n";
    exit;
}

// ==================== 2. RESUMEN DE ESTADÍSTICAS ====================

$sin_retencion = 0;
$sin_soporte   = 0;
$fuera_tiempo  = 0;

foreach ($anticipos as $a) {
    if ($a['tipo_alerta'] === "No se han aplicado retenciones") {
        $sin_retencion++;
    } elseif ($a['tipo_alerta'] === "No se ha cargado el soporte") {
        $sin_soporte++;
    } elseif ($a['tipo_alerta'] === "Pasaron más de 36 horas sin realizar el anticipo") {
        $fuera_tiempo++;
    }
}

$total_alertas = $sin_retencion + $sin_soporte + $fuera_tiempo;

// ==================== 3. ARMAR EL CORREO HTML ====================

$hoy = date('Y-m-d H:i');

$tabla = '
<table style="width:100%; border-collapse: collapse; font-size: 12px;">
    <thead>
        <tr style="background-color:#0d6efd; color:white;">
            <th style="padding:6px; border:1px solid #ddd;">Estado</th>
            <th style="padding:6px; border:1px solid #ddd;">Alerta</th>
            <th style="padding:6px; border:1px solid #ddd;">Identificación</th>
            <th style="padding:6px; border:1px solid #ddd;">Fecha Registro</th>
            <th style="padding:6px; border:1px solid #ddd;">Fecha Retención</th>
            <th style="padding:6px; border:1px solid #ddd;">Fecha Soporte</th>
            <th style="padding:6px; border:1px solid #ddd;">Fecha Pago</th>
            <th style="padding:6px; border:1px solid #ddd;">Proveedor</th>
            <th style="padding:6px; border:1px solid #ddd;">Localizador</th>
            <th style="padding:6px; border:1px solid #ddd;">Tiempo transcurrido</th>
            <th style="padding:6px; border:1px solid #ddd;">Valor</th>
        </tr>
    </thead>
    <tbody>
';

foreach ($anticipos as $a) {
    $colorFila = '#ffffff';
    if ($a['tipo_alerta'] === "No se han aplicado retenciones" || $a['tipo_alerta'] === "No se ha cargado el soporte") {
        $colorFila = '#f8d7da'; // rojo claro
    } elseif ($a['tipo_alerta'] === "Pasaron más de 36 horas sin realizar el anticipo") {
        $colorFila = '#fff3cd'; // amarillo
    }

    $tabla .= '
        <tr style="background-color:'.$colorFila.';">
            <td style="padding:4px; border:1px solid #ddd;">'.htmlspecialchars($a['estado']).'</td>
            <td style="padding:4px; border:1px solid #ddd;">'.htmlspecialchars($a['tipo_alerta']).'</td>
            <td style="padding:4px; border:1px solid #ddd;">'.htmlspecialchars($a['identificacion']).'</td>
            <td style="padding:4px; border:1px solid #ddd;">'.htmlspecialchars($a['fecha']).'</td>
            <td style="padding:4px; border:1px solid #ddd;">'.htmlspecialchars($a['fecha_Retencion']).'</td>
            <td style="padding:4px; border:1px solid #ddd;">'.htmlspecialchars($a['fecha_Soporte']).'</td>
            <td style="padding:4px; border:1px solid #ddd;">'.htmlspecialchars($a['fecha_pago']).'</td>
            <td style="padding:4px; border:1px solid #ddd;">'.htmlspecialchars($a['proveedor']).'</td>
            <td style="padding:4px; border:1px solid #ddd;">'.htmlspecialchars($a['localizador']).'</td>
            <td style="padding:4px; border:1px solid #ddd;">'.htmlspecialchars($a['tiempo_txt']).'</td>
            <td style="padding:4px; border:1px solid #ddd;">$'.htmlspecialchars($a['valor_formato']).'</td>
        </tr>
    ';
}

$tabla .= '</tbody></table>';

$body = '
<p>Cordial saludo,</p>
<p>Se han detectado <strong>'.$total_alertas.'</strong> anticipos que incumplen los tiempos o condiciones establecidas desde <strong>'.$FECHA_CORTE.'</strong>.</p>

<ul>
    <li><strong>'.$sin_retencion.'</strong> sin aplicar retenciones</li>
    <li><strong>'.$sin_soporte.'</strong> sin soporte cargado</li>
    <li><strong>'.$fuera_tiempo.'</strong> con más de 36 horas entre registro y pago</li>
</ul>

<p><strong>Fecha de generación del informe:</strong> '.$hoy.'</p>

<h3>Detalle de anticipos:</h3>
'.$tabla.'

<p>Por favor, revisar y tomar las acciones correspondientes.</p>

<p>Atentamente,<br>
Sistema de Alertas de Anticipos</p>
';

// ==================== 4. ENVIAR CORREO CON PHPMailer ====================

try {
    $mail = new PHPMailer(true);
    $smtp = require __DIR__ . '/../../aws.php';

    // Configura con los mismos datos que ya usas en tu sistema
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = $smtp['ses_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp['ses_user'];
    $mail->Password = $smtp['ses_pass'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('contabilidad8@panamericanaviajes.com', 'Alertas Anticipos');
    foreach ($DESTINATARIOS as $dest) {
        $mail->addAddress($dest);
    }
    $mail->addCC('lider.contabilidad@panamericanaviajes.com');

    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = "ALERTA: Incumplimientos en anticipos a proveedores ({$total_alertas})";
    $mail->Body    = $body;
    $mail->AltBody = 'Se han detectado anticipos que incumplen los tiempos establecidos.';

    $mail->send();

} catch (Exception $e) {
    error_log("PHPMailer Exception en alertaAnticiposCorreo: " . $e->getMessage());
    // Si quieres ver errores al probarlo manualmente:
    // echo "Error enviando correo: " . $e->getMessage();
}

// ==================== 5. MARCAR COMO NOTIFICADOS (OPCIONAL) ====================

if ($USAR_FLAG_NOTIFICADA && count($anticipos) > 0) {
    $ids = array_column($anticipos, 'id_anticipo'); // ajusta al nombre real de la PK
    // Si tu PK se llama distinto, cambia 'id_anticipo' por el nombre correcto
    $ids = array_map('intval', $ids);
    $idsList = implode(',', $ids);

    if (!empty($idsList)) {
        $sqlUpdate = "UPDATE tbl_anticipos SET alerta_notificada = 1 WHERE id_anticipo IN ($idsList)";
        $conn->query($sqlUpdate);
    }
}

// Si lo ejecutas en navegador, puedes imprimir algo simple:
echo "Correo de alertas enviado con éxito. Total anticipos: " . count($anticipos) . "\n";
