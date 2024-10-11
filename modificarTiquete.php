<?php 
include "seguridad.php"

 ?>

 <?php


   $consulta = ConsultarPaAdmin($_GET['id_tiquete_resum']);
   function ConsultarPaAdmin($id_tiquete_resum)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_resum_tiquetes  where id_tiquete_resum = '".$id_tiquete_resum ."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrar = $ejecutar->fetch_assoc();
    return [
    $mostrar['id_tiquete_resum'],
    $mostrar['tipo_trato'],
    $mostrar['vendedor'],
    $mostrar['importe'],
    $mostrar['fecha_cierre'],
    $mostrar['nombre_trato'],
    $mostrar['nom_agen_cli'],
    $mostrar['tipo_moneda'],
    $mostrar['tipo_soli_serv'],
    $mostrar['canal_venta'],
    $mostrar['fecha_salida'],
    $mostrar['num_pasajeros'],
    $mostrar['destinos'],
    $mostrar['Centro_costo'],
    $mostrar['localizador'],
    $mostrar['fecha_emision'],
    $mostrar['fecha_servicio'],
    $mostrar['num_tiquete'],
    $mostrar['tipo_pago'],
    $mostrar['proveedor'],
    $mostrar['ruta'],
    $mostrar['emd']
    
  ];
  }
   ?>

 

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SGC</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    
</head>

<body>
    <?php include "sidebar.php"; ?>
    <br>
    <br>
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="p-3">
                    <div class="d-flex align-items-center p-3 border bg-primary text-white">
                        <h2 class="">Modificar Tiquete: <?php echo $consulta[17] ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card mt-3">
                    <div class="card-body p-5">
                        <form action="editartiquetes.php" method="post" enctype="multipart/form-data">
                            <input name="id_tiquete_resum" type="hidden" class="form-control"
                                value="<?php echo $consulta[0] ?>">
                            <form action="editartiquetes.php" class="container-fluid" method="post" enctype="multipart/form-data" >
   <input name="id_tiquete_resum" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[0]?>">

      <div class="form-row">
    <div class="col">
      <label for="exampleInputEmail1">Tipo de trato</label>
    <input name="tipo_trato" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[1]?>">
    </div>
    <div class="col">
      <label for="exampleInputEmail1">Vendedor</label>
    <input name="vendedor" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[2]?>">
    </div>
  </div>
  <div class="form-row">
    <div class="col">
      <label for="exampleInputEmail1">Valor</label>
    <input name="importe" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[3]?>">
    </div>
    <div class="col">
      <label for="exampleInputEmail1">Fecha de cierre</label>
    <input name="fecha_cierre" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[4]?>">
    </div>
  </div>
  <div class="form-row">
    <div class="col">
      <label for="exampleInputEmail1">Nombre del trato</label>
    <input name="nombre_trato" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[5]?>">
    </div>
    <div class="col">
      <label for="exampleInputEmail1">Nombre Agencia/Cliente</label>
    <input name="nom_agen_cli" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[6]?>">
    </div>
  </div>
  <div class="form-row">
    <div class="col">
      <label for="exampleInputEmail1">Tipo de moneda</label>
    <input name="tipo_moneda" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[7]?>">
    </div>
    <div class="col">
      <label for="exampleInputEmail1">Tipo de solicitud o servicio</label>
    <input name="tipo_soli_serv" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[8]?>">
    </div>
  </div>
  <div class="form-row">
    <div class="col">
      <label for="exampleInputEmail1">Canal de venta</label>
    <input name="canal_venta" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[9]?>">
    </div>
    <div class="col">
      <label for="exampleInputEmail1">Fecha de salida</label>
    <input name="fecha_salida" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[10]?>">
    </div>
  </div>
  <div class="form-row">
    <div class="col">
      <label for="exampleInputEmail1">Numero de pasajeros</label>
    <input name="num_pasajeros" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[11]?>">
    </div>
    <div class="col">
      <label for="exampleInputEmail1">Destinos</label>
    <input name="destinos" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[12]?>">
    </div>

  </div>
  <div class="form-row">
    <div class="col">
      <label for="exampleInputEmail1">Cento de costo</label>
    <input name="Centro_costo" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[13]?>">
    </div>
    <div class="col">
      <label for="exampleInputEmail1">Localizador</label>
    <input name="localizador" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[14]?>">
    </div>
    
  </div>
  <div class="form-row">
    <div class="col">
      <label for="exampleInputEmail1">Fecha de emision</label>
    <input name="fecha_emision" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[15]?>">
    </div>
    <div class="col">
      <label for="exampleInputEmail1">Fecha de servicio</label>
    <input name="fecha_servicio" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[16]?>">
    </div>
    
  </div>
  <div class="form-row">
    <div class="col">
      <label for="exampleInputEmail1">No. Tiquete</label>
    <input name="num_tiquete" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[17]?>">
    </div>
    <div class="col">
      <label for="exampleInputEmail1">Tipo de pago</label>
    <input name="tipo_pago" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[18]?>">
    </div>
    
  </div>
  <div class="form-row">
    <div class="col">
      <label for="exampleInputEmail1">Aerolinea</label>
    <input name="proveedor" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consulta[19]?>">
    </div>
  </div>
  <br> 
  <div class="form-row">
    <div class="col">
      <div class="card text-white bg-dark mb-3" style="max-width: 100rem;">
        <div class="card-header">Productos Adicionales</div>
        <div class="card-body">
