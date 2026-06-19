<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
$openAiConfig = require __DIR__ . '/../../aws.php';
$apiKey = trim((string) ($openAiConfig['api_key_openai'] ?? ''));

if ($apiKey === '') {
    echo json_encode(["error" => "Falta api_key_openai en aws.php"]);
    exit;
}

function subirArchivoOpenAI($apiKey, $tmpPath, $fileName, $fileType)
{
    $ch = curl_init("https://api.openai.com/v1/files");

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Authorization: Bearer " . $apiKey],
        CURLOPT_POSTFIELDS => [
            "purpose" => "assistants",
            "file" => new CURLFile($tmpPath, $fileType, $fileName)
        ]
    ]);

    $fileResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($fileResponse === false) {
        return ["error" => "Error CURL subiendo archivo", "detalle" => $curlError];
    }

    $fileData = json_decode($fileResponse, true);
    if (empty($fileData["id"])) {
        return ["error" => "No se pudo subir el archivo a OpenAI", "detalle" => $fileData, "raw" => $fileResponse];
    }

    return ["id" => $fileData["id"]];
}

function extraerTextoRespuestaOpenAI($response)
{
    $data = json_decode($response, true);

    if (isset($data["error"])) {
        return ["error" => "OpenAI devolvio error", "detalle" => $data["error"]];
    }

    if (isset($data["output_text"])) {
        return ["text" => $data["output_text"]];
    }

    if (isset($data["output"]) && is_array($data["output"])) {
        foreach ($data["output"] as $item) {
            if (($item["type"] ?? "") === "message" && isset($item["content"][0]["text"])) {
                return ["text" => $item["content"][0]["text"]];
            }
        }
    }

    return ["error" => "OpenAI no devolvio texto", "raw_response" => $response];
}

if (isset($_FILES['archivos']) && is_array($_FILES['archivos']['tmp_name'] ?? null)) {
    $archivos = $_FILES['archivos'];
    $tipos = is_array($_POST['tipos'] ?? null) ? $_POST['tipos'] : [];
    $filesOpenAI = [];
    $errores = [];

    foreach ($archivos['tmp_name'] as $id => $tmpPath) {
        $error = $archivos['error'][$id] ?? UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($tmpPath)) {
            $errores[$id] = ["error" => "Archivo no recibido correctamente"];
            continue;
        }

        $fileName = $archivos['name'][$id] ?? ($id . '.pdf');
        $fileType = $archivos['type'][$id] ?? "application/pdf";
        $subida = subirArchivoOpenAI($apiKey, $tmpPath, $fileName, $fileType);

        if (!empty($subida['error'])) {
            $errores[$id] = $subida;
            continue;
        }

        $filesOpenAI[$id] = [
            "file_id" => $subida["id"],
            "tipo_documento" => $tipos[$id] ?? $id,
            "nombre_archivo" => $fileName
        ];
    }

    if (empty($filesOpenAI)) {
        echo json_encode(["error" => "No se pudo subir ningun archivo a OpenAI", "errores" => $errores]);
        exit;
    }

    $descripcionDocumentos = [];
    foreach ($filesOpenAI as $id => $info) {
        $descripcionDocumentos[] = "- {$id}: {$info['tipo_documento']} ({$info['nombre_archivo']})";
    }

    $prompt = "You are a specialized assistant for reading supplier, legal, tax, administrative and compliance PDF documents.\n\n"
        . "Extract validity information from EACH uploaded document.\n\n"
        . "Return ONLY valid JSON as an object keyed by these exact ids:\n"
        . implode("\n", $descripcionDocumentos)
        . "\n\nEach value must use this format:\n"
        . "{\"document_type\":\"\",\"document_number\":\"\",\"nit\":\"\",\"holder_name\":\"\",\"company_name\":\"\",\"issuing_entity\":\"\",\"issue_date\":\"\",\"generation_date\":\"\",\"renewal_date\":\"\",\"valid_until\":\"\",\"expiration_date\":\"\",\"best_validity_date\":\"\",\"validity_source\":\"\",\"status\":\"\",\"days_until_expiration\":\"\",\"observations\":\"\"}\n\n"
        . "Rules:\n"
        . "- Never invent information.\n"
        . "- If a field is not found, return an empty string.\n"
        . "- Dates must use YYYY-MM-DD format.\n"
        . "- best_validity_date must be the best date to use for validation.\n"
        . "- For Colombian RUT, extract Fecha generacion documento PDF as generation_date and best_validity_date.\n"
        . "- For RNT, use expiration date or valid_until.\n"
        . "- For Certificacion Bancaria, use issue_date and treat validity as 365 days from issue_date.\n"
        . "- status must be VALID, EXPIRED, ABOUT TO EXPIRE, or UNKNOWN.\n"
        . "- days_until_expiration must be only a number or an empty string.\n"
        . "- Respond ONLY with JSON.";

    $content = [["type" => "input_text", "text" => $prompt]];
    foreach ($filesOpenAI as $info) {
        $content[] = ["type" => "input_file", "file_id" => $info["file_id"]];
    }

    $payload = [
        "model" => "gpt-5.5",
        "input" => [["role" => "user", "content" => $content]]
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
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        echo json_encode(["error" => "Error CURL en OpenAI", "detalle" => $curlError, "errores" => $errores]);
        exit;
    }

    $texto = extraerTextoRespuestaOpenAI($response);
    if (!empty($texto["error"])) {
        echo json_encode($texto + ["errores" => $errores]);
        exit;
    }

    $resultados = json_decode($texto["text"], true);
    if (!is_array($resultados)) {
        echo json_encode(["error" => "La respuesta no fue JSON valido", "raw_text" => $texto["text"], "errores" => $errores]);
        exit;
    }

    echo json_encode(["batch" => true, "resultados" => $resultados, "errores" => $errores]);
    exit;
}

