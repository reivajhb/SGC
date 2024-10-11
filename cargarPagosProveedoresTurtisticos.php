<?php 
include "seguridad.php"
?>

<?php 

include("conexion.php");

$proveedor =  $_POST["proveedor"];
$cop  =  $_POST["cop"];
$novedad =  $_POST["novedad"];
$fecha =  $_POST["fecha"];
$estado = $_POST["estado"];

if ($_FILES["archivo"]) {

	$nombre_base = basename($_FILES ['archivo']['name']);
	$nombre_final = date("m-d-y"). "-". date("H-i-s"). "-" . $nombre_base;
	$ruta = "FacturasPagosProveedoresTurtisticos/" .$nombre_final;

	$subirarchivo = move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta);

	if ($subirarchivo) {
		$insertar = "INSERT INTO tbl_proveedores_turtisticos (proveedor, cop, novedad, fecha, archivo, estado) VALUES ('$proveedor','$cop','$novedad','$fecha', '$ruta', '$estado')";
		$resultado = mysqli_query($conn, $insertar);
		if ($resultado) {
			 echo '<script>
              alert("Pago proveedor cargado con exito");
              window.location = "indexPagosProveedoresTuristicos.php";
              </script>';
		}
	}
}
 
?> 
	   
    