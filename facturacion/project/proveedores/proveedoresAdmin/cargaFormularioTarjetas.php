<?php
include "../../../config/seguridad.php";
include_once '../../../../google-api-php-client--PHP8.0/vendor/autoload.php';

// ================== CONFIG ==================
$folderId = '1y9_H6CLWqryXkBo2tGCVXezvu-ao-9P8';
$pathJSON = '../../../../drive-contabilidad-a46b4f106c64.json';

// ZOHO
$client_id = '1000.F3WTTRTNQ9GQDH9H4FX8O2H726TDLY';
$client_secret = '5aba2e894613158756904fbbbfca0df7c15bebab6f';
$refresh_token = '1000.f659621e217defa283eb5b6f2cd1d955.4482cc2b42da4131fe4ab5ebf442ac7e';

// WEBHOOK
$webhook = "https://flow.zoho.com/793286638/flow/webhook/incoming?zapikey=1001.5e225fd2a241fbd1b84c23ff85c9d4c5.02377e9a02aa42f0be41fa5c069cedc2&isdebug=false";

// ================== GOOGLE DRIVE ==================
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $pathJSON);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes(['https://www.googleapis.com/auth/drive.file']);

$service = new Google_Service_Drive($client);

// ================== FUNCIONES ==================
function uploadFileToDrive($service, $input, $folderId) {
    if (!isset($_FILES[$input]) || $_FILES[$input]['error'] != UPLOAD_ERR_OK) return null;

    $file = new Google_Service_Drive_DriveFile();
    $file->setName($_FILES[$input]['name']);
    $file->setParents([$folderId]);

    $mime = mime_content_type($_FILES[$input]['tmp_name']);

    $res = $service->files->create($file, [
        'data' => file_get_contents($_FILES[$input]['tmp_name']),
        'mimeType' => $mime,
        'uploadType' => 'media',
    ]);

    return 'https://drive.google.com/open?id=' . $res->id;
}

function getZohoAccessToken($client_id, $client_secret, $refresh_token) {
    $url = "https://accounts.zoho.com/oauth/v2/token";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'refresh_token' => $refresh_token,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => 'refresh_token'
        ]),
        CURLOPT_RETURNTRANSFER => true
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (!isset($data['access_token'])) {
        throw new Exception("Error autenticando con Zoho");
    }

    return $data['access_token'];
}

function buscarProveedorZoho($nit, $access_token) {
    $url = "https://www.zohoapis.com/crm/v2/Vendors/search?criteria=(RUT_NIT_CC:equals:" . urlencode($nit) . ")";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => ["Authorization: Zoho-oauthtoken $access_token"],
        CURLOPT_RETURNTRANSFER => true
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['data']) && count($data['data']) > 0) {
        return $data['data'][0]['id'];
    }

    return null;
}

// ================== INICIO ==================
try {

    // 1. Capturar datos
    $nit = $_POST['identificacion_proveedor'] ?? '';

    if (empty($nit)) {
        throw new Exception("Debe ingresar identificación del proveedor");
    }

    // 2. Subir archivo
    $archivo_drive = uploadFileToDrive($service, 'factura_proveedor', $folderId);

    // 3. Obtener token Zoho
    $access_token = getZohoAccessToken($client_id, $client_secret, $refresh_token);

    // 4. Buscar proveedor
    $vendor_id = buscarProveedorZoho($nit, $access_token);

    if (!$vendor_id) {
        // 🔥 REDIRECCIÓN SI NO EXISTE
        echo '<script>
    if (confirm("El proveedor no existe. ¿Deseas crearlo ahora?")) {
        window.location.href = "https://forms.zohopublic.com/panamericadeviajes/form/FORMATOVINCULACINPROVEEDORESNOTURSTICOS/formperma/e-vVt3wIfi0jjh8dG-HA_fkutPodJBGpqoTm8q9tAlQ?nit=' . urlencode($nit) . '";
    } else {
        window.history.back();
    }
    </script>';
    exit;
    }

    // 5. Construir data
    $data = [
        "Vendor_ID" => $vendor_id,
        "tipo_identificacion" => $_POST['tipo_identificacion'] ?? '',
        "identificacion_proveedor" => $nit,
        "nombre_proveedor" => $_POST['nombre_proveedor'] ?? '',
        "solicitante" => $_POST['solicitante'] ?? '',
        "tipo_servicio" => $_POST['tipo_servicio'] ?? '',
        "area" => $_POST['area'] ?? '',
        "localizador" => $_POST['localizador'] ?? '',
        "factura_panamericana" => $_POST['factura_panamericana'] ?? '',
        "tipo_moneda" => $_POST['tipo_moneda'] ?? '',
        "valor" => $_POST['valor'] ?? '',
        "fee_bancario" => $_POST['fee_bancario'] ?? '',
        "link_pagos" => $_POST['link_pagos'] ?? '',
        "observaciones" => $_POST['observaciones'] ?? '',
        "archivo_drive" => $archivo_drive,
        "usuario" => $_SESSION['usuario'] ?? '',
        "correo" => $_SESSION['correo'] ?? '',
        "fecha_envio" => date("Y-m-d H:i:s")
    ];

    // 6. Enviar a Zoho Flow
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $webhook,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception("Error enviando a Zoho Flow");
    }

    curl_close($ch);

    // 7. OK
    echo '<script>
        alert("Formulario enviado correctamente");
        window.location = "pagosTarjeta.php";
    </script>';

} catch (Exception $e) {
    echo '<script>
        alert("Error: ' . htmlspecialchars($e->getMessage()) . '");
        window.history.back();
    </script>';
}
?>