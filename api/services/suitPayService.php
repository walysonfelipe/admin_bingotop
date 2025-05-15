<?php

function generateSuitPayPixTransfer(
    string $apiUrl,
    string $clientId,
    string $clientSecret,
    string $key,
    string $typeKey,
    float  $value,
    string $callbackUrl = '',
    string $documentValidation = '',
    string $externalId = ''
): array {
    $url = rtrim($apiUrl, '/') . '/api/v1/gateway/pix-payment';
    $headers = [
        "ci: {$clientId}",
        "cs: {$clientSecret}",
        "Content-Type: application/json"
    ];

    // Monta o corpo da requisição
    $bodyArray = [
        'key'               => $key,
        'typeKey'           => $typeKey,
        'value'             => $value,
    ];
    if ($callbackUrl !== '') {
        $bodyArray['callbackUrl'] = $callbackUrl;
    }
    if ($documentValidation !== '') {
        $bodyArray['documentValidation'] = $documentValidation;
    }
    if ($externalId !== '') {
        $bodyArray['externalId'] = $externalId;
    }
    $body = json_encode($bodyArray);

    // Executa o cURL
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
    ]);
    $resp     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Decodifica a resposta JSON
    $json = json_decode($resp, true);


    return [
        'idTransaction' => $json['idTransaction'] ?? null,
        'response'      => $json['response'] ?? null,
        'httpCode'      => $httpCode
    ];
}
