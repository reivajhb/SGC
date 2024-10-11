<?php
// insertar_mensaje.php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];
    $mensaje = $_POST['mensaje'];

    $sql = "INSERT INTO tbl_mensajes (id_usuario, mensaje) VALUES ('$id_usuario', '$mensaje')";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}
?>