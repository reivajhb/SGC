<?php
session_start();
if (isset($_SESSION['usuario']) && !empty($_SESSION['id_rol'])) {
    switch ((int)$_SESSION['id_rol']) {
        case 1:
            header("Location: /facturacion/facturacion/project/usuarios/paneladmin.php");
            break;
        case 2:
            header("Location: /facturacion/facturacion/project/proveedores/proveedoresTur/buscarProveedor.php");
            break;
        case 3:
            header("Location: /facturacion/facturacion/views/indexFacturacion.php");
            break;
        case 4:
            header("Location: /facturacion/proveedores/vista/formularioIncripHotel.php");
            break;
        case 5:
        case 8:
            header("Location: /facturacion/facturacion/project/proveedores/proveedoresPrepago/buscarProveedorPrepago.php");
            break;
        case 6:
        case 7:
            header("Location: /facturacion/proveedores/vista/formularioIncripHotel.php");
            break;
        case 9:
            header("Location: /facturacion/facturacion/project/contabilidad/proveedores/consultaFichaProveedores.php");
            break;
        default:
            header("Location: /facturacion/facturacion/project/usuarios/paneladmin.php");
            break;
    }
    exit();
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/x-icon" href="../../../img/favicon.jpg">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">

    <!-- Bootstrap Icons (para el ojito) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <title>SGC</title>

    <style>
        /* Fondo */
        body,
        html {
            height: 100%;
            margin: 0;
            font-family: 'Roboto', sans-serif;
        }

        .bg-cover {
            background: url('../../../img/fondo.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Tarjeta */
        .login-card {
            border-radius: 1rem;
            background-color: #ffffff;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            padding: 2rem;
        }

        .login-card h3 {
            font-weight: 700;
            color: #0044cc;
        }

        .login-card p {
            color: #555;
        }

        /* Inputs */
        .form-control {
            border-radius: 0.5rem;
            border: 1px solid #ccc;
            padding: 12px;
            padding-right: 45px; /* espacio para el ojito */
        }

        .form-control:focus {
            border-color: #0044cc;
            box-shadow: 0 0 0 0.2rem rgba(0, 68, 204, .25);
        }

        .form-label {
            font-size: 0.9rem;
            color: #333;
        }

        /* Botón */
        .btn-custom {
            background: linear-gradient(135deg, #0044cc, #003399);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.3s ease-in-out;
        }

        .btn-custom:hover {
            background: linear-gradient(135deg, #003399, #002266);
            transform: scale(1.02);
        }

        /* Toggle password */
        .input-password-wrap {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            font-size: 1.1rem;
            user-select: none;
            line-height: 1;
        }
        .toggle-password:hover {
            color: #0044cc;
        }
        .error-credenciales {
            color: #dc3545;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 8px;
        }

        .brand-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            border-radius: .5rem;
            background: #ffffffff;
        }
    </style>
</head>

<body>
    <div class="bg-cover">
        <div class="container">
            <div class="row justify-content-center align-items-center min-vh-100">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="login-card">
                        <div class="text-center mb-4">
                            <img src="../../../img/pnv.png" class="brand-logo mb-3" alt="PDV" />
                            <h3 class="fw-bold mb-1">Acceso Panamericana</h3>
                            <p class="text-muted mb-0">Ingrese su usuario y contraseña</p>
                        </div>

                        <form action="validar.php" method="post">
                            <div class="form-group position-relative mb-3">
                                <label class="form-label" for="usuario">Usuario</label>
                                <input name="usuario" type="text" id="usuario" class="form-control" required />
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="contraseña">Contraseña</label>
                                <div class="input-password-wrap">
                                    <input name="contraseña" type="password" id="contraseña" class="form-control" required />
                                    <span class="toggle-password" onclick="togglePassword()" aria-label="Mostrar u ocultar contraseña">
                                        <i id="eyeIcon" class="bi bi-eye"></i>
                                    </span>
                                </div>
                                <?php if (!empty($_GET['error'])): ?>
                                <p class="error-credenciales">Usuario o contraseña inválidos.</p>
                                <?php endif; ?>
                            </div>

                            <button class="btn btn-custom btn-block mt-4" type="submit">Ingresar</button>
                        </form>

                        <div class="mt-4 text-center">
                            <p class="mb-0">
                                ¿Olvidó su contraseña?
                                <a href="RecuperarPass.php" class="text-primary font-weight-bold">Recuperar Contraseña</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script>
        function togglePassword() {
            const passwordField = document.getElementById("contraseña");
            const eyeIcon = document.getElementById("eyeIcon");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.remove("bi-eye");
                eyeIcon.classList.add("bi-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.remove("bi-eye-slash");
                eyeIcon.classList.add("bi-eye");
            }
        }
    </script>
</body>

</html>
