<!-- sidebar.php -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        .navbar-custom {
            background: linear-gradient(90deg, #007bff, #0056b3);
        }

        .navbar-custom .nav-link {
            color: #fff !important;
            font-weight: 500;
        }

        .navbar-custom .nav-link:hover {
            text-decoration: underline;
        }

        .navbar-brand {
            font-weight: bold;
            color: #fff !important;
        }

        .chat-link {
            background: #28a745;
            border-radius: 20px;
            padding: 5px 15px;
            margin-left: 10px;
        }

        .chat-link:hover {
            background: #218838;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-custom">
        <a class="navbar-brand" href="#">📦 Inventario</a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">

                <li class="nav-item">
                    <a class="nav-link" href="/facturacion/inventario/public/index.php?action=insertar">📥 Insertar Datos</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/facturacion/inventario/public/index.php?action=consultar">📊 Consultar Inventario</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/facturacion/inventario/public/index.php?action=showAll">🏷️ Marquillas Equipo</a>
                </li>

            </ul>

            <!-- BOTÓN CHAT -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link chat-link" href="/facturacion/inventario/public/index.php?action=chat">
                        💬 Soporte IA
                    </a>
                </li>
                <li class="nav-item ml-2">
                    <a class="nav-link" href="/facturacion/facturacion/project/usuarios/paneladmin.php">
                        ← Volver al Sistema
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">