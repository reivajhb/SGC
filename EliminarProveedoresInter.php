 <?php 
include "seguridad.php"
?>

 <?php


    include 'conexion.php'; 

    $id_pagoint =$_GET['id_pagoint'];
    $sentencia =   "DELETE FROM tbl_pagos_inter where id_pagoint= '$id_pagoint' "; 
    $resultadoEliminar = mysqli_query($conn,$sentencia);

  if ($resultadoEliminar) {
    echo '<script>
              alert("Pago proveedor internacional eliminado con exito");
              window.location = "consultaProveedoresInter.php";
              </script>';

      
      
    }else {
             echo '<script>alert("Error")</script>';

    }
   ?>

 