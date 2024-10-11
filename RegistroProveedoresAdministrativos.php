<?php 
include "seguridad.php";
?>

<!doctype html>
<html lang="es">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="estilos/estilos.css">

    <title>Registro de nuevos proveedores Administrativos</title>
</head>

<body>
    <header>
        <?php include "sidebar.php"; ?>
    </header>

    <div class="container mt-4">
        <div class="row">
            <div class="col">
                <div class="bg-primary text-white p-3 mb-4">
                    <h2 class="text-center">Registro de nuevos proveedores Administrativos</h2>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body p-5">
                        <form action="cargaProveedopdvAdmin.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="identificacion">Identificación*</label>
                                <input name="identificacion" type="number" class="form-control" id="identificacion" placeholder="Ingrese el número de Identificación" required>
                            </div>
                            <div class="form-group">
                                <label for="nombre">Nombre Proveedor Administrativo*</label>
                                <input name="nombre" type="text" class="form-control" id="nombre" placeholder="Ingrese el nombre del Proveedor" required>
                            </div>
                            <div class="form-group">
                                <label for="email_contabilidad">Correo Contabilidad*</label>
                                <input name="email_contabilidad" type="email" class="form-control" id="email_contabilidad" placeholder="Ingrese el correo de Contabilidad" required>
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
