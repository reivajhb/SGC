<?php 
include "seguridad.php"

 ?>
 
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$mail = new PHPMailer(true);	

try {

 
 
	$correo =  $_POST['correo'];
	$subject = $_POST['asunto'];
	$localizador = $_POST['localizador'];
	$Tipo_Servicio  = $_POST['Tipo_Servicio'];
	$Valor = $_POST['Valor'];
  $Vendedor = $_POST['Vendedor'];
	$fecha = $_POST['fecha'];
	$factura = $_POST['factura'];
	
	
	
	$datos = array('Cordial saludo,','Se realiza envio de soporte de pago junto con la relacion de las facturas , Gracias por su atención',
    '<H1>localizador</H1>','<h3>'."$localizador".'</h3>',
    '<H1>Tipo_Servicio</H1>', '<h3>'."$Tipo_Servicio".'</h3>', 
    '<H1>Valor</H1>','<h3>'."$Valor".'</h3>', 
    '<H1>Vendedor</H1>','<h3>'."$Vendedor".'</h3>',
    '<H1>Fecha de registro</H1>', '<h3>'."$fecha".'</h3>',
    '<H1>Facturas de venta</H1>', '<h3>'."$factura".'</h3>', 
    'Gracias

Atentamente,


  
Tesoreria Panamericana de viajes sas

contabilidad8@panamericanaviajes.com
Carrera 11a #93a-80 Ofic. 104
Tel: (+57 60 1) 6500 400 
Bogotá, Colombia
    
Para mayor información visite www.turivel.com - www.panamericanadeviajes.net');
	
	
	

    //Server settings
    $mail->SMTPDebug = 0;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'contabilidad8@panamericanaviajes.com';                     //SMTP username
    $mail->Password   = '%+qw633Dl}Hq';                               //SMTP password
    $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
    $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('contabilidad8@panamericanaviajes.com', 'Factura de venta Panamericana de viajes');
    $mail->addAddress($correo);     //Add a recipient
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
    $mail->Body    = implode($datos);
    $mail->AltBody = 'Este es un correo de prueba';

    $mail->send();
    echo '<script>
              alert("Correo Enviado con Éxito");
              window.location = "consultaSolicitudesFacturacionTransporte.php";
              </script>';

} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

 ?>