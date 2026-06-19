

<?php 

if($_POST['retencion'] ==  '0.035') {
$valor = $consulta[10];
$retencion = $_POST[$valor] * 0.035; 
$result = $valor - $retencion;  
echo  $retencion;
} else {
     echo "error";
}


 ?>