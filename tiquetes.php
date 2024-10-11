
<!doctype html>
<html lang="en">

<?php 
include "sidebar.php"
 ?>
<br>
<br>


  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="estilos/estilos.css">
    <!-- SCRIPTS JS-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="peticion.js"></script>


    <title>Facturacion Administrativa!</title>

  </head>
  <body >

    <br> 
   
   <br>

 <br>
   <div  class="mx-auto" style="width: 800px;" class="container" >
     <div class="container" style="width: 100%;">
      <button class="btn-success" >  
    <a  class="text-light" href="ExcelPagosAdministrativos.php">Descargar Informe

    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
    </svg>
    </a>
    </button>
    </div>
  </div>
<br>
  <div class="container">
     <h2>Filtro</h2>
      <p>Escriba algo en el campo de entrada para buscar en la tabla :</p>  
    <input class="form-control" id="myInput2" type="text" placeholder="Search..">
   <br>
  <div class="table-responsive" style="width: 100%;" >
 <table class="table">
   <thead class="thead-light">
    <tr>
      <th scope="col">Editar</th>
      <th scope="col">Eliminar</th>
      <th scope="col">Detalles tiquete</th>
      <!--<th scope="col">Id Pago administrativo</th>-->
      <th>Estado</th>
      <th>Ruta</th>
      <th>Localizador</th>
      <th>Tiquete</th>
      <th>Fecha</th>
      <th>Tipo de pago</th>
      <th>Aerolinea</th>
      <th>Importe</th>
      <th>Cliente o Agencia</th>
      <th>Tipo servicio</th>
      <th>No. Pasajeros</th>
      <th scope="col">Enviar a facturar</th>
      
    </tr>
 <?php
include 'conexion.php';

// Consulta para los datos que empiezan con "T-K" limitando a los últimos 3000 registros basados en id_tiquete
$consulta_tk = "
    SELECT * FROM (
        SELECT * FROM tbl_tiquetes 
        WHERE campo1 LIKE 'T-K%' OR campo2 LIKE 'T-K%' OR campo3 LIKE 'T-K%' OR campo4 LIKE 'T-K%' OR campo5 LIKE 'T-K%' OR campo6 LIKE 'T-K%' OR campo7 LIKE 'T-K%' OR campo8 LIKE 'T-K%' OR campo9 LIKE 'T-K%' OR campo10 LIKE 'T-K%' OR campo11 LIKE 'T-K%' OR campo12 LIKE 'T-K%' OR campo13 LIKE 'T-K%' OR campo14 LIKE 'T-K%' OR campo15 LIKE 'T-K%' OR campo16 LIKE 'T-K%' OR campo17 LIKE 'T-K%' OR campo18 LIKE 'T-K%' OR campo19 LIKE 'T-K%' OR campo20 LIKE 'T-K%' OR campo21 LIKE 'T-K%' OR campo22 LIKE 'T-K%' OR campo23 LIKE 'T-K%' OR campo24 LIKE 'T-K%' OR campo25 LIKE 'T-K%' OR campo26 LIKE 'T-K%' OR campo27 LIKE 'T-K%' OR campo28 LIKE 'T-K%' OR campo29 LIKE 'T-K%' OR campo30 LIKE 'T-K%' OR campo31 LIKE 'T-K%' OR campo32 LIKE 'T-K%' OR campo33 LIKE 'T-K%' OR campo34 LIKE 'T-K%' OR campo35 LIKE 'T-K%' OR campo36 LIKE 'T-K%' OR campo37 LIKE 'T-K%' OR campo38 LIKE 'T-K%' OR campo39 LIKE 'T-K%' OR campo40 LIKE 'T-K%' OR campo41 LIKE 'T-K%' OR campo42 LIKE 'T-K%' OR campo43 LIKE 'T-K%' OR campo44 LIKE 'T-K%' OR campo45 LIKE 'T-K%' OR campo46 LIKE 'T-K%' OR campo47 LIKE 'T-K%' OR campo48 LIKE 'T-K%' OR campo49 LIKE 'T-K%' OR campo50 LIKE 'T-K%' OR campo51 LIKE 'T-K%' OR campo52 LIKE 'T-K%' OR campo53 LIKE 'T-K%' OR campo54 LIKE 'T-K%' OR campo55 LIKE 'T-K%' 
        ORDER BY id_tiquete DESC
        LIMIT 100
    ) sub
    ORDER BY id_tiquete ASC";

$ejecutar_tk = mysqli_query($conn, $consulta_tk);

