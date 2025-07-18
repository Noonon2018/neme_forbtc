<?php
// --- Database Connection Details ---
$servername = "localhost"; // หรือ IP ของเซิร์ฟเวอร์ฐานข้อมูล
$username = "root";        // ชื่อผู้ใช้ของฐานข้อมูล (ของ XAMPP มักจะเป็น root)
$password = "";            // รหัสผ่าน (ของ XAMPP มักจะว่าง)
$dbname = "my_project";    // ชื่อฐานข้อมูลที่คุณสร้าง

// --- Create Connection ---
$conn = new mysqli($servername, $username, $password, $dbname);

// --- Check Connection ---
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Check if form was submitted ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- Google reCAPTCHA validation ---
    $recaptcha_secret = '6LfM54YrAAAAAMV8QnfIpO96qeIdkHzScRo1DzX8';
    
    if (isset($_POST['g-recaptcha-response'])) {
        $recaptcha_response = $_POST['g-recaptcha-response'];
        
        // Send POST request to Google's verification URL
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $post_data = http_build_query([
            'secret' => $recaptcha_secret,
            'response' => $recaptcha_response
        ]);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verify_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        // Decode the JSON response
        $recaptcha_data = json_decode($response, true);
        
        // Check if verification failed
        if (!$recaptcha_data['success']) {
            header('Location: index.php?error=recaptcha_failed');
            exit();
        }
    } else {
        // No reCAPTCHA response provided
        header('Location: index.php?error=recaptcha_failed');
        exit();
    }
    
    // --- Get data from form ---
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = $_POST['phone'];

    // --- Validate password confirmation ---
    if ($password !== $confirm_password) {
        header('Location: index.php?error=passwords_do_not_match');
        exit();
    }

    // --- Check for duplicate email ---
    $email_check_stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
    $email_check_stmt->bind_param("s", $email);
    $email_check_stmt->execute();
    $email_check_result = $email_check_stmt->get_result();

    if ($email_check_result->num_rows > 0) {
        // Email already exists
        header('Location: index.php?error=email_taken');
        exit();
    }

    // --- Close email check statement ---
    $email_check_stmt->close();

    // --- Check for duplicate phone number ---
    $check_stmt = $conn->prepare("SELECT id FROM customers WHERE phone = ?");
    $check_stmt->bind_param("s", $phone);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Phone number already exists
        echo "This phone number is already registered.";
    } else {
        // Phone number is new, proceed with insert
        // --- Hash the password ---
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // --- Prepare and Bind (to prevent SQL Injection) ---
        $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, email, password, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $phone);

        // --- Execute the statement and give feedback ---
        if ($stmt->execute()) {
            header("Location: register_success.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        // --- Close statement ---
        $stmt->close();
    }

    // --- Close check statement ---
    $check_stmt->close();
}

// --- Close connection ---
$conn->close();
?> 