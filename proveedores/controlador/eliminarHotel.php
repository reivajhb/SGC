<?php
// eliminarHotel.php

include_once '../../facturacion/config/seguridad.php';
include_once '../../facturacion/config/conexion.php';
define('ZOHO_CALLED_AS_LIBRARY', true);
require_once __DIR__ . '/enviar_zoho.php';

// PHPMailer
require '../../PHPMailer/Exception.php';
require '../../PHPMailer/PHPMailer.php';
require '../../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DEBUG = (isset($_GET['debug']) && $_GET['debug'] === '1');

$conn->set_charset('utf8mb4');

// ====== Constantes de roles ======
define('ROL_ADMIN', 1);
define('ROL_CADENA', 7);
define('ROL_2', 2); // ANALISTA CONTABLE
define('ROL_8', 8);
define('ROL_GESTORAS', 9);

// ====== Variables de sesión ======
$idRol       = (int) ($_SESSION['id_rol'] ?? 0);
$nitCadena   = $_SESSION['usuario'] ?? null;
$isProveedor = (bool) ($_SESSION['PROV_AUTH'] ?? false);

// Usuario (para auditoría y correo)
$usuarioSesion = $_SESSION['usuario'] ?? ($_SESSION['correo'] ?? ('ID:' . ($_SESSION['id_usuario'] ?? 'NA')));

// ================== PERMISOS ==================
$rolesPermitidos = [ROL_ADMIN, ROL_2, ROL_8, ROL_GESTORAS, ROL_CADENA];

if (!in_array($idRol, $rolesPermitidos, true)) {
    echo "<script>
            alert('Acceso denegado: No tienes permisos para eliminar registros.');
            window.history.back();
          </script>";
    exit;
}

// ====== Validar ID del hotel ======
$idHotel = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($idHotel <= 0) {
    if ($DEBUG) {
        header('Content-Type: text/plain; charset=utf-8');
        die("ID de hotel no recibido o inválido.");
    }
    exit("ID de hotel no recibido o inválido.");
}

// ================== REGLA ESPECIAL PARA CADENA ==================
if ($idRol === ROL_CADENA) {
    if (!$nitCadena) {
        if ($DEBUG) {
            header('Content-Type: text/plain; charset=utf-8');
            die("No se pudo identificar la cadena asociada al usuario.");
        }
        exit("No se pudo identificar la cadena asociada al usuario.");
    }

    $stmt = $conn->prepare("
    SELECT h.id_hotel
    FROM tbl_alojamiento_general h
    LEFT JOIN tbl_usuarios u 
        ON u.id_usuario = h.id_usuario_creacion
    WHERE h.id_hotel = ?
      AND (h.usuario_creacion = ? OR u.usuario = ?)
    LIMIT 1
    ");
    $stmt->bind_param("iss", $idHotel, $nitCadena, $nitCadena);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $stmt->close();
        if ($DEBUG) {
            header('Content-Type: text/plain; charset=utf-8');
            die("No puedes eliminar este hotel: no pertenece a tu cadena.");
        }
        exit("No puedes eliminar este hotel: no pertenece a tu cadena.");
    }
    $stmt->close();
}

// ================== HELPERS SNAPSHOT (WHITELIST) ==================
function fetchOneHotel(mysqli $conn, int $idHotel): array {
    $stmt = $conn->prepare("SELECT * FROM tbl_alojamiento_general WHERE id_hotel = ? LIMIT 1");
    $stmt->bind_param("i", $idHotel);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row ?: [];
}

