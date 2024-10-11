<?php 
include "seguridad.php"
?>
<?php 

include("conexion.php");

$locacion =  $_POST["locacion"];
$valor  =  $_POST["valor"];
$novedad =  $_POST["novedad"];
$fecha =  $_POST["fecha"];
$estado = $_POST["estado"];



if ($_FILES["archivo"]) {

	$nombre_base = basename($_FILES ['archivo']['name']);
	$nombre_final = date("m-d-y"). "-". date("H-i-s"). "-" . $nombre_base;
	$ruta = "FacturasAdministrativas/" .$nombre_final;

	$subirarchivo = move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta);

	if ($subirarchivo) {
		$insertar = "INSERT INTO tbl_pagos_administrativos (locacion, valor, novedad, fecha, archivo, estado) VALUES ('$locacion','$valor','$novedad','$fecha', '$ruta', '$estado')";
		$resultado = mysqli_query($conn, $insertar);
		if ($resultado) {
			 echo '<script>
              alert("Pago dministrativo cargado con exito");
              window.location = "indexPagosAdministrativos.php";
              </script>';
		}elseif ($resultado =  0) {

			echo '<script>alert("Error en la carga")</script>';
		}

	}
}
 
?> 
	   
    