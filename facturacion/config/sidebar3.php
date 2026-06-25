<?php
// Comprobar si la sesión ya ha sido iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="/facturacion/img/pnv.png">

    <!-- Bootstrap 4.6.2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <style>
    /* ================================================= */
    /* 1. Estilos Globales, Tipografía y Fondo           */
    /* ================================================= */
    body {
        background-color: #f7f9fc;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 14px;
        margin: 0;
        padding: 0;
        color: #333;
    }

    .content {
        margin-top: 75px; /* Ajustado para que no tape el contenido */
        padding: 20px;
    }

    /* ================================================= */
    /* 2. Top Navbar (Barra Superior) Estilo Moderno     */
    /* ================================================= */
    .navbar {
        background-color: #111827 !important; /* Gris ultra oscuro profesional */
        padding: 12px 25px;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1050;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .navbar-brand {
        font-weight: 800;
        font-size: 1.25rem;
        color: #ffffff !important;
        letter-spacing: -0.5px;
    }

    /* Enlaces principales */
    .navbar-nav .nav-item .nav-link {
        color: #9ca3af !important; /* Gris suave */
        padding: 10px 18px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .navbar-nav .nav-item .nav-link:hover,
    .navbar-nav .nav-item.show .nav-link {
        color: #ffffff !important;
    }

    /* ================================================= */
    /* 3. Dropdowns con Animación y Estilo Card          */
    /* ================================================= */
    .dropdown-menu {
        background-color: #1f2937;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
        border-radius: 12px;
        padding: 8px;
        min-width: 230px;
        margin-top: 10px !important; /* Espacio para que respire */
    }

    /* Animación Slide Down que pediste */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-menu.show {
        display: block;
        animation: slideDown 0.3s ease-out forwards;
    }

    .dropdown-item {
        color: #d1d5db;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 0.88rem;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background: linear-gradient(
        to right, 
        transparent 0%, 
        rgba(79, 70, 229, 0.20) 15%, 
        rgba(79, 70, 229, 0.20) 85%, 
        transparent 100%
    ) !important;
        color: #818cf8 !important;
        transform: translateX(5px); /* Efecto de desplazamiento */
        width: 98.3%;
    }

    .dropdown-header {
        color: #6b7280;
        text-transform: uppercase;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 1px;
        padding: 10px 15px 5px;
    }

    .dropdown-divider {
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        margin: 6px 0;
    }

    /* ================================================= */
    /* 4. Estilos para Botones de Sesión y Especiales   */
    /* ================================================= */
    .nav-link.text-danger i {
        color: #ef4444 !important;
    }

    .nav-link.text-danger:hover {
        background-color: rgba(239, 68, 68, 0.1) !important;
        border-radius: 8px;
    }

    /* Botón flotante */
    .btn-float {
        position: fixed;
        right: 20px;
        bottom: 20px;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        background-color: #4f46e5;
        color: white;
        box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .btn-float:hover {
        transform: scale(1.1);
        background-color: #4338ca;
    }

    a.outsession {
        font-size: 13.6px !important;
        padding-left: 15px !important;
        padding-top: 8px !important;
        border-radius: 0px !important;
        height: 35px !important;
        width: 98% !important;
        font-weight: 400 !important;
    }
    
    .outsession:hover {
        background: linear-gradient(
        to right, 
        transparent 0%, 
        rgba(229, 70, 70, 0.2) 20%, 
        rgba(229, 70, 70, 0.2) 80%, 
        transparent 100%
        ) !important;
        transform: translateX(3px);
        border-radius: 8px;
        color: #ef4444 !important;
    }
</style>
</head>

<body>
    <div class="content">
        <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
            <a class="navbar-brand" href="#">
                <img style="margin-left: 1.5rem !important;" src="/facturacion/img/pnv.png" width="30" height="30" alt="Logo">
                SGC ERP
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">

                    <!-- INFORMES -->
                    <?php if (isset($_SESSION['id_rol']) && in_array($_SESSION['id_rol'], [1, 8])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown1" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-chart-bar"></i> Informes
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown1">
                            <h6 class="dropdown-header"><i class="fas fa-money-bill"></i> Pagos</h6>
                            <a class="dropdown-item"
                                href="/facturacion/informes/informesTesoreria/BusquedaPorFechaProveedoresTuristicos.php">Turísticos</a>
                            <a class="dropdown-item"
                                href="/facturacion/informes/informesTesoreria/BusquedaPorFechaProveedoresAdministrativo.php">Administrativos</a>
                            <a class="dropdown-item"
                                href="/facturacion/informes/informesTesoreria/BusquedaPorFechaAnticipos.php">Anticipos</a>
                            <div class="dropdown-divider"></div>

                            <h6 class="dropdown-header"><i class="fas fa-university"></i> Tesorería</h6>
                            <a class="dropdown-item"
                                href="/facturacion/informes/informesTesoreria/InformeCargaPorUsuario.php?TipoPago=PagosAdministrativos&id_usuario_ad=laura.moreno">
                                Carga por usuario</a>
                            <div class="dropdown-divider"></div>

                            <h6 class="dropdown-header"><i class="fas fa-clock"></i> Anticipos</h6>
                            <a class="dropdown-item"
                                href="/facturacion/informes/informesTesoreria/consultaProveedoresTiemposRT.php">Tiempos</a>
                            <div class="dropdown-divider"></div>

                            <a class="dropdown-item" href="/facturacion/informes/dashboard/Dashboard.php"><i
                                    class="fas fa-chart-pie"></i> Dashboard</a>
                        </div>
                    </li>
                    <?php endif; ?>

                    <!-- PAGOS -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown2" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-hand-holding-usd"></i> Pagos
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown2">
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresTur/buscarProveedor.php">Registrar Turísticos</a>
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresAdmin/buscarProveedorAdministrativo.php">Registrar
                                Administrativos</a>
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresInternacionales/buscarProveedorInter.php">Registrar
                                Internacionales</a>
                            <?php if (isset($_SESSION['id_rol']) && in_array($_SESSION['id_rol'], [1,2,8])): ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresAdmin/pagosTarjeta.php">Pagos Tarjeta de credito</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresTur/consultaProveedoresTuristicos.php">Consultar
                                Turísticos</a>
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresAdmin/consultaPagosAdministrativos.php">Consultar
                                Administrativos</a>
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresInternacionales/consultaProveedoresInter.php">Consultar
                                Internacionales</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/facturacion/facturacion/project/contabilidad/causacion/causacion.php">Facturas Proveedores</a>
                        </div>
                    </li>

                    <!-- ANTICIPOS -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown3" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-wallet"></i> Anticipos
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown3">
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresPrepago/consultaProveedoresPrepago.php">Consultar
                                Anticipos</a>
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresPrepago/consultaProveedoresPrepagoRT.php">Consultar con
                                RT</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresPrepago/buscarProveedorPrepago.php">Registrar
                                Anticipos</a>
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresPrepago/buscarProveedorPrepagoAdm.php">Registrar
                                Administrativos</a>
                        </div>
                    </li>

                    <!-- PROVEEDORES -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown4" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-truck"></i> Proveedores
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown4">
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresTur/RegistroProveedoresTuristicos.php">Registrar
                                Turísticos</a>
                            <a class="dropdown-item"
                                href="/facturacion/facturacion/project/proveedores/proveedoresAdmin/RegistroProveedoresAdministrativos.php">Registrar Administrativos</a>
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresPrepago/RegistroProveedoresPrepago.php">Registrar para
                                Anticipos</a>
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresInternacionales/RegistroProveedoresInter.php">Registrar
                                Internacionales</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/facturacion/facturacion/project/proveedores/proveedoresTur/consultaProveedoresTuristicosCs.php">Consultar
                                Proveedores</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/facturacion/facturacion/project/contabilidad/proveedores/consultaFichaProveedores.php">Consultar Ficha
                                Proveedores</a>
                        </div>

                    </li>

                    <!-- TIQUETES -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown5" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ticket-alt"></i> Tiquetes
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown5">
                            <a class="dropdown-item" href="/facturacion/facturacion/project/tiquetes/consultatiquetes.php">A facturar</a>
                        </div>
                    </li>

                    <!-- GENERADOR QR -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown6" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-qrcode"></i> Generador QR
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown6">
                            <a class="dropdown-item" href="/facturacion/generadorQR/generateqr.php">Nuevo QR</a>
                            <a class="dropdown-item" href="/facturacion/generadorQR/conteo_escaneos.php">Conteo de
                                Escaneos</a>
                            <a class="dropdown-item" href="/facturacion/generadorQR/informe_escaneos.php">Informe de
                                Escaneos</a>
                        </div>
                    </li>
                    <?php if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1): ?>
                        <!-- INVENTARIO -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownInventario" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-boxes"></i> Inventario
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdownInventario">
                                <a class="dropdown-item" href="/facturacion/inventario/public/index.php?action=chat">Chat de Soporte
                                    IA</a>
                                <a class="dropdown-item" href="/facturacion/inventario/public/index.php?action=consultar">Consultar
                                    Inventario</a>
                                <a class="dropdown-item" href="/facturacion/inventario/public/index.php?action=showAll">Ver Todo</a>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>


                <!-- CUENTA -->
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown my-2 my-lg-0">
                        <a class="nav-link dropdown-toggle" id="navbarDropdownUser" role="button" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownUser">
                            <a class="dropdown-item" href="/facturacion/facturacion/project/usuarios/micuenta.php">Editar Perfil</a>
                            <?php
                            if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
                                echo '<a class="dropdown-item" href="/facturacion/facturacion/project/usuarios/paneladmin.php">Panel Admin</a>';
                            }
                            ?>
                            <div class="dropdown-divider"></div>
                            <a class="nav-link text-danger outsession" style="color: #282828 !important;" href="/facturacion/facturacion/project/usuarios/CerrarSesion.php" onclick="return confirm('¿Estás seguro que deseas cerrar sesión?');">
                            Cerrar Sesión    <i style="margin-right: 1rem; margin-left: 0.2rem;" class="fas fa-sign-out-alt fa-lg"></i>
                            </a>
                        </div>
                    </li>

                    <!-- Cerrar Sesión -->
                    <li class="nav-item my-2 my-lg-0">
                        <a class="nav-link text-danger" href="/facturacion/facturacion/project/usuarios/CerrarSesion.php" onclick="return confirm('¿Estás seguro que deseas cerrar sesión?');">
                            <i style="margin-right: 1rem; margin-left: 0.2rem;" class="fas fa-sign-out-alt fa-lg"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <!-- Botón flotante -->
    <div id="scrollTopBtn" class="fixed-bottom text-right p-3" style="display: none;">
        <button class="btn btn-primary" onclick="topFunction()">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>

    <!-- JS Bootstrap 4 (orden correcto: jQuery -> Popper -> Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Añadir efecto de deslizamiento suave a los dropdowns
            $('.dropdown').on('show.bs.dropdown', function() {
                $(this).find('.dropdown-menu').first().stop(true, true).slideDown(300);
            });

            $('.dropdown').on('hide.bs.dropdown', function() {
                $(this).find('.dropdown-menu').first().stop(true, true).slideUp(200);
            });
        });

        // Mostrar/ocultar botón según scroll
        window.onscroll = function () {
            toggleScrollButton();
        };

        function toggleScrollButton() {
            const scrollTopBtn = document.getElementById("scrollTopBtn");
            if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                scrollTopBtn.style.display = "block";
            } else {
                scrollTopBtn.style.display = "none";
            }
        }

        function topFunction() {
            document.body.scrollTop = 0; // Safari
            document.documentElement.scrollTop = 0; // Chrome, Firefox, IE, Opera
        }
    </script>
</body>

</html>