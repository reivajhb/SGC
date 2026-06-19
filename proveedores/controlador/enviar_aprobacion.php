<?php
// ==================== BOOT / INCLUDES ====================
include "../../facturacion/config/seguridad.php";
include "../../facturacion/config/conexion.php";

// Incluir funciones de Zoho
define('ZOHO_CALLED_AS_LIBRARY', true);
require_once __DIR__ . '/enviar_zoho.php';

require_once '../../PHPMailer/Exception.php';
require_once '../../PHPMailer/PHPMailer.php';
require_once '../../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug opcional: enviar_aprobacion.php?id=123&debug=1
$DEBUG = (isset($_GET['debug']) && $_GET['debug'] === '1');

if ($DEBUG) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "========================================\n";
    echo "DEBUG MODE ACTIVADO\n";
    echo "========================================\n";
    echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
    echo "ID Hotel: " . ($_GET['id'] ?? 'N/A') . "\n";
    echo "Usuario: " . ($_SESSION['usuario'] ?? 'N/A') . "\n\n";
}

// ==================== APROBADO POR / FECHA ====================
$aprobado_por = $_SESSION['usuario'] ?? 'Usuario SGC';
$fecha_aprobacion_db = date('Y-m-d H:i:s');

// ==================== FUNCIONES JUNIPER ====================
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
    } else {
        $soloNumeros = str_pad($soloNumeros, 16, '0', STR_PAD_LEFT);
        $soloNumeros = substr($soloNumeros, 0, 16);
        return substr($soloNumeros, 0, 8) . '-' . substr($soloNumeros, 8, 8);
    }
}

function enviarProveedorJuniper($data)
{
    $url      = "https://www.panamericanadeviajes.net/wsExportacion/wsSuppliers.asmx";
    $user     = "XMLZeusPNV";
    $password = "LQ3UbZke";

    $nit            = xmlSafe($data["nit"]           ?? "");
    $nombre         = xmlSafe($data["nombre"]         ?? "");
    $email          = xmlSafe($data["email"]          ?? "");
    $telefono       = xmlSafe($data["telefono"]       ?? "");
    $direccion      = xmlSafe($data["direccion"]      ?? "");
    $razonSocial    = xmlSafe($data["razon_social"]   ?? $nombre);
    $pais           = xmlSafe($data["pais"]           ?? "Colombia");
    $ciudad         = xmlSafe($data["ciudad"]         ?? "BOGOTA");
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
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $soap,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "http://juniper.es/addSupplier"',
            'Content-Length: ' . strlen($soap)
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    $errno    = curl_errno($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $juniperReject = false;
    $juniperId     = null;

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
        "ok"             => ($errno === 0 && $httpCode >= 200 && $httpCode < 300 && !$juniperReject),
        "juniper_id"     => $juniperId,
        "http_code"      => $httpCode,
        "curl_errno"     => $errno,
        "curl_error"     => $error,
        "juniper_reject" => $juniperReject,
        "response"       => $response
    ];
}

// ==================== VALIDAR CONEXION ====================
if (!isset($conn) || $conn->connect_error) {
    if ($DEBUG) { die("Error de conexion a la base de datos: " . ($conn->connect_error ?? 'N/A')); }
    $_SESSION['flash_error'] = "Error de conexion a la base de datos.";
    header("Location: ../vista/consultaHotel.php");
    exit();
}

$conn->set_charset('utf8mb4');

// ==================== OBTENER ID HOTEL ====================
$id_hotel = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_hotel <= 0) {
    if ($DEBUG) { die("ID de hotel invalido."); }
    $_SESSION['flash_error'] = "ID de hotel invalido.";
    header("Location: ../vista/consultaHotel.php");
    exit();
}

// ==================== CONSULTAR DATOS DEL HOTEL ====================
if ($DEBUG) { echo "[PASO 0] Consultando datos del hotel ID: $id_hotel...\n"; }

try {
    $stmt = $conn->prepare("
        SELECT id_hotel, nombre, nit, nit_consecutivo, razon_social,
               direccion, telefono, ciudad, pais, numero_cuenta, juniper_id,
               estado_firma, estado_aprobacion
        FROM tbl_alojamiento_general
        WHERE id_hotel = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $id_hotel);
    $stmt->execute();
    $hotel = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$hotel) {
        if ($DEBUG) { die("No se encontro el hotel con ID: $id_hotel"); }
        $_SESSION['flash_error'] = "No se encontro la ficha del hotel.";
        header("Location: ../vista/consultaHotel.php");
        exit();
    }
    if ($DEBUG) { echo "OK Hotel encontrado: " . $hotel['nombre'] . " | NIT: " . $hotel['nit'] . "\n"; }
} catch (Exception $e) {
    if ($DEBUG) { die("Error al consultar datos: " . $e->getMessage()); }
    $_SESSION['flash_error'] = "Error al consultar los datos del hotel.";
    header("Location: ../vista/consultaHotel.php");
    exit();
}

