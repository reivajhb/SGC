<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";

// Comprobar si la sesión ya ha sido iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario es administrador
if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    // Incluir el sidebar para el administrador
    include "../../../config/sidebar3.php";
    include "../../../config/boton_volver.php"; 
} else {
    // Incluir el sidebar normal para usuarios no administradores
    include "../../../config/sidebar.php";
    include "../../../config/boton_volver.php"; 
}
?>

<!doctype html>
<html lang="es">


<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Tu CSS -->
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <!-- jQuery (solo si peticion.js lo usa) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <!-- Script propio -->

    <title>Facturacion Proveedores!</title>
</head>

<body class="bg-image">

    <?php
    include "../../../config/conexion.php";

    $consultaSum = "SELECT estado, SUM(valor) as 'total' 
  FROM tbl_anticipos 
  WHERE (estado = 'Pagado' OR estado = 'Soporte enviado y pagado') 
  AND YEAR(fecha) = YEAR(CURDATE())";
    $ejecutarSum = mysqli_query($conn, $consultaSum);
    $mostrarSum = mysqli_fetch_array($ejecutarSum);
    $total = $mostrarSum['total'];
    $numero = number_format($total, 2, ",", ".");
    ?>

    <?php
    $consultaSum2 = "SELECT estado, SUM(valor) as 'total' FROM tbl_anticipos where estado = 'Pendiente'  AND YEAR(fecha) = YEAR(CURDATE())";
    $ejecutarSum2 = mysqli_query($conn, $consultaSum2);
    $mostrarSum2 = mysqli_fetch_array($ejecutarSum2);
    $total2 = $mostrarSum2['total'];
    $numero2 = number_format($total2, 2, ",", ".");

    ?>
    <div class="container px-4 mt-0">
        <div class="row justify-content-center mb-3">
            <div class="col-auto">
                <div class="bg-primary text-white text-center px-5 py-3 rounded" style="box-shadow: 0 6px 20px rgba(0,0,0,0.50); border: none;">
                    <h2 class="mb-0 fw-bold">INFORMACIÓN PAGOS ANTICIPOS</h2>
                </div>
            </div>
        </div>
        <div class="row gx-4 mb-4">
            <div class="col">
                <div class="bg-success text-white px-4 py-3 rounded" style="box-shadow: 0 6px 20px rgba(0, 0, 0, 0.40); border: none;">
                    <h2 class="mb-0">Pagado: $<?php echo $numero ?></h2>
                </div>
            </div>
            <div class="col">
                <div class="bg-danger text-white px-4 py-3 rounded" style="box-shadow: 0 6px 20px rgba(0, 0, 0, 0.40); border: none;">
                    <h2 class="mb-0">Pendiente: $<?php echo $numero2 ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="width: 100%;">

        <div class="row g-4 mb-3">
                <div class="col-auto text-start">
                    <label for="myInput3" class="col-form-label">Filtro: </label>
                </div>
                <div class="col-auto text-start">
                    <input class="form-control" id="myInput3" type="text" placeholder="Search..">
                </div>
                <div class="col-auto text-start">
                    <button class="btn btn-success">
                        <a class="text-light" href="../../../genexcel/ExcelPagosProveedoresPrepago.php">Descargar información
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-download" viewBox="0 0 16 16">
                                <path
                                    d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                                <path
                                    d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                            </svg>
                        </a>
                    </button>
                </div>
        </div>
        <!-- Ventana de carga (pantalla completa) -->
        <div id="overlay" class="overlay">
            <div class="loader"></div>
        </div>
        <div class="table-responsive-scroll" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
        <table class="table mb-0" id="myTable3">
                    <thead style="background-color: #1a3a5c; color: #ffffff;">
                        <tr>
                            <th style="text-align: center;" scope="col">Acciones</th>
                            <!--<th scope="col">Id Anticipo</th>-->
                            <th scope="col">Identificación</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Proveedor</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Moneda</th>
                            <th scope="col">Localizador</th>
                            <th scope="col">Valor a pagar</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Fecha Limite de pago</th>
                        </tr>


                    </thead>
                    <tbody id="tabla_resultados">


                    </tbody>
                </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
            <span id="paginacion-info" class="text-muted small"></span>
            <nav><ul class="pagination pagination-sm mb-0" id="paginacion"></ul></nav>
        </div>
        <br>
        <br>
        <br>

        <style>
            .overlay {
                display: none;
                position: fixed;
                z-index: 1055;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.8);
                justify-content: center;
                align-items: center;
            }

            .loader {
                border: 8px solid #f3f3f3;
                border-top: 8px solid #28a745;
                border-radius: 50%;
                width: 60px;
                height: 60px;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }
            .page-jump-input {
                width: 60px;
                display: inline-block;
                text-align: center;
                padding: 2px 4px;
                border: 1px solid #dee2e6;
                border-radius: 4px;
                font-size: 0.875rem;
            }

            .table-responsive-scroll {
                max-height: 500px; /* Ajusta esta altura a tu gusto */
                overflow-y: auto !important;
                overflow-x: auto;
                border-radius: 8px;
                border: 1px solid #dee2e6;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                position: relative;
                background: #ffffff;
            }

            .table-responsive-scroll table {
                border-collapse: separate; /* Importante para que respete bordes individuales */
                border-spacing: 0;
                width: 100%;
                border: none; /* Quitamos borde a la tabla para usar el del div */
            }

            /* Mantener el encabezado fijo mientras haces scroll */
            #myTable3 thead th {
                position: sticky;
                top: -1px;
                z-index: 10;
                background-color: #0069d8; /* Color de fondo del header */
                border-bottom: 2px solid #0069d8;
                padding: 12px;
            }

            /* Ajuste fino para que las celdas no se vean pegadas */
            .table td, .table th {
                white-space: nowrap; /* Evita que el texto se rompa en varias líneas si hay mucho contenido */
                vertical-align: middle;
            }
            #myTable3 thead tr th:first-child {
                border-top-left-radius: 7px;
            }
            
            #myTable3 thead th {
                /* ... lo anterior ... */
                box-shadow: inset 0 1px 0 #0069d8, inset 0 -1px 0 #0069d8;
            }
            
        </style>

        <script>
            var LIMIT = 30;
            var currentPage = 1;
            var totalRecords = 0;

            document.addEventListener('DOMContentLoaded', function () {
                cargarTabla(1);
                var searchTimer;
                document.getElementById('myInput3').addEventListener('keyup', function () {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function () { currentPage = 1; cargarTabla(1); }, 400);
                });
            });

            function cargarTabla(page) {
                var search = document.getElementById('myInput3').value;
                var overlay = document.getElementById('overlay');
                overlay.style.display = 'flex';
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function () {
                    if (this.readyState == 4) {
                        overlay.style.display = 'none';
                        if (this.status == 200) {
                            try {
                                var data = JSON.parse(this.responseText);
                                document.getElementById('tabla_resultados').innerHTML = data.html;
                                totalRecords = data.total;
                                currentPage = page;
                                renderPaginacion();
                            } catch (e) {
                                document.getElementById('tabla_resultados').innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error al cargar los datos.</td></tr>';
                            }
                        }
                    }
                };
                xhttp.open('GET', 'tablaproveedoresprepago.php?page=' + page + '&limit=' + LIMIT + '&search=' + encodeURIComponent(search), true);
                xhttp.send();
            }

            function renderPaginacion() {
                var totalPages = Math.ceil(totalRecords / LIMIT);
                var container = document.getElementById('paginacion');
                var info = document.getElementById('paginacion-info');

                var start = totalRecords === 0 ? 0 : (currentPage - 1) * LIMIT + 1;
                var end = Math.min(currentPage * LIMIT, totalRecords);
                info.textContent = 'Mostrando ' + start + ' - ' + end + ' de ' + totalRecords + ' registros';

                document.querySelector('.table-responsive-scroll').scrollTop = 0;

                container.innerHTML = '';
                if (totalPages <= 1) return;

                function crearItem(label, page, disabled, active) {
                    var li = document.createElement('li');
                    li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
                    var a = document.createElement('a');
                    a.className = 'page-link';
                    a.href = '#';
                    a.textContent = label;
                    if (!disabled) {
                        a.addEventListener('click', function (e) {
                            e.preventDefault();
                            cargarTabla(page);
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        });
                    }
                    li.appendChild(a);
                    return li;
                }

                function crearInputPagina(totalPages) {
                    var li = document.createElement('li');
                    li.className = 'page-item';
                    var input = document.createElement('input');
                    input.type = 'number';
                    input.min = 1;
                    input.max = totalPages;
                    input.placeholder = '✏';
                    input.className = 'page-link page-jump-input';
                    input.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') {
                            var pg = parseInt(input.value);
                            if (pg >= 1 && pg <= totalPages) {
                                cargarTabla(pg);
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            }
                            input.value = '';
                        }
                    });
                    li.appendChild(input);
                    return li;
                }

                container.appendChild(crearItem('«', currentPage - 1, currentPage === 1, false));

                var startPage = Math.max(1, currentPage - 2);
                var endPage = Math.min(totalPages, currentPage + 2);

                if (startPage > 1) {
                    container.appendChild(crearItem('1', 1, false, false));
                    if (startPage > 2) container.appendChild(crearInputPagina(totalPages));
                }
                for (var i = startPage; i <= endPage; i++) {
                    container.appendChild(crearItem(i, i, false, i === currentPage));
                }
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) container.appendChild(crearInputPagina(totalPages));
                    container.appendChild(crearItem(totalPages, totalPages, false, false));
                }

                container.appendChild(crearItem('»', currentPage + 1, currentPage === totalPages, false));
            }
        </script>

        <br>
        <br>
        <br>
        <br>
    </div>

    <!-- Bootstrap 5 JS Bundle (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>


</html>