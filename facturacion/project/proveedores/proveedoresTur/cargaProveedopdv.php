<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";

include_once '../../../../google-api-php-client--PHP8.0/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

require '../../../../PHPMailer/Exception.php';
require '../../../../PHPMailer/PHPMailer.php';
require '../../../../PHPMailer/SMTP.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================== CONFIG GENERAL ==================

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

$folderId = '1y9_H6CLWqryXkBo2tGCVXezvu-ao-9P8';
$pathJSON = '../../../../drive-contabilidad-a46b4f106c64.json';

// WEBHOOK ZOHO FLOW
$config = require __DIR__ . '/../../../../aws.php';
$webhook = $config['zoho_url_webhook_preformulario'] ?? '';

// ================== OBTENER NOMBRE USUARIO LOGUEADO ==================

$nombre_usuario_logueado = '';
if (isset($_SESSION['usuario'])) {
    $stmt_nombre = mysqli_prepare($conn, "SELECT nombre FROM tbl_usuarios WHERE usuario = ? LIMIT 1");
    if ($stmt_nombre) {
        mysqli_stmt_bind_param($stmt_nombre, "s", $_SESSION['usuario']);
        mysqli_stmt_execute($stmt_nombre);
        mysqli_stmt_bind_result($stmt_nombre, $nombre_usuario_logueado);
        mysqli_stmt_fetch($stmt_nombre);
        mysqli_stmt_close($stmt_nombre);
    }
}

// ================== DATOS FORMULARIO ==================

$nit = trim((string)($_POST["nit_identificacion"] ?? ''));
$nombre = trim((string)($_POST["nombre"] ?? ''));
$email_contabilidad = trim((string)($_POST["email_contabilidad"] ?? ''));
$email_cartera = trim((string)($_POST["email_cartera"] ?? ''));
$tipo_proveedor = trim((string)($_POST["tipo_proveedor"] ?? 'Turístico'));
$tipo_proveedor_hotel = trim((string)($_POST["tipo_proveedor_hotel"] ?? ''));
$razon_social = trim((string)($_POST['razon_social'] ?? ''));
$pais = trim((string)($_POST['pais'] ?? ''));
$ciudad = trim((string)($_POST['ciudad'] ?? ''));
$departamento = trim((string)($_POST['departamento'] ?? ''));
$telefono_hotel = trim((string)($_POST['telefono_hotel'] ?? ''));
$direccion = trim((string)($_POST['direccion'] ?? ''));
$sitio_web = trim((string)($_POST['sitio_web'] ?? ''));
$cuenta_bancaria = trim((string)($_POST['cuenta_bancaria'] ?? ''));
$nit_consecutivo = trim((string)($_POST['nit_consecutivo'] ?? ''));
$contacto_reservas = trim((string)($_POST['contacto_reservas'] ?? ''));
$email_reservas = trim((string)($_POST['email_reservas'] ?? ''));
$telefono_reservas = trim((string)($_POST['telefono_reservas'] ?? ''));
$contacto_extranet = trim((string)($_POST['contacto_extranet'] ?? ''));
$email_extranet = trim((string)($_POST['email_extranet'] ?? ''));
$telefono_extranet = trim((string)($_POST['telefono_extranet'] ?? ''));
$otro_servicio = trim((string)($_POST['otro_servicio'] ?? ''));
$cual_servicio = trim((string)($_POST['cual_servicio'] ?? ''));
$vigencias_documentos = is_array($_POST['vigencias_documentos'] ?? null) ? $_POST['vigencias_documentos'] : [];

$id_hotel_creado = null;
$juniper_id = null;

// ================== HELPERS DB ==================

function colExists(mysqli $conn, string $table, string $col): bool
{
    $dbRes = $conn->query("SELECT DATABASE() AS db");
    $dbRow = $dbRes ? $dbRes->fetch_assoc() : null;
    $db = $dbRow['db'] ?? '';
    if ($db === '') {
        return false;
    }

    $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=? LIMIT 1";
    $st = $conn->prepare($sql);
    if (!$st) {
        return false;
    }
    $st->bind_param("sss", $db, $table, $col);
    $st->execute();
    $res = $st->get_result();
    $ok = ($res && $res->num_rows > 0);
    $st->close();
    return $ok;
}

function insertDynamic(mysqli $conn, string $table, array $data): int
{
    $cols = [];
    $vals = [];

    foreach ($data as $col => $value) {
        if (colExists($conn, $table, $col)) {
            $cols[] = $col;
            $vals[] = $value;
        }
    }

    if (empty($cols)) {
        throw new Exception("No hay columnas válidas para insertar en {$table}.");
    }

    $placeholders = implode(',', array_fill(0, count($cols), '?'));
    $sql = "INSERT INTO {$table} (" . implode(',', $cols) . ") VALUES ({$placeholders})";
    $stmt = $conn->prepare($sql);
    $types = str_repeat('s', count($vals));
    $stmt->bind_param($types, ...$vals);
    $stmt->execute();
    $insertId = (int)$conn->insert_id;
    $stmt->close();

    return $insertId;
}

function safeIntString($value): string
{
    return (string)((int)($value ?? 0));
}

function limpiarFechaVigencia($value): ?string
{
    $value = trim((string)($value ?? ''));
    if ($value === '') {
        return null;
    }

    if (preg_match('/\d{4}-\d{2}-\d{2}/', $value, $m)) {
        return $m[0];
    }

    return $value;
}

function obtenerVigenciaDocumento(array $vigencias, string $inputName): array
{
    $data = $vigencias[$inputName] ?? [];
    if (!is_array($data)) {
        $data = [];
    }

    $dias = trim((string)($data['dias_vencimiento'] ?? ''));

    return [
        'fecha_vigencia' => limpiarFechaVigencia($data['fecha_vigencia'] ?? null),
        'fuente_vigencia' => trim((string)($data['fuente_vigencia'] ?? '')) ?: null,
        'estado_vigencia' => trim((string)($data['estado_vigencia'] ?? '')) ?: null,
        'dias_vencimiento' => is_numeric($dias) ? (int)$dias : null,
        'validacion_ia_json' => trim((string)($data['validacion_ia_json'] ?? '')) ?: null
    ];
}

