<?php
include "seguridad.php"
?>

<!doctype html>
<html lang="es">

<header>

    <?php
    include "sidebar.php"
    ?>

</header>

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
    <!-- Busqueda Select hoteles -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Liberia graficas-->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title></title>

</head>

<body>


    <br>



    <?php
    include 'conexion.php';

    $TipoPago =
        ConsultaTipoPago(!empty($_GET['TipoPago']) ? $_GET['TipoPago'] : null);
    $id_usuario_ad =
        Consultarusuarioca(!empty($_GET['id_usuario_ad']) ? $_GET['id_usuario_ad'] : null);

    function ConsultaTipoPago($TipoPago)
    {
        include 'conexion.php';

        if ($TipoPago == 'PagosAdministrativos') {
            $inicio = !empty($_GET['inicio']) ? $_GET['inicio'] : null;
            $fin = !empty($_GET['fin']) ? $_GET['fin'] : null;




            function Consultarusuarioca($id_usuario_ad)
            {
                include 'conexion.php';

                $inicio = !empty($_GET['inicio']) ? $_GET['inicio'] : null;
                $fin = !empty($_GET['fin']) ? $_GET['fin'] : null;

                $sentencia = "SELECT  count(id_proveedoradmin_fo) as 'totalregistros', fecha, id_usuario_fo 
        FROM tbl_pagos_administrativos 
        where   fecha BETWEEN '$inicio' AND '$fin' AND id_usuario_fo = '$id_usuario_ad'";

                $ejecutarPa = mysqli_query($conn, $sentencia);

                $mostrarProveedoradmin = $ejecutarPa->fetch_assoc();
                return [
                    $mostrarProveedoradmin['id_usuario_fo'],
                    $mostrarProveedoradmin['totalregistros'],
                ];
            }
        } elseif ($TipoPago == 'PagosProveedoresturisticos') {
            $inicio = !empty($_GET['inicio']) ? $_GET['inicio'] : null;
            $fin = !empty($_GET['fin']) ? $_GET['fin'] : null;




            function Consultarusuarioca($id_usuario_ad)
            {
                include 'conexion.php';

                $inicio = !empty($_GET['inicio']) ? $_GET['inicio'] : null;
                $fin = !empty($_GET['fin']) ? $_GET['fin'] : null;

                $sentencia = "SELECT  count(id_proveedor_fo) as 'totalregistros', fecha, id_usuario_tufo 
        FROM tbl_proveedores_turtisticos 
        where   fecha BETWEEN '$inicio' AND '$fin' AND id_usuario_tufo = '$id_usuario_ad'";

                $ejecutarPa = mysqli_query($conn, $sentencia);

                $mostrarProveedoradmin = $ejecutarPa->fetch_assoc();
                return [
                    $mostrarProveedoradmin['id_usuario_tufo'],
                    $mostrarProveedoradmin['totalregistros'],
                ];
            }
        }
    }


    ?>
    <div class="mx-auto" style="width: 800px;">
        <h2 class="display-0">Usuario:
            <?php
            echo $id_usuario_ad[0]
            ?>
        </h2>
    </div>

    <div class="mx-auto" style="width: 800px;">
        <tr>
            <!--<th scope="col"><h1 class="display-0">Total Pagado: $<?php echo $numero  ?></h1></th>
  <th scope="col"><h1 class="display-0">Pendiente por pagar: $<?php echo $numero2  ?></h1></th>-->
        </tr>
    </div>
    <br>
    <div class="container" style="width: 100%;">
        <br>
        <div class="container is-fluid">
            <div class="col-xs-12">
                <h2></h2>
                <br>

                <div>
                    <style>
                        th {
                            font-weight: bold;
                            color: white;
                        }
                    </style>

                    <form action="" method="GET">
                        <div class="row">
                            <div class="col-md-5">
                                <label><b>Seleccionar Tipo De pago</b></label>
                                <select name="TipoPago" class="form-control" required aria-label="Default select example">

                                    <option value="PagosAdministrativos">Pagos Administrativos</option>
                                    <option value="PagosProveedoresturisticos">Pagos Proveedores turisticos</option>

                                </select>

                                <label><b>Seleccione el Usuario</b></label>
                                <select id="bucarh" name="id_usuario_ad" class="form-control" required>
                                    <?php
                                    include 'conexion.php';
                                    $consulta = "SELECT usuario, id_rol, id_usuario  FROM tbl_usuarios where id_rol = '2'";
                                    $ejecutar = mysqli_query($conn, $consulta);
                                    ?>
                                    <?php foreach ($ejecutar as $opciones) : ?>
                                        <option value="<?php echo $opciones['usuario'] ?>"><?php echo $opciones['usuario'] ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label><b>Fecha inicial:</b></label>
                                    <input type="date" name="inicio" value="<?php if (isset($_GET['inicio'])) {
                                                                                echo $_GET['inicio'];
                                                                            } ?>" class="form-control" required>


                                    <label><b>Fecha final:</b></label>
                                    <input type="date" name="fin" value="<?php if (isset($_GET['fin'])) {
                                                                                echo $_GET['fin'];
                                                                            } ?>" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary" style="width: 100%">
                                Consultar
                            </button>
                        </div>
                        <script>
                            $('#bucarh').select2();
                        </script>
                    </form>
                    <style>
                        .center-div {
                            width: 100px;
                            /* Ancho deseado del div */
                            margin-left: auto;
                            margin-right: auto;
                        }
                    </style>
                    <br>
                    <div class="table-responsive" style="width: 100%;">
                        <table class="table">
                            <thead class="thead-light">
                                <tr>
                                    <!--<th scope="col">ID Proveedor</th>-->
                                    <th scope="col">Usuario</th>
                                    <th scope="col">Pagos Cargados</th>

                            </thead>
                            <tbody id="myTable3">

                                <tr>

                                    <!--<td><?php echo $id_usuario_ad[0]  ?></td>-->
                                    <td><?php echo $id_usuario_ad[0] ?></td>
                                    <td><?php echo $id_usuario_ad[1] ?></td>


                                </tr>


                            </tbody>
                        </table>


                    </div>
                </div>
                <br>
                <br>


                <button type="submit" class="btn btn-success" data-toggle="modal" data-target="#myModal" style="width: 100%" ;>
                    Generar Grafica
                </button>

                <br>
                <br>
                <br>
                <br>
                <br>

                <!-- Ventana modal -->
                <div class="modal" id="myModal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Grafica
                                    <?php
                                    if (empty($id_usuario_ad[1])) {
                                        echo $id_usuario_ad[1];
                                    } else {
                                        echo $id_usuario_ad[1];
                                    }

                                    ?></h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <canvas id="columnChart"></canvas>
                                <script>
                                    // Obtener los datos de MySQL utilizando PHP para cada estado y valor
                                    <?php
                                    // Conexión a la base de datos MySQL
                                    include 'conexion.php';
                                    // Consulta SQL para obtener los datos de los estados y valores
                                    $sql = "SELECT locacion, SUM(valor) as 'total', estado, fecha FROM tbl_pagos_administrativos where id_proveedoradmin_fo = $id_usuario_caPe[0]  GROUP BY estado";
                                    $result = $conn->query($sql);

                                    // Arreglos para almacenar los datos de cada estado y valor
                                    $estados = array();
                                    $valores = array();
                                    $colores = array();

                                    // Colores de las columnas
                                    $coloresDisponibles = array('rgba(75, 192, 192, 0.8)', 'rgba(192, 75, 75, 0.8)', 'rgba(75, 192, 75, 0.8)');

                                    if ($result->num_rows > 0) {
                                        // Almacenar los datos en los arreglos correspondientes
                                        while ($row = $result->fetch_assoc()) {
                                            $estados[] = $row["estado"];
                                            $valores[] = $row["total"];
                                            $colores[] = $coloresDisponibles[array_rand($coloresDisponibles)]; // Asignar un color aleatorio a cada columna
                                        }
                                    }

                                    $conn->close();
                                    ?>
                                    // Obtener los datos desde PHP
                                    var estados = <?php echo json_encode($estados); ?>;
                                    var valores = <?php echo json_encode($valores); ?>;
                                    var colores = <?php echo json_encode($colores); ?>;

                                    // Crear la estructura de datos para la gráfica
                                    var dataset = [];
                                    for (var i = 0; i < estados.length; i++) {
                                        dataset.push({
                                            label: estados[i],
                                            data: [valores[i]],
                                            backgroundColor: colores[i]
                                        });
                                    }

                                    var data = {
                                        labels: [''],
                                        datasets: dataset
                                    };

                                    // Crear la gráfica de columnas
                                    var ctx = document.getElementById('columnChart').getContext('2d');
                                    new Chart(ctx, {
                                        type: 'bar',
                                        data: data,
                                        options: {
                                            scales: {
                                                y: {
                                                    beginAtZero: true
                                                }
                                            }
                                        }
                                    });
                                </script>


                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Incluir los archivos JavaScript de Bootstrap -->


            </div>
        </div>
    </div>

    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>


</html>