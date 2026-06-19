<?php
include "../../../config/seguridad.php";
include "../../../config/conexion.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Obtener nombre del usuario logueado
$nombre_usuario_logueado = '';
if (isset($_SESSION['usuario'])) {
    $stmt_nombre = mysqli_prepare($conn, "SELECT nombre FROM tbl_usuarios WHERE usuario = ? LIMIT 1");
    if ($stmt_nombre) {
        mysqli_stmt_bind_param($stmt_nombre, "s", $_SESSION['usuario']);
        mysqli_stmt_execute($stmt_nombre);
        mysqli_stmt_bind_result($stmt_nombre, $nombre_usuario_logueado);
        mysqli_stmt_fetch($stmt_nombre);
        mysqli_stmt_close($stmt_nombre);
    }
}

if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    include "../../../config/sidebar3.php";
    include "../../../config/boton_volver.php";
} else {
    include "../../../config/sidebar.php";
    include "../../../config/boton_volver.php";
}

header('Content-Type: application/json; charset=utf-8');

if (!isset($_FILES['rut'])) {
    echo json_encode([
        "ok" => false,
        "error" => "No se recibió el archivo RUT"
    ]);
    exit;
}

$rutaPdf = $_FILES['rut']['tmp_name'];

$python = "python";
$script = __DIR__ . "/leer_pdf.py";

$cmd = escapeshellcmd($python) . " " .
       escapeshellarg($script) . " " .
       escapeshellarg($rutaPdf);

$respuesta = shell_exec($cmd);