function construirPayloadVigenciasDocumentos(array $vigencias): array
{
    $mapa = [
        'rut' => 'RUT',
        'rnt' => 'RNT',
        'certificacion_bancaria' => 'Certificacion Bancaria',
        'planes_especiales' => 'Planes Especiales'
    ];

    $payload = [];

    foreach ($mapa as $inputName => $tipoDocumento) {
        $payload[$inputName] = array_merge(
            ['tipo_documento' => $tipoDocumento],
            obtenerVigenciaDocumento($vigencias, $inputName)
        );
    }

    return $payload;
}

// ================== GOOGLE DRIVE ==================

putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $pathJSON);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes([
    'https://www.googleapis.com/auth/drive.file'
]);
$service = new Google_Service_Drive($client);

// ================== FUNCION SUBIR ARCHIVOS ==================

function uploadFileToDrive($service, $input, $folderId)
{
    if (!isset($_FILES[$input]) || $_FILES[$input]['error'] != UPLOAD_ERR_OK) {
        return null;
    }

    $file = new Google_Service_Drive_DriveFile();
    $file->setName($_FILES[$input]['name']);
    $file->setParents([$folderId]);

    $mime = mime_content_type($_FILES[$input]['tmp_name']);

    $res = $service->files->create($file, [
        'data' => file_get_contents($_FILES[$input]['tmp_name']),
        'mimeType' => $mime,
        'uploadType' => 'media',
    ]);

    $permission = new Google_Service_Drive_Permission();
    $permission->setType('anyone');
    $permission->setRole('reader');

    $service->permissions->create($res->id, $permission);

    return 'https://drive.google.com/open?id=' . $res->id;
}

// ================== FUNCIONES JUNIPER ==================

