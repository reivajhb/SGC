<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migración a la Nube - Información</title>
    <!-- Enlace a Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
        <a class="navbar-brand" href="#">Comunicados Sistemas</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="comunicados.php">Migracion Nube</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="manuales.php">Información uso One drive</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="politicas.php">Politicas de seguridad</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container mt-5">
        <!-- Sección de información -->
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Proceso de Migración a la Nube</h3>
                        <p class="card-text">
                            Actualmente, manejamos una infraestructura ON-PREMISE que será reemplazada por una infraestructura en la nube utilizando Azure. Aquí tienes una visión general del proceso:
                        </p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>Autenticación en la Nube de Azure:</strong> La autenticación ya no será local, sino que se realizará a través de Azure.
                            </li>
                            <li class="list-group-item">
                                <strong>Asignación de Cuentas de OneDrive:</strong> Inicialmente, se asignará una cuenta de OneDrive por departamento y equipos para almacenar sus archivos.
                            </li>
                            <li class="list-group-item">
                                <strong>Depuración de Información:</strong> El objetivo es cargar los archivos necesarios en cada cuenta departamental de OneDrive después de la depuración de la información.
                            </li>
                            <li class="list-group-item">
                                <strong>Entrega de Accesos:</strong> Se estarán entregando los accesos para que los departamentos suban sus archivos.
                            </li>
                            <li class="list-group-item">
                                <strong>Migración Progresiva:</strong> La autenticación de usuarios se realizará de forma paulatina, comenzando con equipos pequeños y progresivamente integrando más usuarios al nuevo Directorio Activo.
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Detalles del Proceso</h5>
                        <p class="card-text">
                            Este es el proceso detallado que seguiremos para la migración:
                        </p>
                        <ol class="list-group list-group-flush">
                            <li class="list-group-item">Evaluación y preparación de la infraestructura actual.</li>
                            <li class="list-group-item">Configuración de Azure AD y OneDrive para empresas.</li>
                            <li class="list-group-item">Asignación de cuentas y espacios de almacenamiento por departamento.</li>
                            <li class="list-group-item">Depuración y organización de la información actual.</li>
                            <li class="list-group-item">Carga inicial de archivos depurados a las nuevas cuentas de OneDrive.</li>
                            <li class="list-group-item">Capacitación y entrega de accesos a los equipos de trabajo.</li>
                            <li class="list-group-item">Migración paulatina de autenticación de usuarios al nuevo Directorio Activo en Azure.</li>
                            <li class="list-group-item">Soporte continuo y resolución de incidencias durante la migración.</li>
                        </ol>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Equipos Migrados A la Nube</h5>
                        <p class="card-text">
                            Nos complace informar los equipos migrados a la Nube:
                        </p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>Equipo Financiero y administrativo</strong>
                                <p>El equipo financiero utiliza Microsoft Entra para garantizar su autenticación en la nube. Además, cuentan con cuentas de OneDrive y han migrado todos sus archivos a la nube.</p>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">100%</div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <strong>Equipo Turivel, Counter, Agencia de viajes y Corporativo</strong>
                                <p>Nos encontramos en el proceso de socialización, configuración y entrega de cuentas para la posterior migración de archivos.</p>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 90%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">90%</div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <strong>Equipo Reservas, A la carta, Grupos, Soporte Web, Transportes y Negociaciones</strong>
                                <p>Nos encontramos en el proceso de socialización, configuración y entrega de cuentas para la posterior migración de archivos.</p>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 90%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="90">90%</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <iframe aria-label='Contacto' frameborder="0" style="height:500px;width:100%;border:none;" src='https://forms.zohopublic.com/panamericadeviajes/form/Contacto/formperma/rMhF6QyOA3w4QNkzrpb7gqEprNtbpU_t3iY9tYb_jW8' target="_blank"></iframe>
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
