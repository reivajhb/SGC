<?php 
include "seguridad.php"
?>

<?php
include("conexion.php");
include_once 'google-api-php-client--PHP8.0/vendor/autoload.php';


    $claveJSON = '1ykMGKFveWrWurSWhJEEnjMAd5bjZR4xR';
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
        echo '<script>
              alert("Factura cargada con exito");
              window.location = "RegistroFacturasTrasporte.php";
              </script>';
    }catch(Google_Service_Exception $gs){
        $m=json_decode($gs->getMessage());
        echo $m->error->message;
    }catch(Exception $e){
        echo $e->getMessage();
      
    }
        $localizador =  $_POST["localizador"];
        $Tipo_Servicio =  $_POST["Tipo_Servicio"];
        $Valor =  $_POST["Valor"];
        $Vendedor =  $_POST["Vendedor"];
        $correo =  $_POST["correo"];
        $fecha =  $_POST["fecha"];
        $estado =  $_POST["estado"];
        
        

        $ruta = 'https://drive.google.com/open?id=' . $resultadoCargapt->id;

        $insertar = "INSERT INTO tbl_SolicitudFacturacionTransporte  (localizador, Tipo_Servicio, Valor, Vendedor, correo,fecha, estado, soporte) VALUES ('$localizador','$Tipo_Servicio','$Valor', '$Vendedor', '$correo', '$fecha','$estado','$ruta')";
        $resultado = mysqli_query($conn, $insertar);
        if ($resultado) {
             echo '<script>
              alert("Solicitud cargada con exito");
              window.location = "RegistroFacturasTrasporte.php";
              </script>';
        }elseif ($resultado =  0) {

            echo '<script>alert("Error en la carga")</script>';
        }

        



?>



  
