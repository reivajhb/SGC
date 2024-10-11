
<?php 
include "seguridad.php"
?>

<?php
include("conexion.php");
        $id_anticipo = $_POST['id_anticipo'];
        $fecha   = $_POST ['fecha'];
        $identificacion  = $_POST ['identificacion'];
        $proveedor = $_POST['proveedor'];
        $email_proveedor = $_POST ['email_proveedor'];
        $localizador = $_POST ['localizador'];
        $num_factura = $_POST ['num_factura'];
        $concepto = $_POST ['concepto'];
        $descripcion = $_POST ['descripcion'];
        $moneda = $_POST ['moneda'];
        $valor = $_POST ['valor'];
        $usuario = $_POST ['usuario'];
        $fecha_ingreso = $_POST ['fecha_ingreso'];
        $certificacion = $_POST['certificacion'];
        $fecha_salida = $_POST ['fecha_salida'];
        $cuentadecobro = $_POST['cuentadecobro'];
        $ValorTotalApagar = $_POST['ValorTotalApagar'];
        $estado = $_POST['estado'];
        $fecha_Retencion =$_POST['fecha_Retencion'];
        $descripcionRT =$_POST['descripcionRT'];


       $editarProveedorPrepago = "UPDATE tbl_anticipos SET fecha='$fecha', identificacion='$identificacion', proveedor='$proveedor', email_proveedor='$email_proveedor' , localizador='$localizador', num_factura='$num_factura', concepto='$concepto', descripcion='$descripcion', moneda='$moneda', valor='$valor', usuario='$usuario', fecha_ingreso='$fecha_ingreso',certificacion='$certificacion', fecha_salida='$fecha_salida', cuentadecobro='$cuentadecobro', ValorTotalApagar='$ValorTotalApagar', estado='$estado', fecha_Retencion='$fecha_Retencion', descripcionRT='$descripcionRT' where id_anticipo='$id_anticipo' "; 
      
        $resultado = mysqli_query($conn, $editarProveedorPrepago);
   
        if ($resultado) {
             echo '<script>
              alert("Pago anticipo proveedor actualizado con exito");
              window.location = "consultaProveedoresPrepago.php";
              </script>';
        }elseif ($resultado =  0) {

            echo '<script>alert("Error en la carga")</script>';
        }

        



?>



  
