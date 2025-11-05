<?php
//reference the below
//https://www.youtube.com/watch?v=LGjDebAuAd4

// Enable error reporting for debugging

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type
header('Content-Type: application/json');

// Get the prompt from POST data
$prompt = $_POST['prompt'] ?? '';

// Validate input
if (empty(trim($prompt))) {
    echo json_encode([
        'error' => [
            'message' => 'Prompt cannot be empty',
            'type' => 'invalid_request_error'
        ]
    ]);
    exit;
}

// IMPORTANT: Replace this with your actual API key
// Store your API key in environment variable or config file for security
$api_key = 'YOUR_OPENAI_API_KEY_HERE';
//$api_key = getenv('OPENAI_API_KEY');

// Validate API key
//if ($api_key === 'YOUR_OPENAI_API_KEY_HERE') {
if (!$api_key) {
    echo json_encode([
        'error' => [
            'message' => 'Please configure your OpenAI API key in the PHP file',
            'type' => 'authentication_error'
        ]
    ]);
    exit;
}

// Prepare the request data
$data = [
    'model' => 'dall-e-2',
    'prompt' => $prompt,
    'n' => 1,
    'size' => '1024x1024'
];

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/images/generations');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Execute the request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Check for cURL errors
if ($curl_error) {
    echo json_encode([
        'error' => [
            'message' => 'Network error: ' . $curl_error,
            'type' => 'network_error'
        ]
    ]);
    exit;
}

// Check HTTP status
if ($http_code !== 200) {
    $error_data = json_decode($response, true);
    echo json_encode([
        'error' => [
            'message' => $error_data['error']['message'] ?? 'HTTP Error: ' . $http_code,
            'type' => $error_data['error']['type'] ?? 'http_error',
            'http_code' => $http_code
        ]
    ]);
    exit;
}

// Log the response for debugging
file_put_contents("log.txt", date('Y-m-d H:i:s') . " - " . $response . "\n", FILE_APPEND);

// Return the response
echo $response;
?>
