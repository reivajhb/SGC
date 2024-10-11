<?php 
include "seguridad.php"
?>

<?php 

include("conexion.php");


$id_tiquete_resum= $_POST["id_tiquete_resum"];
$tipo_trato      = $_POST["tipo_trato"];
$vendedor        = $_POST["vendedor"];
$importe         = $_POST["importe"];            
$fecha_cierre    = $_POST["fecha_cierre"];
$nombre_trato    = $_POST["nombre_trato"];
$nom_agen_cli    = $_POST["nom_agen_cli"];
$tipo_moneda     = $_POST["tipo_moneda"];
$tipo_soli_serv  = $_POST["tipo_soli_serv"];
$canal_venta     = $_POST["canal_venta"];
$fecha_salida    = $_POST["fecha_salida"];
$num_pasajeros   = $_POST["num_pasajeros"];
$destinos        = $_POST["destinos"];
$Centro_costo    = $_POST["Centro_costo"];
$localizador     = $_POST["localizador"];
$fecha_emision   = $_POST["fecha_emision"];
$fecha_servicio  = $_POST["fecha_servicio"];
$num_tiquete     = $_POST["num_tiquete"];
$tipo_pago       = $_POST["tipo_pago"];
$proveedor       = $_POST["proveedor"];



$editarTiquete= "UPDATE tbl_resum_tiquetes SET id_tiquete_resum='$id_tiquete_resum', tipo_trato='$tipo_trato', vendedor='$vendedor', importe='$importe' , fecha_cierre='$fecha_cierre', nombre_trato='$nombre_trato', nom_agen_cli='$nom_agen_cli', tipo_moneda='$tipo_moneda', tipo_soli_serv='$tipo_soli_serv', canal_venta='$canal_venta', fecha_salida='$fecha_salida', num_pasajeros='$num_pasajeros', destinos='$destinos' , Centro_costo='$Centro_costo' , localizador='$localizador' , fecha_emision='$fecha_emision' , fecha_servicio='$fecha_servicio' , num_tiquete='$num_tiquete', tipo_pago='$tipo_pago', proveedor='$proveedor'  where id_tiquete_resum='$id_tiquete_resum' "; 
		$resultado = mysqli_query($conn, $editarTiquete);
		if ($resultado) {
			 echo '<script>
              alert("Informacion Tiquete editada con exito");
              window.location = "consultatiquetes.php";
              </script>';

		}
	
?> 




