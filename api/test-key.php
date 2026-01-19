<?php
/**
 * FoodFlow - Test API Keys
 */

// Start output buffering to catch any PHP warnings
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../includes/functions.php';
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['error' => 'Server error']);
    exit;
}

// Clear any buffered output from includes
ob_end_clean();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$provider = $input['provider'] ?? '';
$apiKey = $input['api_key'] ?? '';
$useSaved = $input['use_saved'] ?? false;

// If no key provided and use_saved is true, get from database
if (empty($apiKey) && $useSaved) {
    if ($provider === 'gemini') {
        $apiKey = getSetting('gemini_api_key', '');
    } elseif ($provider === 'openai') {
        $apiKey = getSetting('openai_api_key', '');
    } elseif ($provider === 'stripe') {
        $apiKey = getSetting('stripe_secret_key', '');
    }
}

if (empty($provider)) {
    echo json_encode(['error' => 'Provider is required']);
    exit;
}

if (empty($apiKey)) {
    echo json_encode(['error' => 'No API key configured. Please enter a key first.']);
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
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . urlencode($apiKey);

    $data = [
        'contents' => [['parts' => [['text' => 'Say "API key is valid" in 5 words or less']]]],
        'generationConfig' => ['maxOutputTokens' => 20]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Check for curl errors
    if ($curlError) {
        return ['success' => false, 'error' => 'Connection error: ' . $curlError];
    }

    if ($httpCode === 200) {
        return ['success' => true, 'message' => 'Gemini API key is valid!'];
    } else {
        $error = json_decode($response, true);
        $errorMsg = $error['error']['message'] ?? ($error['error']['status'] ?? 'Invalid API key or connection error');
        return ['success' => false, 'error' => $errorMsg];
    }
}

function testOpenAIKey($apiKey)
{
    $url = "https://api.openai.com/v1/models";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Check for curl errors
    if ($curlError) {
        return ['success' => false, 'error' => 'Connection error: ' . $curlError];
    }

    if ($httpCode === 200) {
        return ['success' => true, 'message' => 'OpenAI API key is valid!'];
    } else {
        $error = json_decode($response, true);
        $errorMsg = $error['error']['message'] ?? 'Invalid API key or connection error';
        return ['success' => false, 'error' => $errorMsg];
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
