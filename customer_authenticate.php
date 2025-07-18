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

// Check if email and password were submitted
if (!isset($_POST['email']) || !isset($_POST['password'])) {
    header('Location: customer_login.php?error=1');
    exit();
}

// Get the submitted credentials
$email = $_POST['email'];
$password = $_POST['password'];

// Prepare SELECT statement to find the customer
$stmt = $conn->prepare("SELECT id, password, first_name FROM customers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if customer was found and password matches
if ($result->num_rows > 0) {
    $customer = $result->fetch_assoc();
    
    if (password_verify($password, $customer['password'])) {
        // Login successful
        session_regenerate_id(true);
        $_SESSION['customer_loggedin'] = TRUE;
        $_SESSION['customer_id'] = $customer['id'];
        $_SESSION['customer_name'] = $customer['first_name'];
        
        // Redirect to customer area
                        header('Location: Crupto_show/portfolio.php');
        exit();
    } else {
        // Password doesn't match
        header('Location: customer_login.php?error=1');
        exit();
    }
} else {
    // Customer not found
    header('Location: customer_login.php?error=1');
    exit();
}

// Close statement and connection
$stmt->close();
$conn->close();
?> 