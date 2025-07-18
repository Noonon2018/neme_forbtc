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

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Retrieve data from POST
    $id = $_POST['id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    // Validate email uniqueness (check if email is already used by another customer)
    $check_stmt = $conn->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
    $check_stmt->bind_param("si", $email, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Email is already in use by another customer
        $check_stmt->close();
        header("Location: edit_form.php?id=" . $id . "&error=email_exists");
        exit();
    }
    $check_stmt->close();

    // Check if password field was filled out
    if (!empty($password)) {
        // Validate password confirmation
        if ($password !== $confirm_password) {
            header("Location: edit_form.php?id=" . $id . "&error=password_mismatch");
            exit();
        }

        // Securely hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare UPDATE statement with password and email
        $stmt = $conn->prepare("UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $hashed_password, $id);

        // Execute the statement
        if ($stmt->execute()) {
            // Update successful, redirect to list page
            header("Location: list.php?success=updated");
            exit();
        } else {
            echo "Error updating record: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    } else {
        // Password field was left blank - update without password
        $stmt = $conn->prepare("UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $phone, $id);

        // Execute the statement
        if ($stmt->execute()) {
            // Update successful, redirect to list page
            header("Location: list.php?success=updated");
            exit();
        } else {
            echo "Error updating record: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    }
} else {
    // Not a POST request, redirect to list page
    header("Location: list.php?success=updated");
    exit();
}

// Close connection
$conn->close();
?> 