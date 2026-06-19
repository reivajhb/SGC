<?php
require_once __DIR__ . '/../app/controllers/InventoryController.php';
require_once __DIR__ . '/../config/database.php';

$conn = new Database();
$controller = new InventoryController($conn);
$controller->update(); // Llama al método de actualización
?>
