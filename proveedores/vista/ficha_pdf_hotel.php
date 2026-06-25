<?php
include_once '../../facturacion/config/seguridad.php';
include_once '../../facturacion/config/conexion.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$idRol        = (int) ($_SESSION['id_rol']    ?? 0);
$nitSession   = $_SESSION['usuario']           ?? null;
$isProveedor  = (bool) ($_SESSION['PROV_AUTH'] ?? false);

$rolesPermitidos = [1, 2, 6, 5, 7, 8, 9, 10];
if (!in_array($idRol, $rolesPermitidos, true) && !$isProveedor) {
    http_response_code(403);
    exit('Acceso denegado.');
}

$id_hotel = isset($_GET['id']) && ctype_digit((string)$_GET['id']) ? (int)$_GET['id'] : null;
if (!$id_hotel) die('ID de hotel no válido.');

// ── Datos generales ──────────────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT * FROM tbl_alojamiento_general WHERE id_hotel = ?");
$stmt->bind_param('i', $id_hotel);
$stmt->execute();
$h = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$h) die('Hotel no encontrado.');

// ── Fotos promocionales ──────────────────────────────────────────────────────
$tipos_promo = ['Foto Fachada','Foto Habitaciones','Foto Piscina','Foto Zona Comun','Foto Promocional'];
$placeholders = implode(',', array_fill(0, count($tipos_promo), '?'));
$stmt2 = $conn->prepare(
    "SELECT tipo_documento, ruta_almacenamiento, nombre_archivo
     FROM tbl_alojamiento_documentos
     WHERE id_hotel = ? AND tipo_documento IN ($placeholders)
     ORDER BY FIELD(tipo_documento,'Foto Fachada','Foto Habitaciones','Foto Piscina','Foto Zona Comun','Foto Promocional')
     LIMIT 4"
);
$bind_types = 'i' . str_repeat('s', count($tipos_promo));
$bind_values = array_merge([$id_hotel], $tipos_promo);
$stmt2->bind_param($bind_types, ...$bind_values);
$stmt2->execute();
$res2 = $stmt2->get_result();
$fotos = [];
while ($r = $res2->fetch_assoc()) $fotos[] = $r;
$stmt2->close();


function driveThumb($url) {
    if (!$url) return null;
    if (preg_match('~/file/d/([a-zA-Z0-9_-]+)~', $url, $m)) $id = $m[1];
    elseif (preg_match('~[?&]id=([a-zA-Z0-9_-]+)~', $url, $m)) $id = $m[1];
    else return $url;
    return "https://drive.google.com/thumbnail?id={$id}&sz=w800";
}

function labelCategoria($v) {
    $map = ['3-star'=>'3 Estrellas','4-star'=>'4 Estrellas','5-star'=>'5 Estrellas',
            'boutique'=>'Boutique','glamping'=>'Glamping'];
    return $map[$v] ?? ucfirst($v ?? '—');
}

function labelDesayuno($v) {
    $map = ['a_la_carta'=>'A la Carta','americano'=>'Americano','buffet'=>'Buffet','continental'=>'Continental','0'=>'No incluido'];
    return $map[(string)$v] ?? '—';
}

function labelTipoHotel($json) {
    if (!$json) return '—';
    $arr = json_decode($json, true);
    if (!is_array($arr) || empty($arr)) return '—';
    return implode(', ', array_map('ucfirst', $arr));
}

$etiquetasFoto = [
    'Foto Fachada'      => 'Fachada',
    'Foto Habitaciones' => 'Habitaciones',
    'Foto Piscina'      => 'Piscina / Zona recreativa',
    'Foto Zona Comun'   => 'Zona común',
    'Foto Promocional'  => 'Foto',
];


