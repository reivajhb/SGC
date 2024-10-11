
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
        $file_path = $_FILES['factura']['tmp_name'];
        //instacia de archivo
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($_FILES['factura']['name']);
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
              alert("Factura Cargada con exito");
              window.location = "consultaFacturasCorp.php";
              </script>';
    }catch(Google_Service_Exception $gs){
        $m=json_decode($gs->getMessage());
        echo $m->error->message;
    }catch(Exception $e){
        echo $e->getMessage();
      
    }
        $id_facturas_corp  =  $_POST["id_facturas_corp"];
        $nit               =  $_POST["nit"];
        $nom_clien_corp    =  $_POST["nom_clien_corp"];
        $fecha             =  $_POST["fecha"];
        $email_interesado  =  $_POST["email_interesado"];
        $localizador       =  $_POST["localizador"];
        $novedad           =  $_POST["novedad"];
        $estado            =  $_POST["estado"];
        $tarifadmin        =  $_POST["tarifadmin"];
        $record            =  $_POST['record'];
        $formadepago       =  $_POST['formadepago'];
        $fechafac          =  $_POST['fechafac'];
        $ordencompra       =  $_POST['ordencompra'];  
    

        $ruta = 'https://drive.google.com/open?id=' . $resultadoCargapt->id;

        $editarFacturaCorp = "UPDATE tbl_facturas_corp SET nit='$nit', nom_clien_corp='$nom_clien_corp', localizador='$localizador',  novedad='$novedad', fecha='$fecha',email_interesado='$email_interesado', estado='$estado', factura='$ruta', tarifadmin='$tarifadmin', record='$record', formadepago='$formadepago', fechafac='$fechafac', ordencompra='$ordencompra' where id_facturas_corp='$id_facturas_corp' "; 
        $resultado = mysqli_query($conn, $editarFacturaCorp);
        if ($resultado) {
             echo '<script>
              alert("Factura Cargada con exito");
              window.location = "consultaFacturasCorp.php";
              </script>';

        }elseif ($resultado =  0) {

            echo '<script>alert("Error en la carga")</script>';
        }




?>
