<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";
include_once '../../../../google-api-php-client--PHP8.0/vendor/autoload.php';

// Variables de credenciales de Google Drive.
// Se usa la clave del script de carga, no la del de actualización.
$claveJSON = '1OYIk2dZUWoveke-hKtJom6GTDu-P3N8_';
$pathJSON = '../../../../drive-contabilidad-a46b4f106c64.json';

// Configurar la variable de entorno
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $pathJSON);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes(['https://www.googleapis.com/auth/drive.file']);

try {
    // === Lógica de subida de archivo a Google Drive ===
    $service = new Google_Service_Drive($client);
    $file_path = $_FILES['facturaPagosAdministrativos']['tmp_name'];
    
    // Instancia de archivo
    $file = new Google_Service_Drive_DriveFile();
    $file->setName($_FILES['facturaPagosAdministrativos']['name']);
    
    // Obtenemos el tipo MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE); 
    $mime_type = finfo_file($finfo, $file_path);

    // ID de la carpeta donde hemos dado el permiso a la cuenta de servicio
    $file->setParents(array($claveJSON));
    $file->setDescription('Archivo Cargado a drive con exito');
    $file->setMimeType($mime_type);

    $resultadoCargapa = $service->files->create(
        $file,
        [
            'data' => file_get_contents($file_path),
            'mimeType' => $mime_type,
            'uploadType' => 'media',
        ]
    );
    $ruta = 'https://drive.google.com/open?id=' . $resultadoCargapa->id;

    // === Obtener datos del formulario de forma segura ===
    $identificacion = $_POST['nit'] ?? '';
    $locacion = $_POST["locacion"] ?? '';
    $valor = $_POST["valor"] ?? '';
    $novedad = $_POST["novedad"] ?? '';
    $fecha = $_POST["fecha"] ?? '';
    $estado = $_POST["estado"] ?? '';
    $id_usuario_fo = $_POST["id_usuario_fo"] ?? '';
    $id_causacion_unidos = $_POST['id_causacion_unidos'] ?? '';
    
    // === Nuevos campos para el total de pago ===
    $reteica = $_POST['reteica'] ?? 0;
    $retefuente = $_POST['retefuente'] ?? 0;
    $reteiva = $_POST['reteiva'] ?? 0;
    $total_pagar = $_POST['total_pagar'] ?? 0;
    
    // === PASO CLAVE: Buscar el id_proveedor con la identificacion y tipo_proveedor ===
    $id_proveedor = null;
    $tipo_proveedor = null;
    $consulta_id = "SELECT id_proveedor, tipo_proveedor FROM tbl_proveedores WHERE nit_identificacion = ?";
    $stmt_id = mysqli_prepare($conn, $consulta_id);
    mysqli_stmt_bind_param($stmt_id, "s", $identificacion);
    mysqli_stmt_execute($stmt_id);
    $resultado_id = mysqli_stmt_get_result($stmt_id);
    if ($fila = mysqli_fetch_assoc($resultado_id)) {
        $id_proveedor = $fila['id_proveedor'];
        $tipo_proveedor = $fila['tipo_proveedor'];
    }
    mysqli_stmt_close($stmt_id);

    if (is_null($id_proveedor)) {
        throw new Exception("Error: No se pudo encontrar el proveedor con la identificación proporcionada.");
    }
    
    // === Inserción del nuevo pago en la tabla `tbl_pagos` ===
    // Se ha actualizado la consulta para incluir los nuevos campos de retenciones y total a pagar.
    $insertar = "INSERT INTO tbl_pagos (id_proveedor, tipo_pago, valor_pagado, fecha_pago, estado, novedad, archivo_factura, locacion, identificacion, id_usuario, reteica, retefuente, reteiva, total_pagar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertar);

    // Se ha actualizado la vinculación de parámetros para los nuevos campos.
    // 'i' para id_proveedor, 's' para tipo_pago, 'd' para valor_pagado, etc.
    mysqli_stmt_bind_param($stmt, "isdssssssdssss", $id_proveedor, $tipo_proveedor, $valor, $fecha, $estado, $novedad, $ruta, $locacion, $identificacion, $id_usuario_fo, $reteica, $retefuente, $reteiva, $total_pagar);
    
    // Ejecutar la declaración
    $resultado = mysqli_stmt_execute($stmt);

    // Verificar si la inserción fue exitosa
    if ($resultado) {
        echo '<script>
            alert("Pago cargado con exito");
            window.location = "consultaPagosAdministrativos.php";
            </script>';
    } else {
        throw new Exception("Error en la carga: " . mysqli_error($conn));
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} catch (Exception $e) {
    echo '<script>alert("Error: ' . htmlspecialchars($e->getMessage()) . '"); window.location = "consultaPagosAdministrativos.php";</script>';
    exit;
}
?>
