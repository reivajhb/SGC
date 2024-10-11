

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SGC-Sistema de gestión contable</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
  <style>
    /* Estilo del sidebar */
    .navbar {
        background-color: #343a40; /* Color de fondo moderno */
        padding: 10px;
    }

    .navbar-brand img {
        margin-right: 10px;
    }

    .navbar-nav .nav-item .nav-link {
        color: #f8f9fa !important; /* Color de texto claro */
        padding: 10px 15px;
        transition: background-color 0.3s, border-radius 0.3s;
    }

    .navbar-nav .nav-item .nav-link:hover {
        background-color: #495057; /* Color de fondo en hover */
        color: #ffffff !important; /* Mantener color blanco en hover */
        border-radius: 5px; /* Bordes redondeados en hover */
    }

    .dropdown-menu {
        background-color: #343a40; /* Fondo oscuro para el menú desplegable */
        border: none; /* Sin borde para el menú desplegable */
        border-radius: 5px; /* Bordes redondeados para el menú desplegable */
    }

    .dropdown-item {
        color: #f8f9fa; /* Color de texto claro para los ítems del menú */
        padding: 10px 20px;
        transition: background-color 0.3s;
    }

    .dropdown-item:hover {
        background-color: #495057; /* Fondo en hover para los ítems del menú */
        color: #ffffff; /* Mantener color blanco en hover */
    }

    .fixed-bottom .btn {
        border-radius: 50%; /* Botón redondeado */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra ligera */
    }

    .fixed-bottom .btn-primary {
        background-color: #007bff; /* Color de fondo del botón */
        border: none; /* Sin borde */
    }

    .fixed-bottom .btn-primary:hover {
        background-color: #0056b3; /* Color de fondo en hover del botón */
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-dark navbar-expand-lg">
    <a class="navbar-brand" href="#">
      <img src="img/pnv.png" width="30" height="30" alt="Logo">
      SGC
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown1" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Informes
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown1">
            <a class="dropdown-item" href="BusquedaPorFechaProveedoresTuristicos.php">Informe de pagos por fecha general Proveedor Turistico</a>
            <a class="dropdown-item" href="BusquedaPorFechaProveedoresAdministrativo.php">Informe de pagos por fecha general Proveedor Administrativos</a>
            <a class="dropdown-item" href="BusquedaPorFechaAnticipos.php">Informe de pagos por fecha general Anticipos</a>
            <a class="dropdown-item" href="InformePorProveedorTuristico.php?id_proveedor_pdv=2">Informe de pagos por Fecha Proveedor Turistico especifico</a>
            <a class="dropdown-item" href="InformePorProveedorAdministrativo.php?id_proveedor_adm=17">Informe de pagos por Fecha Proveedor Administrativos especifico</a>
            <a class="dropdown-item" href="InformeCargaPorUsuario.php?TipoPago=PagosAdministrativos&id_usuario_ad=laura.moreno">Informe de Carga por usuario de tesorería</a>
            <a class="dropdown-item" href="consultaProveedoresTiemposRT.php">Tiempos Anticipos</a>
            <a class="dropdown-item" href="Dashboard.php">Dashboard</a>
          </div>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown2" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Pagos
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown2">
            <a class="dropdown-item" href="buscarProveedor.php">Registrar Pagos Proveedores Turisticos</a>
            <a class="dropdown-item" href="buscarProveedorAdministrativo.php">Registrar Pagos Proveedores Administrativos</a>
            <a class="dropdown-item" href="buscarProveedorInter.php">Registrar Pagos Internacionales</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="consultaProveedoresTuristicos.php">Consultar pagos Proveedores Turistico</a>
            <a class="dropdown-item" href="consultaPagosAdministrativos.php">Consultar pagos Administrativos</a>
            <a class="dropdown-item" href="consultaProveedoresInter.php">Consulta Pagos Internacionales</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="causacion.php">Facturas Proveedores</a>
          </div>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown3" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Anticipos
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown3">
            <a class="dropdown-item" href="consultaProveedoresPrepago.php">Consultar Anticipo</a>
            <a class="dropdown-item" href="consultaProveedoresPrepagoRT.php">Consulta Anticipo con RT</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="buscarProveedorPrepago.php">Registro de anticipos</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="buscarProveedorPrepagoAdm.php">Registro de anticipos Administrativos</a>
          </div>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown4" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Proveedores
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown4">
            <a class="dropdown-item" href="RegistroProveedoresTuristicos.php">Registrar Proveedores Turisticos</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="RegistroProveedoresAdministrativos.php">Registrar Proveedores Administrativos</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="RegistroProveedoresPrepago.php">Registrar Proveedores Para Anticipos</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="RegistroProveedoresInter.php">Registrar Proveedores Internacionales</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="consultaProveedoresTuristicosCs.php">Consultar Proveedores Turisticos</a>
          </div>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown5" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Facturación
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown5">
            <a class="dropdown-item" href="tiquetes.php">Sistema de tiquetes</a>
            <a class="dropdown-item" href="consultatiquetes.php">Tiquetes a facturar</a>
          </div>
        </li>
      </ul>
      <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown my-2 my-lg-0">
          <a class="nav-link dropdown-toggle" id="navbarDropdown6" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Mi cuenta
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown6">
            <a class="dropdown-item" href="CerrarSesion.php">Cerrar sesión</a>
          
            <a class="dropdown-item" href="micuenta.php">Editar Perfil</a>
          </div>
        </li>
      </ul>
    </div>
  </nav>

  <div class="fixed-bottom text-right p-2">
    <button class="btn btn-primary" onclick="topFunction()">
      <i class="fas fa-arrow-up"></i>
    </button>
  </div>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8j6J0c6D9hI4k2yD+WJZ4ffg5zwpA8xhDTOM+U8xFh6Phf" crossorigin="anonymous"></script>
  <script>
    function topFunction() {
      document.body.scrollTop = 0;
      document.documentElement.scrollTop = 0;
    }
  </script>
</body>

</html>