echo $respuesta;
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">
    <title>Registro Proveedor Turístico</title>
    <style>
        /* Encapsulamiento para proteger el Navbar */
        #cuerpo-registro {
            background-color: #f4f7f6;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        #container-formulario {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px 15px;
        }

        /* Encabezado Corporativo */
        .header-proveedor {
            background: linear-gradient(135deg, #1a2a6c, #2a4858);
            color: white;
            padding: 2.5rem 1rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            margin-bottom: -50px; 
            position: relative;
            z-index: 2;
        }

        /* Tarjeta del Formulario */
        .card-proveedor {
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            background: #ffffff;
            padding-top: 60px; 
            z-index: 1;
        }

        .label-custom {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .input-custom {
            border-radius: 8px;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }

        .input-custom:focus {
            border-color: #2ecc71;
            box-shadow: 0 0 0 0.25rem rgba(46, 204, 113, 0.15);
            outline: none;
        }

        /* Botón Verde Vivo solicitado */
        .btn-registrar-prov {
            background-color: #27ae60;
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-weight: 700;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            cursor: pointer;
            width: 100%;
        }

        .btn-registrar-prov:hover {
            background-color: #2ecc71;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.3);
            color: white;
        }
        
        .btn-registrar-prov:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
            transform: none;
        }

        /* Overlay de carga */
        #loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        #loading-overlay.active {
            display: flex;
        }
        
        .loading-content {
            text-align: center;
            color: white;
        }
        
        .spinner {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
        }
        
        .spinner::before {
            content: '';
            position: absolute;
            inset: 0;
            border: 4px solid rgba(255, 255, 255, 0.2);
            border-top: 4px solid #27ae60;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        #loading-overlay.ai-mode .spinner::before {
            border-top-color: #3498db;
        }
        
        .spinner img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
            display: block;
            z-index: 1;
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
            50% {
                transform: translate(-50%, -50%) scale(1.05);
                opacity: 0.9;
            }
        }
        
        .loading-text {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .loading-text.ai-processing::after {
            content: '';
            animation: dots 1.5s steps(4, end) infinite;
        }
        
        @keyframes dots {
            0%, 20% { content: ''; }
            40% { content: '.'; }
            60% { content: '..'; }
            80%, 100% { content: '...'; }
        }
        
        .loading-subtext {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .spacer-nav { height: 30px; }
    </style>
</head>

<body id="cuerpo-registro">

    <div style="margin-top: 0 !important; padding-top: 0 !important;"></div>

    <div id="container-formulario">
        
        <div class="header-proveedor text-center">
            <h2 class="fw-bold mb-1">Registro de Proveedores</h2>
            <p class="mb-0 text-white-50">Gestión de Proveedores Turísticos</p>
        </div>

        <div style="margin-top: 100px; padding-top: 0;" class="card card-proveedor">
            <div class="card-body p-4 p-md-5">
                <form id="formRegistroProveedor" action="cargaProveedopdv.php" method="post" enctype="multipart/form-data">

                    <input name="tipo_proveedor" type="hidden" value="Turístico">

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">NIT / Identificación*</label>
                            <input name="nit_identificacion" id="nit_identificacion" type="number" class="form-control input-custom"
                                placeholder="Ingrese el NIT" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">Tipo de Proveedor*</label>
                            <select name="tipo_proveedor_hotel" id="tipo_proveedor_hotel" class="form-control input-custom" required>
                                <option value="" disabled selected>Seleccione el tipo...</option>
                                <option value="Hotel">Hotel</option>
                                <option value="Cadena Hotelera">Cadena Hotelera</option>
                            </select>
                            <small class="text-muted">Debe seleccionar una opción antes de continuar</small>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">Nombre comercial del Proveedor*</label>
                            <input name="nombre" id="nombre" type="text" class="form-control input-custom"
                                placeholder="Nombre" required>
                        </div>
                        <div class="mb-4 col-md-6">
                            <label class="label-custom">Correo Electrónico Contabilidad*</label>
                            <input name="email_contabilidad" id="email_contabilidad" type="email" class="form-control input-custom"
                                placeholder="contabilidad@empresa.com" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">Razon social*</label>
                            <input name="razon_social" id="razon_social" type="text" class="form-control input-custom"
                                placeholder="Nombre o Razón Social" required>
                        </div>
                       <div class="col-md-6 mb-4">
                            <label class="label-custom">País*</label>
                            <select name="pais" id="pais" class="form-control input-custom" required>
                                <option value="">Cargando países...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">Ciudad*</label>
                            <select name="ciudad" id="ciudad" class="form-control input-custom" required disabled>
                                <option value="">Seleccione un país primero...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                                <label class="label-custom">Departamento*</label>
                                <input name="departamento" id="departamento" type="text" class="form-control input-custom"
                                    placeholder="Departamento" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">Telefono hotel*</label>
                            <input name="telefono_hotel" id="telefono_hotel" type="text" class="form-control input-custom"
                                placeholder="Ingrese el teléfono del hotel" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">Direccion*</label>
                            <input name="direccion" id="direccion" type="text" class="form-control input-custom"
                                placeholder="Ingrese la dirección" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">Sitio web*</label>
                            <input name="sitio_web" type="text" class="form-control input-custom"
                                placeholder="Ingrese el sitio web" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">Diligenciado por:</label>
                            <input type="text" class="form-control input-custom" 
                                value="<?php echo htmlspecialchars($nombre_usuario_logueado); ?>" 
                                readonly style="background-color: #e9ecef; cursor: not-allowed;">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">Cuenta bancaria*</label>
                            <input name="cuenta_bancaria" type="text" class="form-control input-custom"
                                placeholder="Ingrese la cuenta bancaria" required>
                        </div>
                    </div>
                    <h3>Datos de contacto</h3>
                    <div style="background-color: grey; margin: 0 0 15px 0; padding: 0; border-radius: 10px; width: 100%; height: 2px;"></div>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="label-custom">Contacto reservas*</label>
                                <input name="contacto_reservas" type="text" class="form-control input-custom"
                                    placeholder="Ingrese el contacto de reservas" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="label-custom">Correo Electrónico reservas*</label>
                                <input name="email_reservas" type="email" class="form-control input-custom"
                                    placeholder="reservas@empresa.com" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="label-custom">Telefono reservas*</label>
                                <input name="telefono_reservas" type="text" class="form-control input-custom"
                                    placeholder="Ingrese el teléfono de reservas" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="label-custom">Contacto Extranet*</label>
                                <input name="contacto_extranet" type="text" class="form-control input-custom"
                                    placeholder="Ingrese el contacto de extranet" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="label-custom">Correo electronico contacto extranet</label>
                                <input name="email_extranet" type="email" class="form-control input-custom"
                                    placeholder="Ingrese el correo electrónico de contacto extranet" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="label-custom">Telefono Contacto Extranet</label>
                                <input name="telefono_extranet" type="text" class="form-control input-custom"
                                    placeholder="Ingrese el teléfono de contacto extranet" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="label-custom">¿Cuenta con algún otro servicio adicional?</label>
                                <input name="otro_servicio" type="text" class="form-control input-custom"
                                    placeholder="Ingrese el otro servicio" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="label-custom">¿Cuál?</label>
                                <input name="cual_servicio" type="text" class="form-control input-custom"
                                    placeholder="Ingrese el nombre del otro servicio" required>
                            </div>
                        </div> 
                    <h3>Documentos</h3>
                    <div style="background-color: grey; margin: 0 0 15px 0; padding: 0; border-radius: 10px; width: 100%; height: 2px;"></div> 
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">RUT*</label>
                            <input id="rut" name="rut" type="file" class="form-control input-custom"
                                placeholder="Suba el documento del RUT" required>
                            <small id="msg_rut" class="text-muted">Se validará la vigencia antes de registrar.</small>
                        </div>  
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">RNT*</label>
                            <input id="rnt" name="rnt" type="file" class="form-control input-custom"
                                placeholder="Suba el documento del RNT" required>
                            <small id="msg_rnt" class="text-muted">Se validará la vigencia antes de registrar.</small>
                        </div>  
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">Certificación bancaria*</label>
                            <input id="certificacion_bancaria" name="certificacion_bancaria" type="file" class="form-control input-custom"
                                placeholder="Suba el documento de certificación bancaria" required>
                            <small id="msg_certificacion_bancaria" class="text-muted">Se validará la vigencia antes de registrar.</small>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">Planes especiales*</label>
                            <input id="planes_especiales" name="planes_especiales" type="file" class="form-control input-custom"
                                placeholder="Suba el documento de planes especiales" required>
                            <small id="msg_planes_especiales" class="text-muted">Se validará la vigencia antes de registrar.</small>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="label-custom">Certificado de sostenibilidad*</label>
                            <input id="certificado_sostenibilidad" name="certificado_sostenibilidad" type="file" class="form-control input-custom"
                                placeholder="Suba el documento de certificado de sostenibilidad" required>
                        </div>
                    </div>               
                    <div class="mb-4 mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="aceptarTerminos" required>
                            <label class="form-check-label" for="aceptarTerminos">
                                Acepto los <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalTerminos" style="color: #27ae60; text-decoration: none; font-weight: 600; cursor: pointer;">Términos y Condiciones</a>*
                            </label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" id="btnRegistrar" class="btn-registrar-prov">
                            Registrar Proveedor
                        </button>
                    </div>
                </form>
                
                <!-- Overlay de carga -->
                <div id="loading-overlay">
                    <div class="loading-content">
                        <div class="spinner">
                            <img src="/facturacion/img/faviconxd.png" alt="Cargando..." />
                        </div>
                        <div class="loading-text">Estamos validando la vigencia de sus documentos</div>
                        <div class="loading-subtext">Por favor espere un momento</div>
                    </div>
                </div>
            </div>
        </div>
        <p class="text-center mt-4 text-muted small">
            SGC ERP - Módulo Turístico
        </p>
    </div>
    <div class="modal fade" id="modalTerminos" tabindex="-1" aria-labelledby="modalTerminosLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);">
                <div class="modal-header" style="background: linear-gradient(135deg, #1a2a6c, #2a4858); color: white; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                    <h5 class="modal-title fw-bold" id="modalTerminosLabel">
                        <i class="bi bi-file-text"></i> Términos y Condiciones
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 20px; max-height: 400px; overflow-y: auto;">
                    <h4 class="fw-bold mb-3">Términos y Condiciones para Proveedores Turísticos</h4>
                    <p>Referencia: Ley de Protección de Datos Personales
                    De acuerdo con la Ley 1581 de 2012 y en cumplimiento a lo establecido en el artículo 10 del decreto reglamentario 1377 de 2013, PANAMERICANA DE VIAJES, con nit. 860.402.288-1, manejan la política de privacidad en la protección de datos personales, la cual garantiza a los titulares la debida seguridad sobre los mismos.
                    Los datos recolectados por nosotros y la actualización de los mismos, podrán ser procesados, almacenados y utilizados para campañas de fidelización, publicidad y mercadeo, estudio de preferencia de consumo, estudio de crédito y cobranza, convenios, eventos, para informar sobre los productos, evaluar la calidad de los productos, todo lo cual consta en las políticas de tratamiento y de procedimientos adoptados por la compañía.
                    Si usted desea que sus datos sean suprimidos de nuestra base de datos, se pueden contactar con el correo electrónico, servicio.cliente@panamericanaviajes.com, dentro de los treinta días hábiles siguientes a partir de la fecha de esta publicación.
                    Si dentro del tiempo siguiente no se presenta ninguna novedad PANAMERICANA DE VIAJES, podrán hacer uso de la información suministrada por los titulares de manera directa, expresa e inequívoca, para continuar realizando el procedimiento de dichos datos de acuerdo con las políticas de privacidad para el manejo de los datos personales.
                    La protección de sus datos personales es importante para PANAMERICANA DE VIAJES</p>
                </div>
                <div class="modal-footer" style="background-color: #f8f9fa; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        // Todo el código jQuery debe ir dentro de document.ready
        $(document).ready(function () {

            const apiBase = "https://countriesnow.space/api/v0.1";

            // Cargar países
            $.ajax({
                url: apiBase + "/countries",
                method: "GET",
                success: function (response) {
                    let paisSelect = $("#pais");
                    paisSelect.empty();
                    paisSelect.append('<option value="">Seleccione un país...</option>');

                    response.data.forEach(function (item) {
                        paisSelect.append(
                            `<option value="${item.country}">${item.country}</option>`
                        );
                    });
                },
                error: function () {
                    $("#pais").html('<option value="">Error cargando países</option>');
                }
            });

            // Cargar ciudades según país
            $("#pais").on("change", function () {
                let pais = $(this).val();
                let ciudadSelect = $("#ciudad");

                ciudadSelect.prop("disabled", true);
                ciudadSelect.html('<option value="">Cargando ciudades...</option>');

                if (!pais) {
                    ciudadSelect.html('<option value="">Seleccione un país primero...</option>');
                    return;
                }

                $.ajax({
                    url: apiBase + "/countries/cities",
                    method: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
                        country: pais
                    }),
                    success: function (response) {
                        ciudadSelect.empty();
                        ciudadSelect.append('<option value="">Seleccione una ciudad...</option>');

                        if (response.data && response.data.length > 0) {
                            response.data.forEach(function (ciudad) {
                                ciudadSelect.append(
                                    `<option value="${ciudad}">${ciudad}</option>`
                                );
                            });

                            ciudadSelect.prop("disabled", false);
                        } else {
                            ciudadSelect.html('<option value="">No hay ciudades disponibles</option>');
                        }
                    },
                    error: function () {
                        ciudadSelect.html('<option value="">Error cargando ciudades</option>');
                    }
                });
            });

            // ============================================================
            // VALIDACIÓN DE VIGENCIAS CON IA
            // ============================================================
            
            const form = document.getElementById("formRegistroProveedor");
            const btnRegistrar = document.getElementById("btnRegistrar");
            const loadingOverlay = document.getElementById("loading-overlay");
            
            let formularioValidadoPorIA = false;
            
            async function validarDocumentoIA(id, nombre) {
    console.log("==================================");
    console.log("VALIDANDO DOCUMENTO:", id, nombre);

    const input = document.getElementById(id);
    const msg = document.getElementById("msg_" + id);

    console.log("INPUT:", input);
    console.log("MSG:", msg);

    if (!input || !input.files || input.files.length === 0) {
        console.warn("NO HAY ARCHIVO PARA:", id);

        if (msg) {
            msg.className = "text-danger fw-bold";
            msg.textContent = "Debe subir el archivo.";
        }

        return false;
    }

    console.log("ARCHIVO:", input.files[0]);
    console.log("NOMBRE ARCHIVO:", input.files[0].name);
    console.log("TAMAÑO:", input.files[0].size);

    const formData = new FormData();
    formData.append("archivo", input.files[0]);
    formData.append("tipo_documento", nombre);

    console.log("ENVIANDO A validar_vigencia_openai.php");

    const res = await $.ajax({
        url: "../../../../proveedores/vista/validar_vigencia_openai.php",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json"
    });

    console.log("RESPUESTA IA:", res);

    if (res.error) {
        console.error("ERROR IA:", res);

        if (msg) {
            msg.className = "text-danger fw-bold";
            msg.textContent = "Error validando documento con IA.";
        }

        return false;
    }

    // Para RUT, priorizar generation_date sobre otras fechas
    let fecha = "";
    if (id === "rut") {
        fecha = res.generation_date || res.issue_date || res.best_validity_date || "";
        console.log("RUT: Usando generation_date o issue_date:", fecha);
    } else {
        fecha = res.best_validity_date || res.expiration_date || res.valid_until || res.issue_date || "";
    }

    console.log("DOCUMENT TYPE:", res.document_type);
    console.log("GENERATION DATE:", res.generation_date);
    console.log("ISSUE DATE:", res.issue_date);
    console.log("EXPIRATION DATE:", res.expiration_date);
    console.log("VALID UNTIL:", res.valid_until);
    console.log("BEST VALIDITY:", res.best_validity_date);
    console.log("FECHA USADA:", fecha);

    if (!fecha) {
        console.warn("NO SE ENCONTRÓ FECHA:", id);

        if (msg) {
            msg.className = "text-danger fw-bold";
            msg.textContent = "No se encontró fecha de " + (id === "rut" ? "generación" : "vigencia") + ".";
        }

        return false;
    }

    const hoy = new Date();
    const fechaDoc = new Date(fecha);
    const dias = Math.floor((hoy - fechaDoc) / (1000 * 60 * 60 * 24));

    console.log("HOY:", hoy);
    console.log("FECHA DOCUMENTO:", fechaDoc);
    console.log("DÍAS DIFERENCIA:", dias);

    let valido = true;
    let mensaje = "Documento vigente. Fecha: " + fecha;

    if (id === "rut") {
        console.log("APLICANDO REGLA RUT: máximo 1 año desde generación");

        if (dias > 365) {
            valido = false;
            mensaje = "RUT no válido: tiene más de 1 año desde su generación (" + Math.floor(dias) + " días). Fecha: " + fecha;
        } else {
            mensaje = "RUT válido. Generado hace " + Math.floor(dias) + " días. Fecha: " + fecha;
        }
    }

    if (id === "rnt") {
        console.log("APLICANDO REGLA RNT");

        const vencimiento = res.expiration_date || res.valid_until || "";

        console.log("VENCIMIENTO RNT:", vencimiento);

        if (!vencimiento) {
            valido = false;
            mensaje = "RNT no válido: no se encontró fecha de vencimiento.";
        } else if (new Date(vencimiento) < hoy) {
            valido = false;
            mensaje = "RNT vencido. Fecha: " + vencimiento;
        } else {
            mensaje = "RNT vigente hasta " + vencimiento;
        }
    }

    if (id === "certificacion_bancaria") {
        console.log("APLICANDO REGLA CERTIFICACIÓN BANCARIA: máximo 30 días");

        if (dias > 30) {
            valido = false;
            mensaje = "Certificación bancaria no válida: tiene más de 30 días.";
        }
    }

    if (id === "planes_especiales") {
        console.log("APLICANDO REGLA PLANES ESPECIALES");

        const vencimiento = res.expiration_date || res.valid_until || "";

        if (vencimiento && new Date(vencimiento) < hoy) {
            valido = false;
            mensaje = "Planes especiales vencido. Fecha: " + vencimiento;
        } else if (!vencimiento && dias > 365) {
            valido = false;
            mensaje = "Planes especiales no válido: tiene más de 1 año.";
        }
    }

    if (msg) {
        msg.className = valido ? "text-success fw-bold" : "text-danger fw-bold";
        msg.textContent = mensaje;
    }

    console.log("RESULTADO FINAL:", {
        documento: id,
        valido: valido,
        mensaje: mensaje
    });

    return valido;
}

form.addEventListener("submit", async function (e) {
    console.log("==================================");
    console.log("CLICK EN REGISTRAR PROVEEDOR");

    if (formularioValidadoPorIA) {
        console.log("YA VALIDADO POR IA. ENVIANDO FORMULARIO.");
        return true;
    }

    e.preventDefault();

    btnRegistrar.disabled = true;
    btnRegistrar.innerHTML = "Validando vigencias...";

    loadingOverlay.classList.add("active", "ai-mode");
    $(".loading-text").text("Estamos validando la vigencia de sus documentos").addClass("ai-processing");
    $(".loading-subtext").text("Por favor espere un momento");

    // Timeout de seguridad: ocultar overlay después de 90 segundos máximo
    const validationSafetyTimeout = setTimeout(function() {
        loadingOverlay.classList.remove("active", "ai-mode");
        $(".loading-text").text("Estamos validando la vigencia de sus documentos").removeClass("ai-processing");
        $(".loading-subtext").text("Por favor espere un momento");
        btnRegistrar.disabled = false;
        btnRegistrar.innerHTML = "Registrar Proveedor";
        alert("La validación está tardando demasiado. Por favor, intente de nuevo.");
    }, 90000);

    try {
        const rutOk = await validarDocumentoIA("rut", "RUT");
        console.log("RUT OK:", rutOk);

        const rntOk = await validarDocumentoIA("rnt", "RNT");
        console.log("RNT OK:", rntOk);

        const bancariaOk = await validarDocumentoIA("certificacion_bancaria", "Certificación bancaria");
        console.log("BANCARIA OK:", bancariaOk);

        const planesOk = await validarDocumentoIA("planes_especiales", "Planes especiales");
        console.log("PLANES OK:", planesOk);

        if (!rutOk || !rntOk || !bancariaOk || !planesOk) {
            console.warn("NO SE REGISTRA. DOCUMENTOS NO VÁLIDOS.");

            alert("No se puede registrar el proveedor. Hay documentos vencidos o sin vigencia válida.");

            // Cancelar timeout de seguridad
            clearTimeout(validationSafetyTimeout);

            btnRegistrar.disabled = false;
            btnRegistrar.innerHTML = "Registrar Proveedor";

            loadingOverlay.classList.remove("active", "ai-mode");
            $(".loading-text").text("Estamos validando la vigencia de sus documentos").removeClass("ai-processing");
            $(".loading-subtext").text("Por favor espere un momento");

            return false;
        }

        console.log("TODOS LOS DOCUMENTOS ESTÁN VIGENTES.");
        console.log("ENVIANDO FORMULARIO REAL...");

        // Cancelar timeout de seguridad
        clearTimeout(validationSafetyTimeout);

        formularioValidadoPorIA = true;
        btnRegistrar.innerHTML = "Registrando...";

        form.submit();

    } catch (error) {
    console.error("ERROR VALIDANDO VIGENCIAS:", error);
    console.log("STATUS:", error.status);
    console.log("RESPONSE TEXT:", error.responseText);
    console.log("STATUS TEXT:", error.statusText);

    alert("Error validando vigencias. Revisa la consola.");

    clearTimeout(validationSafetyTimeout);

    btnRegistrar.disabled = false;
    btnRegistrar.innerHTML = "Registrar Proveedor";

    loadingOverlay.classList.remove("active", "ai-mode");
    $(".loading-text").text("Estamos validando la vigencia de sus documentos").removeClass("ai-processing");
    $(".loading-subtext").text("Por favor espere un momento");
}
});
    
    // ============================================================
    // LECTOR DE RUT CON IA
    // ============================================================
    
    $("#rut").on("change", function () {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append("rut", file);

        // Mostrar overlay de carga con mensaje de IA
        const loadingOverlay = $("#loading-overlay");
        const loadingText = $(".loading-text");
        const loadingSubtext = $(".loading-subtext");
        
        // Cambiar el texto para IA
        loadingText.text("Analizando RUT con IA").addClass("ai-processing");
        loadingSubtext.text("La inteligencia artificial está extrayendo los datos del documento");
        loadingOverlay.addClass("active ai-mode");

        // Timeout de seguridad: ocultar overlay después de 60 segundos máximo
        const safetyTimeout = setTimeout(function() {
            loadingOverlay.removeClass("active ai-mode");
            loadingText.text("Registrando Proveedor...").removeClass("ai-processing");
            loadingSubtext.text("Por favor espere, esto puede tomar unos momentos");
            alert("La petición está tardando demasiado. Por favor, intente de nuevo.");
        }, 60000);

        $.ajax({
            url: "leer_rut_openai.php",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            timeout: 55000, // Timeout de 55 segundos

            success: function (data) {
                const nitLimpio = (data.nit || "").replace(/[^0-9]/g, "");
                $("#nit_identificacion").val(nitLimpio);
                $("#nombre").val(data.business_name || "");
                $("#email_contabilidad").val(data.accounting_email || "");
                $("#razon_social").val(data.legal_name || "");
                $("#departamento").val(data.department || "");
                $("#telefono_hotel").val(data.phone || "");
                $("#direccion").val(data.address || "");

                if (data.country) {
                    $("#pais option").filter(function () {
                        return $(this).text().toLowerCase() === data.country.toLowerCase();
                    }).prop("selected", true);
                    $("#pais").trigger("change");
                    function normalizarTexto(texto) {
                        return (texto || "")
                            .toString()
                            .normalize("NFD")
                            .replace(/[\u0300-\u036f]/g, "")
                            .replace(/[^a-zA-Z0-9 ]/g, "")
                            .replace(/\s+/g, " ")
                            .trim()
                            .toLowerCase();
                    }

                    if (data.country) {
                        const paisIA = normalizarTexto(data.country);

                        $("#pais option").each(function () {
                            if (normalizarTexto($(this).text()) === paisIA) {
                                $("#pais").val($(this).val()).trigger("change");
                            }
                        });
                    }

                    if (data.city) {
                        const ciudadIA = normalizarTexto(data.city)
                            .replace(" dc", "")
                            .replace(" d c", "");

                        let intentos = 0;

                        const esperarCiudades = setInterval(function () {
                            intentos++;

                            $("#ciudad option").each(function () {
                                const ciudadOption = normalizarTexto($(this).text());

                                if (
                                    ciudadOption === ciudadIA ||
                                    ciudadOption.includes(ciudadIA) ||
                                    ciudadIA.includes(ciudadOption)
                                ) {
                                    $("#ciudad").val($(this).val());
                                    clearInterval(esperarCiudades);
                                }
                            });

                            if (intentos >= 20) {
                                clearInterval(esperarCiudades);
                                console.log("No se encontró ciudad:", data.city);
                            }
                        }, 500);
                    }
                }
                
                // Cancelar timeout de seguridad
                clearTimeout(safetyTimeout);
                
                // Ocultar overlay de carga
                loadingOverlay.removeClass("active ai-mode");
                
                // Restaurar texto original para el envío del formulario
                loadingText.text("Registrando Proveedor...").removeClass("ai-processing");
                loadingSubtext.text("Por favor espere, esto puede tomar unos momentos");
            },
            error: function (xhr) {
                console.log("STATUS:", xhr.status);
                console.log("RESPONSE:", xhr.responseText);
                console.log("XHR COMPLETO:", xhr);

                // Cancelar timeout de seguridad
                clearTimeout(safetyTimeout);

                // Ocultar overlay de carga
                loadingOverlay.removeClass("active ai-mode");
                
                // Restaurar texto original
                loadingText.text("Registrando Proveedor...").removeClass("ai-processing");
                loadingSubtext.text("Por favor espere, esto puede tomar unos momentos");

                alert("Error leyendo el RUT. Mira la consola.");
            }
        });
    });
    
    // Fin del $(document).ready()
});
    </script>
</body>
</html>