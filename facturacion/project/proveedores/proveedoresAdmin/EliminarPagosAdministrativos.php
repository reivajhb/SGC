<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php"; 

try {
    // Captura el nombre de usuario actual desde la sesión
    $usuario_eliminacion = $_SESSION['usuario'] ?? 'Sistema';

    // Recupera el ID del pago a eliminar de la URL
    $id_pago = $_GET['id_pago'] ?? null;

    if (is_null($id_pago)) {
        throw new Exception("ID de pago no proporcionado.");
    }

    // === PASO 1: Consulta para recuperar los datos del registro a eliminar (con sentencia preparada) ===
    $consulta_registro = "SELECT * FROM tbl_pagos WHERE id_pago = ?";
    $stmt_registro = mysqli_prepare($conn, $consulta_registro);
    mysqli_stmt_bind_param($stmt_registro, "i", $id_pago);
    mysqli_stmt_execute($stmt_registro);
    $resultado_registro = mysqli_stmt_get_result($stmt_registro);

    if (mysqli_num_rows($resultado_registro) === 0) {
        throw new Exception("Error: No se encontraron datos para el registro con ID " . $id_pago);
    }
    
    // Si se encontraron resultados, almacenamos los datos antes de eliminar el registro
    $registro_eliminado = mysqli_fetch_assoc($resultado_registro);
    mysqli_stmt_close($stmt_registro);
    
    // === PASO 2: Almacenamos los datos en la tabla de auditoría (con sentencia preparada) ===
    $tabla_afectada = "tbl_pagos";
    $datos_eliminados = json_encode($registro_eliminado);
    $sentencia_auditoria = "INSERT INTO auditoria_eliminar (id_registro, tabla_afectada, usuario, datos_eliminados) VALUES (?, ?, ?, ?)";
    $stmt_auditoria = mysqli_prepare($conn, $sentencia_auditoria);
    mysqli_stmt_bind_param($stmt_auditoria, "isss", $id_pago, $tabla_afectada, $usuario_eliminacion, $datos_eliminados);
    $resultado_auditoria = mysqli_stmt_execute($stmt_auditoria);

    if (!$resultado_auditoria) {
        throw new Exception("Error al registrar la auditoría: " . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt_auditoria);

    // === PASO 3: Eliminamos el registro de la tabla principal (con sentencia preparada) ===
    $sentencia_eliminar = "DELETE FROM tbl_pagos WHERE id_pago = ?"; 
    $stmt_eliminar = mysqli_prepare($conn, $sentencia_eliminar);
    mysqli_stmt_bind_param($stmt_eliminar, "i", $id_pago);
    $resultado_eliminar = mysqli_stmt_execute($stmt_eliminar);

    if (!$resultado_eliminar) {
        throw new Exception("Error al eliminar el registro: " . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt_eliminar);
    
    echo '<script>
        alert("Pago proveedor eliminado con éxito");
        window.location = "consultaPagosAdministrativos.php";
        </script>';

} catch (Exception $e) {
    echo '<script>alert("Error: ' . htmlspecialchars($e->getMessage()) . '")</script>';
} finally {
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?>
