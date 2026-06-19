<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/facturacion/facturacion/config/sidebar3.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inventario</title>

    <!-- Fuente Inter: La más usada en diseño minimalista moderno -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

    <style>
        :root {
            --bg-body: #f8fafc;
            --primary-color: #2563eb;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
        }

        h1 {
            font-weight: 600;
            letter-spacing: -0.025em;
            color: var(--text-main);
        }

        /* Estilo del Formulario de Filtros */
        .filter-container {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .form-select, .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.6rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-select:focus, .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Botones Minimalistas */
        .btn {
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-success {
            background-color: #10b981;
            border: none;
        }

        /* Tarjetas de Equipos */
        .equipment-card {
            background: #ffffff;
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .equipment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        .equipment-card .card-body {
            padding: 1.5rem;
        }

        .equipment-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .equipment-title i {
            color: var(--primary-color);
            margin-right: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }

        .info-label {
            color: var(--text-muted);
            font-weight: 400;
        }

        .info-value {
            color: var(--text-main);
            font-weight: 500;
            text-align: right;
        }

        .empty-state {
            text-align: center;
            padding: 4rem;
            color: var(--text-muted);
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Listado de Equipos</h1>
        </div>

        <!-- Contenedor de Filtros -->
        <div class="filter-container">
            <form method="GET" action="/facturacion/inventario/public/index.php">
                <input type="hidden" name="action" value="filter_area">
                <div class="row align-items-end g-3">
                    <div class="col-md-5">
                        <label for="filter_area" class="form-label text-uppercase">Filtrar por Área</label>
                        <select name="filter_area" id="filter_area" class="form-select">
                            <option value="">Todas las áreas</option>
                            <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
                            <option value="TURIVEL">TURIVEL</option>
                            <option value="CONTABILIDAD">CONTABILIDAD</option>
                            <option value="AGENCIAS">AGENCIAS</option>
                            <option value="RECEPTIVO">RECEPTIVO</option>
                            <option value="SISTEMAS">SISTEMAS</option>
                        </select>
                    </div>
                    <div class="col-md-7 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-filter me-2"></i>Filtrar
                        </button>
                        <a href="/facturacion/inventario/public/index.php?action=print_pdf&filter_area=<?php echo urlencode($_GET['filter_area'] ?? ''); ?>"
                            class="btn btn-success px-4">
                            <i class="fas fa-file-pdf me-2"></i>Descargar PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <?php if (!empty($data)): ?>
            <div class="row g-4 mt-2">
                <?php foreach ($data as $item): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card equipment-card">
                            <div class="card-body">
                                <div class="equipment-title">
                                    <i class="fas fa-laptop"></i>
                                    <?php echo htmlspecialchars($item['nombre_equipo']); ?>
                                </div>
                                
                                <div class="info-row border-bottom pb-2">
                                    <span class="info-label">Sistema:</span>
                                    <span class="info-value text-primary"><?php echo htmlspecialchars($item['sistema_operativo_nombre']); ?></span>
                                </div>
                                
                                <div class="info-row pt-2">
                                    <span class="info-label">Windows Serial:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($item['serial_windows']); ?></span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Office:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($item['version_office']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                <p class="fs-5">No se encontraron equipos en esta área.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
</body>

</html>