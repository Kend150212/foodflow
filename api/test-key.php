<?php
/**
 * FoodFlow - Test API Keys
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$provider = $input['provider'] ?? '';
$apiKey = $input['api_key'] ?? '';

if (empty($provider) || empty($apiKey)) {
    echo json_encode(['error' => 'Provider and API key required']);
    exit;
}

try {
    if ($provider === 'gemini') {
        $result = testGeminiKey($apiKey);
    } elseif ($provider === 'openai') {
        $result = testOpenAIKey($apiKey);
    } elseif ($provider === 'stripe') {
        $result = testStripeKey($apiKey);
    } else {
        echo json_encode(['error' => 'Unknown provider']);
        exit;
    }

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function testGeminiKey($apiKey)
{
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

    $data = [
        'contents' => [['parts' => [['text' => 'Say "API key is valid" in 5 words or less']]]],
        'generationConfig' => ['maxOutputTokens' => 20]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        return ['success' => true, 'message' => 'Gemini API key is valid!'];
    } else {
        $error = json_decode($response, true);
        return ['success' => false, 'error' => $error['error']['message'] ?? 'Invalid API key'];
    }
}

function testOpenAIKey($apiKey)
{
    $url = "https://api.openai.com/v1/models";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        return ['success' => true, 'message' => 'OpenAI API key is valid!'];
    } else {
        $error = json_decode($response, true);
        return ['success' => false, 'error' => $error['error']['message'] ?? 'Invalid API key'];
    }
}

function testStripeKey($apiKey)
{
    $url = "https://api.stripe.com/v1/balance";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $balance = ($data['available'][0]['amount'] ?? 0) / 100;
        return ['success' => true, 'message' => 'Stripe connected! Balance: $' . number_format($balance, 2)];
    } else {
        $error = json_decode($response, true);
        return ['success' => false, 'error' => $error['error']['message'] ?? 'Invalid API key'];
    }
}
