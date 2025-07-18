<?php
// 1. Initialize language system (which also starts the session)
include_once '../init_lang.php';

// 2. Perform security check for customers
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header('Location: ../customer_login.php');
    exit;
}

// 3. Set page title
$page_title = 'My Profile';

// 4. Include the header
include '../header.php';

// 5. Get customer ID from session
$customer_id = $_SESSION['customer_id'];

// 6. Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 7. Fetch customer data using prepared statement
$stmt = $conn->prepare("SELECT first_name, last_name, email FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $customer = $result->fetch_assoc();
} else {
    // Handle case where customer data is not found
    header('Location: ../customer_logout.php');
    exit;
}

$stmt->close();
$conn->close();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">My Profile</h2>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-muted">Full Name</h5>
                            <p class="h4"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-muted">Email Address</h5>
                            <p class="h4"><?php echo htmlspecialchars($customer['email']); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-muted">Current Time</h5>
                            <p class="h4"><span id="live-clock"></span></p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <a href="../customer_logout.php" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Live clock functionality
function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleString();
    document.getElementById('live-clock').textContent = timeString;
}

// Update clock every second
setInterval(updateClock, 1000);
updateClock(); // Initial call
</script>

<?php
// 8. Include the footer
include '../footer.php';
?> 