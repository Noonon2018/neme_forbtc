<?php
// 1. Start session and security check for customers only
session_start();

if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header('Location: customer_login.php');
    exit;
}

// 2. Get customer ID from session
$customer_id = $_SESSION['customer_id'];

// 3. Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: crupto_show/index.php');
    exit;
}

// 4. Retrieve form data from POST
$coin_id = isset($_POST['coin_id']) ? trim($_POST['coin_id']) : '';
$transaction_type = isset($_POST['transaction_type']) ? trim($_POST['transaction_type']) : '';
$quantity = isset($_POST['quantity']) ? trim($_POST['quantity']) : '';
$price_per_coin = isset($_POST['price_per_coin']) ? trim($_POST['price_per_coin']) : '';
$transaction_date = isset($_POST['transaction_date']) ? trim($_POST['transaction_date']) : '';

// 5. Validation - check if required fields are not empty
if (empty($coin_id) || empty($transaction_type) || empty($quantity) || empty($price_per_coin) || empty($transaction_date)) {
    header('Location: crupto_show/index.php?error=missing_fields');
    exit;
}

// Validate transaction type
if (!in_array($transaction_type, ['buy', 'sell'])) {
    header('Location: crupto_show/index.php?error=invalid_transaction_type');
    exit;
}

// Validate numeric fields
if (!is_numeric($quantity) || !is_numeric($price_per_coin)) {
    header('Location: crupto_show/index.php?error=invalid_numeric_values');
    exit;
}

// Validate quantity and price are positive
if ($quantity <= 0 || $price_per_coin <= 0) {
    header('Location: crupto_show/index.php?error=invalid_values');
    exit;
}

// 6. Database connection and insert
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header('Location: crupto_show/index.php?error=database_error');
    exit;
}

// Prepare INSERT statement
$stmt = $conn->prepare("INSERT INTO transactions (customer_id, coin_id, transaction_type, quantity, price_per_coin, transaction_date) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssds", $customer_id, $coin_id, $transaction_type, $quantity, $price_per_coin, $transaction_date);

// Execute the statement
if ($stmt->execute()) {
    // Success - redirect with success message
    $stmt->close();
    $conn->close();
    header('Location: crupto_show/index.php?success=transaction_added');
    exit;
} else {
    // Error - redirect with error message
    $stmt->close();
    $conn->close();
    header('Location: crupto_show/index.php?error=insert_failed');
    exit;
}
?> 