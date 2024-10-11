<?php
include "seguridad.php";
include "conexion.php";
include_once 'google-api-php-client--PHP8.0/vendor/autoload.php';

// Variables de credenciales.
$claveJSON = '1KeEPpC7KT32uuMxxhV8YNgABdWxPn0l-';
$pathJSON = 'drive-contabilidad-cdd148a483c0.json';

// Configurar la variable de entorno
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $pathJSON);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes(['https://www.googleapis.com/auth/drive.file']);

try {
    // Instanciar el servicio
    $service = new Google_Service_Drive($client);
    $file_path = $_FILES['soporteAdmin']['tmp_name'];
    
    // Instancia de archivo
    $file = new Google_Service_Drive_DriveFile();
    $file->setName($_FILES['soporteAdmin']['name']);
    
    // Obtener el tipo MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE); 
    $mime_type = finfo_file($finfo, $file_path);

    // ID de la carpeta donde hemos dado el permiso a la cuenta de servicio 
    $file->setParents([$claveJSON]);
    $file->setDescription('Archivo Cargado a drive con éxito');
    $file->setMimeType($mime_type);

    $resultadoCarga = $service->files->create(
        $file,
        [
            'data' => file_get_contents($file_path),
            'mimeType' => $mime_type,
            'uploadType' => 'media',
        ]
    );
    
    // Mensaje de éxito
    echo '<script>
        alert("Pago cargado con éxito");
        window.location = "consultaPagosAdministrativos.php";
        </script>';
} catch (Google_Service_Exception $gs) {
    $m = json_decode($gs->getMessage());
    echo $m->error->message;
} catch (Exception $e) {
    echo $e->getMessage();
}

// Obtener datos del formulario
$id_pago_ad = $_POST["id_pago_ad"];
$locacion = $_POST["locacion"];
$valor = $_POST["valor"];
$novedad = $_POST["novedad"];
$fecha = $_POST["fecha"];
$estado = $_POST["estado"];

// Construir la URL del archivo en Google Drive
$ruta = 'https://drive.google.com/open?id=' . $resultadoCarga->id;

// Consulta preparada para la actualización de datos
$editarPa = "UPDATE tbl_pagos_administrativos SET locacion=?, valor=?, novedad=?, fecha=?, estado=?, soporteAdmin=? WHERE id_pago_ad=?";
$stmt = mysqli_prepare($conn, $editarPa);

// Vincular parámetros a la declaración
mysqli_stmt_bind_param($stmt, "ssssssi", $locacion, $valor, $novedad, $fecha, $estado, $ruta, $id_pago_ad);

// Ejecutar la declaración
$resultado = mysqli_stmt_execute($stmt);

// Verificar si la actualización fue exitosa
if ($resultado) {
    echo '<script>
        alert("Pago actualizado con éxito");
        window.location = "consultaPagosAdministrativos.php";
        </script>';
} else {
    echo '<script>alert("Error en la actualización")</script>';
}

// Cerrar la declaración
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
