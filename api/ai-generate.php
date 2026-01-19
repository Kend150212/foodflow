<?php
/**
 * FoodFlow - AI Content Generation API
 */

// Start session first before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header('Content-Type: application/json');

// Error handling for production
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Custom error handler
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    require_once __DIR__ . '/../includes/functions.php';

    if (!isset($_SESSION['admin_id'])) {
        echo json_encode(['error' => 'Unauthorized. Please login first.']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $description = trim($input['description'] ?? '');

    if (empty($description)) {
        echo json_encode(['error' => 'Please provide a description of your restaurant']);
        exit;
    }

    $provider = getSetting('ai_provider', 'gemini');
    $apiKey = $provider === 'gemini' ? getSetting('gemini_api_key') : getSetting('openai_api_key');

    if (empty($apiKey)) {
        echo json_encode(['error' => 'Please configure your AI API key in Settings → AI Settings']);
        exit;
    }

    $prompt = 'You are a professional copywriter for restaurant websites. Based on the restaurant description below, generate compelling landing page content.

Restaurant Description:
' . $description . '

Generate content in the following JSON format (respond ONLY with valid JSON, no markdown):
{
    "hero": {
        "title": "A catchy headline (max 8 words)",
        "subtitle": "An engaging description (1-2 sentences, max 30 words)",
        "cta_text": "Call-to-action button text (2-3 words)"
    },
    "about": {
        "title": "About section title (2-4 words)",
        "description": "A compelling story about the restaurant (2-3 sentences, max 60 words)"
    },
    "cta": {
        "title": "Call-to-action headline (max 6 words)",
        "subtitle": "Supporting text (max 15 words)",
        "button_text": "Button text (2-3 words)"
    }
}

Make the content authentic, appetizing, and aligned with the restaurant\'s brand.';

    if ($provider === 'gemini') {
        $response = callGeminiAPI($apiKey, $prompt);
    } else {
        $response = callOpenAI($apiKey, $prompt);
    }

    // Parse JSON from response
    $content = parseAIResponse($response);

    if ($content) {
        echo json_encode(['success' => true, 'content' => $content]);
    } else {
        echo json_encode(['error' => 'Failed to parse AI response. Raw: ' . substr($response, 0, 200)]);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['error' => 'Fatal error: ' . $e->getMessage()]);
}

function callGeminiAPI($apiKey, $prompt)
{
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

    $data = [
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 1024
        ]
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

    if ($curlError) {
        throw new Exception("cURL error: " . $curlError);
    }

    if ($httpCode !== 200) {
        $error = json_decode($response, true);
        throw new Exception($error['error']['message'] ?? 'Gemini API error (HTTP ' . $httpCode . ')');
    }

    $result = json_decode($response, true);
    return $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
}

function callOpenAI($apiKey, $prompt)
{
    $url = "https://api.openai.com/v1/chat/completions";

    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7,
        'max_tokens' => 1024
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        $error = json_decode($response, true);
        throw new Exception($error['error']['message'] ?? 'OpenAI API error');
    }

    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? '';
}

function parseAIResponse($text)
{
    if (empty($text)) {
        return null;
    }

    // Remove markdown code blocks if present
    $text = preg_replace('/```json\s*/i', '', $text);
    $text = preg_replace('/```\s*/', '', $text);
    $text = trim($text);

    $json = json_decode($text, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        return $json;
    }

    // Try to extract JSON from text
    if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
        $json = json_decode($matches[0], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }
    }

    return null;
}
