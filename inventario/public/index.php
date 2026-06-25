<?php
// public/index.php

require_once __DIR__ . '/seguridad.php';

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/InventoryController.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Crear conexión
$database = new Database();
$conn = $database->getConnection();

// Crear controlador
$controller = new InventoryController($conn);

// Acción solicitada
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'consultar':
        $controller->showInventory();
        break;

    case 'insertar':
        $controller->insert();
        break;

    case 'edit':
        $controller->edit();
        break;

    case 'update':
        $controller->update();
        break;

    case 'delete':
        $controller->delete();
        break;

    case 'downloadExcel':
        $controller->downloadExcel();
        break;

    case 'showAll':
        $controller->showAll();
        break;

    case 'filter_area':
        $controller->filter_area();
        break;

    case 'print_pdf':
        $controller->print_pdf();
        break;

    case 'chat':
        require_once __DIR__ . '/../app/views/inventory/chat.php';
        break;

    case 'chat_session':
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'ok' => true,
                'message' => 'Endpoint activo. Usa POST para crear sesión.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $openAiConfig = require __DIR__ . '/../../aws.php';
        $apiKey = trim((string) ($openAiConfig['api_key_openai_inventario'] ?? ''));

        if (!$apiKey) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Falta api_key_openai_inventario en aws.php.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $userIdentifier = !empty($_SESSION['correo'])
            ? $_SESSION['correo']
            : 'inventario_' . session_id();

        $payload = [
            'user' => $userIdentifier,
            'workflow' => [
                'id' => 'wf_69bac7d94770819085f669a93ebcca950ea42cfb55cb7a84'
            ],
            'chatkit_configuration' => [
                'file_upload' => [
                    'enabled' => true,
                    'max_files' => 10,
                    'max_file_size' => 25
                ]
            ],
            'expires_after' => [
                'anchor' => 'created_at',
                'seconds' => 600
            ]
        ];

        $ch = curl_init('https://api.openai.com/v1/chatkit/sessions');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'OpenAI-Beta: chatkit_beta=v1'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            http_response_code(500);
            echo json_encode([
                'error' => 'Error cURL: ' . $error
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300) {
            http_response_code($httpCode);
            echo json_encode([
                'error' => $decoded['error']['message'] ?? 'No se pudo crear la sesión de ChatKit.',
                'details' => $decoded
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!isset($decoded['client_secret'])) {
            http_response_code(500);
            echo json_encode([
                'error' => 'La API respondió, pero no devolvió client_secret.',
                'details' => $decoded
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'client_secret' => $decoded['client_secret']
        ], JSON_UNESCAPED_UNICODE);
        exit;

    default:
        $controller->showInventory();
        break;
}

ob_end_flush();
?>