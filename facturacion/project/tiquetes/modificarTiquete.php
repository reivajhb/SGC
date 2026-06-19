<?php
include "../../config/seguridad.php";
include "../../config/conexion.php";

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Selección de Sidebar
if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    include "../../config/sidebar3.php";
} else {
    include "../../config/sidebar.php";
}
include "../../config/boton_volver.php";

// Consulta de datos (Optimizado)
$id_get = mysqli_real_escape_string($conn, $_GET['id_tiquete_resum']);
$sentencia = "SELECT * FROM tbl_resum_tiquetes WHERE id_tiquete_resum = '$id_get'";
$ejecutar = mysqli_query($conn, $sentencia);
$f = mysqli_fetch_assoc($ejecutar);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <title>Modificar Tiquete | SGC</title>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; color: #333; }
        
        .main-container { padding: 40px 0; }

        /* Título Estilo Corporativo */
        .form-header {
            background-color: #1a3a5c;
            color: white;
            padding: 25px;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .section-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 30px;
            margin-bottom: 25px;
            border: none;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a3a5c;
            margin-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        label { font-weight: 600; font-size: 0.85rem; color: #495057; margin-bottom: 5px; }

        .form-control {
            border-radius: 8px;
            padding: 10px 12px;
            border: 1px solid #dce1e5;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #1a3a5c;
            box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1);
        }

        .form-control[readonly] { background-color: #f8f9fa; }

        /* Estilo para tablas de adicionales y rutas */
        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #eee;
            font-size: 0.85rem;
        }
        .table-custom th { background: #343a40; color: white; padding: 10px; }
        .table-custom td { padding: 10px; border-bottom: 1px solid #eee; }

        .btn-submit {
            background-color: #28a745;
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            color: white;
            width: 100%;
        }
        .btn-submit:hover { background-color: #218838; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3); }

        .btn-crm { background-color: #1a3a5c; color: white; border-radius: 8px; font-weight: 600; }
    </style>
</head>

<body>
    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                
                <div class="form-header text-center">
                    <h2 class="mb-0 h4">MODIFICAR TIQUETE: <span class="fw-light"><?php echo $f['num_tiquete']; ?></span></h2>
                </div>

                <form action="editartiquetes.php" method="post" enctype="multipart/form-data">
                    <input name="id_tiquete_resum" type="hidden" value="<?php echo $f['id_tiquete_resum']; ?>">

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-info-circle"></i> Información General</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label>Tipo de Trato</label>
                                <input name="tipo_trato" type="text" class="form-control" value="<?php echo $f['tipo_trato']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Vendedor</label>
                                <input name="vendedor" type="text" class="form-control" value="<?php echo $f['vendedor']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Valor (Importe)</label>
                                <input name="importe" type="text" class="form-control" value="<?php echo $f['importe']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Fecha de Cierre</label>
                                <input name="fecha_cierre" type="date" class="form-control" value="<?php echo $f['fecha_cierre']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Tipo de Moneda</label>
                                <input name="tipo_moneda" type="text" class="form-control" value="<?php echo $f['tipo_moneda']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Tipo de Pago</label>
                                <input name="tipo_pago" type="text" class="form-control" value="<?php echo $f['tipo_pago']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-plane"></i> Detalles del Servicio</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Nombre del Trato</label>
                                <input name="nombre_trato" type="text" class="form-control" value="<?php echo $f['nombre_trato']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Nombre Agencia / Cliente</label>
                                <input name="nom_agen_cli" type="text" class="form-control" value="<?php echo $f['nom_agen_cli']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Localizador</label>
                                <input name="localizador" type="text" class="form-control" value="<?php echo $f['localizador']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Aerolínea</label>
                                <input name="proveedor" type="text" class="form-control" value="<?php echo $f['proveedor']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>No. Tiquete</label>
                                <input name="num_tiquete" type="text" class="form-control" value="<?php echo $f['num_tiquete']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Fecha de Salida</label>
                                <input name="fecha_salida" type="date" class="form-control" value="<?php echo $f['fecha_salida']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>No. de Pasajeros</label>
                                <input name="num_pasajeros" type="number" class="form-control" value="<?php echo $f['num_pasajeros']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Centro de Costo</label>
                                <input name="Centro_costo" type="text" class="form-control" value="<?php echo $f['Centro_costo']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-sync"></i> Estado CRM</div>
                        <div class="row align-items-end g-3">
                            <div class="col-md-6">
                                <label>Estado Actual</label>
                                <input type="text" class="form-control" id="estadoEnvioText" readonly>
                                <input type="hidden" name="estado_envio" id="estadoEnvio" value="<?php echo $f['enviado']; ?>">
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-crm w-100 p-2" onclick="enviarACRM()">
                                    <i class="fas fa-paper-plane me-2"></i> Resetear para Re-enviar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="section-card bg-light">
                        <div class="section-title text-dark"><i class="fas fa-plus-square"></i> Productos Adicionales</div>
                        <div class="table-responsive">
                            <?php
                            $cadena = $f['emd'];
                            $partes_cadena = explode(',', $cadena);
                            if(!empty($cadena)) {
                                $partes_variables = [];
                                foreach ($partes_cadena as $dato) {
                                    $partes_variables[] = array_filter(explode(';', $dato));
                                }
                                $max_length = !empty($partes_variables) ? max(array_map('count', $partes_variables)) : 0;

                                echo "<table class='table table-custom bg-white'>";
                                echo "<thead><tr>";
                                for ($i = 1; $i <= count($partes_cadena); $i++) { echo "<th>Adicional $i</th>"; }
                                echo "</tr></thead><tbody>";

                                for ($j = 0; $j < $max_length; $j++) {
                                    echo "<tr>";
                                    for ($i = 0; $i < count($partes_cadena); $i++) {
                                        echo "<td>" . ($partes_variables[$i][$j] ?? '') . "</td>";
                                    }
                                    echo "</tr>";
                                }
                                echo "</tbody></table>";
                            } else { echo "<p class='text-muted small'>No hay productos adicionales registrados.</p>"; }
                            ?>
                        </div>
                    </div>

                    <div class="section-card bg-light">
                        <div class="section-title text-dark"><i class="fas fa-route"></i> Itinerario de Ruta</div>
                        <div class="table-responsive">
                            <?php 
                            $rutas = explode(',', $f['ruta']);
                            foreach ($rutas as $vuelo) {
                                $segmentos = explode(';', $vuelo);
                                echo "<table class='table table-custom bg-white mb-2'><tr>";
                                foreach ($segmentos as $segmento) { echo "<td>$segmento</td>"; }
                                echo "</tr></table>";
                            }
                            ?>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit mt-4 shadow">
                        Actualizar Información del Tiquete
                    </button>

                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            actualizarEstadoVisual();
        });

        function actualizarEstadoVisual() {
            var val = document.getElementById('estadoEnvio').value;
            var text = document.getElementById('estadoEnvioText');
            text.value = (val == 1) ? "ENVIADO" : "PENDIENTE / NO ENVIADO";
            text.classList.remove('text-success', 'text-danger');
            text.classList.add(val == 1 ? 'text-success' : 'text-danger');
            text.style.fontWeight = "700";
        }

        function enviarACRM() {
            if(confirm("¿Desea marcar este tiquete para que sea procesado nuevamente por el CRM?")) {
                document.getElementById('estadoEnvio').value = 0;
                actualizarEstadoVisual();
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>