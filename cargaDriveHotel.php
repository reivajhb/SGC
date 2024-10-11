
<?php 
include "seguridad.php"
?>
<?php

include("conexion.php");
include_once 'google-api-php-client--PHP8.0/vendor/autoload.php';


    // Variables de credenciales.
    $claveJSON = '1t36Yc6G4myjs4E2hYyGwnOZy4ERkSBRP';
    $pathJSON = 'drive-contabilidad-cdd148a483c0.json';
    //configurar variable de entorno
    putenv('GOOGLE_APPLICATION_CREDENTIALS='.$pathJSON);

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->setScopes(['https://www.googleapis.com/auth/drive.file']);
    try{		
        //instanciamos el servicio
        $service = new Google_Service_Drive($client);
        $file_path = $_FILES['facturaHotel']['tmp_name'];
        //instacia de archivo
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($_FILES['facturaHotel']['name']);
        //obtenemos el mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE); 
        $mime_type=finfo_file($finfo, $file_path);

        //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
        $file->setParents(array($claveJSON));
        $file->setDescription('Archivo Cargado a drive con exito');
        $file->setMimeType($mime_type);

        $resultadoCarga = $service->files->create(
          $file,
          array(
            'data' => file_get_contents($file_path),
            'mimeType' => $mime_type,
            'uploadType' => 'media',
          )
        );
        /* FICHERO SUBIDO A GOOGLE DRIVE */
        echo '<script>
              alert("Pago cargado con exito");
              window.location = "formularioCargaDriveHotel.php";
              </script>';
    }catch(Google_Service_Exception $gs){
        $m=json_decode($gs->getMessage());
        echo $m->error->message;
    }catch(Exception $e){
        echo $e->getMessage();
      
    }

        $Hotel =  $_POST["Hotel"];
        $Cop  =  $_POST["Cop"];
        $Novedad =  $_POST["Novedad"];
        $Fecha =  $_POST["Fecha"];
        $estado = $_POST["estado"];
        $documento = $_FILES["documento"];

        $ruta = 'https://drive.google.com/open?id=' . $resultadoCarga->id;

        $insertar = "INSERT INTO tbl_hoteles (Hotel, Cop, Novedad, Fecha, archivo, estado) VALUES ('$Hotel','$Cop','$Novedad','$Fecha', '$ruta', '$estado')";
        $resultado = mysqli_query($conn, $insertar);
        if ($resultado) {
              echo '<script>
              alert("Pago cargado con exito");
              window.location = "formularioCargaDriveHotel.php";
              </script>';

        }elseif ($resultado =  0) {

            echo '<script>alert("Error en la carga")</script>';
        }



?>



  
