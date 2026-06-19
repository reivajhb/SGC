<?php
include "../../facturacion/config/seguridad.php";
include "../../facturacion/config/conexion.php";

// PHPMailer
require '../../PHPMailer/Exception.php';
require '../../PHPMailer/PHPMailer.php';
require '../../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn->set_charset('utf8mb4');

// ==================== ROLES ====================
define('ROL_ADMIN', 1);
define('ROL_2', 2);
define('ROL_8', 8);
define('ROL_CADENA', 7);
define('ROL_GESTORAS', 9);

$idRol       = (int) ($_SESSION['id_rol'] ?? 0);
$isProveedor = (bool) ($_SESSION['PROV_AUTH'] ?? false);

// Roles permitidos para RECHAZAR ficha
$rolesPermitidosRechazo = [ROL_ADMIN, ROL_2, ROL_8, ROL_GESTORAS];

// Validar permisos (y bloquear proveedores)
if ($isProveedor === true || !in_array($idRol, $rolesPermitidosRechazo, true)) {
    $_SESSION['flash_error'] = "❌ No tiene permisos para rechazar fichas.";
    header("Location: ../vista/consultaHotel.php");
    exit();
}

// ==================== LEER POST ====================
$id_hotel = isset($_POST['id_hotel']) ? (int)$_POST['id_hotel'] : 0;
$motivo   = trim($_POST['motivo_rechazo'] ?? '');

if ($id_hotel <= 0 || $motivo === '') {
    $_SESSION['flash_error'] = "❌ Debe indicar un motivo de rechazo válido.";
    header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
    exit();
}

// Usuario que rechaza (para auditoría y correo)
$rechazado_por = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? ($_SESSION['correo'] ?? 'Usuario SGC');
$fecha_rechazo_db  = date('Y-m-d H:i:s');
$fecha_rechazo_txt = date('d/m/Y H:i');

// ==================== HELPERS SNAPSHOT ====================
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
    $sql = "SELECT * FROM {$table} WHERE id_hotel = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idHotel);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// ==================== CONSULTAR DATOS HOTEL ====================
$hotel = fetchOneHotel($conn, $id_hotel);

if (!$hotel) {
    $_SESSION['flash_error'] = "❌ No se encontró la ficha del hotel.";
    header("Location: ../vista/consultaHotel.php");
    exit();
}

$nombre_hotel  = $hotel['nombre'] ?? 'Hotel sin nombre';
$nit           = $hotel['nit'] ?? '';
$razon_social  = $hotel['razon_social'] ?? '';
$ciudad        = $hotel['ciudad'] ?? '';
$pais          = $hotel['pais'] ?? '';

// ==================== ARMAR SNAPSHOT COMPLETO (AUDITORIA) ====================
$tablasRelacionadas = [
    'tbl_alojamiento_contactos',
    'tbl_alojamiento_documentos',
    'tbl_alojamiento_habitaciones',
    'tbl_alojamiento_salones',
    'tbl_alojamiento_servicios',
];

$snapshot = [
    'tbl_alojamiento_general' => $hotel,
];

foreach ($tablasRelacionadas as $t) {
    $snapshot[$t] = fetchAllByHotel($conn, $t, $id_hotel);
}

$snapshot['_meta'] = [
    'evento'        => 'RECHAZO',
    'id_hotel'      => $id_hotel,
    'rechazado_por' => $rechazado_por,
    'fecha'         => $fecha_rechazo_db,
    'motivo'        => $motivo,
];

// JSON a guardar
$datosJson = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// ==================== GUARDAR AUDITORIA + ACTUALIZAR ESTADO (TRANSACCION) ====================
$auditId = null;

