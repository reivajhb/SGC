<?php 
include "seguridad.php"
?>

<?php
include("conexion.php");
include_once 'google-api-php-client--PHP8.0/vendor/autoload.php';


    // Variables de credenciales.

    $claveJSON = '1QG_C8MMMsITdM7XXIO39A5PlVhPhCWDw';
    $pathJSON = 'drive-contabilidad-cdd148a483c0.json';
    //configurar variable de entorno
    putenv('GOOGLE_APPLICATION_CREDENTIALS='.$pathJSON);

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->setScopes(['https://www.googleapis.com/auth/drive.file']);
    try{		
        //instanciamos el servicio
        $service = new Google_Service_Drive($client);
        $file_path = $_FILES['soporte']['tmp_name'];
        //instacia de archivo
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($_FILES['soporte']['name']);
        //obtenemos el mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE); 
        
        if (empty($file_path)) {
             $file_path= "Sin dato";
             $mime_type=finfo_file($finfo, $file_path);
       
        
     
        //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
        $file->setParents(array($claveJSON));
        $file->setDescription('Archivo Cargado a drive con exito');
        $file->setMimeType($mime_type);

        $resultadoCargapt = "Sin datos";
        /* FICHERO SUBIDO A GOOGLE DRIVE */
        
    }else{
        $mime_type=finfo_file($finfo, $file_path);
       
        
     
        //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
        $file->setParents(array($claveJSON));
        $file->setDescription('Archivo Cargado a drive con exito');
        $file->setMimeType($mime_type);

        $resultadoCargapt = "Sin datos";
        /* FICHERO SUBIDO A GOOGLE DRIVE */
        

    }

    }catch(Google_Service_Exception $gs){
        $m=json_decode($gs->getMessage());
        echo $m->error->message;
    }catch(Exception $e){
        echo $e->getMessage();
      
    }
        
     try{        
        //instanciamos el servicio
        $service = new Google_Service_Drive($client);
        $file_path = $_FILES['OrdenCompra']['tmp_name'];
        //instacia de archivo
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($_FILES['OrdenCompra']['name']);
        //obtenemos el mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if (empty($file_path)) {
          $file_path = "Sin dato";
          $mime_type=finfo_file($finfo, $file_path); 
      

        //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
        $file->setParents(array($claveJSON));
        $file->setDescription('Archivo Cargado a drive con exito');
        $file->setMimeType($mime_type);

        $resultadoCargaptOrdenCompra = "Sin datos";
        /* FICHERO SUBIDO A GOOGLE DRIVE */
        
    }else{
        $mime_type=finfo_file($finfo, $file_path); 
      

        //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
        $file->setParents(array($claveJSON));
        $file->setDescription('Archivo Cargado a drive con exito');
        $file->setMimeType($mime_type);

        $resultadoCargaptOrdenCompra = $service->files->create(
          $file,
          array(
            'data' => file_get_contents($file_path),
            'mimeType' => $mime_type,
            'uploadType' => 'media',
          )
        );
        /* FICHERO SUBIDO A GOOGLE DRIVE */
    }
    }catch(Google_Service_Exception $gs){
        $m=json_decode($gs->getMessage());
        echo $m->error->message;
    }catch(Exception $e){
        echo $e->getMessage();
      
    }

     try{        
        //instanciamos el servicio
        $service = new Google_Service_Drive($client);
        $file_path = $_FILES['soporteproveedor']['tmp_name'];
        //instacia de archivo
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($_FILES['soporteproveedor']['name']);
        //obtenemos el mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE); 
        
        if (empty($file_path)) {
            $file_path = "Sin dato";
            $mime_type=finfo_file($finfo, $file_path);
        
        //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
        $file->setParents(array($claveJSON));
        $file->setDescription('Archivo Cargado a drive con exito');
        $file->setMimeType($mime_type);

        $resultadoCargaptspp = "Sin datos";
        /* FICHERO SUBIDO A GOOGLE DRIVE */
        
    }else{
        $mime_type=finfo_file($finfo, $file_path);
        
        //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
        $file->setParents(array($claveJSON));
        $file->setDescription('Archivo Cargado a drive con exito');
        $file->setMimeType($mime_type);

        $resultadoCargaptspp = $service->files->create(
          $file,
          array(
            'data' => file_get_contents($file_path),
            'mimeType' => $mime_type,
            'uploadType' => 'media',
          )
        );
        /* FICHERO SUBIDO A GOOGLE DRIVE */
    }
    }catch(Google_Service_Exception $gs){
        $m=json_decode($gs->getMessage());
        echo $m->error->message;
    }catch(Exception $e){
        echo $e->getMessage();
      
    }
        


    $nit         =  $_POST["nit"];
    $nom_cliente =  $_POST["nom_cliente"];
    $localizador =  $_POST["localizador"];
    $novedad     =  $_POST["novedad"];
    $fecha       =  $_POST["fecha"];
    $email_interesado =  $_POST["email_interesado"];
    $estado           =  $_POST["estado"];
    $valor            = $_POST['valor'];
    $fee              = $_POST['fee'];
    $formadepago      = $_POST['formadepago'];
    $tipomoneda       = $_POST['tipomoneda'];
    $fechafac         = $_POST['fechafac'];
    $valoraloj         = $_POST['valoraloj'];
    $valoralim         = $_POST['valoralim'];
    

    $ruta = 'https://drive.google.com/open?id=' . $resultadoCargapt->id;
    $ruta2 = 'https://drive.google.com/open?id=' . $resultadoCargaptOrdenCompra->id;
    $ruta3 = 'https://drive.google.com/open?id=' . $resultadoCargaptspp->id;
    

    $insertar = "INSERT INTO tbl_solicitudfacturacionHAA (nit, nom_cliente, localizador, novedad, fecha, soporte, email_interesado, estado, valor, fee, formadepago, tipomoneda, fechafac, valoraloj, valoralim, soporteproveedor,OrdenCompra) VALUES ('$nit','$nom_cliente','$localizador','$novedad','$fecha', '$ruta', '$email_interesado', '$estado', '$valor', '$fee', '$formadepago', '$tipomoneda', '$fechafac', '$valoraloj', '$valoralim', '$ruta3', '$ruta2')";
    $resultado = mysqli_query($conn, $insertar);
    
         echo '<script>
          alert("Solicitud cargada con exito");
          window.location = "buscarClienteHAAFac.php";
          </script>';
 
   
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$mail = new PHPMailer(true);    

