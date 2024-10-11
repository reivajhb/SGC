<?php
include "seguridad.php";
include "conexion.php";
include_once 'google-api-php-client--PHP8.0/vendor/autoload.php';

// Variables de credenciales.
$claveJSON = '1OYIk2dZUWoveke-hKtJom6GTDu-P3N8_';
$pathJSON = 'drive-contabilidad-cdd148a483c0.json';

// Configurar la variable de entorno
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $pathJSON);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes(['https://www.googleapis.com/auth/drive.file']);

try {
    // Instanciar el servicio
    $service = new Google_Service_Drive($client);
    $file_path = $_FILES['facturaProveedorTuristico']['tmp_name'];
    
    // Instancia de archivo
    $file = new Google_Service_Drive_DriveFile();
    $file->setName($_FILES['facturaProveedorTuristico']['name']);
    
    // Obtener el tipo MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE); 
    $mime_type = finfo_file($finfo, $file_path);

    // ID de la carpeta donde hemos dado el permiso a la cuenta de servicio 
    $file->setParents([$claveJSON]);
    $file->setDescription('Archivo Cargado a drive con exito');
    $file->setMimeType($mime_type);

    $resultadoCargapt = $service->files->create(
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
        window.location = "buscarProveedor.php";
        </script>';
} catch (Google_Service_Exception $gs) {
    $m = json_decode($gs->getMessage());
    echo $m->error->message;
} catch (Exception $e) {
    echo $e->getMessage();
}

// Obtener datos del formulario
$proveedor = $_POST["proveedor"];
$cop = $_POST["cop"];
$novedad = $_POST["novedad"];
$fecha = $_POST["fecha"];
$estado = $_POST["estado"];
$nit = $_POST["nit"];
$email_contabilidad = $_POST["email_contabilidad"];
$email_cartera = $_POST["email_cartera"];
$id_proveedor_pdv = $_POST["id_proveedor_pdv"];
$id_usuario_tufo = $_POST["id_usuario_tufo"];

// Construir la URL del archivo en Google Drive
$ruta = 'https://drive.google.com/open?id=' . $resultadoCargapt->id;

// Consulta preparada para la inserción de datos
$insertar = "INSERT INTO tbl_proveedores_turtisticos (proveedor, cop, novedad, fecha, archivo, estado, nit, email_contabilidad, email_cartera, id_proveedor_fo, id_usuario_tufo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Preparar la declaración para ejecutar la consulta
$stmt = mysqli_prepare($conn, $insertar);

// Vincular parámetros a la declaración
mysqli_stmt_bind_param($stmt, "sssssssssss", $proveedor, $cop, $novedad, $fecha, $ruta, $estado, $nit, $email_contabilidad, $email_cartera, $id_proveedor_pdv, $id_usuario_tufo);

// Ejecutar la declaración
$resultado = mysqli_stmt_execute($stmt);

// Verificar si la inserción fue exitosa
if ($resultado) {
    echo '<script>
        alert("Pago proveedor cargado con éxito");
        window.location = "formularioCargaDriveProveedoresTuristicos.php";
        </script>';
} else {
    echo '<script>alert("Error en la carga")</script>';
}

// Cerrar la declaración
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
