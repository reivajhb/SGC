
<!DOCTYPE html>

<html>
<head>


<script>
window.addEventListener('popstate', function(event) {
  history.pushState(null, null, window.location.pathname);
  history.pushState(null, null, window.location.pathname);
  }, false);
</script>
<nav class="navbar fixed-top navbar-expand-lg  navbar-dark "style="background-color: black;">

  <a class="navbar-brand" href=""><img src="img/pnv.png" width="50" height="50"> </a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="CerrarSesion.php">Cerrar Sesión <span class="sr-only">(current)</span></a>
      <li class="nav-item dropdown">
      <li class="nav-item active">
        <a class="nav-link" href="indexFacturacion.php">Inicio<span class="sr-only">(current)</span></a>
      <!--<li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Registrar Pagos
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
         <a href="formularioCargaDriveHotel.php" class="dropdown-item" href="#">Hoteles</a>
          <a href="buscarProveedor.php" class="dropdown-item" href="#">Proveedores Turisticos</a>
          <a href="formularioCargaDriveAdministrativos.php" class="dropdown-item" href="#">Administrativos</a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Consulta Pagos
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a href="consulta.php" class="dropdown-item" href="#">Hoteles</a>
          <a href="consultaProveedoresTuristicos.php" class="dropdown-item" href="#">Proveedores Turisticos</a>
          <a href="consultaPagosAdministrativos.php" class="dropdown-item" href="#">Administrativos</a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Registro de Proveedores
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a href="consulta.php" class="dropdown-item" href="#">Hoteles</a>
          <a href="RegistroProveedoresTuristicos.php" class="dropdown-item" href="#">Registrar Proveedores Turisticos</a>
          <a href="RegistroProveedoresAdministrativos.php" class="dropdown-item" href="#">Registrar Proveedores Administrativos</a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Registro de Facturas PDV
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a href="buscarClienteCorp.php" class="dropdown-item" href="#">Registrar facturas Corporativo</a>
          <a href="buscarClienteRecep.php" class="dropdown-item" href="#">Registrar facturas Receptivo</a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Consulta Facturas
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a href="consultaFacturasCorp.php" class="dropdown-item" href="#">Corporativos</a>
          <a href="consultaFacturasRecep.php." class="dropdown-item" href="#">Receptivo</a>
      </li>
       <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
         Registro clientes
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a href="RegistroClientesCorp.php" class="dropdown-item" href="#">Corporativos</a>
          <a href="RegistroClientesRecep.php." class="dropdown-item" href="#">Receptivo</a>-->
      </li>
    </ul>
    <form class="form-inline my-2 my-lg-0">
      <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
      <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
    </form>
  </div>
</nav>
<br>

</script>
  
</html>

