<style>
    body {
        font-family: 'Arial', sans-serif;
        background: #f8fafc;
        margin: 0;
        padding: 10px;
    }
    h2 {
        text-align: center;
        color: #1e293b;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        letter-spacing: -0.5px;
    }
    .equipment-grid {
        width: 100%;
        border-collapse: separate;
        border-spacing: 5px;
    }
    .equipment-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        width: 31%;
        vertical-align: top;
    }
    .equipment-title {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 14px;
    }
    .equipment-title::before {
        content: "💻 ";
        font-size: 13px;
    }
    .info-row {
        margin-bottom: 10px;
        font-size: 10px;
        line-height: 1.5;
    }
    .info-label {
        color: #64748b;
        font-weight: 400;
    }
    .info-value {
        color: #1e293b;
        font-weight: 500;
        float: right;
        text-align: right;
        max-width: 60%;
        word-wrap: break-word;
    }
    .info-value.primary {
        color: #2563eb;
        font-weight: 600;
    }
</style>

<h2>Listado de Equipos - Área: <?php echo htmlspecialchars($_GET['filter_area'] ?? 'Todos'); ?></h2>

<table class="equipment-grid">
<?php $i = 0; foreach ($data as $item): ?>
    <?php if ($i % 3 == 0): ?><tr><?php endif; ?>
    <td class="equipment-card">
        <div class="equipment-title">
            <?php echo htmlspecialchars($item['nombre_equipo']); ?>
        </div>
        
        <div class="info-row">
            <span class="info-label">Sistema:</span>
            <span class="info-value primary"><?php echo htmlspecialchars($item['sistema_operativo_nombre']); ?></span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Windows Serial:</span>
            <span class="info-value"><?php echo htmlspecialchars($item['serial_windows']); ?></span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Office:</span>
            <span class="info-value"><?php echo htmlspecialchars($item['version_office']); ?></span>
        </div>
    </td>
    <?php if ($i % 3 == 2): ?></tr><?php endif; ?>
<?php $i++; endforeach; ?>
<?php if ($i % 3 != 0): ?></tr><?php endif; ?>
</table>