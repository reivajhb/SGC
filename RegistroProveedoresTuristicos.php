<?php 
include "seguridad.php";
?>

<!doctype html>
<html lang="es">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="estilos/estilos.css">

    <title>Registro de nuevos proveedores turísticos</title>
</head>

<body>
    <header>
        <?php include "sidebar.php"; ?>
    </header>

    <div class="container mt-4">
        <div class="row">
            <div class="col">
                <div class="bg-primary text-white p-3 mb-4">
                    <h2 class="text-center">Registro de nuevos proveedores turísticos</h2>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <form action="cargaProveedopdv.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="nit">Nit*</label>
                                <input name="nit" type="number" class="form-control" id="nit" placeholder="Ingrese el Nit" required>
                            </div>
                            <div class="form-group">
                                <label for="nom_proveedor">Nombre Proveedor Turístico*</label>
                                <input name="nom_proveedor" type="text" class="form-control" id="nom_proveedor" placeholder="Ingrese el nombre del Proveedor" required>
                            </div>
                            <div class="form-group">
                                <label for="email_contabilidad">Correo Contabilidad*</label>
                                <input name="email_contabilidad" type="email" class="form-control" id="email_contabilidad" placeholder="Correo Contabilidad" required>
                            </div>
                            <div class="form-group">
                                <label for="email_cartera">Correo Cartera*</label>
                                <input name="email_cartera" type="email" class="form-control" id="email_cartera" placeholder="Correo Cartera" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Registrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>

</html>
