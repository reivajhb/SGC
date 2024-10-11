<?php 
include "seguridad.php";
include "conexion.php";
include_once 'google-api-php-client--PHP8.0/vendor/autoload.php';

// Variables de credenciales.
$claveJSON = '1muXXYjfW4h-I7NBRJfsA9MgWSa4GiCpQ';
$pathJSON = 'drive-contabilidad-cdd148a483c0.json';

// Configurar variable de entorno
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $pathJSON);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes(['https://www.googleapis.com/auth/drive.file']);

try {
    // Instanciar el servicio
    $service = new Google_Service_Drive($client);

    // Función para cargar archivo a Google Drive
    function uploadFileToDrive($service, $fileInputName, $folderId) {
        $file_path = $_FILES[$fileInputName]['tmp_name'];
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($_FILES[$fileInputName]['name']);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);
        $file->setParents([$folderId]);
        $file->setDescription('Archivo cargado a drive con éxito');
        $file->setMimeType($mime_type);

        return $service->files->create(
            $file,
            [
                'data' => file_get_contents($file_path),
                'mimeType' => $mime_type,
                'uploadType' => 'media',
            ]
        );
    }

    $resultadoCarga = uploadFileToDrive($service, 'soporteProveedor', $claveJSON);

    // Mensaje de éxito
    echo '<script>
        alert("Pago cargado con éxito");
        window.location = "consultaProveedoresTuristicos.php";
        </script>';
} catch (Google_Service_Exception $gs) {
    $m = json_decode($gs->getMessage());
    echo $m->error->message;
    exit;
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}

// Validar y sanitizar entradas
$id_proveedor = mysqli_real_escape_string($conn, $_POST["id_proveedor"]);
$proveedor = mysqli_real_escape_string($conn, $_POST["proveedor"]);
$cop = mysqli_real_escape_string($conn, $_POST["cop"]);
$novedad = mysqli_real_escape_string($conn, $_POST["novedad"]);
$fecha = mysqli_real_escape_string($conn, $_POST["fecha"]);
$estado = mysqli_real_escape_string($conn, $_POST["estado"]);

// Escapar y formatear la fecha y hora para el formato de MySQL
$fechaFormatoMySQL = date("Y-m-d H:i:s", strtotime($fecha));

$ruta = 'https://drive.google.com/open?id=' . $resultadoCarga->id;

// Preparar la consulta
$editarProveedor = "UPDATE tbl_proveedores_turtisticos SET proveedor=?, cop=?, novedad=?, fecha=?, estado=?, soporteProveedor=? WHERE id_proveedor=?";

if ($stmt = mysqli_prepare($conn, $editarProveedor)) {
    mysqli_stmt_bind_param($stmt, 'ssssssi', $proveedor, $cop, $novedad, $fechaFormatoMySQL, $estado, $ruta, $id_proveedor);

    if (mysqli_stmt_execute($stmt)) {
        echo '<script>
            alert("Pago cargado con éxito");
            window.location = "consultaProveedoresTuristicos.php";
            </script>';
    } else {
        echo '<script>alert("Error en la carga")</script>';
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
