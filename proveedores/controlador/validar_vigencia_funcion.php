<?php

function validarVigenciaOpenAI($tmpPath, $fileName, $fileType = 'application/pdf')
{
    $openAiConfig = require __DIR__ . '/../../aws.php';
    $apiKey = trim((string) ($openAiConfig['api_key_openai'] ?? ''));

    if (!$apiKey) {
        return ["error" => "No existe api_key_openai en aws.php"];
    }

    if (!file_exists($tmpPath)) {
        return ["error" => "Archivo temporal no existe"];
    }

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
        return [
            "error" => "Error CURL subiendo archivo",
            "detalle" => $curlError
        ];
    }

    $fileData = json_decode($fileResponse, true);

    if (empty($fileData["id"])) {
        return [
            "error" => "No se pudo subir el archivo a OpenAI",
            "detalle" => $fileData,
            "raw" => $fileResponse
        ];
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
- For Colombian RUT:
  * Validity is indefinite, but we need the document generation date.
  * Look for "Fecha generación documento PDF:".
  * Extract that date and put it in BOTH issue_date AND generation_date.
  * Set best_validity_date to the generation date.
  * Set validity_source to "generation_date".
  * Set observations: "RUT con vigencia indefinida. Fecha de generación extraída."
- For RNT:
  * Look for expiration date or valid_until date.
  * Set best_validity_date to the expiration date.
- For Certificación Bancaria:
  * Use issue_date.
  * Set best_validity_date to issue_date.
- If validity is indefinite but has generation date, still return generation date in best_validity_date.
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
        return [
            "error" => "Error CURL en OpenAI",
            "detalle" => $curlError
        ];
    }

    $data = json_decode($response, true);

    if (isset($data["error"])) {
        return [
            "error" => "OpenAI devolvió error",
            "detalle" => $data["error"]
        ];
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
        return [
            "error" => "OpenAI no devolvió texto",
            "raw_response" => $response
        ];
    }

    $json = json_decode($text, true);

    if (!is_array($json)) {
        return [
            "error" => "La respuesta no fue JSON válido",
            "raw_text" => $text
        ];
    }

    return $json;
}