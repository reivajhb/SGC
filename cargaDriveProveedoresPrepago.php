<?php 
include "seguridad.php";
include "conexion.php";
include_once 'google-api-php-client--PHP8.0/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

    // Función para cargar archivo a Google Drive
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

    // Datos del formulario
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
    $fecha_salida = $_POST['fecha_salida'];
    $estado = $_POST['estado'];
    $fecha_lmtpago = $_POST['fecha_lmtpago'];

    // Escapar y formatear la fecha y hora para el formato de MySQL
    $fechaHoraFormatoMySQL = date("Y-m-d H:i:s", strtotime($fecha));

    $ruta1 = 'https://drive.google.com/open?id=' . $resultadoCargapt1->id;
    $ruta2 = 'https://drive.google.com/open?id=' . $resultadoCargapt2->id;

    // Insertar datos en la base de datos
    $insertar = "INSERT INTO tbl_anticipos (fecha, identificacion, proveedor, email_proveedor, localizador, num_factura, concepto, descripcion, moneda, valor, usuario, fecha_ingreso, certificacion, fecha_salida, cuentadecobro, estado, fecha_lmtpago) 
                 VALUES ('$fechaHoraFormatoMySQL', '$identificacion', '$proveedor', '$email_proveedor', '$localizador', '$num_factura', '$concepto', '$descripcion', '$moneda', '$valor', '$usuario', '$fecha_ingreso', '$ruta1', '$fecha_salida', '$ruta2', '$estado', '$fecha_lmtpago')";
    $resultado = mysqli_query($conn, $insertar);

    if ($resultado) {
        echo '<script>
              alert("Pago anticipo proveedor cargado con éxito");
              window.location = "formularioCargaDriveProveedoresPrepago.php";
              </script>';
    } else {
        echo '<script>alert("Error en la carga")</script>';
    }
} catch (Google_Service_Exception $gs) {
    $m = json_decode($gs->getMessage());
    echo $m->error->message;
    exit;
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}

// Enviar correo electrónico
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$mail = new PHPMailer(true);

try {
    $correo = "contabilidad8@panamericanaviajes.com";
    $subject = "Solicitud Anticipos";
    
    $datosProveedores = array(
        'Cordial saludo,',
        'Se ha cargado un nuevo anticipo por parte del asesor:',
        '<h3>' . $usuario . '</h3>',
        '<H1>Nombre del Proveedor</H1>',
        '<h3>' . $proveedor . '</h3>',
        '<H1>Descripción</H1>', 
        '<h3>' . $descripcion . '</h3>', 
        '<H1>Fecha de registro</H1>', 
        '<h3>' . $fecha . '</h3>',
        '<H1>Valor a pagar</H1>', 
        '<h3>' . $valor . '</h3>', 
        'Gracias',
        'Atentamente,',
        'Tesoreria Panamericana de viajes sas',
        'contabilidad8@panamericanaviajes.com',
        'Carrera 11a #93a-80 Ofic. 104',
        'Tel: (+57 60 1) 6500 400 Ext: 102',
        'Bogotá, Colombia',
        'Para mayor información visite www.turivel.com - www.panamericanadeviajes.net'
    );

    // Configuración del servidor SMTP
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contabilidad8@panamericanaviajes.com';
    $mail->Password = '3obb);M>4J"5';
    $mail->SMTPSecure = 'tls';
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
          alert("Correo Enviado con Éxito");
          window.location = "consultaProveedoresPrepago.php";
          </script>';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
