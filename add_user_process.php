<?php
// Start the session
session_start();

// Security check - ensure only logged-in users can access this page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header('Location: login.php');
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

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Retrieve POST data
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify that password and confirm_password match
    if ($password !== $confirm_password) {
        header('Location: manage_users.php?error=password_mismatch');
        exit();
    }
    
    // Check for existing username
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Username already exists
        header('Location: manage_users.php?error=username_taken');
        exit();
    }
    
    // Close the check statement
    $check_stmt->close();
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $insert_stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $insert_stmt->bind_param("ss", $username, $hashed_password);
    
    if ($insert_stmt->execute()) {
        // User added successfully
        header('Location: manage_users.php?success=user_added');
        exit();
    } else {
        // Database error
        header('Location: manage_users.php?error=database_error');
        exit();
    }
    
    // Close the insert statement
    $insert_stmt->close();
    
} else {
    // Not a POST request, redirect to manage users
    header('Location: manage_users.php');
    exit();
}

// Close connection
$conn->close();
?> 