<?php
include '../conexion.php';

$response = array();

$sql = "SELECT tbl_mensajes.*, tbl_usuarios.usuario AS usuario FROM tbl_mensajes
        JOIN tbl_usuarios ON tbl_mensajes.id_usuario = tbl_usuarios.id_usuario
        ORDER BY tbl_mensajes.fecha DESC";

$result = $conn->query($sql);

if ($result) {
    $mensajes = array();

    while ($row = $result->fetch_assoc()) {
        $mensajes[] = $row;
    }

    $response['success'] = true;
    $response['mensajes'] = $mensajes;
} else {
    $response['success'] = false;
    $response['error'] = $conn->error;
}

echo json_encode($response);

$conn->close();
?>