function fetchAllByHotel(mysqli $conn, string $table, int $idHotel): array {
    // Importante: el nombre de tabla NO se parametriza; por eso usamos whitelist afuera.
    $sql = "SELECT * FROM {$table} WHERE id_hotel = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idHotel);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// ================== CONSULTAR DATOS (SNAPSHOT COMPLETO) ==================
$general = fetchOneHotel($conn, $idHotel);

if (!$general) {
    if ($DEBUG) {
        header('Content-Type: text/plain; charset=utf-8');
        die("No se encontró el hotel a eliminar.");
    }
    exit("No se encontró el hotel a eliminar.");
}

// ================== NOTIFICAR A ZOHO CRM (ELIMINACIÓN) ==================
$zohoResult = construirYEnviarZoho($idHotel, $conn, false, 'delete');
if ($zohoResult['ok']) {
    error_log("✅ Hotel ID $idHotel → Zoho eliminación OK");
} else {
    error_log("❌ Hotel ID $idHotel → Zoho eliminación FAIL: " . $zohoResult['message']);
}

$nombreHotel     = $general['nombre'] ?? ('ID ' . $idHotel);
$nitHotel        = $general['nit'] ?? '';
$nitConsecutivo  = $general['nit_consecutivo'] ?? '';
// Si no tiene nit_consecutivo (hotel sin cadena), usar el nit normal
if (empty($nitConsecutivo)) {
    $nitConsecutivo = $nitHotel;
}
$ciudad          = $general['ciudad'] ?? '';
$pais            = $general['pais'] ?? '';
$razon           = $general['razon_social'] ?? '';

// Tablas relacionadas por id_hotel
$tablasRelacionadas = [
    'tbl_alojamiento_contactos',
    'tbl_alojamiento_documentos',
    'tbl_alojamiento_habitaciones',
    'tbl_alojamiento_salones',
    'tbl_alojamiento_servicios',
];

// Armamos snapshot tipo {tabla: data}
$snapshot = [
    'tbl_alojamiento_general' => $general,
];

foreach ($tablasRelacionadas as $t) {
    $snapshot[$t] = fetchAllByHotel($conn, $t, $idHotel);
}

// Meta opcional (te ayuda para debug/auditoría)
$snapshot['_meta'] = [
    'id_hotel' => $idHotel,
    'usuario'  => $usuarioSesion,
    'fecha'    => date('c'),
];

$datosJson = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// ================== ELIMINAR CON AUDITORIA (TRANSACCION) ==================
$conn->begin_transaction();
$auditId = null;

try {
    // 1) Guardar auditoría (snapshot completo)
    $stmtAud = $conn->prepare("
        INSERT INTO auditoria_eliminar (id_registro, tabla_afectada, usuario, fecha_hora, datos_eliminados)
        VALUES (?, ?, ?, NOW(), ?)
    ");

    $tabla = "tbl_alojamiento_general|relacionadas"; // etiqueta informativa
    $stmtAud->bind_param("isss", $idHotel, $tabla, $usuarioSesion, $datosJson);
    $stmtAud->execute();
    $auditId = $stmtAud->insert_id;
    $stmtAud->close();

    // 2) Eliminar hotel
    //    (Si tienes FK sin cascade y falla, mira el bloque opcional al final)
    $conn->query("DELETE FROM tbl_alojamiento_contactos WHERE id_hotel=" . (int)$idHotel);
    $conn->query("DELETE FROM tbl_alojamiento_documentos WHERE id_hotel=" . (int)$idHotel);
    $conn->query("DELETE FROM tbl_alojamiento_habitaciones WHERE id_hotel=" . (int)$idHotel);
    $conn->query("DELETE FROM tbl_alojamiento_salones WHERE id_hotel=" . (int)$idHotel);
    $conn->query("DELETE FROM tbl_alojamiento_servicios WHERE id_hotel=" . (int)$idHotel);
    $stmtDel = $conn->prepare("DELETE FROM tbl_alojamiento_general WHERE id_hotel = ? LIMIT 1");
    $stmtDel->bind_param("i", $idHotel);
    $stmtDel->execute();

    if ($stmtDel->affected_rows <= 0) {
        $stmtDel->close();
        throw new Exception("No se pudo eliminar (no hubo filas afectadas).");
    }

    $stmtDel->close();

    // 3) Commit
    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();

    die("Error eliminando hotel: " . $e->getMessage());
}


// ================== NOTIFICAR POR CORREO ==================
$mailError = null;

try {
    $smtp = require __DIR__ . '/../../aws.php';

    $mail = new PHPMailer(true);

    // Config SMTP
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = $smtp['ses_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp['ses_user'];
    $mail->Password = $smtp['ses_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $smtp['ses_port'];

    $mail->setFrom('negociaciones@panamericanaviajes.com', 'Sistema de Proveedores');

    // Destinatarios internos
    $mail->addAddress('negociaciones@panamericanaviajes.com');
    $mail->addAddress('director.sistemas@panamericanaviajes.com');

    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);

    $mail->Subject = "ALERTA: Eliminación de hotel en SGC - {$nombreHotel} (ID {$idHotel})";

    $bodyHtml = "
        <p><strong>Se realizó una eliminación de ficha de proveedor (hotel).</strong></p>
        <table style='width:100%; border-collapse:collapse;'>
            <tr><td style='padding:8px;border:1px solid #ddd;'><strong>ID Hotel</strong></td><td style='padding:8px;border:1px solid #ddd;'>{$idHotel}</td></tr>
            <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Hotel</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($nombreHotel) . "</td></tr>
            <tr><td style='padding:8px;border:1px solid #ddd;'><strong>NIT</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($nitHotel) . "</td></tr>
            <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Razón Social</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($razon) . "</td></tr>
            <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Ciudad / País</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($ciudad) . " / " . htmlspecialchars($pais) . "</td></tr>
            <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Usuario que eliminó</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($usuarioSesion) . "</td></tr>
            <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Auditoría ID</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars((string)$auditId) . "</td></tr>
            <tr><td style='padding:8px;border:1px solid #ddd;'><strong>Fecha/Hora</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . date('d/m/Y H:i') . "</td></tr>
        </table>
        <p style='margin-top:12px;'><strong>Nota:</strong> Se almacenó un snapshot (general + tablas relacionadas) en <code>auditoria_eliminar.datos_eliminados</code>.</p>
    ";

    $bodyAlt =
        "Se eliminó una ficha de hotel.\n" .
        "ID Hotel: {$idHotel}\n" .
        "Hotel: {$nombreHotel}\n" .
        "NIT: {$nitHotel}\n" .
        "Razón Social: {$razon}\n" .
        "Ciudad/Pais: {$ciudad} / {$pais}\n" .
        "Usuario que eliminó: {$usuarioSesion}\n" .
        "Auditoría ID: {$auditId}\n" .
        "Fecha/Hora: " . date('d/m/Y H:i') . "\n" .
        "Nota: Snapshot guardado en auditoria_eliminar.\n";

    $mail->Body    = $bodyHtml;
    $mail->AltBody = $bodyAlt;

    $mail->send();

} catch (Exception $e) {
    $mailError = $e->getMessage();
}

// ================== RESPUESTA / REDIRECCION ==================
if ($DEBUG) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "✅ Hotel eliminado.\n";
    echo "ID: {$idHotel}\n";
    echo "Hotel: {$nombreHotel}\n";
    echo "Auditoría ID: " . ($auditId ?? 'N/A') . "\n";
    echo "Correo: " . ($mailError ? "FALLÓ ({$mailError})" : "OK") . "\n";
    exit;
}

if ($mailError) {
    $_SESSION['flash_error'] = "⚠️ Hotel eliminado, pero falló la notificación por correo: {$mailError}";
} else {
    $_SESSION['flash_success'] = "✅ Hotel eliminado y notificado por correo.";
}

if ($idRol === ROL_CADENA) {
    header("Location: ../vista/listadoHotelesCadena.php?msg=deleted");
    exit;
}

$url = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../../';
if (strpos($url, 'msg=deleted') === false) {
    $url .= (strpos($url, '?') === false ? '?msg=deleted' : '&msg=deleted');
}
header("Location: " . $url);
exit;
