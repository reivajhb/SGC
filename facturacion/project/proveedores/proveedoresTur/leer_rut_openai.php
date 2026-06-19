<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$openAiConfig = require __DIR__ . '/../../../../aws.php';
$apiKey = trim((string) ($openAiConfig['api_key_openai'] ?? ''));

if (!isset($_FILES['rut'])) {
    echo json_encode(["error" => "No se recibio archivo RUT"]);
    exit;
}

if ($apiKey === '') {
    echo json_encode(["error" => "Falta api_key_openai en aws.php"]);
    exit;
}

$tmpPath = $_FILES['rut']['tmp_name'];
$fileName = $_FILES['rut']['name'];
$fileType = $_FILES['rut']['type'] ?: 'application/pdf';

$ch = curl_init("https://api.openai.com/v1/files");

$postFields = [
    "purpose" => "assistants",
    "file" => new CURLFile($tmpPath, $fileType, $fileName)
];

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $apiKey
    ],
    CURLOPT_POSTFIELDS => $postFields
]);

$fileResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($fileResponse === false) {
    echo json_encode([
        "error" => "Error CURL subiendo archivo",
        "curl_error" => $curlError
    ]);
    exit;
}

$fileData = json_decode($fileResponse, true);

if (!isset($fileData["id"])) {
    echo json_encode([
        "error" => "No se pudo subir el PDF a OpenAI",
        "http_code" => $httpCode,
        "raw_response" => $fileResponse,
        "detalle" => $fileData
    ]);
    exit;
}

$fileId = $fileData["id"];

$prompt = <<<PROMPT
Read this Colombian RUT PDF and return ONLY valid JSON.

Extract:
{
  "document_type": "",
  "nit": "",
  "business_name": "",
  "legal_name": "",
  "accounting_email": "",
  "country": "",
  "department": "",
  "city": "",
  "phone": "",
  "address": "",
  "issue_date": "",
  "valid_until": "",
  "expiration_date": "",
  "status": "",
  "observations": ""
}

Rules:
- Do not invent data.
- If a value is not found, return "".
- Dates must be YYYY-MM-DD.
- For Colombian RUT, validity is indefinite unless the document says otherwise.
- Use field 61 as issue/update date.
- Return JSON only.
PROMPT;

$payload = [
    "model" => "gpt-5.5",
    "input" => [
        [
            "role" => "user",
            "content" => [
                [
                    "type" => "input_text",
                    "text" => $prompt
                ],
                [
                    "type" => "input_file",
                    "file_id" => $fileId
                ]
            ]
        ]
    ]
];

$ch = curl_init("https://api.openai.com/v1/responses");

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer " . $apiKey
    ],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data["error"])) {
    echo json_encode([
        "error" => "OpenAI devolvio error",
        "detalle" => $data["error"],
        "raw" => $response
    ]);
    exit;
}

$text = "";

if (isset($data["output_text"])) {
    $text = $data["output_text"];
} elseif (isset($data["output"]) && is_array($data["output"])) {
    foreach ($data["output"] as $item) {
        if (
            isset($item["type"]) &&
            $item["type"] === "message" &&
            isset($item["content"][0]["text"])
        ) {
            $text = $item["content"][0]["text"];
            break;
        }
    }
}

if ($text === "") {
    echo json_encode([
        "error" => "OpenAI no devolvio texto",
        "raw_response" => $response,
        "data" => $data
    ]);
    exit;
}

echo $text;
exit;
