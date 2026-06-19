<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INDUCCIÓN Y COMUNICADOS</title>
    <!-- Enlace a Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
    body {
        padding-top: 56px;
    }

    .card {
        margin-bottom: 20px;
    }
    </style>
</head>

<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="#">INDUCCIÓN Y COMUNICADOS</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="induccion.php">Inducción Nuevo Personal</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="politicas.php">Politicas de seguridad</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="capacitaciones.php">Capacitaciones</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="comunicados.php">Migracion Nube</a>
                </li>

                <li class="nav-item active">
                    <a class="nav-link" href="manuales.php">Información uso One drive<span
                            class="sr-only">(current)</span></a>
                </li>
            </ul>
            </ul>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container mt-5">
        <!-- Sección de manuales -->
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Charla de finanzas personales</h3>
                        <p class="card-text">
                            Comparto Archivos, Plantillas y Videos para Organizar tus Finanzas
                        </p>
                        <div class="embed-responsive embed-responsive-16by9">
                            <iframe class="embed-responsive-item"
                                src="https://drive.google.com/file/d/1xjN4XWbB7LfUd-l88qvsMoz7j2EDXh7f/preview"
                                allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-folder-fill me-2"></i>Plantillas</h5>
                        <p class="card-text">Descarga las plantillas a continuación:</p>
                        <ul class="list-group">
                            <li class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bi bi-file-earmark-pdf-fill text-danger me-3 fs-4"></i>
                                <a href="https://drive.google.com/uc?export=download&id=1DgWznzJ-mrACQVQ7UF9v09tXXYHRSU6e"
                                    class="text-decoration-none" target="_blank">ABONOS A CAPITAL EN TU DEUDA</a>
                            </li>
                            <li class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bi bi-file-earmark-excel-fill text-success me-3 fs-4"></i>
                                <a href="https://drive.google.com/uc?export=download&id=1HneGTuinGIc_CS7dcMoL0cNxFzFVVOab"
                                    class="text-decoration-none" target="_blank">Abonos vs Inversión</a>
                            </li>
                            <li class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bi bi-file-earmark-text-fill text-primary me-3 fs-4"></i>
                                <a href="https://drive.google.com/uc?export=download&id=1Ew4EEO_vGeedRgWR4822oBvXQGbxKG_r"
                                    class="text-decoration-none" target="_blank">Cuentas de ahorro de alto
                                    rendimiento</a>
                            </li>
                            <li class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bi bi-file-earmark-excel-fill text-success me-3 fs-4"></i>
                                <a href="https://drive.google.com/uc?export=download&id=1IcZzrPOsjQIxqvBcE6yqxtOQJDF_CFML"
                                    class="text-decoration-none" target="_blank">Presupuesto_v3</a>
                            </li>
                            <li class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bi bi-file-earmark-pdf-fill text-danger me-3 fs-4"></i>
                                <a href="https://drive.google.com/uc?export=download&id=1qgd0J_KGT8nsznIbE1fTkP1h_QojD7ZJ"
                                    class="text-decoration-none" target="_blank">Reto de ahorro 100 días</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enlace a Bootstrap JS y dependencias de Popper.js -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>