$estadoFirma = strtoupper(trim((string) ($hotel['estado_firma'] ?? 'PENDIENTE')));
$estadoAprobacion = strtoupper(trim((string) ($hotel['estado_aprobacion'] ?? 'PENDIENTE')));

if ($estadoAprobacion === 'APROBADO') {
    if ($DEBUG) { die("La ficha ya se encuentra aprobada. No se ejecuta nuevamente la aprobacion."); }
    $_SESSION['flash_success'] = "El hotel ha sido aprobado.";
    header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
    exit();
}

if ($estadoFirma !== 'FIRMADO') {
    if ($DEBUG) { die("No se puede aprobar la ficha porque aun no esta firmada. Estado firma: " . $estadoFirma); }
    $_SESSION['flash_error'] = "No se puede aprobar la ficha hasta que este firmada.";
    header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
    exit();
}

// ==================== OBTENER CONTACTO (para correo) ====================
$contacto = null;
try {
    $stmt_c = $conn->prepare("
        SELECT nombre, email FROM tbl_alojamiento_contactos
        WHERE id_hotel = ? AND tipo_contacto = 'Comercial' AND email != ''
        ORDER BY id_contacto ASC LIMIT 1
    ");
    $stmt_c->bind_param("i", $id_hotel);
    $stmt_c->execute();
    $contacto = $stmt_c->get_result()->fetch_assoc();
    $stmt_c->close();

    if (!$contacto) {
        $stmt_c2 = $conn->prepare("
            SELECT nombre, email FROM tbl_alojamiento_contactos
            WHERE id_hotel = ? AND email != ''
            ORDER BY id_contacto ASC LIMIT 1
        ");
        $stmt_c2->bind_param("i", $id_hotel);
        $stmt_c2->execute();
        $contacto = $stmt_c2->get_result()->fetch_assoc();
        $stmt_c2->close();
    }
} catch (Exception $e) {
    $contacto = null;
}

// ==================== GUARDAR APROBACION EN BD ====================
if ($DEBUG) { echo "\n[PASO 1] Guardando aprobacion en BD...\n"; }

try {
    $stmt_up = $conn->prepare("
        UPDATE tbl_alojamiento_general
        SET aprobado_por = ?, fecha_aprobacion = ?, estado_aprobacion = 'APROBADO'
        WHERE id_hotel = ?
          AND UPPER(TRIM(COALESCE(estado_firma, ''))) = 'FIRMADO'
          AND UPPER(TRIM(COALESCE(estado_aprobacion, 'PENDIENTE'))) <> 'APROBADO'
        LIMIT 1
    ");
    $stmt_up->bind_param("ssi", $aprobado_por, $fecha_aprobacion_db, $id_hotel);
    $stmt_up->execute();
    $affected = $stmt_up->affected_rows;
    $stmt_up->close();
    if ($DEBUG) { echo "OK Aprobacion guardada. Filas afectadas: $affected\n"; }

    if ($affected < 1) {
        if ($DEBUG) { die("No se actualizo la aprobacion porque la ficha no esta firmada o ya estaba aprobada."); }
        $_SESSION['flash_error'] = "No se pudo aprobar: la ficha no esta firmada o ya estaba aprobada.";
        header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
        exit();
    }
} catch (Exception $e) {
    if ($DEBUG) { die("Error guardando aprobacion en BD: " . $e->getMessage()); }
    $_SESSION['flash_error'] = "Error guardando aprobacion.";
    header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
    exit();
}

// ==================== ENVIAR A JUNIPER ====================
if ($DEBUG) { echo "\n[PASO 2] Validando envío a Juniper...\n"; }

$juniper_id = $hotel['juniper_id'] ?? null;

if (!empty($juniper_id)) {

    if ($DEBUG) {
        echo "[JUNIPER] Ya existe Juniper ID: {$juniper_id}. No se vuelve a enviar.\n";
    }

} else {

    if ($DEBUG) { echo "[JUNIPER] No tiene Juniper ID. Enviando a Juniper...\n"; }

    try {
        $emailJuniper = '';

        $stmt_email = $conn->prepare("
            SELECT email 
            FROM tbl_alojamiento_contactos 
            WHERE id_hotel = ? 
            AND email != '' 
            LIMIT 1
        ");
        $stmt_email->bind_param("i", $id_hotel);
        $stmt_email->execute();
        $result_email = $stmt_email->get_result();

        if ($row_email = $result_email->fetch_assoc()) {
            $emailJuniper = $row_email['email'];
        }

        $stmt_email->close();

        $nit_para_juniper = !empty($hotel['nit_consecutivo'])
            ? $hotel['nit_consecutivo']
            : $hotel['nit'];

        $dataJuniper = [
            "nit"             => $nit_para_juniper,
            "nombre"          => $hotel['nombre'],
            "email"           => $emailJuniper,
            "telefono"        => $hotel['telefono'],
            "direccion"       => $hotel['direccion'],
            "razon_social"    => $hotel['razon_social'],
            "pais"            => $hotel['pais'],
            "ciudad"          => $hotel['ciudad'],
            "cuenta_bancaria" => $hotel['numero_cuenta'] ?? ""
        ];

        if ($DEBUG) {
            echo "Datos enviados a Juniper:\n";
            echo json_encode($dataJuniper, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        }

        $juniperResponse = enviarProveedorJuniper($dataJuniper);
        $juniper_id = $juniperResponse['juniper_id'] ?? null;

        if ($DEBUG) {
            echo "HTTP Code: " . $juniperResponse['http_code'] . "\n";
            echo "CURL Errno: " . $juniperResponse['curl_errno'] . "\n";
            echo "Juniper Reject: " . ($juniperResponse['juniper_reject'] ? 'SI' : 'NO') . "\n";
            echo "Response raw:\n" . ($juniperResponse['response'] ?? 'N/A') . "\n\n";
        }

        if (!$juniperResponse['ok']) {
            $errMsg = "CURL error: " . ($juniperResponse['curl_error'] ?? '') . " | HTTP: " . $juniperResponse['http_code'];
            error_log("[JUNIPER ERROR] Hotel ID: $id_hotel | $errMsg");

            if ($DEBUG) {
                echo "[JUNIPER] ERROR: $errMsg\n";
            }
        }

        if (!empty($juniper_id)) {
            $stmt_j = $conn->prepare("
                UPDATE tbl_alojamiento_general 
                SET juniper_id = ? 
                WHERE id_hotel = ? 
                LIMIT 1
            ");

            if ($stmt_j) {
                $stmt_j->bind_param("si", $juniper_id, $id_hotel);
                $stmt_j->execute();
                $stmt_j->close();
            }

            if ($DEBUG) {
                echo "[JUNIPER] OK ID recibido: $juniper_id - guardado en BD\n";
            }
        } else {
            if ($DEBUG) {
                echo "[JUNIPER] No se obtuvo juniper_id\n";
            }
        }

    } catch (Exception $e) {
        if ($DEBUG) {
            echo "[JUNIPER] Excepcion: " . $e->getMessage() . "\n";
        }

        error_log("[JUNIPER EXCEPTION] Hotel ID: $id_hotel | " . $e->getMessage());
    }
}

// ==================== ENVIAR A ZOHO CRM ====================
if ($DEBUG) { echo "\n[PASO 3] Enviando a Zoho CRM...\n"; }

    $resultadoZoho = null;
    try {
        if (function_exists('construirYEnviarZoho')) {
            // Determinar si el proveedor original fue creado como Hotel o Cadena Hotelera
    $tipoProveedorHotel = '';

    try {
        $stmtTipo = $conn->prepare("
            SELECT tipo_proveedor_hotel
            FROM tbl_proveedores
            WHERE nit_identificacion = ?
            LIMIT 1
        ");

        if ($stmtTipo) {
            $stmtTipo->bind_param("s", $hotel['nit']);
            $stmtTipo->execute();
            $rowTipo = $stmtTipo->get_result()->fetch_assoc();
            $stmtTipo->close();

            $tipoProveedorHotel = trim($rowTipo['tipo_proveedor_hotel'] ?? '');
        }
    } catch (Exception $e) {
        error_log("[ZOHO] Error consultando tipo_proveedor_hotel: " . $e->getMessage());
    }

    // Si es Hotel, ya existe en CRM: actualizar.
    // Si es Cadena Hotelera, mantener flujo normal de aprobación/registro.
    $accionZoho = 'registrar';

    $resultadoRegistro = construirYEnviarZoho($id_hotel, $conn, $DEBUG, 'registrar');

    $resultadoEditar = construirYEnviarZoho($id_hotel, $conn, $DEBUG, 'editar');

    $resultadoZoho = [
        'ok' => ($resultadoRegistro['ok'] ?? false) && ($resultadoEditar['ok'] ?? false),
        'registro' => $resultadoRegistro,
        'editar' => $resultadoEditar
    ];
        if ($DEBUG) {
            echo "[ZOHO] " . ($resultadoZoho['ok'] ? 'OK' : 'ERROR') . " " . ($resultadoZoho['message'] ?? 'N/A') . "\n";
        }
    } else {
        if ($DEBUG) { echo "[ZOHO] Funcion construirYEnviarZoho() no encontrada\n"; }
    }
} catch (Exception $e) {
    if ($DEBUG) { echo "[ZOHO] Excepcion: " . $e->getMessage() . "\n"; }
    error_log("[ZOHO EXCEPTION] Hotel ID: $id_hotel | " . $e->getMessage());
}

// ==================== ENVIAR CORREOS ====================
$fecha_aprobacion_txt = date('d/m/Y H:i:s');
$nombre_hotel    = $hotel['nombre']           ?? '';
$nit             = $hotel['nit']              ?? '';
$nit_consecutivo = $hotel['nit_consecutivo']  ?? '';
$razon_social    = $hotel['razon_social']     ?? '';
$direccion       = $hotel['direccion']        ?? '';
$telefono        = $hotel['telefono']         ?? '';
$ciudad          = $hotel['ciudad']           ?? '';
$pais            = $hotel['pais']             ?? '';
$link_consulta   = "https://sgc.panamericanaviajes.com/facturacion/proveedores/vista/consultaHotel.php?id=" . $id_hotel;

$documentos_notificacion = [
    'RUT' => null,
    'Sostenibilidad' => null,
    'Certificacion Bancaria' => null
];

try {
    $stmtDocsNotif = $conn->prepare("
        SELECT tipo_documento, nombre_archivo, ruta_almacenamiento
        FROM tbl_alojamiento_documentos
        WHERE id_hotel = ?
        AND tipo_documento IN ('RUT', 'Sostenibilidad', 'Certificacion Bancaria', 'Certificación Bancaria')
        ORDER BY id_doc DESC
    ");

    if ($stmtDocsNotif) {
        $stmtDocsNotif->bind_param("i", $id_hotel);
        $stmtDocsNotif->execute();
        $resDocsNotif = $stmtDocsNotif->get_result();

        while ($docNotif = $resDocsNotif->fetch_assoc()) {
            $tipoDocNotif = $docNotif['tipo_documento'] ?? '';
            if ($tipoDocNotif === 'Certificación Bancaria') {
                $tipoDocNotif = 'Certificacion Bancaria';
            }

            if (array_key_exists($tipoDocNotif, $documentos_notificacion) && $documentos_notificacion[$tipoDocNotif] === null) {
                $documentos_notificacion[$tipoDocNotif] = [
                    'nombre_archivo' => $docNotif['nombre_archivo'] ?? '',
                    'ruta_almacenamiento' => $docNotif['ruta_almacenamiento'] ?? ''
                ];
            }
        }

        $stmtDocsNotif->close();
    }
} catch (Exception $e) {
    error_log("[CORREOS APROBACION] Error consultando documentos RUT/Sostenibilidad/Certificacion Bancaria: " . $e->getMessage());
}

$rutDoc = $documentos_notificacion['RUT'];
$sostenibilidadDoc = $documentos_notificacion['Sostenibilidad'];
$certificacionBancariaDoc = $documentos_notificacion['Certificacion Bancaria'];

$rutNombre = $rutDoc['nombre_archivo'] ?? 'No registrado';
$rutUrl = $rutDoc['ruta_almacenamiento'] ?? '';

$sostenibilidadNombre = $sostenibilidadDoc['nombre_archivo'] ?? 'No registrado';
$sostenibilidadUrl = $sostenibilidadDoc['ruta_almacenamiento'] ?? '';

$certificacionBancariaNombre = $certificacionBancariaDoc['nombre_archivo'] ?? 'No registrado';
$certificacionBancariaUrl = $certificacionBancariaDoc['ruta_almacenamiento'] ?? '';

$filaRutInternos = !empty($rutUrl)
    ? "<tr><td style='padding:8px;border:1px solid #ddd;'><strong>RUT para Contabilidad</strong></td><td style='padding:8px;border:1px solid #ddd;'><a href='" . htmlspecialchars($rutUrl) . "' target='_blank'>" . htmlspecialchars($rutNombre) . "</a></td></tr>"
    : "<tr><td style='padding:8px;border:1px solid #ddd;'><strong>RUT para Contabilidad</strong></td><td style='padding:8px;border:1px solid #ddd;'>No registrado</td></tr>";

$filaSostenibilidadInternos = !empty($sostenibilidadUrl)
    ? "<tr><td style='padding:8px;border:1px solid #ddd;'><strong>Certificado de Sostenibilidad para Calidad</strong></td><td style='padding:8px;border:1px solid #ddd;'><a href='" . htmlspecialchars($sostenibilidadUrl) . "' target='_blank'>" . htmlspecialchars($sostenibilidadNombre) . "</a></td></tr>"
    : "<tr><td style='padding:8px;border:1px solid #ddd;'><strong>Certificado de Sostenibilidad para Calidad</strong></td><td style='padding:8px;border:1px solid #ddd;'>No registrado</td></tr>";

$filaCertificacionBancariaInternos = !empty($certificacionBancariaUrl)
    ? "<tr><td style='padding:8px;border:1px solid #ddd;'><strong>Certificación Bancaria para Contabilidad</strong></td><td style='padding:8px;border:1px solid #ddd;'><a href='" . htmlspecialchars($certificacionBancariaUrl) . "' target='_blank'>" . htmlspecialchars($certificacionBancariaNombre) . "</a></td></tr>"
    : "<tr><td style='padding:8px;border:1px solid #ddd;'><strong>Certificación Bancaria para Contabilidad</strong></td><td style='padding:8px;border:1px solid #ddd;'>No registrado</td></tr>";

$bodyProveedorHtml = "
<p>Cordial saludo,</p>
<p>Nos complace informarle que la ficha de inscripcion de su establecimiento de alojamiento ha sido <strong>APROBADA</strong> como proveedor de Panamericana de Viajes.</p>
<h3>Datos de Proveedor de Alojamiento</h3>
<table style='width:100%;border-collapse:collapse;'>
  <tr><th colspan='2' style='background:#198754;color:white;padding:10px;text-align:left;'>Informacion de Registro</th></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Fecha de aprobacion</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($fecha_aprobacion_txt) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>NIT</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($nit) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Razon Social</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($razon_social) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Ciudad / Pais</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($ciudad) . " / " . htmlspecialchars($pais) . "</td></tr>
</table>
<p>A partir de este momento, su establecimiento podra ser considerado dentro de nuestros procesos de cotizacion y reserva.</p>
<p>Gracias por confiar en Panamericana de Viajes.</p>
<p>Atentamente,<br>Equipo de Negociaciones<br>Panamericana de Viajes</p>
";

$bodyProveedorAlt = "Su ficha de alojamiento ha sido APROBADA.\n"
    . "Fecha: {$fecha_aprobacion_txt}\n"
    . "Hotel: {$nombre_hotel}\nNIT: {$nit}\nRazon Social: {$razon_social}\n"
    . "Ciudad: {$ciudad} / {$pais}\n";

$bodyInternosHtml = "
<p>Cordial saludo,</p>
<p>Se informa que la ficha de inscripcion del proveedor de alojamiento fue <strong>APROBADA</strong>.</p>
<h3>Datos de Proveedor de Alojamiento</h3>
<table style='width:100%;border-collapse:collapse;'>
  <tr><th colspan='2' style='background:#198754;color:white;padding:10px;text-align:left;'>Informacion de Registro</th></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Aprobado por</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($aprobado_por) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Fecha de aprobacion</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($fecha_aprobacion_txt) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>NIT</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($nit) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>NIT Consecutivo</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($nit_consecutivo) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Razon Social</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($razon_social) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Direccion</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($direccion) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Telefono</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($telefono) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Ciudad / Pais</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($ciudad) . " / " . htmlspecialchars($pais) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Juniper ID</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($juniper_id ?? 'Pendiente') . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Link de Consulta</strong></td><td style='padding:8px;border:1px solid #ddd;'><a href='" . htmlspecialchars($link_consulta) . "' target='_blank'>" . htmlspecialchars($link_consulta) . "</a></td></tr>
  " . $filaRutInternos . "
  " . $filaCertificacionBancariaInternos . "
  " . $filaSostenibilidadInternos . "
</table>
<p><strong>Distribucion interna:</strong></p>
<ul>
    <li>Contabilidad: revisar RUT y datos bancarios/tributarios.</li>
    <li>Calidad: revisar certificado de sostenibilidad si fue registrado.</li>
    <li>SARLAFT: revisar documentacion del proveedor.</li>
</ul>
<p>Atentamente,<br>Sistema de Proveedores</p>
";

$bodyInternosAlt = "Ficha de alojamiento APROBADA.\n"
    . "Aprobado por: {$aprobado_por}\nFecha: {$fecha_aprobacion_txt}\n"
    . "Hotel: {$nombre_hotel}\nNIT: {$nit}\nRazon Social: {$razon_social}\n"
    . "Ciudad: {$ciudad} / {$pais}\nJuniper ID: " . ($juniper_id ?? 'N/A') . "\nLink: {$link_consulta}\n"
    . "RUT: " . (!empty($rutUrl) ? $rutUrl : 'No registrado') . "\n"
    . "Certificacion Bancaria: " . (!empty($certificacionBancariaUrl) ? $certificacionBancariaUrl : 'No registrado') . "\n"
    . "Sostenibilidad: " . (!empty($sostenibilidadUrl) ? $sostenibilidadUrl : 'No registrado') . "\n";

$bodySarlaftHtml = "
<p>Cordial saludo,</p>
<p>Se informa que el siguiente proveedor de alojamiento fue <strong>APROBADO</strong> y requiere revision SARLAFT.</p>
<h3>Datos del proveedor</h3>
<table style='width:100%;border-collapse:collapse;'>
  <tr><th colspan='2' style='background:#6f42c1;color:white;padding:10px;text-align:left;'>Revision SARLAFT</th></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Hotel</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($nombre_hotel) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>NIT</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($nit) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>NIT Consecutivo</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($nit_consecutivo) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Razon Social</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($razon_social) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Direccion</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($direccion) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Telefono</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($telefono) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Ciudad / Pais</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($ciudad) . " / " . htmlspecialchars($pais) . "</td></tr>
  <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Link de Consulta</strong></td><td style='padding:8px;border:1px solid #ddd;'><a href='" . htmlspecialchars($link_consulta) . "' target='_blank'>" . htmlspecialchars($link_consulta) . "</a></td></tr>
  " . $filaRutInternos . "
  " . $filaSostenibilidadInternos . "
</table>
<p>Atentamente,<br>Sistema de Proveedores</p>
";

$bodySarlaftAlt = "Proveedor de alojamiento aprobado para revision SARLAFT.\n"
    . "Hotel: {$nombre_hotel}\nNIT: {$nit}\nRazon Social: {$razon_social}\n"
    . "Ciudad: {$ciudad} / {$pais}\nLink: {$link_consulta}\n"
    . "RUT: " . (!empty($rutUrl) ? $rutUrl : 'No registrado') . "\n"
    . "Sostenibilidad: " . (!empty($sostenibilidadUrl) ? $sostenibilidadUrl : 'No registrado') . "\n";

if ($DEBUG) { echo "\n[PASO 4] Enviando correos de notificacion...\n"; }

try {
    $smtp = require __DIR__ . '/../../aws.php';

    // ==================== CORREO 1: PROVEEDOR ====================
    if (!empty($contacto['email'])) {
        try {
            $mailProveedor = new PHPMailer(true);
            $mailProveedor->SMTPDebug  = 0;
            $mailProveedor->isSMTP();
            $mailProveedor->Host       = $smtp['ses_host'];
            $mailProveedor->SMTPAuth   = true;
            $mailProveedor->Username   = $smtp['ses_user'];
            $mailProveedor->Password   = $smtp['ses_pass'];
            $mailProveedor->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mailProveedor->Port       = $smtp['ses_port'];

            $mailProveedor->setFrom('negociaciones@panamericanaviajes.com', 'Sistema de Proveedores');
            $mailProveedor->addAddress($contacto['email'], $contacto['nombre'] ?? $nombre_hotel);
            $mailProveedor->addCC('negociaciones@panamericanaviajes.com');
            $mailProveedor->addCC('director.sistemas@panamericanaviajes.com');

            $mailProveedor->CharSet = 'UTF-8';
            $mailProveedor->isHTML(true);
            $mailProveedor->Subject = "APROBACION PROVEEDOR DE ALOJAMIENTO - " . $nombre_hotel;
            $mailProveedor->Body    = $bodyProveedorHtml;
            $mailProveedor->AltBody = $bodyProveedorAlt;

            $mailProveedor->send();

            if ($DEBUG) { echo "OK Correo proveedor enviado a: " . $contacto['email'] . "\n"; }
        } catch (MailException $e) {
            error_log("Error enviando correo al proveedor: " . $mailProveedor->ErrorInfo);
            if ($DEBUG) { echo "Error correo proveedor: " . $mailProveedor->ErrorInfo . "\n"; }
        }
    } else {
        error_log("[CORREOS APROBACION] No se encontro correo del proveedor para hotel ID {$id_hotel}");
        if ($DEBUG) { echo "AVISO No se encontro correo externo del proveedor.\n"; }
    }

    // ==================== CORREO 2: INTERNOS ====================
    try {
        $mailInternos = new PHPMailer(true);
        $mailInternos->SMTPDebug  = 0;
        $mailInternos->isSMTP();
        $mailInternos->Host       = $smtp['ses_host'];
        $mailInternos->SMTPAuth   = true;
        $mailInternos->Username   = $smtp['ses_user'];
        $mailInternos->Password   = $smtp['ses_pass'];
        $mailInternos->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailInternos->Port       = $smtp['ses_port'];

        $mailInternos->setFrom('negociaciones@panamericanaviajes.com', 'Sistema de Proveedores');
        $mailInternos->addAddress('contabilidad@panamericanaviajes.com');
        $mailInternos->addAddress('contabilidad8@panamericanaviajes.com');
        $mailInternos->addAddress('lider.contabilidad@panamericanaviajes.com');
        $mailInternos->addAddress('calidad@panamericanaviajes.com');
        $mailInternos->addAddress('director.sistemas@panamericanaviajes.com');
        $mailInternos->addAddress('director.negociaciones@panamericanaviajes.com');
        $mailInternos->addAddress('soporteweb@panamericanaviajes.com');
        $mailInternos->addCC('negociaciones@panamericanaviajes.com');

        $mailInternos->CharSet = 'UTF-8';
        $mailInternos->isHTML(true);
        $mailInternos->Subject = "APROBACION INTERNA PROVEEDOR DE ALOJAMIENTO - " . $nombre_hotel;
        $mailInternos->Body    = $bodyInternosHtml;
        $mailInternos->AltBody = $bodyInternosAlt;

        $mailInternos->send();

        if ($DEBUG) { echo "OK Correo internos enviado.\n"; }
    } catch (MailException $e) {
        error_log("Error enviando correo internos: " . $mailInternos->ErrorInfo);
        if ($DEBUG) { echo "Error correo internos: " . $mailInternos->ErrorInfo . "\n"; }
    }

    // ==================== CORREO 3: SARLAFT ====================
    try {
        $mailSarlaft = new PHPMailer(true);
        $mailSarlaft->SMTPDebug  = 0;
        $mailSarlaft->isSMTP();
        $mailSarlaft->Host       = $smtp['ses_host'];
        $mailSarlaft->SMTPAuth   = true;
        $mailSarlaft->Username   = $smtp['ses_user'];
        $mailSarlaft->Password   = $smtp['ses_pass'];
        $mailSarlaft->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailSarlaft->Port       = $smtp['ses_port'];

        $mailSarlaft->setFrom('negociaciones@panamericanaviajes.com', 'Sistema de Proveedores');
        $mailSarlaft->addAddress('lider.transporte@panamericanaviajes.com');
        $mailSarlaft->addAddress('director.sistemas@panamericanaviajes.com');
        $mailSarlaft->addCC('negociaciones@panamericanaviajes.com');
        

        $mailSarlaft->CharSet = 'UTF-8';
        $mailSarlaft->isHTML(true);
        $mailSarlaft->Subject = "REVISION SARLAFT PROVEEDOR DE ALOJAMIENTO - " . $nombre_hotel;
        $mailSarlaft->Body    = $bodySarlaftHtml;
        $mailSarlaft->AltBody = $bodySarlaftAlt;

        $mailSarlaft->send();

        if ($DEBUG) { echo "OK Correo SARLAFT enviado a lider.transporte@panamericanaviajes.com\n"; }
    } catch (MailException $e) {
        error_log("Error enviando correo SARLAFT: " . $mailSarlaft->ErrorInfo);
        if ($DEBUG) { echo "Error correo SARLAFT: " . $mailSarlaft->ErrorInfo . "\n"; }
    }

    // ==================== ENVIAR CORREO A COMUNICACIONES CON FOTOS ====================

    try {
        $mailCom = new PHPMailer(true);

        $mailCom->SMTPDebug  = 0;
        $mailCom->isSMTP();
        $mailCom->Host       = $smtp['ses_host'];
        $mailCom->SMTPAuth   = true;
        $mailCom->Username   = $smtp['ses_user'];
        $mailCom->Password   = $smtp['ses_pass'];
        $mailCom->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailCom->Port       = $smtp['ses_port'];

        $mailCom->setFrom('negociaciones@panamericanaviajes.com', 'Sistema de Proveedores');
        $mailCom->addAddress('comunicaciones@panamericanaviajes.com');
        $mailCom->addAddress('director.sistemas@panamericanaviajes.com');

        $mailCom->CharSet = 'UTF-8';
        $mailCom->isHTML(true);

        $mailCom->Subject = 'Nuevo hotel aprobado para comunicaciones - ' . $nombre_hotel;

        $linksFotos = '';
        $totalFotos = 0;

        $stmtFotos = $conn->prepare("
            SELECT tipo_documento, nombre_archivo, ruta_almacenamiento
            FROM tbl_alojamiento_documentos
            WHERE id_hotel = ?
            AND tipo_documento IN (
                'Foto Promocional',
                'Foto Fachada',
                'Foto Habitaciones',
                'Foto Piscina',
                'Foto Zona Comun',
                'Foto Zona Común'
            )
            ORDER BY id_doc ASC
        ");

        if ($stmtFotos) {
            $stmtFotos->bind_param("i", $id_hotel);
            $stmtFotos->execute();
            $resFotos = $stmtFotos->get_result();

            while ($foto = $resFotos->fetch_assoc()) {
                $tipoFoto = $foto['tipo_documento'] ?? 'Fotografia';
                $nombreFoto = $foto['nombre_archivo'] ?? 'Foto promocional';
                $rutaFoto = $foto['ruta_almacenamiento'] ?? '';

                if (!empty($rutaFoto)) {
                    $linksFotos .= '
                        <li style="margin-bottom:8px;">
                            <strong>' . htmlspecialchars($tipoFoto) . ':</strong>
                            <a href="' . htmlspecialchars($rutaFoto) . '" target="_blank">
                                ' . htmlspecialchars($nombreFoto) . '
                            </a>
                        </li>
                    ';
                    $totalFotos++;
                }
            }

            $stmtFotos->close();
        }

        if ($totalFotos === 0) {
            $linksFotos = '<li>No se encontraron fotografias registradas.</li>';
        }

        $bodyComunicaciones = "
            <p>Cordial saludo,</p>

            <p>
                Se informa que el siguiente hotel fue aprobado como proveedor de alojamiento
                y queda disponible para revision del area de Comunicaciones.
            </p>

            <h3 style='color:#0d6efd;'>Datos del hotel aprobado</h3>

            <table style='width:100%;border-collapse:collapse;font-family:Arial,sans-serif;'>
                <tr>
                    <th colspan='2' style='background:#0d6efd;color:white;padding:10px;text-align:left;'>
                        Informacion del proveedor
                    </th>
                </tr>

                <tr>
                    <td style='padding:8px;border:1px solid #ddd;'><strong>Hotel</strong></td>
                    <td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($nombre_hotel) . "</td>
                </tr>

                <tr>
                    <td style='padding:8px;border:1px solid #ddd;'><strong>NIT</strong></td>
                    <td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($nit) . "</td>
                </tr>

                <tr>
                    <td style='padding:8px;border:1px solid #ddd;'><strong>Razon Social</strong></td>
                    <td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($razon_social) . "</td>
                </tr>

                <tr>
                    <td style='padding:8px;border:1px solid #ddd;'><strong>Ciudad / Pais</strong></td>
                    <td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($ciudad) . " / " . htmlspecialchars($pais) . "</td>
                </tr>

                <tr>
                    <td style='padding:8px;border:1px solid #ddd;'><strong>Fecha aprobacion</strong></td>
                    <td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($fecha_aprobacion_txt) . "</td>
                </tr>

                <tr>
                    <td style='padding:8px;border:1px solid #ddd;'><strong>Link de consulta</strong></td>
                    <td style='padding:8px;border:1px solid #ddd;'>
                        <a href='" . htmlspecialchars($link_consulta) . "'>" . htmlspecialchars($link_consulta) . "</a>
                    </td>
                </tr>
            </table>

            <p>
                Se relacionan los enlaces de Google Drive de las fotografias registradas en la ficha del hotel para su uso interno.
            </p>

            <h3 style='color:#0d6efd;'>Fotografias del hotel</h3>

            <p>
                A continuacion se listan las fotografias registradas en la ficha del hotel.
                Haga clic en cada enlace para visualizar la imagen.
            </p>

            <ul>
                {$linksFotos}
            </ul>

            <p>Atentamente,<br>Sistema de Proveedores</p>
        ";

        $mailCom->Body = $bodyComunicaciones;

        $mailCom->AltBody =
            "Nuevo hotel aprobado para comunicaciones\n" .
            "Hotel: {$nombre_hotel}\n" .
            "NIT: {$nit}\n" .
            "Ciudad/Pais: {$ciudad} / {$pais}\n" .
            "Link: {$link_consulta}\n";

        $mailCom->send();

        if ($DEBUG) {
            echo "OK Correo enviado a comunicaciones con {$totalFotos} enlaces de fotos.\n";
        }

    } catch (MailException $e) {
        error_log("Error enviando correo a comunicaciones: " . $mailCom->ErrorInfo);
        if ($DEBUG) { echo "Error correo comunicaciones: " . $mailCom->ErrorInfo . "\n"; }
    }

    $_SESSION['flash_success'] = "El hotel ha sido aprobado.";
} catch (Exception $e) {
    if ($DEBUG) { echo "Error general al enviar correos: " . $e->getMessage() . "\n"; }
    $_SESSION['flash_error'] = "Hotel aprobado y enviado a sistemas externos, pero hubo un error general al enviar las notificaciones.";
}

// ==================== RESUMEN DEBUG / REDIRECCION ====================
if ($DEBUG) {
    echo "\n========================================\n";
    echo "RESUMEN FINAL\n";
    echo "========================================\n";
    echo "Hotel ID:   $id_hotel\n";
    echo "Hotel:      $nombre_hotel\n";
    echo "NIT:        $nit\n";
    echo "Juniper ID: " . ($juniper_id ?? 'NO recibido') . "\n";
    echo "Zoho:       " . (isset($resultadoZoho) && $resultadoZoho['ok'] ? 'OK Enviado' : 'ERROR/No enviado') . "\n";
    echo "Estado:     APROBADO\n";
    echo "========================================\n";
    exit;
}

header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
exit();
