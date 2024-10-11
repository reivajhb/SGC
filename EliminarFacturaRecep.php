 <?php 
include "seguridad.php"
?>

 <?php


    include 'conexion.php'; 

    $id_factura_recep =$_GET['id_factura_recep'];
    $sentencia =   "DELETE FROM tbl_facturas_recep where id_factura_recep= '$id_factura_recep' "; 
    $resultadoEliminar = mysqli_query($conn,$sentencia);

  if ($resultadoEliminar) {
    echo '<script>
              alert("Registro eliminado con exito");
              window.location = "consultaFacturasRecep.php";
              </script>';

      
      
    }else {
             echo '<script>alert("Error")</script>';

    }
   ?>