<?php
// 1. Initialize language system (which also starts the session)
include_once 'init_lang.php';

// 2. Security check - ensure only logged-in admin can execute this script
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// 3. Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_users.php');
    exit;
}

// 4. Retrieve data from POST
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
$confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

// 5. Validation
if (empty($new_password)) {
    header('Location: reset_password_form.php?id=' . $id . '&error=empty_password');
    exit;
}

if ($new_password !== $confirm_password) {
    header('Location: reset_password_form.php?id=' . $id . '&error=mismatch');
    exit;
}

if (strlen($new_password) < 6) {
    header('Location: reset_password_form.php?id=' . $id . '&error=too_short');
    exit;
}

// 6. Hash the new password securely
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// 7. Update database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header('Location: reset_password_form.php?id=' . $id . '&error=database_error');
    exit;
}

// Prepare UPDATE statement
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $hashed_password, $id);

// Execute the statement
if ($stmt->execute()) {
    // Success - redirect to manage users with success message
    $stmt->close();
    $conn->close();
    header('Location: manage_users.php?success=password_reset');
    exit;
} else {
    // Error - redirect back to form with error
    $stmt->close();
    $conn->close();
    header('Location: reset_password_form.php?id=' . $id . '&error=update_failed');
    exit;
}
?> 