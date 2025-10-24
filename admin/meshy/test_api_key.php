<?php
// Test if Meshy API key is working
header('Content-Type: application/json');

$apiKey = 'msy_2nEgVakB90MIku44AeRdL92XEvliLugCNPDS';

// Test by getting account info
$ch = curl_init('https://api.meshy.ai/v1/account');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apiKey"
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode([
    'http_code' => $httpCode,
    'response' => json_decode($response, true),
    'raw_response' => $response,
    'api_key_valid' => ($httpCode === 200)
]);
?>