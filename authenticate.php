<?php
// Start the session
session_start();

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

// Check if username and password were submitted
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header('Location: login.php?error=1');
    exit();
}

// Get the submitted credentials
$username = $_POST['username'];
$password = $_POST['password'];

// Prepare SELECT statement to find the user
$stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if user was found and password matches
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password'])) {
        // Login successful
        session_regenerate_id(true);
        $_SESSION['loggedin'] = TRUE;
        $_SESSION['name'] = $_POST['username'];
        $_SESSION['id'] = $user['id'];
        
        // Log successful login attempt
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $log_stmt = $conn->prepare("INSERT INTO login_logs (user_id, ip_address) VALUES (?, ?)");
        $log_stmt->bind_param("is", $user['id'], $ip_address);
        $log_stmt->execute();
        $log_stmt->close();
        
        // Redirect to customer list
        header('Location: list.php');
        exit();
    } else {
        // Password doesn't match
        header('Location: login.php?error=1');
        exit();
    }
} else {
    // User not found
    header('Location: login.php?error=1');
    exit();
}

// Close statement and connection
$stmt->close();
$conn->close();
?> 