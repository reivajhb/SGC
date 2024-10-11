<?php 
include "seguridad.php"
?>

<?php 

include("conexion.php");

$nit =  $_POST["nit"];
$nom_clien_corp  =  $_POST["nom_clien_corp"];
$email_interesado =  $_POST["email_interesado"];


$Verifica_Dato = "SELECT * FROM tbl_clientes_corp WHERE nit = '$nit'";

$Verif_Dato = mysqli_query($conn, $Verifica_Dato);

$N_Dato = mysqli_num_rows($Verif_Dato);

if($N_Dato > 0) {

	// Si Existe el dato

	 echo '<script>
              alert("El cliente ya existe");
              window.location = "RegistroClientesCorp.php";
              </script>';

} else{

	// Si No existe el dato
	$insertar = "INSERT INTO tbl_clientes_corp (nit, nom_clien_corp, email_interesado) VALUES ('$nit ','$nom_clien_corp','$email_interesado')";

}if (mysqli_query($conn, $insertar)) {
	

   
              echo '<script>
              alert("Proveedor cargado con exito");
              window.location = "buscarClienteHAAFac.php";
              </script>';

}else{
		// Mostramos si hay algun error al insertar registro
	echo "Error: " . $insertar . "" . mysqli_error($conn);
}


	 

	
 
?> 

	
	 