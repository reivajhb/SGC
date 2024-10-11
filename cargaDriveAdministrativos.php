
<?php 
include "seguridad.php"
?>

<?php
include("conexion.php");
include_once 'google-api-php-client--PHP8.0/vendor/autoload.php';


    // Variables de credenciales.

    $claveJSON = '15gdBiJdu8sN-JT5AaZQDMZejKNg2zSGy';
    $pathJSON = 'drive-contabilidad-cdd148a483c0.json';
    //configurar variable de entorno
    putenv('GOOGLE_APPLICATION_CREDENTIALS='.$pathJSON);

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->setScopes(['https://www.googleapis.com/auth/drive.file']);
    try{		
        //instanciamos el servicio
        $service = new Google_Service_Drive($client);
        $file_path = $_FILES['facturaPagosAdministrativos']['tmp_name'];
        //instacia de archivo
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($_FILES['facturaPagosAdministrativos']['name']);
        //obtenemos el mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE); 
        $mime_type=finfo_file($finfo, $file_path);

        //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
        $file->setParents(array($claveJSON));
        $file->setDescription('Archivo Cargado a drive con exito');
        $file->setMimeType($mime_type);

        $resultadoCargapa = $service->files->create(
          $file,
          array(
            'data' => file_get_contents($file_path),
            'mimeType' => $mime_type,
            'uploadType' => 'media',
          )
        );
        /* FICHERO SUBIDO A GOOGLE DRIVE */
       
    }catch(Google_Service_Exception $gs){
        $m=json_decode($gs->getMessage());
        echo $m->error->message;
    }catch(Exception $e){
        echo $e->getMessage();
      
    }
        $identificacion = $_POST['identificacion'];
        $email_contabilidad = $_POST['email_contabilidad'];
        $locacion =  $_POST["locacion"];
        $valor  =  $_POST["valor"];
        $novedad =  $_POST["novedad"];
        $fecha =  $_POST["fecha"];
        $estado = $_POST["estado"];
        $id_proveedor_administrativo = $_POST["id_proveedor_administrativo"];
        $id_usuario_fo = $_POST["id_usuario_fo"];

        $ruta = 'https://drive.google.com/open?id=' . $resultadoCargapa->id;

        $insertar = "INSERT INTO tbl_pagos_administrativos (locacion, valor, novedad, fecha, archivo, estado, identificacion, email_contabilidad, id_proveedoradmin_fo,id_usuario_fo) VALUES ('$locacion','$valor','$novedad','$fecha', '$ruta', '$estado','$identificacion','$email_contabilidad','$id_proveedor_administrativo','$id_usuario_fo')";
        $resultado = mysqli_query($conn, $insertar);
        if ($resultado) {
              echo '<script>
              alert("Pago cargado con exito");
              window.location = "consultaPagosAdministrativos.php";
              </script>';

        }elseif ($resultado =  0) {

            echo '<script>alert("Error en la carga")</script>';
        }



?>



  
