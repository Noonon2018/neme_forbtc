<?php
// 1. Initialize language system (which also starts the session)
include_once '../init_lang.php';

// 2. Perform security check for customers
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// 3. Check if coin ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Coin ID is required']);
    exit;
}

// 4. Get coin ID from URL parameter
$coin_id = $_GET['id'];

// 5. Fetch data from CoinGecko API
$api_url = "https://api.coingecko.com/api/v3/coins/" . urlencode($coin_id) . "/market_chart?vs_currency=usd&days=30";

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

// Execute cURL request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for cURL errors
if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch data: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// 6. Check HTTP response code
if ($http_code !== 200) {
    http_response_code($http_code);
    echo json_encode(['error' => 'API request failed with status code: ' . $http_code]);
    exit;
}

// 7. Set JSON content type header
header('Content-Type: application/json');

// 8. Output the raw JSON response
echo $response;
?> 