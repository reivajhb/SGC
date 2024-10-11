<?php 
include "seguridad.php";
include 'conexion.php'; 

// Captura el nombre de usuario actual desde la sesión
$usuario_eliminacion = $_SESSION['usuario'];

// Recupera el ID del anticipo a eliminar de la URL
$id_anticipo = $_GET['id_anticipo'];

// Consulta para recuperar los datos del registro que será eliminado
$consulta_registro = "SELECT * FROM tbl_anticipos WHERE id_anticipo = '$id_anticipo'";
$resultado_registro = mysqli_query($conn, $consulta_registro);

if ($resultado_registro && mysqli_num_rows($resultado_registro) > 0) {
    // Si se encontraron resultados, almacenamos los datos antes de eliminar el registro
    $registro_eliminado = mysqli_fetch_assoc($resultado_registro);
    
    // Almacenamos los datos del registro eliminado en la tabla de auditoría
    $tabla_afectada = "tbl_anticipos";
    $datos_eliminados = json_encode($registro_eliminado); // Convertimos el array a formato JSON
    $sentencia_auditoria = "INSERT INTO auditoria_eliminar (id_registro, tabla_afectada, usuario, datos_eliminados) 
                            VALUES ('$id_anticipo', '$tabla_afectada', '$usuario_eliminacion', '$datos_eliminados')";
    $resultado_auditoria = mysqli_query($conn, $sentencia_auditoria);
    
    // Si se logró almacenar en la auditoría, procedemos con la eliminación en la tabla principal
    if ($resultado_auditoria) {
        // Realiza la eliminación en la tabla principal
        $sentencia_eliminar = "DELETE FROM tbl_anticipos WHERE id_anticipo = '$id_anticipo'"; 
        $resultado_eliminar = mysqli_query($conn, $sentencia_eliminar);
        
        if ($resultado_eliminar) {
            echo '<script>
                      alert("Pago anticipo proveedor eliminado con éxito");
                      window.location = "consultaProveedoresPrepagoRT.php";
                      </script>';
        } else {
            echo '<script>alert("Error al eliminar el registro: ' . mysqli_error($conn) . '")</script>';
        }
    } else {
        echo '<script>alert("Error al registrar la auditoría: ' . mysqli_error($conn) . '")</script>';
    }
} else {
    echo '<script>alert("Error: No se encontraron datos para el registro con ID ' . $id_anticipo . '")</script>';
}
?>



 