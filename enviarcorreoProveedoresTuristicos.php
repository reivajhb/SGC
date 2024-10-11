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
  $correo2 =  $_POST['correo2'];
	$subject = $_POST['asunto'];
	$proveedor = $_POST['proveedor'];
	$cop  = $_POST['cop'];
	$novedad = $_POST['novedad'];
	$fecha = $_POST['fecha'];
	$archivo = $_POST['archivo'];
	$soporte = $_POST['soporteProveedor'];
	
	
	$datosProveedores = array('
<!doctype html>
<html lang="en">

<html lang="en">

    <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <!-- SCRIPTS JS-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="peticion.js"></script>



    Cordial saludo,',

'Se realiza envio de soporte de pago junto con la relación de las facturas pagadas, Gracias por su atención',
'<br>',
    '<table border ="1">
  <thead class="thead-dark">
    <tr>
      <th scope="col">#</th>
      <th scope="col">Nombre del Proveedor</th>
      <th scope="col">Valor Pagado</th>
      <th scope="col">Descripción</th>
      <th scope="col">Fecha de registro </th>
      <th scope="col">Valor Pagado</th>
      <th scope="col">Soporte de pago</th>
    </tr>
  </thead>
  <tbody>
    <tr>
    <th scope="row">1</th>
     <td>'."$proveedor".'</td>
     <td>'."$cop".'</td>
     <td>'."$novedad".'</td>
     <td>'."$fecha".'</td>
     <td>'."$archivo".'</td>
     <td>'."$soporte".'</td>
    </tr>
 </tbody>
</table>',

'<H2>Las facturas electronicas solo se reciben en el correo electronico recepcion.panamer@panamericanaviajes.com</H2>',

'Gracias',

'Atentamente,',
  
'Tesoreria Panamericana de viajes sas',
'contabilidad8@panamericanaviajes.com',
'Carrera 11a #93a-80 Ofic. 104',
'Tel: (+57 60 1) 6500 400',
'Bogotá, Colombia',
    
'Para mayor información visite www.turivel.com - www.panamericanadeviajes.net',


'</body>
  
  
</html>');
	
	
	

    //Server settings
    $mail->SMTPDebug = 0;                      //Enable verbose debug output
    $mail->isSMTP();                                           //Send using SMTP
    $mail->Host       = 'email-smtp.us-east-2.amazonaws.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'AKIAYS2NV44SYRA6SBNL';                     //SMTP username
    $mail->Password   = 'BKApQHD4iU6w4isV/sRLOt3ivcsOkXutARfYNHHe3Zow';                              //SMTP password
    $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
    $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('contabilidad8@panamericanaviajes.com', 'Pagos Proveedores turisticos panamericana de viajes');
    $mail->addAddress($correo);  
    $mail->addAddress($correo2);    //Add a recipient
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
              window.location = "consultaProveedoresTuristicos.php";
              </script>';

} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

 ?>