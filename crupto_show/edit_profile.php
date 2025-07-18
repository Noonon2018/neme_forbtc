<?php
// 1. Initialize language system (which also starts the session)
include_once '../init_lang.php';

// 2. Perform security check for customers
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header('Location: ../customer_login.php');
    exit;
}

// 3. Set page title
$page_title = 'Edit My Profile';

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
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $customer = $result->fetch_assoc();
} else {
    // If customer data not found, redirect to login
    header('Location: ../customer_login.php');
    exit;
}

$stmt->close();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <h4 class="mb-0">CryptoReg</h4>
                        </div>
                        <div class="ms-auto">
                            <h5 class="mb-0">Edit My Profile</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="update_profile.php" method="post">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($customer['first_name']); ?>" 
                                       placeholder="Enter your first name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($customer['last_name']); ?>" 
                                       placeholder="Enter your last name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($customer['email']); ?>" 
                                       placeholder="Enter your email address" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($customer['phone']); ?>" 
                                       placeholder="Enter your phone number" required>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <a href="profile.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Close database connection
$conn->close();

// Include footer
include '../footer.php';
?> 