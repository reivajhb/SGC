<?php
include "../facturacion/config/seguridad.php";
include('../facturacion/config/conexion.php'); 
require '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    include "../facturacion/config/sidebar3.php";
    include "../facturacion/config/boton_volver.php";
} else {
    include "../facturacion/config/sidebar.php";
    include "../facturacion/config/boton_volver.php";
}

if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] != 1) {
    echo "<script>alert('Acceso denegado.'); window.location.href = 'buscarProveedor.php';</script>";
    exit();
}

$qrImage = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_qr'])) {
    $descripcion = htmlspecialchars(strip_tags($_POST['descripcion']));
    $urlDestino = filter_var($_POST['url'], FILTER_SANITIZE_URL);
    $tipoQr = htmlspecialchars($_POST['tipo_qr']); 

    if (!filter_var($urlDestino, FILTER_VALIDATE_URL)) {
        die("URL no válida.");
    }

    $qrId = time();
    $trackingUrl = "https://sgc.panamericanaviajes.com/facturacion/generadorQR/track.php?qr_id=$qrId";

    $qrCode = QrCode::create($trackingUrl)
        ->setSize(300)
        ->setEncoding(new Encoding('UTF-8'))
        ->setErrorCorrectionLevel(ErrorCorrectionLevel::High);

    $writer = new PngWriter();
    $result = $writer->write($qrCode);

    $qrFolder = 'qr_codes/';
    if (!is_dir($qrFolder)) {
        mkdir($qrFolder, 0777, true);
    }

    $filename = $qrFolder . $qrId . '.png';
    file_put_contents($filename, $result->getString());
    $qrImage = $filename;

    try {
        if (!isset($conn)) {
            throw new Exception("Error: Conexión a la base de datos no disponible.");
        }
        $stmt = $conn->prepare("INSERT INTO tbl_campañas (qr_id, descripcion, url_destino, url_tracking, tipo_qr) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$qrId, $descripcion, $urlDestino, $trackingUrl, $tipoQr]);
    } catch (PDOException $e) {
        die("Error al guardar en la base de datos: " . $e->getMessage());
    } catch (Exception $e) {
        die($e->getMessage());
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="img/favicon.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../estilos/estilos.css">
    <title>Generador de QR</title>

    <style>
        .modulo-qr {
            margin-top: 3rem;
            margin-bottom: 3rem;
            font-family: 'Inter', sans-serif;
        }

        .modulo-qr .custom-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
        }

        .modulo-qr .custom-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
            padding: 2.5rem 1rem;
            border-bottom: none;
            color: white;
            text-align: center;
        }

        .modulo-qr .custom-header h4 {
            font-weight: 700;
            letter-spacing: -0.5px;
            margin: 0;
        }

        .modulo-qr .custom-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .modulo-qr .custom-input {
            padding: 0.8rem 1rem;
            border-radius: 12px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .modulo-qr .custom-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }

        /* Efecto de desvanecido en el botón solicitado */
        .modulo-qr .btn-generate {
            padding: 1rem;
            font-weight: 700;
            border-radius: 12px;
            border: none;
            background-color: #198754;
            color: white;
            transition: all 0.4s ease;
        }

        .modulo-qr .btn-generate:hover {
            /* Efecto fade en ambos lados con el color solicitado */
            background: linear-gradient(
                to right, 
                transparent 0%, 
                rgba(79, 70, 229, 0.8) 15%, 
                #818cf8 50%, 
                rgba(79, 70, 229, 0.8) 85%, 
                transparent 100%
            ) !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(129, 140, 248, 0.3);
        }

        .modulo-qr .qr-result-container {
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            border: 1px dashed #dee2e6;
        }

        .modulo-qr .qr-image {
            max-width: 250px;
            height: auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            background: white;
            padding: 10px;
        }
    </style>
</head>

<body>
<div class="modulo-qr container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="custom-card card">
                <div class="custom-header card-header">
                    <h4>Generador de QR con Tracking</h4>
                    <p class="mb-0 opacity-75 small">Crea códigos dinámicos para tus campañas</p>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <form method="post">
                        <div class="mb-4">
                            <label class="custom-label">Descripción del QR*</label>
                            <input type="text" name="descripcion" class="custom-input form-control" 
                                   placeholder="Ej: Campaña Verano 2026" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="custom-label">URL de Redirección*</label>
                            <input type="url" name="url" class="custom-input form-control" 
                                   placeholder="https://ejemplo.com" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="tipo_qr" class="custom-label">Tipo de QR*</label>
                            <select name="tipo_qr" id="tipo_qr" class="custom-input form-select" required>
                                <option value="" disabled selected>Seleccione una opción</option>
                                <option value="CONDUCTOR">CONDUCTOR</option>
                                <option value="CAMPAÑA">CAMPAÑA</option>
                                <option value="HOTEL">HOTEL</option>
                                <option value="TOOLKIT">TOOLKIT</option>
                                <option value="GUIAS">GUÍAS</option>
                                <option value="COMENTARIOS">COMENTARIOS</option>
                            </select>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="generate_qr" class="btn-generate btn">
                                GENERAR CÓDIGO QR
                            </button>
                        </div>
                    </form>

                    <?php if ($qrImage): ?>
                        <div class="qr-result-container text-center">
                            <h5 class="custom-label mb-3">¡Código QR Generado Exitosamente!</h5>
                            <img src="<?= $qrImage ?>" class="qr-image img-fluid border rounded" alt="Código QR">
                            <div class="mt-4">
                                <a href="<?= $qrImage ?>" download="QR_<?= time() ?>.png" class="btn btn-primary px-4 py-2 shadow-sm" style="border-radius: 10px; font-weight: 600;">
                                    Descargar Imagen PNG
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>