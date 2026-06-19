<?php 
include "../../../config/seguridad.php"?>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../../../PHPMailer/Exception.php';
require '../../../../PHPMailer/PHPMailer.php';
require '../../../../PHPMailer/SMTP.php';

$mail = new PHPMailer(true);
    $smtp = require __DIR__ . '/../../../../aws.php';
$mail->CharSet = 'UTF-8';

try {


 
	$correo =  $_POST['correo'];
	$subject = $_POST['asunto'];
	$proveedor = $_POST['proveedor'];
	$descripcionRT  = $_POST['descripcionRT'];
	$fecha = $_POST['fecha'];
	$ValorTotalApagar = $_POST['ValorTotalApagar'];
	$soportePrepago = $_POST['soportePrepago'];
  
	


	
	$datosProveedores = array(
    '
    <!doctype html>
    <html lang="es">
    
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <style>
            table {
                width: 100%;
                margin: 20px 0;
                border-collapse: collapse;
            }
            th, td {
                padding: 10px;
                text-align: left;
                border: 1px solid #ddd;
            }
            th {
                background-color: #343a40;
                color: white;
            }
            tr:nth-child(even) {
                background-color: #f2f2f2;
            }
            tr:hover {
                background-color: #ddd;
            }
        </style>
        <!-- SCRIPTS JS-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    </head>
    
    <body>
        <div class="container mt-5">
            <p>Cordial saludo,</p>
            <p>Se realiza envío de soporte de pago junto con la relación de las facturas pagadas. Gracias por su atención.</p>
            
            <br>
    
            <!-- Tabla de detalles -->
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre del Proveedor</th>
                        <th>Descripción</th>
                        <th>Fecha de Registro</th>
                        <th>Valor Pagado</th>
                        <th>Soporte de Pago</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>'."$proveedor".'</td>
                        <td>'."$descripcionRT".'</td>
                        <td>'."$fecha".'</td>
                        <td>'."$ValorTotalApagar".'</td>
                        <td>'."$soportePrepago".'</td>
                    </tr>
                </tbody>
            </table>
            
            <br>
    
            <h2>Las facturas electrónicas solo se reciben en el correo electrónico <a href="mailto:recepcion.panamer@panamericanaviajes.com">recepcion.panamer@panamericanaviajes.com</a></h2>
    
            <br>
    
            <p>Gracias,</p>
            <p>Atentamente,</p>
            <p><strong>Tesorería Panamericana de Viajes S.A.S.</strong></p>
            <p>contabilidad8@panamericanaviajes.com</p>
            <p>Carrera 11a #93a-80 Ofic. 104</p>
            <p>Tel: (+57 60 1) 6500 400</p>
            <p>Bogotá, Colombia</p>
    
            <p>Para mayor información, visite <a href="https://www.turivel.com" target="_blank">www.turivel.com</a> - <a href="https://www.panamericanadeviajes.net" target="_blank">www.panamericanadeviajes.net</a></p>
        </div>
    </body>
    
    </html>
    '
);

	
	
	

    //Server settings
    $mail->SMTPDebug = 0;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = $smtp['ses_host'];
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = $smtp['ses_user'];
    $mail->Password   = $smtp['ses_pass'];
    $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
    $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('contabilidad8@panamericanaviajes.com', 'Pagos Proveedores turisticos panamericana de viajes');
    $mail->addAddress($correo);  
      //Add a recipient
    //$mail->addAddress('ellen@example.com');               //Name is optional
    //$mail->addReplyTo('info@example.com', 'Information');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('bcc@example.com');

    //Attachments
    //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body    = implode("<tr><th scope='col'></th></tr>", $datosProveedores  );
    $mail->AltBody = 'Este es un correo de prueba';

    $mail->send();
    echo '<script>
              alert("Correo Enviado con Éxito");
              window.location = "consultaProveedoresPrepagoRT.php";
              </script>';

} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

 ?>