$conn->begin_transaction();
try {
    // Guardar auditoría del rechazo (snapshot)
    $stmtAud = $conn->prepare("
        INSERT INTO auditoria_eliminar (id_registro, tabla_afectada, usuario, fecha_hora, datos_eliminados)
        VALUES (?, ?, ?, NOW(), ?)
    ");

    $tabla_afectada = "RECHAZO_ALOJAMIENTO|relacionadas";
    $stmtAud->bind_param("isss", $id_hotel, $tabla_afectada, $rechazado_por, $datosJson);
    $stmtAud->execute();
    $auditId = $stmtAud->insert_id;
    $stmtAud->close();

    // ✅ ACTUALIZAR ESTADO A RECHAZADO (igual lógica que aprobación, pero RECHAZADO)
    // También limpiamos aprobado_por / fecha_aprobacion para consistencia.
    $stmtUp = $conn->prepare("
        UPDATE tbl_alojamiento_general
        SET estado_aprobacion = 'RECHAZADO',
            aprobado_por = NULL,
            fecha_aprobacion = NULL
        WHERE id_hotel = ?
        LIMIT 1
    ");
    $stmtUp->bind_param("i", $id_hotel);
    $stmtUp->execute();
    $stmtUp->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['flash_error'] = "❌ Error guardando rechazo (auditoría/estado): " . $e->getMessage();
    header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
    exit();
}

// ==================== CONTACTO COMERCIAL (EMAIL) ====================
$sql_contacto = "
    SELECT email, nombre
    FROM tbl_alojamiento_contactos
    WHERE id_hotel = ?
      AND tipo_contacto = 'Comercial'
      AND email IS NOT NULL AND email <> ''
    ORDER BY id_contacto ASC
    LIMIT 1
";


$stmt_c = $conn->prepare($sql_contacto);
$stmt_c->bind_param("i", $id_hotel);
$stmt_c->execute();
$res_c = $stmt_c->get_result();
$contacto = $res_c->fetch_assoc();
$stmt_c->close();

$correo_proveedor = $contacto['email'] ?? null;
$nombre_contacto  = $contacto['nombre'] ?? ($nombre_hotel ?? 'Proveedor');

// Link a la ficha
$link_consulta = "https://sgc.panamericanaviajes.com/facturacion/proveedores/vista/consultaHotel.php?id=" . $id_hotel;

// ==================== CORREO ====================
$subject = "RECHAZO FICHA PROVEEDOR DE ALOJAMIENTO - " . $nombre_hotel;

$bodyHtml = '
<p>Cordial saludo,</p>
<p>La ficha de inscripción de su establecimiento de alojamiento ha sido <strong>RECHAZADA</strong> por el equipo de Panamericana de Viajes.</p>

<table style="width: 100%; border-collapse: collapse;">
  <tr>
    <th colspan="2" style="background-color: #DF3456; color: white; padding: 10px; text-align: left;">
      Detalle del rechazo
    </th>
  </tr>
  <tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Hotel</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($nombre_hotel) . '</td></tr>
  <tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>NIT</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($nit) . '</td></tr>
  <tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Razón Social</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($razon_social) . '</td></tr>
  <tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Ciudad / País</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($ciudad) . ' / ' . htmlspecialchars($pais) . '</td></tr>
  <tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Rechazado por</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($rechazado_por) . '</td></tr>
  <tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Fecha</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($fecha_rechazo_txt) . '</td></tr>
  <tr>
    <td style="padding: 8px; border: 1px solid #ddd;"><strong>Motivo</strong></td>
    <td style="padding: 8px; border: 1px solid #ddd; white-space: pre-wrap;">' . nl2br(htmlspecialchars($motivo)) . '</td>
  </tr>
  <tr>
    <td style="padding: 8px; border: 1px solid #ddd;"><strong>Link de consulta</strong></td>
    <td style="padding: 8px; border: 1px solid #ddd;"><a href="' . $link_consulta . '">' . $link_consulta . '</a></td>
  </tr>
</table>

<p>Por favor realice los ajustes solicitados y vuelva a enviar la ficha para su revisión.</p>
<p>Atentamente,<br>Equipo de Negociaciones<br>Panamericana de Viajes</p>
';

$bodyAlt = "La ficha de alojamiento ha sido RECHAZADA.\n"
         . "Hotel: {$nombre_hotel}\n"
         . "NIT: {$nit}\n"
         . "Razón Social: {$razon_social}\n"
         . "Ciudad/Pais: {$ciudad} / {$pais}\n"
         . "Rechazado por: {$rechazado_por}\n"
         . "Fecha: {$fecha_rechazo_txt}\n"
         . "Motivo:\n{$motivo}\n"
         . "Link ficha: {$link_consulta}\n"
         . "Auditoría ID: {$auditId}\n";

try {
    $mail = new PHPMailer(true);

     $smtp = include __DIR__ . '/../../aws.php';

    // SMTP (igual que tu script)
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host       = $smtp['ses_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp['ses_user'];
    $mail->Password   = $smtp['ses_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $smtp['ses_port'];

    $mail->setFrom('negociaciones@panamericanaviajes.com', 'Sistema de Proveedores');

    // Enviar al proveedor si existe correo
    if ($correo_proveedor) {
        $mail->addAddress($correo_proveedor, $nombre_contacto);
    }

    // Copias internas
    $mail->addAddress('calidad@panamericanaviajes.com');
    $mail->addAddress('director.sistemas@panamericanaviajes.com');
    $mail->addCC('negociaciones@panamericanaviajes.com');

    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $bodyHtml;
    $mail->AltBody = $bodyAlt;

    $mail->send();

    $_SESSION['flash_success'] = "✅ Rechazo registrado (estado: RECHAZADO) (auditoría #{$auditId}) y correo enviado.";
} catch (Exception $e) {
    $_SESSION['flash_error'] = "⚠️ Rechazo registrado (estado: RECHAZADO) (auditoría #{$auditId}) pero falló el correo: " . $e->getMessage();
}

header("Location: ../vista/consultaHotel.php?id=" . $id_hotel);
exit();
