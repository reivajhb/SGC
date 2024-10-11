<?php 
include "seguridad.php"

 ?>

<!doctype html>
<html>

<header> 

<?php 
include "sidebar.php"
 ?>
<?php


   $consultaProveedor = Consultarproveedor($_GET['identificacion']);
   function Consultarproveedor($identificacion)
   {
    include 'conexion.php'; 
    $sentencia =   "SELECT * FROM tbl_proveedores_inter where identificacion= '".$identificacion."' "; 
    $ejecutar = mysqli_query($conn,$sentencia);
    $mostrarProveedor = $ejecutar->fetch_assoc();

       if ($mostrarProveedor  == 0)
  {
    echo '<script>
              alert("No se encontro ningun proveedor");
              window.location = "buscarProveedorinter.php";
              </script>';
  }
    return [
    $mostrarProveedor['id_proveedor_int'],
    $mostrarProveedor['identificacion'],
    $mostrarProveedor['nombre'],
    $mostrarProveedor['email_proveedor'],
    
  ];
  }  
   ?>
</header>
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="estilos/estilos.css">
    <br>
    <title>Relación Pagos Internacionales</title>

  </head>
 <body >
 <div class="container px-4">
     <div class="row gx-5">
       <div class="col">
         <div class="p-3">
           <div class="p-3 border bg-primary text-white ">
             <h2 class="text-center">Pago a proveedor internacional: <?php echo $consultaProveedor[2] ?> </h2>
           </div>
         </div>
       </div>
     </div>
   </div>
  </div>
  </div>
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div >
        <div class=" text-black" style="border-radius: 0rem;">
          <div class="card-body p-5">  


    <form action="cargaDriveProveedoresInter.php" class="container-fluid" method="post" enctype="multipart/form-data">
<div class="container-fluid">
    <label for="exampleInputEmail1">Fecha de registro*</label>
    <input readonly type="datetime-local" id="fecha_hora_colombia" name="fecha" class="form-control" value="fechaHoraColombia" required>
    
  </div>
      <div class="container-fluid">
    <label for="exampleInputEmail1">Nit o Cedula*</label>
    <input readonly name="identificacion" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[1];?>" required>
    
  </div>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Nombre Proveedor Ocasional*</label>
    <input readonly name="proveedor" type="" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[2];?>" required>
    
  </div>
  <div class="container-fluid">
    <label for="exampleInputEmail1">Correo Proveedor*</label>
    <input readonly name="email_proveedor" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $consultaProveedor[3];?>" required>
    
  </div>
  <div class="container-fluid">
    <label  for="exampleInputEmail1">Localizador*</label>
    <input name="localizador" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el localizador" required>
    
</div>
 <div class="container-fluid">
    <label for="exampleInputEmail1">No de Factura</label>
    <input name="num_factura" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el Numero de factura" >
    
  </div>
 <div class="container-fluid">
    <label  for="exampleInputEmail1">Concepto*</label>
    <input name="concepto" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese el concepto" required>
    
</div>
 
 <div class="container-fluid">
    <label  for="exampleInputEmail1">Información Adicional*</label>
    <input name="descripcion" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese alguna descripción" required>  
</div>



<div class="container-fluid">
  <label for="exampleInputEmail1">Tipo de Moneda</label>
  <select class="form-control" name="moneda"  class="form-select" aria-label="Default select example">
  <small id="emailHelp" class="form-text text0-muted">Tipo de Moneda*</small>
  
  <option value="COP">COP</option>
  <option value="USD">USD</option>
  <option value="EUR">EUR</option>
</select>
</div>
<div class="container-fluid">
    <label  for="exampleInputEmail1">Valor del cheque o Transferencia</label>
    <input name="valor" type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Valor del cheque o Transferencia" required>
    
</div>
<div class="container-fluid">
    <label  for="exampleInputEmail1">Asesor</label>
    <input readonly name="usuario" type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $_SESSION['usuario'] ?>" required>
    
</div>
  
  <div class="container-fluid">
    <label for="exampleInputEmail1">Fecha de entrada de los pasajeros*</label>
    <input name="fecha_ingreso" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese la fecha del registro" required>
    
  </div>

  <div>
  <label class="mt-4" for="exampleInputEmail1">Anexar certificación bancaria*</label>
  <input type="file" name="certificacion" id="" required>
  <br>
  <br>

  <div class="container-fluid">
    <label for="exampleInputEmail1">Fecha de salida de los pasajeros*</label>
    <input name="fecha_salida" type="date" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese la fecha del registro" required>
    
  </div>
  <div class="container-fluid">
    
    <input name="estado" type="hidden" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="Pendiente" required>  
</div>
<br>
  <div>
  <label class="mt-4" for="exampleInputEmail1">Anexar cuenta de cobro*</label>
  <input type="file" name="cuentadecobro" id="" required>
  <br>
  <div class="container" >
   <br>
  <button   id="guardarBtn"  style="width: 100%;" type="submit" class="btn btn-primary">Enviar</button>
  </div>
  </div>
       <!-- Pantalla de espera modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p>Guardando...</p>
                </div>
            </div>
        </div>
    </div>    
  </div>
  </div>
 </div>
 
  
</form>

</div>
</div>
 </div>

<script>
        $(document).ready(function() {
            $("#guardarBtn").click(function() {
                // Muestra la pantalla de espera modal
                $("#loadingModal").modal("show");

                // Simula una operación que toma tiempo (reemplaza con tu lógica de base de datos)
                $.ajax({
                    type: "POST",
                    url: "guardar_registro.php",
                    success: function(response) {
                        // Puedes realizar otras acciones después de completar la operación si es necesario

                        // Oculta la pantalla de espera modal después de completar la operación
                        $("#loadingModal").modal("hide");
                    }
                });
            });
        });
    </script>


 <script>
// Crear un objeto Date para obtener la hora actual del dispositivo
var fechaHoraActualDispositivo = new Date();

// Calcular la diferencia de tiempo entre la zona horaria del dispositivo y la de Colombia
var diferenciaHorariaColombia = -5; // Cambiar esto según las circunstancias

// Ajustar la hora actual del dispositivo a la hora de Colombia
fechaHoraActualDispositivo.setHours(fechaHoraActualDispositivo.getHours() + diferenciaHorariaColombia);

// Formatear la fecha y hora actual en formato "YYYY-MM-DDTHH:mm"
var fechaHoraColombia = fechaHoraActualDispositivo.toISOString().slice(0, 16);

// Obtener el elemento del campo de entrada
var inputFechaHoraColombia = document.getElementById("fecha_hora_colombia");

// Establecer el valor en el campo de entrada
inputFechaHoraColombia.value = fechaHoraColombia;
</script>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
   


  </body>


<br>
<br>
 <div>



 </div>
</html>