<?php 
include "seguridad.php"
?>


<?php 

include("conexion.php");


$id_proveedor  =  $_POST["id_proveedor"];
$proveedor =  $_POST["proveedor"];
$cop  =  $_POST["cop"];
$novedad =  $_POST["novedad"];
$fecha =  $_POST["fecha"];
$estado = $_POST["estado"];


if ($_FILES["soporteProveedor"]) {

	$nombre_base = basename($_FILES ['soporteProveedor']['name']);
	$nombre_final = date("m-d-y"). "-". date("H-i-s"). "-" . $nombre_base;
	$ruta = "SoportesProveedores/" .$nombre_final;

	$subirarchivo = move_uploaded_file($_FILES['soporteProveedor']['tmp_name'], $ruta);

	if ($subirarchivo) {
		$editarProveedor = "UPDATE tbl_proveedores_turtisticos SET proveedor='$proveedor', cop='$cop', novedad='$novedad', fecha='$fecha' , estado='$estado', soporteProveedor='$ruta' where id_proveedor='$id_proveedor' "; 
		$resultado = mysqli_query($conn, $editarProveedor);
		if ($resultado) {
			 echo '<script>
              alert("Pago Proveedor editado con exito");
              window.location = "consultaProveedoresTuristicos.php";
              </script>';

		}
	}
}



?> 




