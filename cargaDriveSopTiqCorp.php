
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
        $mime_type=finfo_file($finfo, $file_path);
        if (empty($file_path)) {
             $file_path= "Sin dato";
             $mime_type=finfo_file($finfo, $file_path);

        //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
        $file->setParents(array($claveJSON));
        $file->setDescription('Archivo Cargado a drive con exito');
        $file->setMimeType($mime_type);

        $resultadoCargapt = "Sin dato";
        /* FICHERO SUBIDO A GOOGLE DRIVE */
        
    }else{
        $mime_type=finfo_file($finfo, $file_path);
       
      

        //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
        $file->setParents(array($claveJSON));
        $file->setDescription('Archivo Cargado a drive con exito');
        $file->setMimeType($mime_type);

        $resultadoCargapt = $service->files->create(
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
        $file_path = $_FILES['tiquete']['tmp_name'];
        //instacia de archivo
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($_FILES['tiquete']['name']);
        //obtenemos el mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE); 
    
         if (empty($file_path)) {
          $file_path = "Sin dato";
          $mime_type=finfo_file($finfo, $file_path); 
        //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
        $file->setParents(array($claveJSON));
        $file->setDescription('Archivo Cargado a drive con exito');
        $file->setMimeType($mime_type);

        $resultadoCargapt2 = "Sin datos";
        /* FICHERO SUBIDO A GOOGLE DRIVE */
        
    }else{

        $mime_type=finfo_file($finfo, $file_path); 
        //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
        $file->setParents(array($claveJSON));
        $file->setDescription('Archivo Cargado a drive con exito');
        $file->setMimeType($mime_type);

        $resultadoCargapt2 = $service->files->create(
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
        $file_path = $_FILES['ordencompra']['tmp_name'];
        //instacia de archivo
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($_FILES['ordencompra']['name']);
        //obtenemos el mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE); 
           if (empty($file_path)) {
          $file_path = "Sin dato";
          $mime_type=finfo_file($finfo, $file_path); 
       

        //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
        $file->setParents(array($claveJSON));
        $file->setDescription('Archivo Cargado a drive con exito');
        $file->setMimeType($mime_type);

        $resultadoCargapt3 = "Sin datos";
        /* FICHERO SUBIDO A GOOGLE DRIVE */
       
    }else{
         $mime_type=finfo_file($finfo, $file_path); 
       

        //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
        $file->setParents(array($claveJSON));
        $file->setDescription('Archivo Cargado a drive con exito');
        $file->setMimeType($mime_type);

        $resultadoCargapt3 = $service->files->create(
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
        $nit            =  $_POST["nit"];
        $nom_clien_corp =  $_POST["nom_clien_corp"];
        $localizador    =  $_POST["localizador"];
        $novedad        =  $_POST["novedad"];
        $fecha          =  $_POST["fecha"];
        $email_interesado =  $_POST["email_interesado"];
        $estado         =  $_POST["estado"];
        $tarifadmin     =  $_POST["tarifadmin"];
        $record     =  $_POST["record"];
        $formadepago    =  $_POST["formadepago"];
        $fechafac       =  $_POST["fechafac"];
        

        $ruta = 'https://drive.google.com/open?id=' . $resultadoCargapt->id;
        $ruta2 = 'https://drive.google.com/open?id=' . $resultadoCargapt2->id;
        $ruta3 = 'https://drive.google.com/open?id=' . $resultadoCargapt3->id;


        $insertar = "INSERT INTO tbl_facturas_corp (nit, nom_clien_corp,localizador, novedad, fecha,  soporte, email_interesado, tiquete, estado, tarifadmin, record, formadepago, fechafac, ordencompra) VALUES ('$nit','$nom_clien_corp','$localizador','$novedad','$fecha', '$ruta', '$email_interesado', '$ruta2', '$estado', '$tarifadmin', '$record', '$formadepago', '$fechafac', '$ruta3')";
        $resultado = mysqli_query($conn, $insertar);
        if ($resultado) {
             echo '<script>
              alert("Solicitud cargada con exito");
              window.location = "buscarClienteCorpFac.php";
              </script>';
        }elseif ($resultado =  0) {

            echo '<script>alert("Error en la carga")</script>';
        }

        

 
 

 use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$mail = new PHPMailer(true);    

try {

 

 
    $correo =  "facturador2@panamericanaviajes.com";
    $subject = "Solicitud Facturación Tiquetes";
        $nit            =  $_POST["nit"];
        $nom_clien_corp =  $_POST["nom_clien_corp"];
        $localizador    =  $_POST["localizador"];
        $novedad        =  $_POST["novedad"];
        $fecha          =  $_POST["fecha"];
        $email_interesado =  $_POST["email_interesado"];
        $estado         =  $_POST["estado"];
        $tarifadmin     =  $_POST["tarifadmin"];
        $record     =  $_POST["record"];
        $formadepago    =  $_POST["formadepago"];
        $fechafac       =  $_POST["fechafac"];
    
    
    
    $datos = array('Cordial saludo,','Se realiza envio de solicitud de facturación  Tiquetes, Gracias por su atención',
    '<H1>Nombre del Cliente</H1>','<h3>'."$nom_clien_corp".'</h3>',
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
    $mail->setFrom('facturador2@panamericanaviajes.com', 'Solicitud Facturación Tiquetes');
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



  
