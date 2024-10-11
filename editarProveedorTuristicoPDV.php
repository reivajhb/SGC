
<?php 
include "seguridad.php"
?>

<?php
include("conexion.php");
        $id_proveedor_pdv = $_POST['id_proveedor_pdv'];
        $nit   = $_POST ['nit'];
        $nom_proveedor  = $_POST ['nom_proveedor'];
        $email_contabilidad	 = $_POST['email_contabilidad'];
        $email_cartera = $_POST ['email_cartera'];
        $TipoRetencion = $_POST ['TipoRetencion'];
        $PorcentajeRTICA = $_POST ['PorcentajeRTICA'];
        $PorcentajeRTFUEN = $_POST ['PorcentajeRTFUEN'];
    


       $editarProveedorPDV = "UPDATE tbl_proveedores_pdv 
	   SET nit='$nit', nom_proveedor='$nom_proveedor', 
	   email_contabilidad='$email_contabilidad', email_cartera='$email_cartera' , 
	   TipoRetencion='$TipoRetencion', PorcentajeRTICA='$PorcentajeRTICA', 
	   PorcentajeRTFUEN='$PorcentajeRTFUEN' where id_proveedor_pdv='$id_proveedor_pdv' "; 
      
        $resultado = mysqli_query($conn, $editarProveedorPDV);
   
        if ($resultado) {
             echo '<script>
              alert("Proveedor Actualizado Con Éxito");
              window.location = "consultaProveedoresTuristicosCs.php";
              </script>';
        }elseif ($resultado =  0) {

            echo '<script>alert("Error en la carga")</script>';
        }

        



?>



  
