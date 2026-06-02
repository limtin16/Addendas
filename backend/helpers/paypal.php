<?php

// ✅ CONFIG (usa sandbox o producción automáticamente)
function getPayPalApiBaseUrl() {

    return PAYPAL_ENV === 'live'
        ? 'https://api.paypal.com'
        : 'https://api.sandbox.paypal.com';
}


// ✅ TOKEN DE ACCESO
function getPayPalAccessToken() {

    $clientId = PAYPAL_CLIENT_ID;
    $secret   = PAYPAL_SECRET;
    $baseUrl  = getPayPalApiBaseUrl();

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/v1/oauth2/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $secret);

    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_POST, true);

    // ✅ headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json",
        "Accept-Language: en_US"
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        file_put_contents(__DIR__ . '/paypal_error.log',
            "TOKEN ERROR: " . curl_error($ch) . "\n",
            FILE_APPEND
        );
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    $data = json_decode($response);

    if (!isset($data->access_token)) {
        file_put_contents(__DIR__ . '/paypal_error.log',
            "TOKEN RESPONSE INVALID: " . $response . "\n",
            FILE_APPEND
        );
        return null;
    }

    return $data->access_token;
}


// ✅ VALIDAR ORDEN (CRÍTICO PARA SEGURIDAD)
function verifyPayPalOrder($orderId) {

    $token   = getPayPalAccessToken();
    $baseUrl = getPayPalApiBaseUrl();

    if (!$token) return false;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/v2/checkout/orders/" . $orderId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        file_put_contents(__DIR__ . '/paypal_error.log',
            "VERIFY ERROR: " . curl_error($ch) . "\n",
            FILE_APPEND
        );
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    $data = json_decode($response);

    if (!$data || !isset($data->status)) {
        file_put_contents(__DIR__ . '/paypal_error.log',
            "VERIFY RESPONSE INVALID: " . $response . "\n",
            FILE_APPEND
        );
        return false;
    }

    return $data;
}


// ✅ VALIDAR CAPTURA (opcional pero pro)
function verifyPayPalCapture($captureId) {

    $token   = getPayPalAccessToken();
    $baseUrl = getPayPalApiBaseUrl();

    if (!$token) return false;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/v2/payments/captures/" . $captureId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        file_put_contents(__DIR__ . '/paypal_error.log',
            "CAPTURE ERROR: " . curl_error($ch) . "\n",
            FILE_APPEND
        );
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    $data = json_decode($response);

    return $data;
}