<?php 
include "seguridad.php"
?>

<?php 

include("conexion.php");


$id_pago_ad  =  $_POST["id_pago_ad"];
$locacion =  $_POST["locacion"];
$valor  =  $_POST["valor"];
$novedad =  $_POST["novedad"];
$fecha =  $_POST["fecha"];
$estado = $_POST["estado"];


if ($_FILES["soporteAdmin"]) {

	$nombre_base = basename($_FILES ['soporteAdmin']['name']);
	$nombre_final = date("m-d-y"). "-". date("H-i-s"). "-" . $nombre_base;
	$ruta = "SoportesPagosAdministrativos/" .$nombre_final;

	$subirarchivo = move_uploaded_file($_FILES['soporteAdmin']['tmp_name'], $ruta);

	if ($subirarchivo) {
		$editarProveedor = "UPDATE tbl_pagos_administrativos SET locacion='$locacion', valor='$valor', novedad='$novedad', fecha='$fecha' , estado='$estado', soporteAdmin='$ruta' where id_pago_ad='$id_pago_ad' "; 
		$resultado = mysqli_query($conn, $editarProveedor);
		if ($resultado) {
			 echo '<script>
              alert("Pago Proveedor editado con exito");
              window.location = "consultaPagosAdministrativos.php";
              </script>';

		}
	}
}



?> 