if (!isset($_FILES['archivo'])) {
    echo json_encode(["error" => "No se recibió archivo"]);
    exit;
}

$tmpPath = $_FILES['archivo']['tmp_name'];
$fileName = $_FILES['archivo']['name'];
$fileType = $_FILES['archivo']['type'] ?: "application/pdf";

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
$curlError = curl_error($ch);
curl_close($ch);

if ($fileResponse === false) {
    echo json_encode([
        "error" => "Error CURL subiendo archivo",
        "detalle" => $curlError
    ]);
    exit;
}

$fileData = json_decode($fileResponse, true);

if (!isset($fileData["id"])) {
    echo json_encode([
        "error" => "No se pudo subir el archivo a OpenAI",
        "detalle" => $fileData,
        "raw" => $fileResponse
    ]);
    exit;
}

$fileId = $fileData["id"];

$prompt = <<<PROMPT
You are a specialized assistant for reading supplier, legal, tax, administrative and compliance PDF documents.

Extract validity information from this document.

Return ONLY valid JSON using this format:

{
  "document_type": "",
  "document_number": "",
  "nit": "",
  "holder_name": "",
  "company_name": "",
  "issuing_entity": "",
  "issue_date": "",
  "generation_date": "",
  "renewal_date": "",
  "valid_until": "",
  "expiration_date": "",
  "best_validity_date": "",
  "validity_source": "",
  "status": "",
  "days_until_expiration": "",
  "observations": ""
}

Rules:
- Never invent information.
- If a field is not found, return "".
- Dates must use YYYY-MM-DD format.
- best_validity_date must be the best date to use for validation.
- Prioritize:
  1. expiration_date
  2. valid_until
  3. renewal_date
  4. issue_date or generation_date
- validity_source must be: expiration_date, valid_until, renewal_date, issue_date, generation_date, or none.
- For Colombian RUT (Registro Único Tributario):
  * Validity is indefinite, but we need the document generation date
  * Look for text like "Fecha generación documento PDF:" at the bottom of the document
  * Extract that date and put it in BOTH issue_date AND generation_date fields
  * Set best_validity_date to the generation date
  * Set validity_source to "generation_date"
  * Set observations: "RUT con vigencia indefinida. Fecha de generación extraída."
- For RNT (Registro Nacional de Turismo):
  * Look for expiration date or valid_until date
  * Set best_validity_date to the expiration date
- - For Certificación Bancaria:
  * Use issue_date
  * Set best_validity_date to issue_date
  * Validity is 365 days from issue_date
  * If issue_date is less than 365 days ago, set status to VALID
  * If issue_date is more than 365 days ago, set status to EXPIRED
  * Calculate days_until_expiration as 365 minus days since issue_date
- If validity is indefinite (but has generation date), still return the generation date in best_validity_date.
- status must be VALID, EXPIRED, ABOUT TO EXPIRE, or UNKNOWN.
- days_until_expiration must be only a number or "".
- Respond ONLY with JSON.
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
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode([
        "error" => "Error CURL en OpenAI",
        "detalle" => $curlError
    ]);
    exit;
}

$data = json_decode($response, true);

if (isset($data["error"])) {
    echo json_encode([
        "error" => "OpenAI devolvió error",
        "detalle" => $data["error"]
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
        "error" => "OpenAI no devolvió texto",
        "raw_response" => $response
    ]);
    exit;
}

echo $text;
exit;
