<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";
include_once '../../../../google-api-php-client--PHP8.0/vendor/autoload.php';

// Variables de credenciales de Google Drive.
$claveJSON = '1OYIk2dZUWoveke-hKtJom6GTDu-P3N8_';
$pathJSON = '../../../../drive-contabilidad-a46b4f106c64.json';

// Configurar la variable de entorno
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $pathJSON);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes(['https://www.googleapis.com/auth/drive.file']);

try {
    // === Lógica de subida de archivo a Google Drive ===
    // Se ha actualizado el nombre del campo de archivo
    $service = new Google_Service_Drive($client);
    $file_path = $_FILES['facturaProveedorTuristico']['tmp_name'];
    
    $file = new Google_Service_Drive_DriveFile();
    $file->setName($_FILES['facturaProveedorTuristico']['name']);
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE); 
    $mime_type = finfo_file($finfo, $file_path);

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
    
    $ruta = 'https://drive.google.com/open?id=' . $resultadoCargapt->id;
    
    // === Obtener datos del formulario de forma segura ===
    // Se han actualizado los nombres de las variables
    $proveedor = $_POST["proveedor"] ?? '';
    $cop = $_POST["cop"] ?? '';
    $novedad = $_POST["novedad"] ?? '';
    $fecha = $_POST["fecha"] ?? '';
    $estado = $_POST["estado"] ?? '';
    $nit = $_POST["nit"] ?? '';
    $id_usuario_tufo = $_POST["id_usuario_tufo"] ?? '';

    //Los campos de retenciones y total a pagar se eliminan ya que no se envían desde este formulario
    $reteica = $_POST['reteica'] ?? 0;
    $retefuente = $_POST['retefuente'] ?? 0;
    $reteiva = $_POST['reteiva'] ?? 0;
    $total_pagar = $_POST['total_pagar'] ?? 0;
    
    // === PASO CLAVE: Buscar el id_proveedor con la identificacion ===
    $id_proveedor_relacionado = 0;
    if (!empty($nit)) {
        $consulta_id = "SELECT id_proveedor FROM tbl_proveedores WHERE nit_identificacion = ?";
        $stmt_id = mysqli_prepare($conn, $consulta_id);
        mysqli_stmt_bind_param($stmt_id, "s", $nit);
        mysqli_stmt_execute($stmt_id);
        $resultado_id = mysqli_stmt_get_result($stmt_id);
        if ($fila = mysqli_fetch_assoc($resultado_id)) {
            $id_proveedor_relacionado = $fila['id_proveedor'];
        }
        mysqli_stmt_close($stmt_id);
    }

    if ($id_proveedor_relacionado == 0) {
        echo '<script>alert("Error: No se pudo encontrar el proveedor relacionado en la nueva tabla."); window.location = "buscarProveedor.php";</script>';
        exit;
    }
    
    // === Inserción del nuevo pago en la tabla `tbl_pagos` ===
    // Se ha actualizado la consulta para incluir los campos de retenciones y total a pagar.
    $insertar = "INSERT INTO tbl_pagos (id_proveedor, tipo_pago, valor_pagado, fecha_pago, estado, novedad, archivo_factura, identificacion, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertar);

    // Se ha actualizado la vinculación de parámetros para los nuevos campos.
    // 'i' para id_proveedor, 's' para tipo_pago, 'd' para valor_pagado, etc.
    $tipo_pago = 'Turístico';
    mysqli_stmt_bind_param($stmt, "isdsssssi", 
        $id_proveedor_relacionado, 
        $tipo_pago, 
        $cop, 
        $fecha, 
        $estado, 
        $novedad, 
        $ruta,
        $nit, 
        $id_usuario_tufo
    );
    
    // Ejecutar la declaración
    $resultado = mysqli_stmt_execute($stmt);

    // Verificar si la inserción fue exitosa
    if ($resultado) {
        echo '<script>
            alert("Pago cargado con exito");
            window.location = "consultaProveedoresTuristicos.php";
            </script>';
    } else {
        throw new Exception("Error en la carga: " . mysqli_error($conn));
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} catch (Exception $e) {
    echo '<script>alert("Error: ' . htmlspecialchars($e->getMessage()) . '"); window.location = "consultaProveedoresTuristicos.php";</script>';
    exit;
}
?>