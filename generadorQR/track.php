<?php
ob_start();
include('../facturacion/config/conexion.php'); // Conexión a la base de datos

if (!isset($_GET['qr_id']) || !is_numeric($_GET['qr_id'])) {
    die("Error: QR ID inválido.");
}

$qr_id = intval($_GET['qr_id']);

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

// 🔹 Verificar si el QR ID existe en tbl_campañas
$check_stmt = $conn->prepare("SELECT qr_id, url_destino FROM tbl_campañas WHERE qr_id = ?");
$check_stmt->bind_param("i", $qr_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$row = $check_result->fetch_assoc();

if (!$row) {
    die("Error: QR ID no existe en tbl_campañas.");
}

$url_destino = trim($row['url_destino']);

if (empty($url_destino)) {
    die("Error: URL de destino vacía.");
}

// 🔹 Capturar la IP y el User-Agent
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

// 🔹 Insertar en la tabla tbl_escaneos (ahora con qr_id correcto)
$insert_stmt = $conn->prepare("INSERT INTO tbl_escaneos (qr_id, ip, user_agent) VALUES (?, ?, ?)");
$insert_stmt->bind_param("iss", $row['qr_id'], $ip, $user_agent);

if (!$insert_stmt->execute()) {
    die("Error al registrar el escaneo: " . $conn->error);
}

// Agregar el parámetro ?src=qr_id si no está en la URL
$parsed_url = parse_url($url_destino);

// Revisar si ya tiene query string
if (isset($parsed_url['query']) && $parsed_url['query'] !== '') {
    $url_destino .= '&src=' . $qr_id;
} else {
    $url_destino .= '?src=' . $qr_id;
}

// 🔹 Redirigir al usuario a la URL de destino
ob_end_clean();
header("Location: " . $url_destino);
exit();
?>
