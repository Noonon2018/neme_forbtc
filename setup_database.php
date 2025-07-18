<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";

// Create connection without specifying database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to MySQL successfully!<br>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS my_project";
if ($conn->query($sql) === TRUE) {
    echo "Database 'my_project' created successfully or already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db("my_project");

// Create customers table
$sql = "CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'customers' created successfully or already exists<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Insert sample data
$sql = "INSERT INTO customers (first_name, last_name, phone) VALUES
('John', 'Doe', '123-456-7890'),
('Jane', 'Smith', '098-765-4321'),
('Bob', 'Johnson', '555-123-4567')";

if ($conn->query($sql) === TRUE) {
    echo "Sample data inserted successfully<br>";
} else {
    echo "Error inserting sample data: " . $conn->error . "<br>";
}

$conn->close();
echo "<br>Database setup complete! You can now use the registration form.";
?> 