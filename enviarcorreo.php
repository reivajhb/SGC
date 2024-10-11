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
	$Hotel = $_POST['Hotel'];
	$Cop  = $_POST['Cop'];
	$Novedad = $_POST['Novedad'];
	$Fecha = $_POST['Fecha'];
	$archivo = $_POST['archivo'];
	$soporte = $_POST['soporte'];
	
	
	$datos = array('Cordial saludo,','Se realiza envio de soporte de pago junto con la relacion de las facturas pagadas, Gracias por su atención',
    '<H1>Nombre del hotel</H1>','<h3>'."$Hotel".'</h3>',
    '<H1>Valor pagado</H1>', '<h3>'."$Cop".'</h3>', 
    '<H1>Descripción</H1>','<h3>'."$Novedad".'</h3>', 
    '<H1>Fecha de registro</H1>', '<h3>'."$Fecha".'</h3>',
    '<H1>Relación de facturas</H1>', '<h3>'."$archivo".'</h3>', 
    '<H1>Soporte de pago</H1>','<h3>'."$soporte".'<h3>','Gracias

Atentamente,


  
Tesoreria Panamericana de viajes sas
Ingeniero de sistemas
contabilidad8@panamericanaviajes.com
Carrera 11a #93a-80 Ofic. 104
Tel: (+57 60 1) 6500 400 Ext: 102
Bogotá, Colombia
    
Para mayor información visite www.turivel.com - www.panamericanadeviajes.net');
	
	
	

    //Server settings
    $mail->SMTPDebug = 0;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'contabilidad8@panamericanaviajes.com';                     //SMTP username
    $mail->Password   = '3obb);M>4J"5';                               //SMTP password
    $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
    $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('contabilidad8@panamericanaviajes.com', 'Pagos Hoteles Panamericana de viajes');
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
    $mail->Body    = implode($datos  );
    $mail->AltBody = 'Este es un correo de prueba';

    $mail->send();
    echo '<script>
              alert("Correo Enviado con Éxito");
              window.location = "consulta.php";
              </script>';

} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

 ?>