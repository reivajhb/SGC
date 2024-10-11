<?php 
include "seguridad.php"
?>

<?php
include("conexion.php");
include_once 'google-api-php-client--PHP8.0/vendor/autoload.php';


    // Variables de credenciales.
    $claveJSON = '1KeEPpC7KT32uuMxxhV8YNgABdWxPn0l-';
    $pathJSON = 'drive-contabilidad-cdd148a483c0.json';
    //configurar variable de entorno
    putenv('GOOGLE_APPLICATION_CREDENTIALS='.$pathJSON);

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->setScopes(['https://www.googleapis.com/auth/drive.file']);
    try{		
        //instanciamos el servicio
        $service = new Google_Service_Drive($client);
        $file_path = $_FILES['soporteAdmin']['tmp_name'];
        //instacia de archivo
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($_FILES['soporteAdmin']['name']);
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
              window.location = "consultaPagosAdministrativos.php";
              </script>';
    }catch(Google_Service_Exception $gs){
        $m=json_decode($gs->getMessage());
        echo $m->error->message;
    }catch(Exception $e){
        echo $e->getMessage();
      
    }
        $id_pago_ad  =  $_POST["id_pago_ad"];
        $locacion =  $_POST["locacion"];
        $valor  =  $_POST["valor"];
        $novedad =  $_POST["novedad"];
        $fecha =  $_POST["fecha"];
        $estado = $_POST["estado"];


        $ruta = 'https://drive.google.com/open?id=' . $resultadoCarga->id;

        $editarPa = "UPDATE tbl_pagos_administrativos SET locacion='$locacion', valor='$valor', novedad='$novedad', fecha='$fecha' , estado='$estado', soporteAdmin='$ruta' where id_pago_ad='$id_pago_ad' ";
        $resultado = mysqli_query($conn, $editarPa);
        if ($resultado) {
              echo '<script>
              alert("Pago cargado con exito");
              window.location = "consultaPagosAdministrativos.php";
              </script>';

        }elseif ($resultado =  0) {

            echo '<script>alert("Error en la carga")</script>';
        }



?>



  
