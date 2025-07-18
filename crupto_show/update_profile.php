<?php
// 1. Start session
session_start();

// 2. Security check - ensure only logged-in customers can access
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header('Location: ../customer_login.php');
    exit;
}

// 3. Get customer ID from session
$customer_id = $_SESSION['customer_id'];

// 4. Check that the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: edit_profile.php');
    exit;
}

// 5. Retrieve form data from $_POST
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

// 6. Basic validation
if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
    header('Location: edit_profile.php?error=missing_fields');
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: edit_profile.php?error=invalid_email');
    exit;
}

// 7. Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header('Location: edit_profile.php?error=database_error');
    exit;
}

// 8. Check if email is already in use by a different customer
$check_stmt = $conn->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
$check_stmt->bind_param("si", $email, $customer_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result && $check_result->num_rows > 0) {
    $check_stmt->close();
    $conn->close();
    header('Location: edit_profile.php?error=email_exists');
    exit;
}

$check_stmt->close();

// 9. Update customer data in database
$update_stmt = $conn->prepare("UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?");
$update_stmt->bind_param("ssssi", $first_name, $last_name, $email, $phone, $customer_id);

if ($update_stmt->execute()) {
    $update_stmt->close();
    $conn->close();
    header('Location: profile.php?success=updated');
    exit;
} else {
    $update_stmt->close();
    $conn->close();
    header('Location: edit_profile.php?error=update_failed');
    exit;
}
?> 