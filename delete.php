<?php
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

// Check if ID is provided in URL
if (isset($_GET['id'])) {
    // Get the ID from URL
    $id = $_GET['id'];
    
    // Prepare DELETE statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Deletion successful, redirect to list page
        header("Location: list.php");
        exit();
    } else {
        echo "Error deleting record: " . $stmt->error;
    }
    
    // Close statement
    $stmt->close();
} else {
    // No ID provided, redirect to list page
    header("Location: list.php");
    exit();
}

// Close connection
$conn->close();
?> 