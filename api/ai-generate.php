<?php
/**
 * FoodFlow - AI Content Generation API
 */

// Start session first before any output
session_start();

// Set headers
header('Content-Type: application/json');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../includes/functions.php';
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to load dependencies: ' . $e->getMessage()]);
    exit;
}

if (!isset($_SESSION['admin_id'])) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$description = trim($input['description'] ?? '');

if (empty($description)) {
    jsonResponse(['error' => 'Please provide a description of your restaurant'], 400);
}

$provider = getSetting('ai_provider', 'gemini');
$apiKey = $provider === 'gemini' ? getSetting('gemini_api_key') : getSetting('openai_api_key');

if (empty($apiKey)) {
    jsonResponse(['error' => 'Please configure your AI API key in Settings → AI Settings'], 400);
}

$prompt = <<<EOT
You are a professional copywriter for restaurant websites. Based on the restaurant description below, generate compelling landing page content.

Restaurant Description:
{$description}

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
    "features": [
        {"title": "Feature 1 title (2-3 words)", "description": "Short description (max 10 words)"},
        {"title": "Feature 2 title", "description": "Short description"},
        {"title": "Feature 3 title", "description": "Short description"}
    ],
    "cta": {
        "title": "Call-to-action headline (max 6 words)",
        "subtitle": "Supporting text (max 15 words)",
        "button_text": "Button text (2-3 words)"
    },
    "testimonials": [
        {"name": "Customer name", "text": "Short positive review (max 20 words)", "rating": 5},
        {"name": "Customer name", "text": "Short positive review", "rating": 5}
    ]
}

Make the content authentic, appetizing, and aligned with the restaurant's brand. Use power words that evoke emotions about food.
EOT;

try {
    if ($provider === 'gemini') {
        $response = callGeminiAPI($apiKey, $prompt);
    } else {
        $response = callOpenAI($apiKey, $prompt);
    }

    // Parse JSON from response
    $content = parseAIResponse($response);

    if ($content) {
        jsonResponse(['success' => true, 'content' => $content]);
    } else {
        jsonResponse(['error' => 'Failed to parse AI response. Please try again.'], 500);
    }
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
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

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        $error = json_decode($response, true);
        throw new Exception($error['error']['message'] ?? 'Gemini API error');
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
    // Remove markdown code blocks if present
    $text = preg_replace('/```json\s*/', '', $text);
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