$logoPath = realpath(dirname(__DIR__, 2) . '/img/pnv.png');
$logoBase64 = $logoPath ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ficha Hotel — <?php echo htmlspecialchars($h['nombre'] ?? ''); ?></title>
  <link rel="icon" type="image/x-icon" href="/facturacion/img/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* ─── Reset & base ─────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --azul:    #1a3a6b;
      --azul2:   #2C56E6;
      --dorado:  #c9a84c;
      --gris:    #f4f6f9;
      --texto:   #222;
      --suave:   #5a6a7e;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: var(--gris);
      color: var(--texto);
      font-size: 13px;
    }

    /* ─── Barra de acciones (no imprime) ───────────────────── */
    .toolbar {
      background: var(--azul);
      color: #fff;
      padding: 10px 24px;
      display: flex;
      align-items: center;
      gap: 12px;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .toolbar h1 { font-size: 1rem; font-weight: 600; flex: 1; }
    .btn-pdf {
      background: var(--dorado);
      color: #fff;
      border: none;
      padding: 8px 22px;
      border-radius: 6px;
      font-family: 'Poppins', sans-serif;
      font-size: .9rem;
      font-weight: 600;
      cursor: pointer;
      transition: opacity .2s;
    }
    .btn-pdf:hover { opacity: .85; }
    .btn-back {
      background: rgba(255,255,255,.15);
      color: #fff;
      border: 1px solid rgba(255,255,255,.4);
      padding: 7px 18px;
      border-radius: 6px;
      font-family: 'Poppins', sans-serif;
      font-size: .85rem;
      cursor: pointer;
      text-decoration: none;
    }

    /* ─── Contenedor del documento ─────────────────────────── */
    .page {
      width: 210mm;
      min-height: 297mm;
      margin: 24px auto;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 32px rgba(0,0,0,.12);
      overflow: hidden;
    }

    /* ─── CABECERA ─────────────────────────────────────────── */
    .page-header {
      background: linear-gradient(135deg, var(--azul) 0%, var(--azul2) 100%);
      padding: 28px 32px 22px;
      display: flex;
      align-items: center;
      gap: 20px;
    }
    .page-header .logo-wrap {
      background: #fff;
      border-radius: 10px;
      padding: 6px 10px;
      flex-shrink: 0;
    }
    .page-header .logo-wrap img { height: 52px; display: block; }
    .page-header .header-text { flex: 1; color: #fff; }
    .page-header .header-text h2 {
      font-size: 1.4rem;
      font-weight: 700;
      letter-spacing: .5px;
      line-height: 1.2;
    }
    .page-header .header-text p {
      font-size: .8rem;
      opacity: .82;
      margin-top: 4px;
    }
    .page-header .header-badge {
      background: var(--dorado);
      color: #fff;
      border-radius: 20px;
      padding: 4px 14px;
      font-size: .75rem;
      font-weight: 600;
      white-space: nowrap;
      align-self: flex-start;
    }

    /* ─── Banda dorada divisora ────────────────────────────── */
    .divider-gold { height: 4px; background: linear-gradient(90deg, var(--dorado), #e8c87a, var(--dorado)); }

    /* ─── Cuerpo ───────────────────────────────────────────── */
    .page-body { padding: 28px 32px; }

    /* ─── Sección título ───────────────────────────────────── */
    .section-title {
      font-size: .78rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      color: var(--azul2);
      border-bottom: 2px solid var(--azul2);
      padding-bottom: 4px;
      margin-bottom: 14px;
    }

    /* ─── Grid de datos ────────────────────────────────────── */
    .info-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px 24px;
      margin-bottom: 22px;
    }
    .info-item { display: flex; flex-direction: column; gap: 1px; }
    .info-label {
      font-size: .7rem;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: var(--suave);
      font-weight: 600;
    }
    .info-value {
      font-size: .88rem;
      font-weight: 500;
      color: var(--texto);
    }
    .info-value.highlight {
      color: var(--azul2);
      font-weight: 600;
    }

    .descripcion-box {
      background: var(--gris);
      border-left: 3px solid var(--dorado);
      border-radius: 0 6px 6px 0;
      padding: 12px 16px;
      font-size: .85rem;
      line-height: 1.6;
      color: var(--texto);
      margin-bottom: 24px;
    }

    .fotos-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
      margin-bottom: 24px;
    }
    .foto-card {
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid #e0e4ea;
      box-shadow: 0 2px 8px rgba(0,0,0,.07);
      position: relative;
    }
    .foto-card img {
      width: 100%;
      height: 140px;
      object-fit: cover;
      display: block;
    }
    .foto-card .foto-label {
      position: absolute;
      bottom: 0; left: 0; right: 0;
      background: linear-gradient(0deg, rgba(26,58,107,.85) 0%, transparent 100%);
      color: #fff;
      font-size: .72rem;
      font-weight: 600;
      padding: 10px 10px 7px;
      letter-spacing: .4px;
    }
    .foto-placeholder {
      width: 100%;
      height: 140px;
      background: var(--gris);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--suave);
      font-size: .8rem;
    }

    .page-footer {
      background: var(--azul);
      color: rgba(255,255,255,.75);
      text-align: center;
      padding: 12px 32px;
      font-size: .72rem;
    }
    .page-footer strong { color: #fff; }

    @media print {
      * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
      }

      body { background: #fff; }
      .toolbar { display: none !important; }
      .page {
        width: 100%;
        margin: 0;
        box-shadow: none;
        border-radius: 0;
      }

      .page-header {
        background: linear-gradient(135deg, #1a3a6b 0%, #2C56E6 100%) !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      .page-header .header-text,
      .page-header .header-text h2,
      .page-header .header-text p { color: #fff !important; }

      .page-header .header-badge {
        background: #c9a84c !important;
        color: #fff !important;
      }

      .page-header .logo-wrap { background: #fff !important; }

      .divider-gold {
        background: linear-gradient(90deg, #c9a84c, #e8c87a, #c9a84c) !important;
        height: 4px !important;
      }

      .section-title { color: #2C56E6 !important; border-color: #2C56E6 !important; }
      .info-value.highlight { color: #2C56E6 !important; }

      .descripcion-box {
        background: #f4f6f9 !important;
        border-left-color: #c9a84c !important;
      }

      .page-footer {
        background: #1a3a6b !important;
        color: rgba(255,255,255,.75) !important;
      }
      .page-footer strong { color: #fff !important; }

      .foto-card .foto-label {
        background: linear-gradient(0deg, rgba(26,58,107,.85) 0%, transparent 100%) !important;
        color: #fff !important;
      }

      .fotos-grid { page-break-inside: avoid; }
      .foto-card img { height: 130px; }
    }
  </style>
</head>
<body>
<div class="toolbar">
  <h1>Ficha Promocional — <?php echo htmlspecialchars($h['nombre'] ?? ''); ?></h1>
  <button class="btn-pdf" onclick="window.print()">⬇ Descargar / Imprimir PDF</button>
</div>
<div class="page">  
  <div class="page-header">
    <?php if ($logoBase64): ?>
    <div class="logo-wrap">
      <img src="<?php echo $logoBase64; ?>" alt="Panamericana de Viajes">
    </div>
    <?php endif; ?>
    <div class="header-text">
      <h2><?php echo htmlspecialchars($h['nombre'] ?? ''); ?></h2>
      <p>
        <?php echo htmlspecialchars(trim(($h['ciudad'] ?? '') . ', ' . ($h['pais'] ?? ''), ', ')); ?>
        <?php if (!empty($h['direccion'])): ?> &nbsp;·&nbsp; <?php echo htmlspecialchars($h['direccion']); ?><?php endif; ?>
      </p>
    </div>
    <?php if (!empty($h['categoria'])): ?>
    <div class="header-badge"><?php echo labelCategoria($h['categoria']); ?></div>
    <?php endif; ?>
  </div>

  <div class="divider-gold"></div>

  
  <div class="page-body">
    <div class="section-title">Información del hotel</div>
    <div class="info-grid">
      <div class="info-item">
        <span class="info-label">Dirección</span>
        <span class="info-value"><?php echo htmlspecialchars($h['direccion'] ?? '—'); ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Ciudad / País</span>
        <span class="info-value highlight">
          <?php echo htmlspecialchars(($h['ciudad'] ?? '') . ', ' . ($h['pais'] ?? '')); ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Categoría</span>
        <span class="info-value"><?php echo labelCategoria($h['categoria'] ?? ''); ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Tipo de hotel</span>
        <span class="info-value"><?php echo htmlspecialchars(labelTipoHotel($h['tipo_hotel_json'] ?? '')); ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Total de habitaciones</span>
        <span class="info-value highlight"><?php echo htmlspecialchars($h['numero_habitaciones'] ?? '—'); ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Tipo de desayuno</span>
        <span class="info-value"><?php echo labelDesayuno($h['tipo_desayuno'] ?? ''); ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Check-in</span>
        <span class="info-value"><?php echo htmlspecialchars($h['hora_check_in'] ?? '—'); ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Check-out</span>
        <span class="info-value"><?php echo htmlspecialchars($h['hora_check_out'] ?? '—'); ?></span>
      </div>
    </div>

    
    <?php if (!empty($h['descripcion_producto'])): ?>
    <div class="section-title">Descripción</div>
    <div class="descripcion-box">
      <?php echo nl2br(htmlspecialchars($h['descripcion_producto'])); ?>
    </div>
    <?php endif; ?>

    
    <div class="section-title">Fotos promocionales</div>
    <?php if (!empty($fotos)): ?>
    <div class="fotos-grid">
      <?php foreach ($fotos as $foto):
        $src = driveThumb($foto['ruta_almacenamiento']);
      ?>
      <div class="foto-card">
        <?php if ($src): ?>
          <img src="<?php echo htmlspecialchars($src); ?>"
               alt="<?php echo htmlspecialchars($label); ?>"
               onerror="this.parentElement.innerHTML='<div class=\'foto-placeholder\'>Imagen no disponible</div>'">
        <?php else: ?>
          <div class="foto-placeholder">Sin imagen</div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p style="color:var(--suave);font-size:.85rem;margin-bottom:20px;">No hay fotos promocionales cargadas.</p>
    <?php endif; ?>

  </div><!-- /page-body -->

  <!-- PIE -->
  <div class="page-footer">
    <strong>Panamericana de Viajes &amp; Turismo</strong> &nbsp;·&nbsp;
    Ficha generada el <?php echo date('d/m/Y'); ?> &nbsp;·&nbsp;
    Documento de uso interno
  </div>

</div><!-- /page -->

</body>
</html>
