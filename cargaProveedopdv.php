<?php 
include "seguridad.php"
?>

<?php
include "seguridad.php";
include "conexion.php";

$nit = $_POST["nit"];
$nom_proveedor = $_POST["nom_proveedor"];
$email_contabilidad = $_POST["email_contabilidad"];
$email_cartera = $_POST["email_cartera"];

// Consulta preparada para verificar si el proveedor ya existe
$verificar_dato_query = "SELECT * FROM tbl_proveedores_pdv WHERE nit = ?";
$stmt = mysqli_prepare($conn, $verificar_dato_query);
mysqli_stmt_bind_param($stmt, "s", $nit);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// Verificar si existe algún resultado
if (mysqli_num_rows($resultado) > 0) {
    // Si existe el proveedor
    echo '<script>
          alert("El proveedor ya existe");
          window.location = "RegistroProveedoresTuristicos.php";
          </script>';
} else {
    // Si no existe el proveedor, insertarlo en la base de datos
    $insertar_query = "INSERT INTO tbl_proveedores_pdv (nit, nom_proveedor, email_contabilidad, email_cartera) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertar_query);
    mysqli_stmt_bind_param($stmt, "ssss", $nit, $nom_proveedor, $email_contabilidad, $email_cartera);

    if (mysqli_stmt_execute($stmt)) {
        // Si la inserción es exitosa
        echo '<script>
              alert("Proveedor cargado con éxito");
              window.location = "buscarProveedor.php";
              </script>';
    } else {
        // Mostrar si hay algún error al insertar el registro
        echo "Error al insertar el proveedor: " . mysqli_error($conn);
    }
}

// Cerrar la conexión
mysqli_close($conn);
?>