// Consulta para los datos que empiezan con "FP" limitando a los últimos 3000 registros basados en id_tiquete
$consulta_fp = "
    SELECT * FROM (
        SELECT * FROM tbl_tiquetes 
        WHERE campo1 LIKE 'FP%' OR campo2 LIKE 'FP%' OR campo3 LIKE 'FP%' OR campo4 LIKE 'FP%' OR campo5 LIKE 'FP%' OR campo6 LIKE 'FP%' OR campo7 LIKE 'FP%' OR campo8 LIKE 'FP%' OR campo9 LIKE 'FP%' OR campo10 LIKE 'FP%' OR campo11 LIKE 'FP%' OR campo12 LIKE 'FP%' OR campo13 LIKE 'FP%' OR campo14 LIKE 'FP%' OR campo15 LIKE 'FP%' OR campo16 LIKE 'FP%' OR campo17 LIKE 'FP%' OR campo18 LIKE 'FP%' OR campo19 LIKE 'FP%' OR campo20 LIKE 'FP%' OR campo21 LIKE 'FP%' OR campo22 LIKE 'FP%' OR campo23 LIKE 'FP%' OR campo24 LIKE 'FP%' OR campo25 LIKE 'FP%' OR campo26 LIKE 'FP%' OR campo27 LIKE 'FP%' OR campo28 LIKE 'FP%' OR campo29 LIKE 'FP%' OR campo30 LIKE 'FP%' OR campo31 LIKE 'FP%' OR campo32 LIKE 'FP%' OR campo33 LIKE 'FP%' OR campo34 LIKE 'FP%' OR campo35 LIKE 'FP%' OR campo36 LIKE 'FP%' OR campo37 LIKE 'FP%' OR campo38 LIKE 'FP%' OR campo39 LIKE 'FP%' OR campo40 LIKE 'FP%' OR campo41 LIKE 'FP%' OR campo42 LIKE 'FP%' OR campo43 LIKE 'FP%' OR campo44 LIKE 'FP%' OR campo45 LIKE 'FP%' OR campo46 LIKE 'FP%' OR campo47 LIKE 'FP%' OR campo48 LIKE 'FP%' OR campo49 LIKE 'FP%' OR campo50 LIKE 'FP%' OR campo51 LIKE 'FP%' OR campo52 LIKE 'FP%' OR campo53 LIKE 'FP%' OR campo54 LIKE 'FP%' OR campo55 LIKE 'FP%' 
        ORDER BY id_tiquete DESC
        LIMIT 100
    ) sub
    ORDER BY id_tiquete ASC";

