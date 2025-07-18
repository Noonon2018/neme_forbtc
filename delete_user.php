<?php
// Start the session
session_start();

// Security check - ensure only logged-in users can access this page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header('Location: login.php');
    exit();
}

// Check if ID is provided in URL
if (!isset($_GET['id'])) {
    header('Location: manage_users.php');
    exit();
}

// Get the user ID from URL
$user_id = $_GET['id'];

// Self-deletion prevention - check if user is trying to delete themselves
if ($user_id == $_SESSION['id']) {
    header('Location: manage_users.php?error=cannot_delete_self');
    exit();
}

// --- Database Connection Details ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_project";

// Create Connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare DELETE statement
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

// Execute the deletion
if ($stmt->execute()) {
    // Deletion successful, redirect to manage users page
    header('Location: manage_users.php');
    exit();
} else {
    // Database error
    header('Location: manage_users.php?error=database_error');
    exit();
}

// Close statement and connection
$stmt->close();
$conn->close();
?> 