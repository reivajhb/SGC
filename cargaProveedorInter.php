<?php
include "seguridad.php";
include "conexion.php";

// Obtener los valores del formulario
$identificacion = $_POST["identificacion"];
$nombre = $_POST["nombre"];
$email_proveedor = $_POST["email_proveedor"];

// Consulta preparada para verificar si el proveedor ya existe
$verificar_dato_query = "SELECT * FROM tbl_proveedores_inter WHERE identificacion = ?";
$stmt = mysqli_prepare($conn, $verificar_dato_query);
mysqli_stmt_bind_param($stmt, "s", $identificacion);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// Verificar si existe algún resultado
if (mysqli_num_rows($resultado) > 0) {
    // Si el proveedor ya existe, mostrar un mensaje de alerta
    echo '<script>
          alert("El proveedor ya existe");
          window.location = "RegistroProveedoresInter.php";
          </script>';
} else {
    // Si el proveedor no existe, insertarlo en la base de datos
    $insertar_query = "INSERT INTO tbl_proveedores_inter (identificacion, nombre, email_proveedor) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertar_query);
    mysqli_stmt_bind_param($stmt, "sss", $identificacion, $nombre, $email_proveedor);

    // Ejecutar la consulta preparada
    if (mysqli_stmt_execute($stmt)) {
        // Si la inserción es exitosa, redirigir a la página de búsqueda de proveedores internacionales
        echo '<script>
              alert("Proveedor cargado con éxito");
              window.location = "buscarProveedorInter.php";
              </script>';
    } else {
        // Mostrar un mensaje de error si hay algún problema con la inserción
        echo "Error al insertar el proveedor: " . mysqli_error($conn);
    }
}

// Cerrar la conexión
mysqli_close($conn);
?>