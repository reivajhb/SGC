<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";
include_once '../../../../google-api-php-client--PHP8.0/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../../../PHPMailer/Exception.php';
require '../../../../PHPMailer/PHPMailer.php';
require '../../../../PHPMailer/SMTP.php';

// Configuración de Google Drive
$claveJSON = '1y9_H6CLWqryXkBo2tGCVXezvu-ao-9P8';
$pathJSON = '../../../../drive-contabilidad-a46b4f106c64.json';
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $pathJSON);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes(['https://www.googleapis.com/auth/drive.file']);

try {
    $service = new Google_Service_Drive($client);
    $file_path = $_FILES['soportePrepago']['tmp_name'];
    $file = new Google_Service_Drive_DriveFile();
    $file->setName($_FILES['soportePrepago']['name']);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);

    $file->setParents(array($claveJSON));
    $file->setDescription('Archivo cargado a Drive con éxito');
    $file->setMimeType($mime_type);

    $resultadoCargapt = $service->files->create(
        $file,
        array(
            'data' => file_get_contents($file_path),
            'mimeType' => $mime_type,
            'uploadType' => 'media',
        )
    );
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Capturar datos del formulario
$id_anticipo = $_POST['id_anticipo'];
$fecha = $_POST['fecha'];
$identificacion = $_POST['identificacion'];
$proveedor = $_POST['proveedor'];
$email_proveedor = $_POST['email_proveedor'];
$localizador = $_POST['localizador'];
$num_factura = $_POST['num_factura'];
$concepto = $_POST['concepto'];
$descripcion = $_POST['descripcion'];
$moneda = $_POST['moneda'];
$valor = $_POST['valor'];
$usuario = $_POST['usuario'];
$fecha_ingreso = $_POST['fecha_ingreso'];
$certificacion = $_POST['certificacion'];
$fecha_salida = $_POST['fecha_salida'];
$cuentadecobro = $_POST['cuentadecobro'];
$egreso = $_POST['egreso'];
$ValorTotalApagar = $_POST['ValorTotalApagar'];
$estado = $_POST['estado'];
$fecha_Soporte = $_POST['fecha_Soporte'];
$descripcionRT = $_POST['descripcionRT'];
$correoasesor  = $_POST['correoasesor'];
$fecha_pago    = $_POST['fecha_pago'];

$ruta = 'https://drive.google.com/open?id=' . $resultadoCargapt->id;

$editarProveedorPrepago = "UPDATE tbl_anticipos SET fecha='$fecha', identificacion='$identificacion',
                            proveedor='$proveedor', email_proveedor='$email_proveedor' , localizador='$localizador',
                            num_factura='$num_factura', concepto='$concepto', descripcion='$descripcion', 
                            moneda='$moneda', valor='$valor', usuario='$usuario', fecha_ingreso='$fecha_ingreso', 
                            certificacion='$certificacion', fecha_salida='$fecha_salida', cuentadecobro='$cuentadecobro', 
                            ValorTotalApagar='$ValorTotalApagar', 
                            estado='$estado', egreso='$egreso', 
                            soportePrepago='$ruta', fecha_Soporte='$fecha_Soporte', fecha_pago='$fecha_pago',  descripcionRT='$descripcionRT', correoasesor='$correoasesor' WHERE id_anticipo='$id_anticipo'";

$resultado = mysqli_query($conn, $editarProveedorPrepago);

echo '<script>alert("Pago actualizado con éxito");</script>';

// Enviar correo con PHPMailer
$mail = new PHPMailer(true);
    $smtp = require __DIR__ . '/../../../../aws.php';
try {
    $correo = "contabilidad8@panamericanaviajes.com";
    // Configuración del servidor SMTP
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = $smtp['ses_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp['ses_user'];
    $mail->Password = $smtp['ses_pass'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Destinatarios
    $mail->setFrom('contabilidad8@panamericanaviajes.com', 'Pagos de anticipos Proveedores turisticos panamericana de viajes');
    $mail->addAddress($correo);
    // Agregar más destinatarios
    $mail->addAddress('asistente.tesoreria@panamericanaviajes.com');
    $mail->addAddress('tesoreria@panamericanaviajes.com');
    $mail->addAddress('valentina.villamil@panamericanaviajes.com');
    // Si deseas agregar una copia (CC)
    $mail->addCC('director.sistemas@panamericanaviajes.com');
    $mail->addCC($correoasesor);
    $mail->addCC($email_proveedor);

    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = "Notificación pago Anticipo - " . $id_anticipo;
    $mail->Body = "
        <html>
        <head><title>Notificación pago Anticipo</title></head>
        <body>
            <h2>Estimado(a) $proveedor,</h2>
            <p>Le informamos que su anticipo ha sido actualizado con la siguiente información:</p>
            <ul>
                <li><strong>Concepto:</strong> $concepto</li>
                <li><strong>Valor:</strong> $valor $moneda</li>
                <li><strong>Fecha de Soporte:</strong> $fecha_Soporte</li>
                <li><strong>Descripcion:</strong> $descripcionRT</li>
                <li><strong>Estado:</strong> $estado</li>
                <li><strong>Soporte:</strong> <a href='$ruta' target='_blank'>Ver documento</a></li>
            </ul>
            <p>Por favor, revise la información y cualquier duda contáctenos.</p>
            <p>Atentamente,</p>
            <p><strong>Panamericana de Viajes</strong></p>
        </body>
        </html>
    ";

    $mail->send();
    echo '<script>
            alert("Correo enviado con éxito al asesor y proveedor.");
            window.location.href = "consultaProveedoresPrepagoRT.php";
          </script>';
    exit;
} catch (Exception $e) {
    echo '<script>
            alert("Error al enviar el correo: ' . $mail->ErrorInfo . '");
            window.location.href = "consultaProveedoresPrepagoRT.php";
          </script>';
    exit;
}


?>