try {

 

 
    $correo =  "facturador2@panamericanaviajes.com";
    $subject = "Solicitud Facturación Hoteles, Alojamientos y alimentación";
    $nit     = $_POST['nit'];
    $nom_cliente  = $_POST['nom_cliente'];
    $localizador     = $_POST['localizador'];
  $novedad         = $_POST['novedad'];
    $fecha           = $_POST['fecha'];
    $factura         = $_POST['factura'];
    
    
    
    $datos = array('Cordial saludo,','Se realiza envio de solicitud de facturación  Hoteles, Alojamientos y alimentación, Gracias por su atención',
    '<H1>Nombre del Cliente</H1>','<h3>'."$nom_cliente".'</h3>',
    '<H1>Localizador</H1>', '<h3>'."$localizador".'</h3>', 
    '<H1>Descripción</H1>','<h3>'."$novedad".'</h3>', 
    '<H1>Fecha de registro</H1>', '<h3>'."$fecha".'</h3>',
    
    'Gracias

Cordialmente

    
Liliana Velandia Mateus
Líder de Facturación 
facturador2@panamericanaviajes.com
Carrera 11a #93a-80 Ofic. 104
Tel: (+57 60 1) 6500 400 Ext: 102
Bogotá, Colombia
 
Para mayor información visite www.turivel.com - www.panamericanadeviajes.net');
    
    
    

    //Server settings
    $mail->SMTPDebug = 0;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'facturador2@panamericanaviajes.com';                     //SMTP username
    $mail->Password   = 'Bogota2021+';                               //SMTP password
    $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
    $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('facturador2@panamericanaviajes.com', 'Solicitud Facturación Hoteles, Alojamientos y alimentación');
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



  
