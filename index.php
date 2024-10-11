<?php 
session_start();
if (isset($_SESSION['usuario']) && !empty($_SESSION['contraseña'])) {
    // Redirigir al usuario si ya está autenticado
    header("Location: buscarProveedor.php");
    exit();
}
?>

<!doctype html>
<html lang="es">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  
    <title>SGC</title>

    <style>
        body, html {
            height: 100%;
            margin: 0;
        }
        .bg-cover {
            background: url('img/fondo.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100%;
        }
        .card {
            border-radius: 1rem;
            background-color: rgba(0, 0, 0, 0.7);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        .btn-custom {
            background-color: #007bff;
            border: none;
            color: #fff;
        }
        .btn-custom:hover {
            background-color: #0056b3;
            color: #fff;
        }
        .form-label {
            color: #ccc;
        }
        .text-white-50 {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        .text-white {
            color: #fff !important;
        }
        .card-body {
            padding: 2rem;
        }
        .position-relative {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 15px;
            cursor: pointer;
            color: #ccc;
        }
    </style>
</head>
<body>
    <div class="bg-cover d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="row d-flex justify-content-center align-items-center min-vh-100">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card text-white" style="border-radius: 1rem;">
                        <div class="card-body p-5 text-center">

                            <div class="mb-md-5 mt-md-4 pb-5">
                                <form action="validar.php" method="post">
                                    <h2 class="fw-bold mb-2 text-uppercase">Acceso</h2>
                                    <p class="text-white-50 mb-5">Ingrese su usuario y contraseña!</p>

                                    <div class="form-outline form-white mb-4">
                                        <input name="usuario" type="text" id="typeEmailX" class="form-control form-control-lg" required />
                                        <label class="form-label" for="typeEmailX">Usuario</label>
                                    </div>

                                    <div class="form-outline form-white mb-4 position-relative">
                                        <input name="contraseña" type="password" id="typePasswordX" class="form-control form-control-lg" required />
                                        <label class="form-label" for="typePasswordX">Contraseña</label>
                                        <span class="toggle-password" onclick="togglePassword()">👁️</span>
                                    </div>

                                    <button style="width:100%" class="btn btn-custom btn-lg px-5 mt-4" type="submit">Ingresar</button>
                                </form>
                            </div>
                            <div>
                                <p class="mb-0">¿No tienes una cuenta? <a href="RegistroUser.php" class="text-white-50 fw-bold">Regístrate</a></p>
                            </div>
                            <div>
                                <p class="mb-0">¿Olvidó su contraseña? <a href="RecuperarPass.php" class="text-white-50 fw-bold">Recuperar Contraseña</a></p>
                            </div>

                        </div>
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
    <script>
        function togglePassword() {
            var passwordField = document.getElementById("typePasswordX");
            var passwordFieldType = passwordField.getAttribute("type");
            if (passwordFieldType === "password") {
                passwordField.setAttribute("type", "text");
            } else {
                passwordField.setAttribute("type", "password");
            }
        }
    </script>
</body>
</html>
