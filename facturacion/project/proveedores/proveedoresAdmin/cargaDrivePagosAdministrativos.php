<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";
include_once '../../../../google-api-php-client--PHP8.0/vendor/autoload.php';

// Variables de credenciales de Google Drive.
$claveJSON = '1muXXYjfW4h-I7NBRJfsA9MgWSa4GiCpQ';
$pathJSON = '../../../../drive-contabilidad-a46b4f106c64.json';

// Configurar variable de entorno
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $pathJSON);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes(['https://www.googleapis.com/auth/drive.file']);

try {
    // Lógica para subir un nuevo archivo si se proporciona
    $ruta_archivo_drive = null;
    $hay_archivo_nuevo = false;

    if (isset($_FILES["soporteProveedor"]) && $_FILES["soporteProveedor"]["error"] == UPLOAD_ERR_OK) {
        $service = new Google_Service_Drive($client);
        $file_path = $_FILES['soporteProveedor']['tmp_name'];
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($_FILES['soporteProveedor']['name']);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);
        $file->setParents([$claveJSON]);
        $file->setDescription('Archivo cargado a drive con éxito');
        $file->setMimeType($mime_type);
        $resultadoCarga = $service->files->create(
            $file,
            [
                'data' => file_get_contents($file_path),
                'mimeType' => $mime_type,
                'uploadType' => 'media',
            ]
        );
        $ruta_archivo_drive = 'https://drive.google.com/open?id=' . $resultadoCarga->id;
        $hay_archivo_nuevo = true;
    }

    // Obtener datos del formulario de forma segura
    $id_pago = $_POST["id_pago"] ?? null;
    $cop = $_POST["cop"] ?? null;
    $novedad = $_POST["novedad"] ?? null;
    $estado = $_POST["estado"] ?? null;
    $total_pagar = $_POST["total_pagar"] ?? null;

    if (is_null($id_pago)) {
        throw new Exception("Error: ID de pago no proporcionado.");
    }


    // === LÓGICA DE ACTUALIZACIÓN CON SENTENCIAS PREPARADAS ===
    $editarProveedor = "";
    $tipos_datos = "";
    $parametros = [];

    if ($hay_archivo_nuevo) {
        $editarProveedor = "UPDATE tbl_pagos 
                        SET valor_pagado=?, total_pagar=?, novedad=?, estado=?, archivo_soporte=? 
                        WHERE id_pago=?";
        $tipos_datos = "ddsssi"; // d=decimal/float, s=string, i=int
        $parametros = [$cop, $total_pagar, $novedad, $estado, $ruta_archivo_drive, $id_pago];
    } else {
        $editarProveedor = "UPDATE tbl_pagos 
                        SET valor_pagado=?, total_pagar=?, novedad=?, estado=? 
                        WHERE id_pago=?";
        $tipos_datos = "ddssi";
        $parametros = [$cop, $total_pagar, $novedad, $estado, $id_pago];
    }
    $stmt = mysqli_prepare($conn, $editarProveedor);
    mysqli_stmt_bind_param($stmt, $tipos_datos, ...$parametros);

    if (mysqli_stmt_execute($stmt)) {
        echo '<script>alert("Pago Proveedor editado con éxito"); window.location = "consultaPagosAdministrativos.php";</script>';
    } else {
        throw new Exception("Error al editar el registro: " . mysqli_error($conn));
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} catch (Exception $e) {
    echo '<script>alert("Error: ' . htmlspecialchars($e->getMessage()) . '"); window.location = "consultaPagosAdministrativos.php";</script>';
    exit;
}
?>