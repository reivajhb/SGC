<?php 
include "seguridad.php";
include "conexion.php";
include_once 'google-api-php-client--PHP8.0/vendor/autoload.php';

// Variables de credenciales.
$claveJSON = '1y9_H6CLWqryXkBo2tGCVXezvu-ao-9P8';
$pathJSON = 'drive-contabilidad-cdd148a483c0.json';

// Configurar variable de entorno
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $pathJSON);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes(['https://www.googleapis.com/auth/drive.file']);

try {
    // Instanciar el servicio
    $service = new Google_Service_Drive($client);

    // Función para cargar archivos a Google Drive
    function uploadFileToDrive($service, $fileInputName, $folderId) {
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

    $resultadoCargapt1 = uploadFileToDrive($service, 'certificacion', $claveJSON);
    $resultadoCargapt2 = uploadFileToDrive($service, 'cuentadecobro', $claveJSON);

} catch (Google_Service_Exception $gs) {
    $m = json_decode($gs->getMessage());
    echo $m->error->message;
    exit;
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}

// Validar y sanitizar entradas
$fecha = mysqli_real_escape_string($conn, $_POST['fecha']);
$identificacion = mysqli_real_escape_string($conn, $_POST['identificacion']);
$proveedor = mysqli_real_escape_string($conn, $_POST['proveedor']);
$email_proveedor = filter_var($_POST['email_proveedor'], FILTER_SANITIZE_EMAIL);
$localizador = mysqli_real_escape_string($conn, $_POST['localizador']);
$num_factura = mysqli_real_escape_string($conn, $_POST['num_factura']);
$concepto = mysqli_real_escape_string($conn, $_POST['concepto']);
$descripcion = mysqli_real_escape_string($conn, $_POST['descripcion']);
$moneda = mysqli_real_escape_string($conn, $_POST['moneda']);
$valor = mysqli_real_escape_string($conn, $_POST['valor']);
$usuario = mysqli_real_escape_string($conn, $_POST['usuario']);
$fecha_ingreso = mysqli_real_escape_string($conn, $_POST['fecha_ingreso']);
$fecha_salida = mysqli_real_escape_string($conn, $_POST['fecha_salida']);
$valorpagar = mysqli_real_escape_string($conn, $_POST['valorpagar']);
$estado = mysqli_real_escape_string($conn, $_POST['estado']);

// Escapar y formatear la fecha y hora para el formato de MySQL
$fechaHoraFormatoMySQL = date("Y-m-d H:i:s", strtotime($fecha));

$ruta1 = 'https://drive.google.com/open?id=' . $resultadoCargapt1->id;
$ruta2 = 'https://drive.google.com/open?id=' . $resultadoCargapt2->id;

// Preparar la consulta
$insertar = "INSERT INTO tbl_pagos_inter (fecha, identificacion, proveedor, email_proveedor, localizador, num_factura, concepto, descripcion, moneda, valor, usuario, fecha_ingreso, certificacion, fecha_salida, cuentadecobro, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

if ($stmt = mysqli_prepare($conn, $insertar)) {
    mysqli_stmt_bind_param($stmt, 'ssssssssssssssss', $fechaHoraFormatoMySQL, $identificacion, $proveedor, $email_proveedor, $localizador, $num_factura, $concepto, $descripcion, $moneda, $valor, $usuario, $fecha_ingreso, $ruta1, $fecha_salida, $ruta2, $estado);

    if (mysqli_stmt_execute($stmt)) {
        echo '<script>
            alert("Pago proveedor cargado con éxito");
            window.location = "formularioCargaDriveProveedoresInter.php";
            </script>';
    } else {
        echo '<script>alert("Error en la carga")</script>';
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';

$mail = new PHPMailer(true);

try {
    $correo = "contabilidad8@panamericanaviajes.com";
    $subject = "Solicitud pago internacional";
    
    $datosProveedores = [
        'Cordial saludo,',
        'Se ha cargado un nuevo pago internacional por parte del asesor:',
        "<h3>$usuario</h3>",
        '<H1>Nombre del Proveedor</H1>',
        "<h3>$proveedor</h3>",
        '<H1>Descripción</H1>',
        "<h3>$descripcion</h3>",
        '<H1>Fecha de registro</H1>',
        "<h3>$fecha</h3>",
        '<H1>Valor a pagar</H1>',
        "<h3>$valor</h3>",
        'Gracias<br><br>Atentamente,<br>Tesoreria Panamericana de viajes sas<br>contabilidad8@panamericanaviajes.com<br>Carrera 11a #93a-80 Ofic. 104<br>Tel: (+57 60 1) 6500 400 Ext: 102<br>Bogotá, Colombia<br>Para mayor información visite www.turivel.com - www.panamericanadeviajes.net'
    ];

    // Configuración del servidor de correo
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contabilidad8@panamericanaviajes.com';
    $mail->Password = 'MbGSf&oM4W#!7A5S';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Destinatarios
    $mail->setFrom('contabilidad8@panamericanaviajes.com', 'Pagos de anticipos Proveedores turisticos panamericana de viajes');
    $mail->addAddress($correo);

    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = implode("<tr><th scope='col'></th></tr>", $datosProveedores);
    $mail->AltBody = 'Este es un correo de prueba';

    $mail->send();
    echo '<script>
        alert("Correo enviado con éxito");
        window.location = "consultaProveedoresPrepago.php";
        </script>';

} catch (Exception $e) {
    echo "El mensaje no pudo ser enviado. Error: {$mail->ErrorInfo}";
}
?>