function xmlSafe($value)
{
    return htmlspecialchars((string)$value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

function formatearCuentaBancariaJuniper($cuenta)
{
    if (empty($cuenta)) {
        return "00000000-00000000";
    }

    $cuenta = trim($cuenta);
    $cuenta = preg_replace('/[^0-9-]/', '', $cuenta);

    if ((strlen($cuenta) === 17 && substr($cuenta, 8, 1) === '-') ||
        (strlen($cuenta) === 23 && substr($cuenta, 4, 1) === '-')) {
        return $cuenta;
    }

    $soloNumeros = str_replace('-', '', $cuenta);

    if (strlen($soloNumeros) > 16) {
        $soloNumeros = str_pad($soloNumeros, 22, '0', STR_PAD_LEFT);
        $soloNumeros = substr($soloNumeros, 0, 22);
        return substr($soloNumeros, 0, 4) . '-' . substr($soloNumeros, 4, 18);
    }

    $soloNumeros = str_pad($soloNumeros, 16, '0', STR_PAD_LEFT);
    $soloNumeros = substr($soloNumeros, 0, 16);
    return substr($soloNumeros, 0, 8) . '-' . substr($soloNumeros, 8, 8);
}

function enviarProveedorJuniper($data)
{
    $url = "https://www.panamericanadeviajes.net/wsExportacion/wsSuppliers.asmx";
    $user = "XMLZeusPNV";
    $password = "LQ3UbZke";

    $nit = xmlSafe($data["nit_identificacion"] ?? "");
    $nombre = xmlSafe($data["nombre"] ?? "");
    $email = xmlSafe($data["email_reservas"] ?? ($data["email_contabilidad"] ?? ""));
    $telefono = xmlSafe($data["telefono_hotel"] ?? "");
    $direccion = xmlSafe($data["direccion"] ?? "");
    $razonSocial = xmlSafe($data["razon_social"] ?? $nombre);
    $pais = xmlSafe($data["pais"] ?? "Colombia");
    $ciudad = xmlSafe($data["ciudad"] ?? "BOGOTA");
    $cuentaBancaria = xmlSafe(formatearCuentaBancariaJuniper($data["cuenta_bancaria"] ?? ""));

    $supplierXml = '<Supplier Id="" TaxId="'.$nit.'" Currency="COP" PaymentType="" DaysToPay="0" RateType="0" Commission="0" BloackPayments="0" ReferenceNumber="'.$nit.'" BlockPayments="0" LastExportationDate="">
    <Name>'.$nombre.'</Name><Remarks/><AccountRefNumber/><RegistrationName>'.$razonSocial.'</RegistrationName>
    <ContactPerson/><Phone1>'.$telefono.'</Phone1><Phone2/><Mobile/><Fax/><Email>'.$email.'</Email>
    <Address>'.$direccion.'</Address><City ZIP="">'.$ciudad.'</City><State>Activo</State>
    <District>DIVISION POLITICA POR DEFECTO</District><Town/><Country ISO2="CO">'.$pais.'</Country>
    <BankAccount Format="FREE"><BankName/><BankAccountNumber>'.$cuentaBancaria.'</BankAccountNumber></BankAccount></Supplier>';

    $soap = '<?xml version="1.0" encoding="utf-8"?>
    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:jun="http://juniper.es/">
    <soapenv:Header/>
    <soapenv:Body>
        <jun:addSupplier>
            <jun:user>'.$user.'</jun:user>
            <jun:password>'.$password.'</jun:password>
            <jun:xml><![CDATA['.$supplierXml.']]></jun:xml>
        </jun:addSupplier>
    </soapenv:Body>
    </soapenv:Envelope>';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $soap,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "http://juniper.es/addSupplier"',
            'Content-Length: ' . strlen($soap)
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlInfo = curl_getinfo($ch);
    curl_close($ch);

    $juniperReject = false;
    $juniperId = null;

    if ($response !== false && strpos($response, 'Incorrect supplier XML') !== false) {
        $juniperReject = true;
    }

    if ($response !== false && !empty($response)) {
        if (preg_match('/<Supplier[^>]+Id="([^"]+)"/', $response, $matches)) {
            $juniperId = $matches[1];
        } elseif (preg_match('/Success ID (\d+)/', $response, $matches)) {
            $juniperId = $matches[1];
        }
    }

    return [
        "ok" => ($errno === 0 && $httpCode >= 200 && $httpCode < 300 && !$juniperReject),
        "juniper_id" => $juniperId,
        "http_code" => $httpCode,
        "curl_errno" => $errno,
        "curl_error" => $error,
        "juniper_reject" => $juniperReject,
        "curl_info" => $curlInfo,
        "soap_enviado" => $soap,
        "response" => $response
    ];
}

// ================== FUNCIONES DE CORREO ==================

function configurarSMTP(PHPMailer $mail): void
{
    $smtp = require __DIR__ . '/../../../../aws.php';

    $mail->CharSet = 'UTF-8';
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = $smtp['ses_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp['ses_user'];
    $mail->Password = $smtp['ses_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $smtp['ses_port'];
}

function addAttachmentIfUploaded(PHPMailer $mail, string $input): void
{
    if (isset($_FILES[$input]) && $_FILES[$input]['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES[$input]['tmp_name'])) {
        $mail->addAttachment($_FILES[$input]['tmp_name'], $_FILES[$input]['name']);
    }
}

function enviarCorreoSeguro(PHPMailer $mail, string $logContext): bool
{
    try {
        $mail->send();
        error_log("[CORREO OK] {$logContext}");
        return true;
    } catch (MailException $e) {
        error_log("[CORREO ERROR] {$logContext}: " . $mail->ErrorInfo . ' | ' . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log("[CORREO ERROR] {$logContext}: " . $e->getMessage());
        return false;
    }
}

function htmlRow(string $label, string $value): string
{
    return "<tr><td style='border:1px solid #ddd;background:#f8f9fa;padding:8px;width:35%;'><strong>" . htmlspecialchars($label) . "</strong></td><td style='border:1px solid #ddd;padding:8px;'>" . htmlspecialchars($value) . "</td></tr>";
}

function construirBodyProveedor(array $ctx): string
{
    return "
    <!DOCTYPE html>
    <html><head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#f4f6f9;font-family:Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f6f9;padding:30px 0;'>
            <tr><td align='center'>
                <table width='720' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.12);'>
                    <tr><td style='background:linear-gradient(135deg,#198754,#0f5132);padding:30px;text-align:center;color:white;'>
                        <h1 style='margin:0;font-size:24px;'>Registro recibido</h1>
                        <p style='margin-top:10px;'>Panamericana de Viajes</p>
                    </td></tr>
                    <tr><td style='padding:30px;'>
                        <p>Cordial saludo,</p>
                        <p>Hemos recibido el registro de su proveedor turístico. Nuestro equipo revisará la información y documentos cargados.</p>
                        <h3 style='background:#198754;color:white;padding:12px;border-radius:6px;'>Información registrada</h3>
                        <table width='100%' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>
                            " . htmlRow('NIT', $ctx['nit']) . "
                            " . htmlRow('Proveedor', $ctx['nombre']) . "
                            " . htmlRow('Razón social', $ctx['razon_social']) . "
                            " . htmlRow('Tipo proveedor', $ctx['tipo_proveedor_hotel']) . "
                            " . htmlRow('Ciudad / País', $ctx['ciudad'] . ' / ' . $ctx['pais']) . "
                            " . htmlRow('Fecha registro', $ctx['fecha']) . "
                        </table>
                        <p>Gracias por confiar en Panamericana de Viajes.</p>
                        <p>Atentamente,<br><strong>Equipo de Negociaciones</strong><br>Panamericana de Viajes</p>
                    </td></tr>
                </table>
            </td></tr>
        </table>
    </body></html>";
}

function construirBodyInterno(array $ctx): string
{
    $linkFicha = !empty($ctx['link_ficha'])
        ? "<tr><td style='border:1px solid #ddd;background:#f8f9fa;padding:8px;'><strong>Link ficha alojamiento</strong></td><td style='border:1px solid #ddd;padding:8px;'><a href='" . htmlspecialchars($ctx['link_ficha']) . "'>" . htmlspecialchars($ctx['link_ficha']) . "</a></td></tr>"
        : "<tr><td style='border:1px solid #ddd;background:#f8f9fa;padding:8px;'><strong>Link ficha alojamiento</strong></td><td style='border:1px solid #ddd;padding:8px;'>No aplica para cadena hotelera o no fue creado.</td></tr>";

    return "
    <!DOCTYPE html>
    <html><head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#f4f6f9;font-family:Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f6f9;padding:30px 0;'>
            <tr><td align='center'>
                <table width='780' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.12);'>
                    <tr><td style='background:linear-gradient(135deg,#0d6efd,#003b8e);padding:30px;text-align:center;color:white;'>
                        <h1 style='margin:0;font-size:26px;'>Nuevo Proveedor Registrado</h1>
                        <p style='margin-top:10px;'>Panamericana de Viajes</p>
                    </td></tr>
                    <tr><td style='padding:30px;'>
                        <p>Se registró exitosamente un nuevo proveedor turístico en el sistema.</p>
                        <h3 style='background:#0d6efd;color:white;padding:12px;border-radius:6px;'>Información general</h3>
                        <table width='100%' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>
                            " . htmlRow('NIT', $ctx['nit']) . "
                            " . htmlRow('Nombre', $ctx['nombre']) . "
                            " . htmlRow('Razón social', $ctx['razon_social']) . "
                            " . htmlRow('Tipo proveedor', $ctx['tipo_proveedor_hotel']) . "
                            " . htmlRow('Correo contabilidad', $ctx['email_contabilidad']) . "
                            " . htmlRow('Correo cartera', $ctx['email_cartera']) . "
                            " . htmlRow('Ciudad / País', $ctx['ciudad'] . ' / ' . $ctx['pais']) . "
                            " . htmlRow('Departamento', $ctx['departamento']) . "
                            " . htmlRow('Teléfono', $ctx['telefono_hotel']) . "
                            " . htmlRow('Dirección', $ctx['direccion']) . "
                            " . htmlRow('Sitio web', $ctx['sitio_web']) . "
                            " . htmlRow('Cuenta bancaria', $ctx['cuenta_bancaria']) . "
                            " . htmlRow('Usuario creado', $ctx['usuario_creado']) . "
                            " . htmlRow('Rol usuario', $ctx['rol_usuario']) . "
                            " . htmlRow('ID alojamiento', $ctx['id_hotel'] ?: 'No aplica') . "
                            " . $linkFicha . "
                        </table>
                        <h3 style='background:#198754;color:white;padding:12px;border-radius:6px;'>Contactos</h3>
                        <table width='100%' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>
                            " . htmlRow('Contacto reservas', $ctx['contacto_reservas']) . "
                            " . htmlRow('Email reservas', $ctx['email_reservas']) . "
                            " . htmlRow('Teléfono reservas', $ctx['telefono_reservas']) . "
                            " . htmlRow('Contacto extranet', $ctx['contacto_extranet']) . "
                            " . htmlRow('Email extranet', $ctx['email_extranet']) . "
                            " . htmlRow('Teléfono extranet', $ctx['telefono_extranet']) . "
                            " . htmlRow('Otro servicio', $ctx['otro_servicio']) . "
                            " . htmlRow('Cuál servicio', $ctx['cual_servicio']) . "
                        </table>
                        <h3 style='background:#6f42c1;color:white;padding:12px;border-radius:6px;'>Registro</h3>
                        <table width='100%' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>
                            " . htmlRow('Diligenciado por', $ctx['diligenciado_por']) . "
                            " . htmlRow('Fecha registro', $ctx['fecha']) . "
                        </table>
                        <p>Los documentos se adjuntan según corresponda al área destinataria.</p>
                    </td></tr>
                </table>
            </td></tr>
        </table>
    </body></html>";
}

function construirBodySarlaft(array $ctx): string
{
    return "
    <!DOCTYPE html>
    <html><head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#f4f6f9;font-family:Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f6f9;padding:30px 0;'>
            <tr><td align='center'>
                <table width='650' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.12);'>
                    <tr><td style='background:linear-gradient(135deg,#0d6efd,#003b8e);padding:25px;text-align:center;color:white;'>
                        <h2 style='margin:0;'>Nuevo proveedor para revisión SARLAFT</h2>
                        <p style='margin-top:10px;'>Panamericana de Viajes</p>
                    </td></tr>
                    <tr><td style='padding:30px;'>
                        <p>Se ha registrado un nuevo proveedor turístico para revisión correspondiente.</p>
                        <table width='100%' style='border-collapse:collapse;'>
                            " . htmlRow('NIT', $ctx['nit']) . "
                            " . htmlRow('Proveedor', $ctx['nombre']) . "
                            " . htmlRow('Razón social', $ctx['razon_social']) . "
                            " . htmlRow('Tipo proveedor', $ctx['tipo_proveedor_hotel']) . "
                            " . htmlRow('Ciudad / País', $ctx['ciudad'] . ' / ' . $ctx['pais']) . "
                            " . htmlRow('Diligenciado por', $ctx['diligenciado_por']) . "
                        </table>
                    </td></tr>
                </table>
            </td></tr>
        </table>
    </body></html>";
}

// ================== INICIO ==================

try {
    if ($nit === '' || $nombre === '' || $tipo_proveedor_hotel === '') {
        throw new Exception("Faltan datos obligatorios: NIT, nombre o tipo de proveedor.");
    }

    mysqli_begin_transaction($conn);

    // ================== SUBIR ARCHIVOS ==================

    $rut_drive = uploadFileToDrive($service, 'rut', $folderId);
    $rnt_drive = uploadFileToDrive($service, 'rnt', $folderId);
    $certificacion_bancaria_drive = uploadFileToDrive($service, 'certificacion_bancaria', $folderId);
    $planes_especiales_drive = uploadFileToDrive($service, 'planes_especiales', $folderId);
    $certificado_sostenibilidad_drive = uploadFileToDrive($service, 'certificado_sostenibilidad', $folderId);

    // ================== INSERTAR PROVEEDOR ==================

    $insertar_query = "
        INSERT INTO tbl_proveedores
        (
            nit_identificacion,
            nombre,
            tipo_proveedor,
            email_contabilidad,
            email_cartera
        )
        VALUES (?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($conn, $insertar_query);
    mysqli_stmt_bind_param(
        $stmt,
        "sssss",
        $nit,
        $nombre,
        $tipo_proveedor,
        $email_contabilidad,
        $email_cartera
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error al insertar el proveedor: " . mysqli_error($conn));
    }

    $id_proveedor_creado = (int)mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // ================== CREAR USUARIO ==================

    $usuario = $nit;
    $contrasena = $nit;

    if (strcasecmp($tipo_proveedor_hotel, "Hotel") === 0) {
        $id_rol = 6;
    } elseif (strcasecmp($tipo_proveedor_hotel, "Cadena Hotelera") === 0) {
        $id_rol = 7;
    } else {
        throw new Exception("Tipo de proveedor no válido. Recibido: '" . htmlspecialchars($tipo_proveedor_hotel) . "'. Debe ser 'Hotel' o 'Cadena Hotelera'.");
    }

    $estado = 1;

    $sql_check = "SELECT usuario FROM tbl_usuarios WHERE usuario = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "s", $usuario);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) == 0) {
        mysqli_stmt_close($stmt_check);

        $hashed_password = password_hash($contrasena, PASSWORD_BCRYPT);

        $sql_usuario = "
            INSERT INTO tbl_usuarios
            (
                usuario,
                contraseña,
                nombre,
                telefono,
                direccion,
                id_rol,
                estado
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt_usuario = mysqli_prepare($conn, $sql_usuario);
        mysqli_stmt_bind_param(
            $stmt_usuario,
            "sssssii",
            $usuario,
            $hashed_password,
            $nombre,
            $telefono_hotel,
            $direccion,
            $id_rol,
            $estado
        );

        if (!mysqli_stmt_execute($stmt_usuario)) {
            throw new Exception("Error al crear el usuario: " . mysqli_error($conn));
        }

        mysqli_stmt_close($stmt_usuario);
    } else {
        mysqli_stmt_close($stmt_check);
    }

    // ================== CREAR ALOJAMIENTO SOLO SI ES HOTEL ==================

    if (strcasecmp($tipo_proveedor_hotel, "Hotel") === 0) {
        $id_usuario_creacion = null;
        $stmtUserId = $conn->prepare("SELECT id_usuario FROM tbl_usuarios WHERE usuario = ? LIMIT 1");
        if ($stmtUserId) {
            $stmtUserId->bind_param("s", $usuario);
            $stmtUserId->execute();
            $stmtUserId->bind_result($id_usuario_creacion);
            $stmtUserId->fetch();
            $stmtUserId->close();
        }

        $stmtHotelExiste = $conn->prepare("SELECT id_hotel FROM tbl_alojamiento_general WHERE nit = ? ORDER BY id_hotel DESC LIMIT 1");
        $stmtHotelExiste->bind_param("s", $nit);
        $stmtHotelExiste->execute();
        $stmtHotelExiste->bind_result($idHotelExistente);
        if ($stmtHotelExiste->fetch()) {
            $id_hotel_creado = (int)$idHotelExistente;
            $stmtHotelExiste->close();
        } else {
            $stmtHotelExiste->close();

            $alojamientoData = [
                'usuario_creacion' => $usuario,
                'id_usuario_creacion' => $id_usuario_creacion,
                'cadena_hotelera' => '',
                'nombre' => $nombre,
                'nit' => $nit,
                'nit_consecutivo' => $nit_consecutivo,
                'razon_social' => $razon_social,
                'telefono' => $telefono_hotel,
                'direccion' => $direccion,
                'ciudad' => $ciudad,
                'pais' => $pais,
                'website' => $sitio_web,
                'categoria' => '',
                'descripcion_producto' => '',
                'incluye_desayuno' => '0',
                'numero_habitaciones' => '0',
                'precio_desayuno' => null,
                'tipo_desayuno' => '',
                'hora_check_in' => '',
                'hora_check_out' => '',
                'es_pet_friendly' => '0',
                'politica_mascotas' => '',
                'habitaciones_discapacidad' => '0',
                'habitaciones_connecting' => '0',
                'tipo_hotel_json' => json_encode([], JSON_UNESCAPED_UNICODE),
                'informacion_adicional' => trim("Registro rápido proveedores turísticos. Otro servicio: {$otro_servicio}. Cuál: {$cual_servicio}"),
                'mercados_distribucion_json' => json_encode([], JSON_UNESCAPED_UNICODE),
                'monto_credito' => null,
                'tiempo_credito' => null,
                'reteica' => null,
                'retefuente' => null,
                'numero_cuenta' => $cuenta_bancaria,
                'forma_conexion' => '',
                'channel_manager_nombre' => '',
                'descuento_dinamico' => null,
                'rep_legal_nombre' => '',
                'rep_legal_cargo' => '',
                'acepta_declaracion' => '0',
                'acepta_terminos' => '1',
                'acepta_politicas' => '1',
                'acepta_compromiso' => '0',
                'tiene_certificado_sostenibilidad' => ($certificado_sostenibilidad_drive !== null ? '1' : '0'),
                'nombre_hotel_legal' => $nombre,
                'ciudad_hotel_legal' => $ciudad,
                'nit_hotel_legal' => $nit,
                'nombre_rep_legal' => '',
                'ciudad_rep_legal' => '',
                'num_documento_rep_legal' => '',
                'ciudad_doc_rep_legal' => '',
                'diligencia_nombre' => $nombre_usuario_logueado,
                'diligencia_correo' => $_SESSION['correo'] ?? '',
                'diligencia_cargo' => '',
                'tarifa_tipo_json' => json_encode([], JSON_UNESCAPED_UNICODE),
                'allotment_selected' => '0',
                'allotment_json' => json_encode([], JSON_UNESCAPED_UNICODE),
                'regimen_alimenticio_json' => json_encode([], JSON_UNESCAPED_UNICODE),
                'estado_registro' => 'BORRADOR',
                'estado_firma' => 'PENDIENTE',
                'estado_aprobacion' => 'PENDIENTE',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $id_hotel_creado = insertDynamic($conn, 'tbl_alojamiento_general', $alojamientoData);
        }

        if ($id_hotel_creado !== null && $id_hotel_creado > 0) {
            $idUsuarioCreacionParam = $id_usuario_creacion !== null ? (string)$id_usuario_creacion : null;
            $stmtAsignarUsuario = $conn->prepare("
                UPDATE tbl_alojamiento_general
                SET usuario_creacion = ?, id_usuario_creacion = ?, updated_at = NOW()
                WHERE id_hotel = ?
                LIMIT 1
            ");
            $stmtAsignarUsuario->bind_param("ssi", $usuario, $idUsuarioCreacionParam, $id_hotel_creado);
            $stmtAsignarUsuario->execute();
            $stmtAsignarUsuario->close();

            $contactos = [
                ['tipo_contacto' => 'Reservas', 'nombre' => $contacto_reservas, 'movil' => '', 'email' => $email_reservas, 'telefono' => $telefono_reservas],
                ['tipo_contacto' => 'Extranet', 'nombre' => $contacto_extranet, 'movil' => '', 'email' => $email_extranet, 'telefono' => $telefono_extranet],
                ['tipo_contacto' => 'Pagos', 'nombre' => 'Contabilidad', 'movil' => '', 'email' => $email_contabilidad, 'telefono' => '']
            ];

            foreach ($contactos as $contactoRow) {
                if (trim($contactoRow['nombre']) === '' && trim($contactoRow['email']) === '') {
                    continue;
                }

                insertDynamic($conn, 'tbl_alojamiento_contactos', array_merge(['id_hotel' => $id_hotel_creado], $contactoRow));
            }

            $documentosAlojamiento = [
                ['input_name' => 'rut', 'tipo_documento' => 'RUT', 'nombre_archivo' => $_FILES['rut']['name'] ?? '', 'ruta_almacenamiento' => $rut_drive],
                ['input_name' => 'rnt', 'tipo_documento' => 'RNT', 'nombre_archivo' => $_FILES['rnt']['name'] ?? '', 'ruta_almacenamiento' => $rnt_drive],
                ['input_name' => 'certificacion_bancaria', 'tipo_documento' => 'Certificacion Bancaria', 'nombre_archivo' => $_FILES['certificacion_bancaria']['name'] ?? '', 'ruta_almacenamiento' => $certificacion_bancaria_drive],
                ['input_name' => 'planes_especiales', 'tipo_documento' => 'Planes Especiales', 'nombre_archivo' => $_FILES['planes_especiales']['name'] ?? '', 'ruta_almacenamiento' => $planes_especiales_drive],
                ['input_name' => 'certificado_sostenibilidad', 'tipo_documento' => 'Sostenibilidad', 'nombre_archivo' => $_FILES['certificado_sostenibilidad']['name'] ?? '', 'ruta_almacenamiento' => $certificado_sostenibilidad_drive]
            ];

            foreach ($documentosAlojamiento as $docRow) {
                if (empty($docRow['ruta_almacenamiento'])) {
                    continue;
                }

                $inputDocumento = $docRow['input_name'];
                unset($docRow['input_name']);

                $vigenciaDocumento = obtenerVigenciaDocumento($vigencias_documentos, $inputDocumento);

                insertDynamic(
                    $conn,
                    'tbl_alojamiento_documentos',
                    array_merge(['id_hotel' => $id_hotel_creado], $docRow, $vigenciaDocumento)
                );
            }
        }
    }

    mysqli_commit($conn);

    // ================== CONTEXTO CORREOS / INTEGRACIONES ==================

    $link_ficha = ($id_hotel_creado !== null && $id_hotel_creado > 0)
        ? "https://sgc.panamericanaviajes.com/facturacion/proveedores/vista/consultaHotel.php?id=" . $id_hotel_creado
        : '';

    $ctx = [
        'nit' => $nit,
        'nit_consecutivo' => $nit_consecutivo,
        'nombre' => $nombre,
        'razon_social' => $razon_social,
        'tipo_proveedor' => $tipo_proveedor,
        'tipo_proveedor_hotel' => $tipo_proveedor_hotel,
        'email_contabilidad' => $email_contabilidad,
        'email_cartera' => $email_cartera,
        'pais' => $pais,
        'ciudad' => $ciudad,
        'departamento' => $departamento,
        'telefono_hotel' => $telefono_hotel,
        'direccion' => $direccion,
        'sitio_web' => $sitio_web,
        'cuenta_bancaria' => $cuenta_bancaria,
        'contacto_reservas' => $contacto_reservas,
        'email_reservas' => $email_reservas,
        'telefono_reservas' => $telefono_reservas,
        'contacto_extranet' => $contacto_extranet,
        'email_extranet' => $email_extranet,
        'telefono_extranet' => $telefono_extranet,
        'otro_servicio' => $otro_servicio,
        'cual_servicio' => $cual_servicio,
        'diligenciado_por' => $nombre_usuario_logueado,
        'fecha' => date("Y-m-d H:i:s"),
        'usuario_creado' => $usuario,
        'rol_usuario' => (string)$id_rol,
        'id_hotel' => $id_hotel_creado ? (string)$id_hotel_creado : '',
        'link_ficha' => $link_ficha
    ];

    $bodyProveedor = construirBodyProveedor($ctx);
    $bodyInterno = construirBodyInterno($ctx);
    $bodySarlaft = construirBodySarlaft($ctx);

    // ================== NOTIFICACION AL PROVEEDOR ==================

    $destinosProveedor = [];
    if (!empty($email_contabilidad)) {
        $destinosProveedor[$email_contabilidad] = $nombre;
    }
    if (!empty($email_reservas)) {
        $destinosProveedor[$email_reservas] = $contacto_reservas ?: $nombre;
    }
    if (!empty($email_extranet)) {
        $destinosProveedor[$email_extranet] = $contacto_extranet ?: $nombre;
    }

    if (!empty($destinosProveedor)) {
        $mailProveedor = new PHPMailer(true);
        configurarSMTP($mailProveedor);
        $mailProveedor->setFrom('negociaciones@panamericanaviajes.com', 'Registro Proveedores Panamericana');
        foreach ($destinosProveedor as $correoProveedor => $nombreProveedor) {
            $mailProveedor->addAddress($correoProveedor, $nombreProveedor);
        }
        $mailProveedor->addCC('negociaciones@panamericanaviajes.com');
        $mailProveedor->isHTML(true);
        $mailProveedor->Subject = 'Registro recibido - ' . $nombre;
        $mailProveedor->Body = $bodyProveedor;
        $mailProveedor->AltBody = "Registro recibido. Proveedor: {$nombre}. NIT: {$nit}.";
        enviarCorreoSeguro($mailProveedor, 'Proveedor registrado - notificación proveedor');
    } else {
        error_log('[CORREO SKIP] No hay correo del proveedor para notificación externa.');
    }

    // ================== NOTIFICACION INTERNA GENERAL ==================

    $mailInterno = new PHPMailer(true);
    configurarSMTP($mailInterno);
    $mailInterno->setFrom('negociaciones@panamericanaviajes.com', 'Sistema de Registro de Proveedores');
    $mailInterno->addAddress('negociaciones@panamericanaviajes.com');
    $mailInterno->addAddress('director.negociaciones@panamericanaviajes.com');
    $mailInterno->addAddress('director.sistemas@panamericanaviajes.com');
    $mailInterno->addAddress('soporteweb@panamericanaviajes.com');
    $mailInterno->addCC('sistemas@panamericanaviajes.com');
    $mailInterno->isHTML(true);
    $mailInterno->Subject = 'Nuevo proveedor turístico registrado - ' . $nombre;
    $mailInterno->Body = $bodyInterno;
    $mailInterno->AltBody = "Nuevo proveedor turístico registrado. Proveedor: {$nombre}. NIT: {$nit}.";
    addAttachmentIfUploaded($mailInterno, 'rut');
    addAttachmentIfUploaded($mailInterno, 'certificado_sostenibilidad');
    enviarCorreoSeguro($mailInterno, 'Proveedor registrado - internos general');

    // ================== NOTIFICACION CONTABILIDAD CON RUT ==================

    $mailContabilidad = new PHPMailer(true);
    configurarSMTP($mailContabilidad);
    $mailContabilidad->setFrom('negociaciones@panamericanaviajes.com', 'Sistema de Registro de Proveedores');
    $mailContabilidad->addAddress('contabilidad8@panamericanaviajes.com');
    $mailContabilidad->addAddress('lider.contabilidad@panamericanaviajes.com');
    $mailContabilidad->addCC('director.sistemas@panamericanaviajes.com');
    $mailContabilidad->addCC('negociaciones@panamericanaviajes.com');
    $mailContabilidad->isHTML(true);
    $mailContabilidad->Subject = 'Nuevo proveedor registrado para contabilidad - ' . $nombre;
    $mailContabilidad->Body = $bodyInterno . "<p><strong>Nota:</strong> Se adjunta RUT si fue cargado en el registro.</p>";
    $mailContabilidad->AltBody = "Nuevo proveedor para contabilidad. Proveedor: {$nombre}. NIT: {$nit}.";
    addAttachmentIfUploaded($mailContabilidad, 'rut');
    enviarCorreoSeguro($mailContabilidad, 'Proveedor registrado - contabilidad');

    // ================== NOTIFICACION CALIDAD CON CERTIFICADO SOSTENIBILIDAD ==================

    if (isset($_FILES['certificado_sostenibilidad']) && $_FILES['certificado_sostenibilidad']['error'] === UPLOAD_ERR_OK) {
        $mailCalidad = new PHPMailer(true);
        configurarSMTP($mailCalidad);
        $mailCalidad->setFrom('negociaciones@panamericanaviajes.com', 'Sistema de Registro de Proveedores');
        $mailCalidad->addAddress('calidad@panamericanaviajes.com');
        $mailCalidad->addCC('director.sistemas@panamericanaviajes.com');
        $mailCalidad->addCC('negociaciones@panamericanaviajes.com');
        $mailCalidad->isHTML(true);
        $mailCalidad->Subject = 'Certificado de sostenibilidad - ' . $nombre;
        $mailCalidad->Body = $bodyInterno . "<p><strong>Nota:</strong> Se adjunta certificado de sostenibilidad cargado por el proveedor.</p>";
        $mailCalidad->AltBody = "Certificado de sostenibilidad de proveedor. Proveedor: {$nombre}. NIT: {$nit}.";
        addAttachmentIfUploaded($mailCalidad, 'certificado_sostenibilidad');
        enviarCorreoSeguro($mailCalidad, 'Proveedor registrado - calidad sostenibilidad');
    } else {
        error_log('[CORREO SKIP] No se envía correo de sostenibilidad porque no se cargó certificado.');
    }

    // ================== NOTIFICACION SARLAFT ==================

    $mailSarlaft = new PHPMailer(true);
    configurarSMTP($mailSarlaft);
    $mailSarlaft->setFrom('negociaciones@panamericanaviajes.com', 'Sistema de Registro de Proveedores');
    $mailSarlaft->addAddress('lider.transporte@panamericanaviajes.com');
    $mailSarlaft->addCC('director.sistemas@panamericanaviajes.com');
    $mailSarlaft->addCC('negociaciones@panamericanaviajes.com');
    $mailSarlaft->isHTML(true);
    $mailSarlaft->Subject = 'Nuevo proveedor para revisión SARLAFT - ' . $nombre;
    $mailSarlaft->Body = $bodySarlaft;
    $mailSarlaft->AltBody = "Nuevo proveedor para revisión SARLAFT. Proveedor: {$nombre}. NIT: {$nit}.";
    addAttachmentIfUploaded($mailSarlaft, 'rut');
    enviarCorreoSeguro($mailSarlaft, 'Proveedor registrado - SARLAFT');

    // ================== DATA PARA ZOHO Y JUNIPER ==================

    $vigenciasPayload = construirPayloadVigenciasDocumentos($vigencias_documentos);

    $data = [
        "tipo_proveedor" => $tipo_proveedor,
        "nit_identificacion" => $nit,
        "nit_consecutivo" => $nit_consecutivo,
        "tipo_proveedor_hotel" => $tipo_proveedor_hotel,
        "nombre" => $nombre,
        "email_contabilidad" => $email_contabilidad,
        "email_cartera" => $email_cartera,
        "razon_social" => $razon_social,
        "pais" => $pais,
        "ciudad" => $ciudad,
        "departamento" => $departamento,
        "telefono_hotel" => $telefono_hotel,
        "direccion" => $direccion,
        "sitio_web" => $sitio_web,
        "diligenciado_por" => $nombre_usuario_logueado,
        "cuenta_bancaria" => $cuenta_bancaria,
        "categoria" => '',
        "numero_habitaciones" => '',
        "contacto_reservas" => $contacto_reservas,
        "email_reservas" => $email_reservas,
        "telefono_reservas" => $telefono_reservas,
        "contacto_extranet" => $contacto_extranet,
        "email_extranet" => $email_extranet,
        "telefono_extranet" => $telefono_extranet,
        "otro_servicio" => $otro_servicio,
        "cual_servicio" => $cual_servicio,
        "contactos" => [
            [
                'tipo' => 'Reservas',
                'nombre' => $contacto_reservas,
                'email' => $email_reservas,
                'telefono' => $telefono_reservas,
                'movil' => ''
            ],
            [
                'tipo' => 'Extranet',
                'nombre' => $contacto_extranet,
                'email' => $email_extranet,
                'telefono' => $telefono_extranet,
                'movil' => ''
            ]
        ],
        "has_rut" => ($rut_drive !== null),
        "has_rnt" => ($rnt_drive !== null),
        "has_certificacion" => ($certificacion_bancaria_drive !== null),
        "has_planes_especiales" => ($planes_especiales_drive !== null),
        "has_sostenibilidad" => ($certificado_sostenibilidad_drive !== null),
        "rut_nombre" => $_FILES['rut']['name'] ?? '',
        "rut_url" => $rut_drive ?? '',
        "rnt_nombre" => $_FILES['rnt']['name'] ?? '',
        "rnt_url" => $rnt_drive ?? '',
        "certificacion_bancaria_nombre" => $_FILES['certificacion_bancaria']['name'] ?? '',
        "certificacion_bancaria_url" => $certificacion_bancaria_drive ?? '',
        "planes_especiales_nombre" => $_FILES['planes_especiales']['name'] ?? '',
        "planes_especiales_url" => $planes_especiales_drive ?? '',
        "certificado_sostenibilidad_nombre" => $_FILES['certificado_sostenibilidad']['name'] ?? '',
        "certificado_sostenibilidad_url" => $certificado_sostenibilidad_drive ?? '',
        "vigencias_documentos" => $vigenciasPayload,
        "rut_fecha_vigencia" => $vigenciasPayload['rut']['fecha_vigencia'] ?? null,
        "rut_fuente_vigencia" => $vigenciasPayload['rut']['fuente_vigencia'] ?? null,
        "rut_estado_vigencia" => $vigenciasPayload['rut']['estado_vigencia'] ?? null,
        "rut_dias_vencimiento" => $vigenciasPayload['rut']['dias_vencimiento'] ?? null,
        "rnt_fecha_vigencia" => $vigenciasPayload['rnt']['fecha_vigencia'] ?? null,
        "rnt_fuente_vigencia" => $vigenciasPayload['rnt']['fuente_vigencia'] ?? null,
        "rnt_estado_vigencia" => $vigenciasPayload['rnt']['estado_vigencia'] ?? null,
        "rnt_dias_vencimiento" => $vigenciasPayload['rnt']['dias_vencimiento'] ?? null,
        "certificacion_bancaria_fecha_vigencia" => $vigenciasPayload['certificacion_bancaria']['fecha_vigencia'] ?? null,
        "certificacion_bancaria_fuente_vigencia" => $vigenciasPayload['certificacion_bancaria']['fuente_vigencia'] ?? null,
        "certificacion_bancaria_estado_vigencia" => $vigenciasPayload['certificacion_bancaria']['estado_vigencia'] ?? null,
        "certificacion_bancaria_dias_vencimiento" => $vigenciasPayload['certificacion_bancaria']['dias_vencimiento'] ?? null,
        "planes_especiales_fecha_vigencia" => $vigenciasPayload['planes_especiales']['fecha_vigencia'] ?? null,
        "planes_especiales_fuente_vigencia" => $vigenciasPayload['planes_especiales']['fuente_vigencia'] ?? null,
        "planes_especiales_estado_vigencia" => $vigenciasPayload['planes_especiales']['estado_vigencia'] ?? null,
        "planes_especiales_dias_vencimiento" => $vigenciasPayload['planes_especiales']['dias_vencimiento'] ?? null,
        "link_ficha" => $link_ficha,
        "usuario_creado" => $usuario,
        "rol_usuario" => $id_rol,
        "id_proveedor" => $id_proveedor_creado,
        "id_hotel" => $id_hotel_creado,
        "usuario_sesion" => $_SESSION['usuario'] ?? '',
        "correo_sesion" => $_SESSION['correo'] ?? '',
        "fecha_envio" => date("Y-m-d H:i:s")
    ];

    // ================== ENVIAR A JUNIPER ==================

    $juniperResponse = enviarProveedorJuniper($data);
    $juniper_id = $juniperResponse['juniper_id'] ?? null;

    // ================== GUARDAR ID JUNIPER EN TBL_ALOJAMIENTO_GENERAL ==================

    if (!empty($juniper_id) && $id_hotel_creado !== null && $id_hotel_creado > 0) {
        try {
            if (colExists($conn, 'tbl_alojamiento_general', 'juniper_id')) {
                $stmt_update = mysqli_prepare($conn, "
                    UPDATE tbl_alojamiento_general
                    SET juniper_id = ?
                    WHERE id_hotel = ?
                    LIMIT 1
                ");

                if ($stmt_update) {
                    mysqli_stmt_bind_param($stmt_update, "si", $juniper_id, $id_hotel_creado);
                    mysqli_stmt_execute($stmt_update);
                    mysqli_stmt_close($stmt_update);
                    error_log("ID Juniper ($juniper_id) guardado en tbl_alojamiento_general (Hotel ID: $id_hotel_creado)");
                }
            }
        } catch (Exception $e) {
            error_log("Error al guardar juniper_id en tbl_alojamiento_general: " . $e->getMessage());
        }
    }

    error_log("Proveedor enviado a Juniper - NIT: $nit, Juniper ID: " . ($juniper_id ?? 'No obtenido'));

    // ================== ENVIAR WEBHOOK ZOHO FLOW ==================

    $data['juniper_id'] = $juniper_id;
    $data['id_juniper'] = $juniper_id;
    $data['juniper_status_ok'] = $juniperResponse['ok'] ?? false;

    error_log("Enviando a Zoho - Juniper ID: " . ($juniper_id ?? 'NULL') . " - NIT: $nit");

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $webhook,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log("Error enviando webhook a Zoho Flow: " . curl_error($ch));
    } else {
        error_log("Respuesta Zoho Flow: " . substr((string)$response, 0, 200));
    }

    curl_close($ch);

    // El webhook del formulario de inscripción de hotel se dispara solo al aprobar la ficha.
    error_log("Registro turístico: no se envía webhook de formulario de inscripción. Hotel ID: " . ($id_hotel_creado ?? 'N/A'));

    echo '
    <script>
        alert("Proveedor registrado con éxito. Usuario creado: ' . addslashes($nit) . ' / Contraseña: ' . addslashes($nit) . '");
        window.location = "buscarProveedor.php";
    </script>
    ';

} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conn);

    if ($e->getCode() == 1062) {
        echo '
        <script>
            alert("Error: El NIT del proveedor ya existe.");
            window.location = "RegistroProveedoresTuristicos.php";
        </script>
        ';
    } else {
        echo '
        <script>
            alert("Error al registrar el proveedor: ' . addslashes($e->getMessage()) . '");
            window.location = "RegistroProveedoresTuristicos.php";
        </script>
        ';
    }
} catch (Exception $e) {
    mysqli_rollback($conn);

    echo '
    <script>
        alert("Error: ' . addslashes($e->getMessage()) . '");
        window.location = "RegistroProveedoresTuristicos.php";
    </script>
    ';
} finally {
    mysqli_close($conn);
}
?>
