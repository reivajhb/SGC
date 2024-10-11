<?php 
include "seguridad.php"
?>


<?php 

include("conexion.php");

$id_facturas_corp  =  $_POST["id_facturas_corp"];
$nom_clien_corp  =  $_POST["nom_clien_corp"];
$nit =  $_POST["nit"];
$localizador  =  $_POST["localizador"];
$novedad =  $_POST["novedad"];
$fecha =  $_POST["fecha"];
$factura = $_POST["factura"];



	
		$editarFacturaCorp = "UPDATE tbl_facturas_corp SET nom_clien_corp='$nom_clien_corp', nit='$nit', localizador='$localizador', novedad='$novedad' , fecha='$fecha', factura='$factura' where id_facturas_corp='$id_facturas_corp' "; 
		$resultado = mysqli_query($conn, $editarFacturaCorp);
		if ($resultado) {
			 echo '<script>
              alert("Informacion Factura editada con exito");
              window.location = "consultaFacturasCorp.php";
              </script>';

		}
	




?>