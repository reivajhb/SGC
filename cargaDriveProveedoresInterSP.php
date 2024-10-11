
<?php
include "seguridad.php"
?>

<?php
include("conexion.php");
include_once 'google-api-php-client--PHP8.0/vendor/autoload.php';


// Variables de credenciales.
https: //drive.google.com/drive/folders/1OYIk2dZUWoveke-hKtJom6GTDu-P3N8_?usp=sharing

$claveJSON = '1y9_H6CLWqryXkBo2tGCVXezvu-ao-9P8';
$pathJSON = 'drive-contabilidad-cdd148a483c0.json';
//configurar variable de entorno
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $pathJSON);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes(['https://www.googleapis.com/auth/drive.file']);
try {

  //instanciamos el servicio
  $service = new Google_Service_Drive($client);
  $file_path = $_FILES['soportePrepago']['tmp_name'];
  //instacia de archivo
  $file = new Google_Service_Drive_DriveFile();
  $file->setName($_FILES['soportePrepago']['name']);
  //obtenemos el mime type
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime_type = finfo_file($finfo, $file_path);

  //id de la carpeta donde hemos dado el permiso a la cuenta de servicio 
  $file->setParents(array($claveJSON));
  $file->setDescription('Archivo Cargado a drive con exito');
  $file->setMimeType($mime_type);

  $resultadoCargapt1 = $service->files->create(
    $file,
    array(
      'data' => file_get_contents($file_path),
      'mimeType' => $mime_type,
      'uploadType' => 'media',
    )
  );

  //instanciamos el servicio
        $service = new Google_Service_Drive($client);
        $file_path = $_FILES['relacionpago']['tmp_name'];
        //instacia de archivo
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($_FILES['relacionpago']['name']);
        //obtenemos el mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE); 
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
} catch (Google_Service_Exception $gs) {
  $m = json_decode($gs->getMessage());
  echo $m->error->message;
} catch (Exception $e) {
  echo $e->getMessage();
}


$id_pagoint = $_POST['id_pagoint'];
$fecha   = $_POST['fecha'];
$identificacion  = $_POST['identificacion'];
$proveedor = $_POST['proveedor'];
$email_proveedor = $_POST['email_proveedor'];
$localizador = $_POST['localizador'];
$num_factura = $_POST['num_factura'];
$concepto = $_POST['concepto'];
$descripcion = $_POST['descripcion'];
$moneda = $_POST['moneda'];
$valor = $_POST['valor'];
$usuario = $_POST['usuario'];
$fecha_ingreso = $_POST['fecha_ingreso'];
$certificacion = $_POST['certificacion'];
$fecha_salida = $_POST['fecha_salida'];
$cuentadecobro = $_POST['cuentadecobro'];
$egreso = $_POST['egreso'];
$ValorTotalApagar = $_POST['ValorTotalApagar'];
$estado = $_POST['estado'];
$fecha_Soporte = $_POST['fecha_Soporte'];



$ruta1 = 'https://drive.google.com/open?id=' . $resultadoCargapt1->id;
$ruta2 = 'https://drive.google.com/open?id=' . $resultadoCargapt2->id;

$editarProveedorPrepago = "UPDATE tbl_pagos_inter SET fecha='$fecha', identificacion='$identificacion',
                                 proveedor='$proveedor', email_proveedor='$email_proveedor' , localizador='$localizador',
                                num_factura='$num_factura', concepto='$concepto', descripcion='$descripcion', 
                                moneda='$moneda', valor='$valor', usuario='$usuario', fecha_ingreso='$fecha_ingreso', 
                                certificacion='$certificacion', fecha_salida='$fecha_salida', cuentadecobro='$cuentadecobro', 
                                ValorTotalApagar='$ValorTotalApagar', 
                                estado='$estado', egreso='$egreso', 
                                soportePrepago='$ruta1', fecha_Soporte='$fecha_Soporte', relacionpago='$ruta2' where id_pagoint='$id_pagoint' ";

$resultado = mysqli_query($conn, $editarProveedorPrepago);
echo '<script>
              alert("Pago actualizado con exito");
              </script>';

$consulta = "SELECT * FROM tbl_pagos_inter where id_pagoint='$id_pagoint' ";
$ejecutar = mysqli_query($conn, $consulta);
$mostrarProveedor = mysqli_fetch_array($ejecutar);

echo "

             <html>
<head>
    <!-- Required meta tags -->
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>

    <!-- Bootstrap CSS -->
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css' integrity='sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm' crossorigin='anonymous'>

    <title>Enviar Email</title>

  </head>
<body  class = 'row align-item-center justify-content-center vh-100'>



  <div class='row align-items-center'>
    
    <div class='col'>
    <a href='formularioenviocorreoproveedorInter.php?id_pagoint= " . $mostrarProveedor['id_pagoint'] . "''>
    <button type='button' class='btn btn-danger btn-lg'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-envelope-plus-fill' viewBox='0 0 16 16'>
  <path d='M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.026A2 2 0 0 0 2 14h6.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586l-1.239-.757ZM16 4.697v4.974A4.491 4.491 0 0 0 12.5 8a4.49 4.49 0 0 0-1.965.45l-.338-.207L16 4.697Z'/>
  <path d='M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z'/>
</svg> Dar click para enviar el correo electronico obligatorio</button></a></div>
   
  </div>
  
</div>

</body>
</html>

  ";









?>



  