<?php
// Cadena de entrada
$cadena = $consulta[21];
$partes_cadena = explode(',', $cadena);

// Verificar si hay al menos un dato separado por coma
if(count($partes_cadena) >= 1) {
    // Si solo hay un dato que no está separado por comas, agregamos una parte vacía para que el código siga funcionando correctamente
    if (count($partes_cadena) == 1) {
        $partes_cadena[] = "";
    }

    // Inicializar un array vacío para almacenar las partes de cada variable
    $partes_variables = [];
    
    // Iterar sobre cada dato separado por coma
    foreach ($partes_cadena as $dato) {
        // Dividir el dato en partes utilizando el punto y coma como delimitador y filtrar partes vacías
        $partes_variables[] = array_filter(explode(';', $dato));
    }

    // Obtener el número máximo de partes de todas las variables
    $max_length = max(array_map('count', $partes_variables));

    // Imprimir la tabla
    echo "<table border='1'>";
    echo "<tr>";
    
    // Imprimir encabezados de columna para cada variable
    for ($i = 1; $i <= count($partes_cadena); $i++) {
        echo "<th>Adicional $i</th>";
    }
    
    echo "</tr>";

    // Iterar sobre las partes de las variables
    for ($j = 0; $j < $max_length; $j++) {
        echo "<tr>";
        // Iterar sobre cada variable
        for ($i = 0; $i < count($partes_cadena); $i++) {
            echo "<td>";
            // Imprimir datos de la variable si existen
            if (isset($partes_variables[$i][$j])) {
                echo $partes_variables[$i][$j];
            }
            echo "</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "La cadena no contiene al menos un dato.";
}
?>
        
        </div>
      </div>
      <br>
     

    </div>
  </div>

  </div>
 </div>

  </div>
<div class="form-row">
    <div class="col">
        <label for="exampleFormControlTextarea1">Ruta</label>

        <?php 
        $rutas = $consulta[20];

        // Primero, dividimos la cadena en un array usando explode
        $rutas = explode(',', $rutas);

        // Luego, iteramos sobre cada elemento del array
        foreach ($rutas as $vuelo) {
            // Dividimos cada vuelo en segmentos usando explode
            $segmentos = explode(';', $vuelo);
            echo "<table class='table'>";
           
            // Finalmente, iteramos sobre cada segmento y generamos una fila en la tabla para cada vuelo
            echo "<tr>";
            foreach ($segmentos as $segmento) {
                echo "<td>$segmento</td>";
            }
            echo "</tr>";
            echo "</table>";
        }
        ?>
</div>
  </div>
<br>
<br>
<br>
  <div class="container" >
  <button  style="width: 100%;" type="submit" class="btn btn-primary">Editar Pago</button>
  </div>
  <br>
  <br>
  <br>
  <br>
</form>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap and other scripts -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
</body>

</html>