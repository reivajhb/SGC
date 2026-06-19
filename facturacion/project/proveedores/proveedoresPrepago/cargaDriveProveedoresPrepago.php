<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";
include_once '../../../../google-api-php-client--PHP8.0/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Variables de credenciales de Google Drive.
$claveJSON = '1y9_H6CLWqryXkBo2tGCVXezvu-ao-9P8'; // Revisa si esta clave es la correcta
$pathJSON = '../../../../drive-contabilidad-a46b4f106c64.json';
// Configurar variable de entorno
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $pathJSON);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes(['https://www.googleapis.com/auth/drive.file']);

try {
    // === LÓGICA DE SUBIDA DE ARCHIVOS A GOOGLE DRIVE ===
    $service = new Google_Service_Drive($client);

    // Función para cargar archivo a Google Drive
    function uploadFileToDrive($service, $fileInputName, $folderId) {
        if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] != UPLOAD_ERR_OK) {
            return null; // No hay archivo para subir
        }
        $file_path = $_FILES[$fileInputName]['tmp_name'];
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($_FILES[$fileInputName]['name']);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);
        $file->setParents([$folderId]);
        $file->setDescription('Archivo cargado a drive con éxito');
        $file->setMimeType($mime_type);
        return $service->files->create(
            $file,
            [
                'data' => file_get_contents($file_path),
                'mimeType' => $mime_type,
                'uploadType' => 'media',
            ]
        );
    }
    // Subir los dos archivos
    $resultadoCertificacion = uploadFileToDrive($service, 'certificacion', $claveJSON);
    $resultadoCuentadecobro = uploadFileToDrive($service, 'cuentadecobro', $claveJSON);

    $ruta1 = $resultadoCertificacion ? 'https://drive.google.com/open?id=' . $resultadoCertificacion->id : null;
    $ruta2 = $resultadoCuentadecobro ? 'https://drive.google.com/open?id=' . $resultadoCuentadecobro->id : null;

    // === Obtener datos del formulario de forma segura ===
    $fecha = $_POST['fecha'] ?? '';
    $identificacion = $_POST['identificacion'] ?? '';
    $proveedor = $_POST['proveedor'] ?? '';
    $email_proveedor = $_POST['email_proveedor'] ?? '';
    $localizador = $_POST['localizador'] ?? '';
    $num_factura = $_POST['num_factura'] ?? '';
    $concepto = $_POST['concepto'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $moneda = $_POST['moneda'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $usuario = $_POST['usuario'] ?? '';
    $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
    $fecha_salida = $_POST['fecha_salida'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $fecha_lmtpago = $_POST['fecha_lmtpago'] ?? '';
    $correoasesor = $_POST['correoasesor'] ?? '';
    
    // === PASO CLAVE: Buscar el id_proveedor con la identificacion ===
    $id_proveedor = null;
    $consulta_id = "SELECT id_proveedor FROM tbl_proveedores WHERE nit_identificacion = ?";
    $stmt_id = mysqli_prepare($conn, $consulta_id);
    mysqli_stmt_bind_param($stmt_id, "s", $identificacion);
    mysqli_stmt_execute($stmt_id);
    $resultado_id = mysqli_stmt_get_result($stmt_id);
    if ($fila = mysqli_fetch_assoc($resultado_id)) {
        $id_proveedor = $fila['id_proveedor'];
    }
    mysqli_stmt_close($stmt_id);

    if (is_null($id_proveedor)) {
        throw new Exception("Error: No se pudo encontrar el proveedor con la identificación proporcionada.");
    }
    
    // === Insertar datos en la base de datos (seguro) ===
    // TRANSACCIÓN: si el correo falla, se hace rollback para no guardar duplicados
    mysqli_begin_transaction($conn);

    $insertar = "INSERT INTO tbl_anticipos (fecha, identificacion, proveedor, email_proveedor, localizador, num_factura, concepto, descripcion, moneda, valor, usuario, fecha_ingreso, certificacion, fecha_salida, cuentadecobro, estado, fecha_lmtpago, correoasesor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $insertar);
    
    // Cadena de tipos de datos corregida: sssssssssdsdssssss
    // 's' para descripcion, 'd' para valor, 's' para usuario, 's' para fecha_ingreso, 's' para certificacion
    mysqli_stmt_bind_param($stmt, "sssssssssdsdssssss", $fecha, $identificacion, $proveedor, $email_proveedor, $localizador, $num_factura, $concepto, $descripcion, $moneda, $valor, $usuario, $fecha_ingreso, $ruta1, $fecha_salida, $ruta2, $estado, $fecha_lmtpago, $correoasesor);
    
    $resultado = mysqli_stmt_execute($stmt);

    if (!$resultado) {
        throw new Exception("Error en la carga del pago en la base de datos: " . mysqli_error($conn));
    }
    
    // === LÓGICA DE ENVÍO DE CORREO ELECTRÓNICO (sin cambios) ===
    require '../../../../PHPMailer/Exception.php';
    require '../../../../PHPMailer/PHPMailer.php';
    require '../../../../PHPMailer/SMTP.php';

    $mail = new PHPMailer(true);

    $correo = "contabilidad8@panamericanaviajes.com";
    $subject = "Solicitud Anticipos - " . $proveedor;
    
    $datosProveedores = array(
        'Cordial saludo,',
        'Se ha cargado un nuevo anticipo por parte del asesor:',
        '<h3>' . $usuario . '</h3>',
        '<table style="width: 100%; border-collapse: collapse;">',
        '<tr><th colspan="2" style="background-color: #0073e6; color: white; padding: 10px; text-align: left;">Detalles del Anticipo</th></tr>',
        '<tr><td style="padding: 8px; border: 1px solid #ddd; text-align: left;"><strong>Nombre del Proveedor</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . $proveedor . '</td></tr>',
        '<tr><td style="padding: 8px; border: 1px solid #ddd; text-align: left;"><strong>Descripción</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . $descripcion . '</td></tr>',
        '<tr><td style="padding: 8px; border: 1px solid #ddd; text-align: left;"><strong>Fecha de Registro</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . $fecha . '</td></tr>',
        '<tr><td style="padding: 8px; border: 1px solid #ddd; text-align: left;"><strong>Valor a Pagar</strong></td><td style="padding: 8px; border: 1px solid #ddd;"><strong>$' . number_format($valor, 2, ',', '.') . '</strong></td></tr>',
        '</table>',
        'Gracias,',
        'Atentamente,',
        'Tesorería Panamericana de Viajes S.A.S',
        'contabilidad8@panamericanaviajes.com',
        'Carrera 11a #93a-80 Ofic. 104',
        'Tel: (+57 60 1) 6500 400 Ext: 102',
        'Bogotá, Colombia',
        'Para mayor información visite <a href="https://www.turivel.com" target="_blank">www.turivel.com</a> - <a href="https://www.panamericanadeviajes.net" target="_blank">www.panamericanadeviajes.net</a>'
    );
    
    $smtp = require __DIR__ . '/../../../../aws.php';
   
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = $smtp['ses_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp['ses_user'];
    $mail->Password = $smtp['ses_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $smtp['ses_port'];

    $mail->setFrom('contabilidad8@panamericanaviajes.com', 'Pagos de anticipos Proveedores turisticos panamericana de viajes');
    $mail->addAddress($correo);
    $mail->addAddress('asistente.tesoreria@panamericanaviajes.com');
    $mail->addAddress('tesoreria@panamericanadeviajes.com');
    $mail->addAddress('valentina.villamil@panamericanaviajes.com');
    $mail->addCC('director.sistemas@panamericanaviajes.com');
    $mail->addCC($correoasesor);

    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = implode("<tr><th scope='col'></th></tr>", $datosProveedores);
    $mail->AltBody = 'Correo Certificado';

    $mail->send();

    // Todo salió bien: confirmar la transacción
    mysqli_commit($conn);

    echo '<script>
        alert("Pago anticipo proveedor cargado con éxito y correo enviado");
        window.location = "consultaProveedoresPrepago.php";
        </script>';
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} catch (Exception $e) {
    // Revertir el INSERT para evitar registros duplicados en futuros reintentos
    if (isset($conn) && $conn) {
        mysqli_rollback($conn);
    }
    echo '<script>alert("Error: ' . htmlspecialchars($e->getMessage()) . '\\nEl anticipo NO fue guardado. Por favor intente nuevamente."); window.location = "formularioCargaDriveProveedoresPrepago.php?identificacion=' . urlencode($identificacion ?? '') . '";</script>';
    exit;
}
?>
