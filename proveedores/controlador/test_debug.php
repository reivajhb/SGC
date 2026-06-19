<?php
// ========================================
// Test básico para verificar ejecución
// ========================================
echo "Content-Type: text/plain; charset=utf-8\n\n";
echo "========================================\n";
echo "TEST DE CONFIGURACIÓN PHP\n";
echo "========================================\n\n";

echo "Fecha actual: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n";
echo "Error Reporting: " . error_reporting() . "\n\n";

// Test de sesión
session_start();
echo "Sesión iniciada: " . (session_status() === PHP_SESSION_ACTIVE ? 'SÍ' : 'NO') . "\n";
echo "Usuario sesión: " . ($_SESSION['usuario'] ?? 'NO DEFINIDO') . "\n";
echo "Rol sesión: " . ($_SESSION['id_rol'] ?? 'NO DEFINIDO') . "\n\n";

// Test de GET
echo "Parámetros GET:\n";
print_r($_GET);
echo "\n";

// Test de conexión a BD
echo "========================================\n";
echo "TEST DE CONEXIÓN A BASE DE DATOS\n";
echo "========================================\n";

try {
    include "../../facturacion/config/conexion.php";
    
    if (isset($conn) && !$conn->connect_error) {
        echo "✅ Conexión a BD exitosa\n";
        echo "Base de datos: " . $conn->server_info . "\n";
        
        // Test query simple
        $result = $conn->query("SELECT COUNT(*) as total FROM tbl_alojamiento_general");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "Total hoteles en BD: " . $row['total'] . "\n";
        }
    } else {
        echo "❌ Error de conexión: " . ($conn->connect_error ?? 'N/A') . "\n";
    }
} catch (Exception $e) {
    echo "❌ Excepción: " . $e->getMessage() . "\n";
}

echo "\n========================================\n";
echo "FIN DEL TEST\n";
echo "========================================\n";
