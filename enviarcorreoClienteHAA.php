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

 

 
	$correo  =  $_POST['correo'];
	$subject = $_POST['asunto'];
	$nit     = $_POST['nit'];
	$nom_cliente  = $_POST['nom_cliente'];
	$localizador     = $_POST['localizador'];
  $novedad         = $_POST['novedad'];
	$fecha           = $_POST['fecha'];
	$factura         = $_POST['factura'];
	
	
	
	$datos = array('Cordial saludo,','Se realiza envio de soporte de pago junto con la relacion de las facturas , Gracias por su atención',
    '<H1>Nombre del Cliente</H1>','<h3>'."$nom_cliente".'</h3>',
    '<H1>Localizador</H1>', '<h3>'."$localizador".'</h3>', 
    '<H1>Descripción</H1>','<h3>'."$novedad".'</h3>', 
    '<H1>Fecha de registro</H1>', '<h3>'."$fecha".'</h3>',
    '<H1>Facturas de venta</H1>', '<h3>'."$factura".'</h3>', 
    'Gracias

Atentamente,


  
Agencia Panamericana de viajes

agencia@panamericanaviajes.com
Carrera 11a #93a-80 Ofic. 104
Tel: (+57 60 1) 6500 400 Ext: 100
Bogotá, Colombia
    
Para mayor información visite www.turivel.com - www.panamericanadeviajes.net');
	
	
	

    //Server settings
    $mail->SMTPDebug = 0;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'agencia@panamericanaviajes.com';                     //SMTP username
    $mail->Password   = 'Bogota2024+';                               //SMTP password
    $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
    $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('agencia@panamericanaviajes.com', 'Factura de venta Panamericana de viajes');
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
              window.location = "consultaSolicitudesHAA.php";
              </script>';

} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

 ?>