$ejecutar_fp = mysqli_query($conn, $consulta_fp);



          $resultado_combinado = array_merge(mysqli_fetch_all($ejecutar_tk, MYSQLI_ASSOC), mysqli_fetch_all($ejecutar_fp, MYSQLI_ASSOC));

          foreach ($resultado_combinado as $mostrarPaAdmin) {
            $id_tiquete = $mostrarPaAdmin['id_tiquete'];
            $campo1 = $mostrarPaAdmin['campo1'];
            $campo2 = $mostrarPaAdmin['campo2'];
            $campo3 = $mostrarPaAdmin['campo3'];
            $campo4 = $mostrarPaAdmin['campo4'];
            $campo5 = $mostrarPaAdmin['campo5'];
            $campo6 = $mostrarPaAdmin['campo6'];
            $campo7 = $mostrarPaAdmin['campo7'];
            $campo8 = $mostrarPaAdmin['campo8'];
            $campo9 = $mostrarPaAdmin['campo9'];
            $campo10 = $mostrarPaAdmin['campo10'];
            $campo11 = $mostrarPaAdmin['campo11'];
            $campo12 = $mostrarPaAdmin['campo12'];
            $campo13 = $mostrarPaAdmin['campo13'];
            $campo14 = $mostrarPaAdmin['campo14'];
            $campo15 = $mostrarPaAdmin['campo15'];
            $campo16 = $mostrarPaAdmin['campo16'];
            $campo17 = $mostrarPaAdmin['campo17'];
            $campo18 = $mostrarPaAdmin['campo18'];
            $campo19 = $mostrarPaAdmin['campo19'];
            $campo20 = $mostrarPaAdmin['campo20'];
            $campo21 = $mostrarPaAdmin['campo21'];
            $campo22 = $mostrarPaAdmin['campo22'];
            $campo23 = $mostrarPaAdmin['campo23'];
            $campo24 = $mostrarPaAdmin['campo24'];
            $campo25 = $mostrarPaAdmin['campo25'];
            $campo26 = $mostrarPaAdmin['campo26'];
            $campo27 = $mostrarPaAdmin['campo27'];
            $campo28 = $mostrarPaAdmin['campo28'];
            $campo29 = $mostrarPaAdmin['campo29'];
            $campo30 = $mostrarPaAdmin['campo30'];
            $campo31 = $mostrarPaAdmin['campo31'];
            $campo32 = $mostrarPaAdmin['campo32'];
            $campo33 = $mostrarPaAdmin['campo33'];
            $campo34 = $mostrarPaAdmin['campo34'];
            $campo35 = $mostrarPaAdmin['campo35'];
            $campo36 = $mostrarPaAdmin['campo36'];
            $campo37 = $mostrarPaAdmin['campo37'];
            $campo38 = $mostrarPaAdmin['campo38'];
            $campo39 = $mostrarPaAdmin['campo39'];
            $campo40 = $mostrarPaAdmin['campo40'];
            $campo41 = $mostrarPaAdmin['campo41'];
            $campo42 = $mostrarPaAdmin['campo42'];
            $campo43 = $mostrarPaAdmin['campo43'];
            $campo44 = $mostrarPaAdmin['campo44'];
            $campo45 = $mostrarPaAdmin['campo45'];
            $campo46 = $mostrarPaAdmin['campo46'];
            $campo47 = $mostrarPaAdmin['campo47'];
            $campo48 = $mostrarPaAdmin['campo48'];
            $campo49 = $mostrarPaAdmin['campo49'];
            $campo50 = $mostrarPaAdmin['campo50'];
            $campo51 = $mostrarPaAdmin['campo51'];
            $campo52 = $mostrarPaAdmin['campo52'];
            $campo53 = $mostrarPaAdmin['campo53'];
            $campo54 = $mostrarPaAdmin['campo54'];
            $campo55 = $mostrarPaAdmin['campo55'];
            $campo56 = $mostrarPaAdmin['campo56'];
            $campo57 = $mostrarPaAdmin['campo57'];
            $campo58 = $mostrarPaAdmin['campo58'];
            $campo59 = $mostrarPaAdmin['campo59'];
            $campo60 = $mostrarPaAdmin['campo60'];
            $campo61 = $mostrarPaAdmin['campo61'];
            $campo62 = $mostrarPaAdmin['campo62'];
            $campo63 = $mostrarPaAdmin['campo63'];
            $campo64 = $mostrarPaAdmin['campo64'];
            
             
             // Verificar si algún campo comienza con "T-K"
            $conteo_tk = 0;

            for ($i = 1; $i <= 64; $i++) {
            $campo = $mostrarPaAdmin['campo' . $i];
               // Verificar si el campo comienza con "T-K"
               if (strpos($campo, 'T-K1') === 0 || strpos($campo, 'T-K0') === 0 || strpos($campo, 'T-K2') === 0 || strpos($campo, 'T-K6') === 0 || strpos($campo, 'T-K9') === 0 || strpos($campo, 'T-E1') === 0) {
                   // Almacenar el valor del campo en dato_tk
                   $dato_tk = $campo;
                   // Incrementar el contador
                   $conteo_tk++;
                   // Salir del bucle una vez que se encuentra un campo que comienza con "T-K"
                   
               }
            }

           
            $dato_vn = ""; // Inicializar la variable fuera del bucle

            for ($i = 1; $i <= 58; $i++) {
                $campo = $mostrarPaAdmin['campo' . $i];
                if (strpos($campo, 'C-7906/') === 0) {
                    $dato_vn = $campo;
                    break; // Salir del bucle una vez que se encuentra un campo que comienza con "C-7906/"
                }
            }

            // Verificar si algún campo comienza con "FP"

            $dato_fp = ""; // Inicializar la variable fuera del bucle

            for ($i = 1; $i <= 58; $i++) {
                $campo = $mostrarPaAdmin['campo' . $i];
                if (strpos($campo, 'FP') === 0) {
                    $dato_fp = $campo;
                    break; // Salir del bucle una vez que se encuentra un campo que comienza con "FP"
                }
            }

      
            // Verificar si algún campo comienza con "I-0"
            
            $dato_nt = ""; // Inicializar la variable fuera del bucle

            for ($i = 1; $i <= 58; $i++) {
                $campo = $mostrarPaAdmin['campo' . $i];
                if (strpos($campo, 'I-0') === 0) {
                    $dato_nt = $campo;
                    break; // Salir del bucle una vez que se encuentra un campo que comienza con "I-0"
                }
            }

            // Verificar si algún campo comienza con "K-F"

            $dato_kf = ""; // Inicializar la variable fuera del bucle

            for ($i = 1; $i <= 58; $i++) {
                $campo = $mostrarPaAdmin['campo' . $i];
                if (strpos($campo, 'K-F') === 0) {
                    $dato_kf = $campo;
                    break; // Salir del bucle una vez que se encuentra un campo que comienza con "K-F"
                }
            }


           // Verificar si algún campo comienza con "O-"
            
           $dato_fs = ""; // Inicializar la variable fuera del bucle

           for ($i = 1; $i <= 58; $i++) {
               $campo = $mostrarPaAdmin['campo' . $i];
               if (strpos($campo, 'O-') === 0) {
                   $dato_fs = $campo;
                   break; // Salir del bucle una vez que se encuentra un campo que comienza con "O-"
               }
           }

            
           // Verificar si algún campo comienza con "H- o U-" 
           $dato_rt = array(); // Inicializar la variable como un array fuera del bucle

           for ($i = 1; $i <= 58; $i++) {
               $campo = $mostrarPaAdmin['campo' . $i];
               if (strpos($campo, 'H-') === 0 || strpos($campo, 'U-') === 0) {
                   $dato_rt[] = $campo; // Agregar el campo al array $dato_rt
               }
           }

           // Concatenar todos los valores del array $dato_rt en una sola cadena
            $dato_rt_concatenado = implode(',', $dato_rt);


            // Verificar si algún campo comienza con "EMD"
            
            $dato_emd = array(); // Inicializar la variable como un array fuera del bucle

           for ($i = 1; $i <= 58; $i++) {
               $campo = $mostrarPaAdmin['campo' . $i];
               if (strpos($campo, 'EMD') === 0) {
                   $dato_emd[] = $campo; // Agregar el campo al array $dato_rt
               }
           }

           // Concatenar todos los valores del array $dato_rt en una sola cadena
            $dato_emd_concatenado = implode(',', $dato_emd);

            // Verificar si algún campo comienza con "K-F"

            $dato_kft = ""; // Inicializar la variable fuera del bucle

            for ($i = 1; $i <= 58; $i++) {
                $campo = $mostrarPaAdmin['campo' . $i];
                if (strpos($campo, 'KFTF') === 0) {
                    $dato_kft = $campo;
                    break; // Salir del bucle una vez que se encuentra un campo que comienza con "kft"
                }
            }


             $dato_tax = ""; // Inicializar la variable fuera del bucle

            for ($i = 1; $i <= 58; $i++) {
                $campo = $mostrarPaAdmin['campo' . $i];
                if (strpos($campo, 'TAX-COP') === 0) {
                    $dato_tax = $campo;
                    break; // Salir del bucle una vez que se encuentra un campo que comienza con "kft"
                }
            }


           
        ?>





  </thead>
  <tbody id="myTable2">
    <tr>
      <?php  
      echo "<td><a href='modificarTiquete.php?id_tiquete= ".$mostrarPaAdmin ['id_tiquete']."'><button type='button' class='btn btn-success'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pen-fill' viewBox='0 0 16 16'>
  <path d='m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z'/>
</svg></button> </a> </td> </td>";
      echo "
       <td>

       <button type='button'class='btn btn-danger' data-toggle='modal' data-target='#exampleModalCenter<?php echo $id_tiquete; ?>'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-trash-fill'viewBox='0 0 16 16'>
  <path d='M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z'/>
</svg></button>
<div class='modal fade' id='exampleModalCenter<?php echo $id_tiquete; ?>' tabindex='-1' role='dialog' aria-labelledby='exampleModalCenterTitle' aria-hidden='true'>
  <div class='modal-dialog modal-dialog-centered' role='document'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title' id='exampleModalLongTitle'>¿Estás seguro de que deseas eliminar este registro?</h5>
        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
          <span aria-hidden='true'>&times;</span>
        </button>
      </div>
      <div class='modal-body'>
       <table class='table'>
  <thead>
    <tr>
      
      <th scope='col'>Id Pago</th>
      <th scope='col'>Proveedor</th>
      <th scope='col'>Valor</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      
      <td>".$mostrarPaAdmin ['id_tiquete'] ."</td>
      <td>".$mostrarPaAdmin ['campo10'] ."</td>
      <td>".$mostrarPaAdmin ['campo10'] ."</td>
      

    </tr>
  </tbody>
</table>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancelar</button>
        <a href='EliminarPagosAdministrativos.php?id_tiquete= ".$mostrarPaAdmin ['id_tiquete']."''><button type='button' class='btn btn-danger'>Eliminar</button></a>
      </div>
    </div>
  </div>
</div>



</td>  </td>";



    echo "<td>
      <button type='button' class='btn btn-primary' data-toggle='modal' data-target='#exampleModal".$id_tiquete."'>
       <?xml version='1.0' encoding='utf-8'?>

<!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
<svg  height='25px' width='25px' version='1.1' id='Uploaded to svgrepo.com' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' 
   width='800px' height='800px' viewBox='0 0 32 32' xml:space='preserve'>
<style type='text/css'>
  .duotone_twee{fill:#555D5E;}
  .duotone_een{fill:#0B1719;}
  .st0{fill:#FFF9F9;}
  .st1{fill:#808080;}
</style>
<g>
  <path class='duotone_een' d='M22.648,27.845c0.391-0.587,0.957-1.225,0.9-1.91l-1.088-12.972c-0.072-0.863,0.666-1.527,4.526-7.141
    c0.537-0.78,0.002-1.366-0.808-0.808C20.572,8.869,19.9,9.613,19.037,9.54L6.065,8.452c-0.692-0.057-1.332,0.514-1.91,0.9
    C3.926,9.504,3.954,9.694,4.218,9.773l11.229,3.369c0.263,0.079,0.329,0.312,0.146,0.517l-6.225,6.987
    c-0.183,0.205-0.558,0.373-0.833,0.373H5.411c-0.662,0-1.399,0.667-0.812,0.992l3.034,1.677c0.241,0.133,0.546,0.439,0.68,0.68
    l1.677,3.034c0.319,0.577,0.992-0.153,0.992-0.812v-3.125c0-0.275,0.168-0.65,0.373-0.833l6.987-6.225
    c0.206-0.183,0.438-0.116,0.517,0.146l3.369,11.229C22.306,28.046,22.496,28.074,22.648,27.845z'/>
  <path class='duotone_twee' d='M14.464,8.153l2.451-2.451c0.194-0.194,0.513-0.194,0.707,0l0.707,0.707
    c0.194,0.194,0.194,0.513,0,0.707l-1.256,1.256L14.464,8.153z M12.921,8.024l0.782-0.782c0.194-0.194,0.194-0.513,0-0.707
    l-0.707-0.707c-0.194-0.194-0.513-0.194-0.707,0l-1.978,1.978L12.921,8.024z M25.59,12.85c-0.194-0.194-0.513-0.194-0.707,0
    l-1.319,1.319l0.219,2.609l2.514-2.514c0.194-0.194,0.194-0.513,0-0.707L25.59,12.85z M25.497,17.756
    c-0.194-0.194-0.513-0.194-0.707,0l-0.853,0.853l0.219,2.61l2.048-2.048c0.194-0.194,0.194-0.513,0-0.707L25.497,17.756z'/>
</g>
</svg>
      </button>

      <!-- Modal -->
<div class='modal fade bd-example-modal-lg' id='exampleModal".$id_tiquete."' tabindex='-1' role='dialog' aria-labelledby='exampleModalLabel' aria-hidden='true'>
  <div class='modal-dialog modal-lg' role='document'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title' id='exampleModalLabel'>Detalle</h5>
        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
          <span aria-hidden='true'>&times;</span>
        </button>

      </div>
      <div class='modal-body'>
        <table class='table table-bordered'>
              <thead>
                <tr>
                  <th scope='col'>Detalle</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                <td>".$mostrarPaAdmin['campo1'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo2'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo3'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo4'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo5'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo6'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo7'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo8'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo9'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo10'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo11'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo12'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo13'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo14'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo15'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo16'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo17'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo18'] ."</td>
                </tr>
                 <td>".$mostrarPaAdmin['campo19'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo20'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo21'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo22'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo23'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo24'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo25'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo26'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo27'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo28'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo29'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo30'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo31'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo32'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo33'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo34'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo35'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo36'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo37'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo38'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo39'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo40'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo41'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo42'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo43'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo44'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo45'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo46'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo47'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo48'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo49'] ."</td>
                </tr>
                <tr>
                  <td>".$mostrarPaAdmin['campo50'] ."</td>
                </tr>
                <!-- Continúa con más filas según las columnas de tu tabla -->
              </tbody>
            </table>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cerrar</button>
        <button type='button' class='btn btn-primary'>Guardar cambios</button>
      </div>
    </div>
  </div>
</div>
 
    </td>";
      ?>
      <!--<td><?php echo $mostrarPaAdmin ['id_tiquete'] ?></td>-->
      <td><?php echo $campo6 ;?></td>
      <td><?php echo $dato_rt_concatenado ;?></td>
      <td><?php 
    // Obtener los últimos 6 dígitos de $campo4 y guardarlos en $localizador
          $localizador1 = substr($campo4, -6);
             echo $localizador1; ?></td>
      <td><?php echo $dato_tk; ?></td>
      <td><?php
          
            $caracter = explode(';', $campo8); // Divide la cadena en partes usando el punto y coma como delimitador
            $caracterfn = end($caracter); // Obtiene la última parte del arreglo

            // Convierte el dato a formato de fecha
           
           // Extrae el año, mes y día del dato
           $anio = substr($caracterfn, 0, 2);
           $mes = substr($caracterfn, 2, 2);
           $dia = substr($caracterfn, 4, 2);

           // Agrega '20' al año
           $anio = '20' . $anio;

           // Formatea la fecha
           $fecha_formateada = $anio . "-" . $mes . "-" . $dia;

           // Imprime la fecha formateada
           echo $fecha_formateada;
           
            
            ?></td>
      <td><?php 
            $tipopago = substr($dato_fp, 0, 50);
            $tipopago_sin_ceros = ltrim($tipopago, '0');
            echo $tipopago_sin_ceros; ?></td>
      <td><?php echo $campo5; ?></td>
      <td><?php
       // Patrón de expresión regular para buscar "COP" seguido de números
      $patron = "/;;;COP(\d+)/";

      // Realizar la búsqueda utilizando la expresión regular
      if (preg_match($patron, $dato_kf, $coincidencias)) {
       // Obtener la primera coincidencia encontrada
      $dato_deseado = $coincidencias[1];
    
      } 

      // Patrón de expresión regular para buscar "COP" seguido de números
      $patrontt = "/(?:;COP|K-FCOP)(\d+)/";

      // Realizar la búsqueda utilizando la expresión regular
      if (preg_match($patrontt, $dato_kf, $coincidenciasnt)) {
       // Obtener la primera coincidencia encontrada
      $dato_deseadont = $coincidenciasnt[1];
    
      } 

       $patrontax = "/CO ;COP(\d+)/";

      // Realizar la búsqueda utilizando la expresión regular
      if (preg_match($patrontax, $dato_tax, $coincidenciastax)) {
       // Obtener la primera coincidencia encontrada
      $dato_deseadotax = $coincidenciastax[1];
    
      } 


      $numero = number_format($dato_deseado, 0, ",", ".");
       echo "$".$numero; ?></td>
       <td><?php

       
          // Divide la cadena en partes usando el punto y coma como delimitador
       $partes = explode(';', $dato_nt);

       // Obtiene el segundo elemento del arreglo resultante
       $dato_ac = $partes[1];

       echo $dato_ac;
         ?></td>
         <td><?php
      if (strpos($campo9, 'G-X') !== false) {
           // Si la cadena contiene 'G-X', entonces es Tiquetes internacionales
           $dato_srv = "Tiquetes Internacionales";
       } elseif (strpos($campo9, 'G-') !== false) {
    // Si la cadena contiene 'G-', entonces es Tiquetes Nacionales
    $dato_srv = "Tiquetes Nacionales";
       } else {
    // Si la cadena no contiene ninguna de las subcadenas especificadas
    $dato_srv = "Cancelado";
       }

       echo $dato_srv;
          ?></td>
          <td><?php echo $conteo_tk; ?></td>

    
      <?php 
       // Divide la cadena en partes usando el punto y coma como delimitador
       $partes3 = explode(';', $dato_fs);

       // Obtiene el primer elemento del arreglo resultante
       $dato_fsa = $partes3[0];

       // Extrae el dato de la fecha (07MAY) de la primera parte
       $dato_fsal = substr($dato_fsa, 2, 5);

if (!function_exists('convertDateFormat')) {
    function convertDateFormat($inputDate) {
        // Array de meses en inglés a sus equivalentes numéricos
        $months = array(
            'JAN' => '01',
            'FEB' => '02',
            'MAR' => '03',
            'APR' => '04',
            'MAY' => '05',
            'JUN' => '06',
            'JUL' => '07',
            'AUG' => '08',
            'SEP' => '09',
            'OCT' => '10',
            'NOV' => '11',
            'DEC' => '12'
        );

        // Extraer el día y el mes del inputDate
        $day = substr($inputDate, 0, 2);
        $month = strtoupper(substr($inputDate, 2, 3)); // Convertir el mes a mayúsculas por si acaso

        // Verificar si el mes está en el array
        if (array_key_exists($month, $months)) {
            // Formar la nueva fecha con el mes numérico
            $dateString = $day . $months[$month] . date('Y');

            // Crear un objeto DateTime a partir del nuevo string de fecha
            $date = DateTime::createFromFormat('dmY', $dateString);

            // Verificar si la fecha fue creada correctamente
            if ($date && $date->format('dmY') === $dateString) {
                // Convertir la fecha al formato deseado
                return $date->format('d/m/Y');
            } else {
                // Manejar el error si la fecha no es válida
                return false;
            }
        } else {
            // Manejar el error si el mes no es válido
            return false;
        }
    }
}

// Verificar si el campo está vacío y reemplazarlo por una fecha genérica
if (empty($dato_fsal)) {
    $fecha_Sformateada = '01/01/2000';
} else {
    // Verificar si la fecha contiene "XX" y reemplazarlo por una fecha genérica
    if (strpos($dato_fsal, 'XX') === 0) {
        $fecha_Sformateada = '01/01/2000';
    } else {
        // Convertir la fecha al formato deseado
        $fecha_Sformateada = convertDateFormat($dato_fsal);
    }

    // Verificar si la fecha fue convertida correctamente, si no, manejar el error
    if ($fecha_Sformateada === false) {
        // Manejar el error según tu lógica (ejemplo: usar una fecha por defecto o lanzar una excepción)
        $fecha_Sformateada = 'Fecha inválida';
    }
}








      // Encontrar la posición del primer '/'
      $posicion1 = strpos($dato_vn, '/');

      // Extraer los 8 caracteres después del primer '/'
      $dato_vnd = substr($dato_vn, $posicion1 + 1, 9);

     if ($dato_vnd == " 1000LMSU") {
    $dato_vnd = "Liliana Mahecha";
} elseif ($dato_vnd == " 1001STSU") {
    $dato_vnd = "Sandra Triana";
}elseif ($dato_vnd == " 0000MCAS") {
    $dato_vnd = "Maria Castro";
}elseif ($dato_vnd == " 0003PDSU") {
    $dato_vnd = "Pedro Duarte";
}elseif ($dato_vnd == " 0004CGAS") {
    $dato_vnd = "Camila Gil";
}elseif ($dato_vnd == " 0008NSGS") {
    $dato_vnd = "Nidia Soto";
}elseif ($dato_vnd == " 0010NFAS") {
    $dato_vnd = "Nicolas Fajardo";
}elseif ($dato_vnd == " 1005LSGS" || $dato_vnd == " 1005LSSU"  ) {
    $dato_vnd = "Luz Angela Sanchez";
}elseif ($dato_vnd == " 1006MPSU") {
    $dato_vnd = "Mariluz Peña";
}elseif ($dato_vnd == " 0011LCGS") {
    $dato_vnd = "Lizeth Cortes";
}elseif ($dato_vnd == " 0011LCGS2") {
    $dato_vnd = "Angela Triana";
}elseif ($dato_vnd == " 2309SPSU") {
    $dato_vnd = "Pedro Duarte";
}elseif ($dato_vnd == " 0002KFAS") {
    $dato_vnd = "Karolina Franco";
}elseif ($dato_vnd == " 0013GPAS") {
    $dato_vnd = "Paola Garzon";
}elseif ($dato_vnd == " 0014ARAS") {
    $dato_vnd = "Andrea Rodriguez";
}elseif ($dato_vnd == " 9998WSSU" || $dato_vnd == " 9996WSSU") {
    $dato_vnd = "OBT";
}

       if ($dato_vnd == " OBT") {
       // Si el dato del vendedor es "9998WSSU", asigna "OBT" a la variable $canal_vn
       $canal_vn = "OBT";
       } else {
       // Si el dato del vendedor es diferente de "9998WSSU", asigna el dato del vendedor a la    variable $oficina_counter
       $canal_vn = "Amadeus";
       }

       if ($dato_vnd == " 9998WSSU" || $dato_vnd == " 9996WSSU") {
       // Si el dato del vendedor es "9998WSSU", asigna "OBT" a la variable $canal_vn
       $ceco = "BOGOTA CORPORATIVO";
       } else {
       // Si el dato del vendedor es diferente de "9998WSSU", asigna el dato del vendedor a la    variable $oficina_counter
       $ceco = "BOGOTA COUNTER";
       }
          
       // Divide la cadena en partes usando el punto y coma como delimitador
       $partes4 = explode(';', $campo10);

      // Verifica si el arreglo tiene al menos 5 elementos antes de intentar acceder al quinto elemento
      if (isset($partes4[4])) {
      // Si el quinto elemento existe, obtén su valor
      $dato_des = $partes4[4];
      } else {
      // Si el quinto elemento no existe, asigna un valor por defecto o maneja el caso según   sea necesario
      $dato_des = "No hay quinto elemento";
       }


// Luego, dentro de las etiquetas PHP, puedes imprimir el código JavaScript utilizando echo
echo '<script type="text/javascript">
    // Función para insertar datos en la base de datos
    function insertarDatos() {
        // Aquí colocas el código PHP que maneja la inserción de datos en la base de datos
        ';

// Aquí colocas el código PHP para la inserción de datos
   
$tipo_trato      = "Reserva";
$vendedor        = $dato_vnd;
$importe         = $dato_deseado;             
$fecha_cierre    = $fecha_formateada;
$nombre_trato    = $dato_nt;
$nom_agen_cli    = $dato_ac;
$tipo_moneda     = "COP";
$tipo_soli_serv  = $dato_srv;
$canal_venta     = $canal_vn;
$fecha_salida    = $fecha_Sformateada;
$num_pasajeros   = $conteo_tk;
$destinos        = $dato_des;
$Centro_costo    = $ceco;
$localizador     = $localizador1;
$fecha_emision   = $fecha_formateada;
$fecha_servicio  = $dato_fsal;
$num_tiquete     = $dato_tk;
$tipo_pago       = $tipopago;
$proveedor       = $campo5;
$ruta            = $dato_rt_concatenado;
$emd             = $dato_emd_concatenado;
$enviado         = 0;
$netotarifatiquete = $dato_deseadont;
$impuestostiquete = $dato_kft;
$totalimpuestostiquete = $dato_deseadotax;

if ($campo6 === "B-NDC VOID") {
    echo 'console.error("El tiquete es igual a B-NDC VOID, Cancelado");';
} else {

// Incluir archivo de conexión a la base de datos
include 'conexion.php'; 

$fecha_actual = date('Y-m-d');

// Consulta SQL para insertar los datos en la tabla solo para la fecha actual
$sql = "INSERT INTO tbl_resum_tiquetes (tipo_trato, vendedor, importe, fecha_cierre, nombre_trato, nom_agen_cli, tipo_moneda, tipo_soli_serv, canal_venta, fecha_salida, num_pasajeros, destinos, Centro_costo, localizador, fecha_emision, fecha_servicio, num_tiquete, tipo_pago, proveedor, ruta, emd, enviado, netotarifatiquete, impuestostiquete, totalimpuestostiquete)
        SELECT '$tipo_trato', '$vendedor', '$importe', '$fecha_cierre', '$nombre_trato', '$nom_agen_cli', '$tipo_moneda', '$tipo_soli_serv', '$canal_venta', '$fecha_salida', '$num_pasajeros', '$destinos', '$Centro_costo', '$localizador', '$fecha_emision', '$fecha_servicio', '$num_tiquete', '$tipo_pago', '$proveedor', '$ruta', '$emd', '$enviado', '$netotarifatiquete', '$impuestostiquete', '$totalimpuestostiquete'WHERE '$fecha_cierre' = '$fecha_actual'";

// Ejecutar consulta
try {
    $resultado = $conn->query($sql);
    if ($resultado === TRUE) {
        echo 'console.log("Los datos se almacenaron correctamente en la tabla.");';
    } else {
        echo 'console.error("Error: ' . $conn->error . '");';
    }
} catch (mysqli_sql_exception $ex) {
    if ($ex->getCode() == 1062) {
        echo 'console.error("Error: El número de tiquete ya existe en la base de datos.");';
    } else {
        echo 'console.error("Error: ' . $ex->getMessage() . '");';
    }
}

// Cerrar conexión
$conn->close();

}

echo '
    }

    // Ejecutar la función insertarDatos() minuto
    setInterval(insertarDatos, 30000); // 30000 milisegundos = 30 segundos
</script>';
?>
       

      <?php   

       echo "<td><a href='modificarTiquete.php?id_tiquete= ".$mostrarPaAdmin ['id_tiquete']."'><button type='button' class='btn btn-success'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-envelope-plus-fill' viewBox='0 0 16 16'>
  <path d='M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.026A2 2 0 0 0 2 14h6.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586l-1.239-.757ZM16 4.697v4.974A4.491 4.491 0 0 0 12.5 8a4.49 4.49 0 0 0-1.965.45l-.338-.207L16 4.697Z'/>
  <path d='M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 0 0 1 0v-1h1a.5.5 0 0 0 0-1h-1v-1a.5.5 0 0 0-.5-.5Z'/>
</svg></button> </a> </td> </td>"; 

            ?>

      
    </tr>

    

<?php 

}




?>
  </tbody>
</table>
<br>
  <br>
  <br>
  <br>
</div>

</div>

<script>
    // Espera a que el documento esté completamente cargado
    $(document).ready(function() {
        // Función para mostrar la ventana modal cuando se hace clic en el botón
        $('#modalDetalles<?php echo $id_tiquete; ?>').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Botón que activó la modal
            var idTiquete = button.data('idtiquete'); // Extraer el ID del tiquete de los datos del botón
            // Actualizar el contenido de la modal con los detalles del tiquete, si es necesario
        });
    });
</script>
<script>
$(document).ready(function(){
  $("#myInput2").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#myTable2 tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});
</script>



  <!-- Scripts de Bootstrap (jQuery y Popper.js) -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <!-- Script de Bootstrap -->
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>