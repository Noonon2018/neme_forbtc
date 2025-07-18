<?php
// 1. Initialize language system (which also starts the session)
include_once 'init_lang.php';

// 2. Perform security check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// 3. Set page title
$page_title = 'Edit Customer';

// 4. Include the header
include 'header.php';

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

// Get the customer ID from URL parameter
$id = $_GET['id'];

// Prepare SELECT statement to get customer data
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the customer's data
if ($result->num_rows > 0) {
    $customer = $result->fetch_assoc();
} else {
    die("Customer not found.");
}

// Close statement
$stmt->close();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <!-- Logo and Title -->
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-shield-check text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h2 class="text-primary mb-0">CryptoReg</h2>
                        <p class="text-muted">Edit Customer Information</p>
                    </div>

                    <!-- Back Button -->
                    <div class="text-center mb-4">
                        <a href="list.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Customer List
                        </a>
                    </div>

                    <form action="update_process.php" method="post">
                        <input type="hidden" name="id" value="<?php echo $customer['id']; ?>">
                        
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person-fill"></i>
                                </span>
                                <input type="text" name="first_name" class="form-control" placeholder="First Name" value="<?php echo htmlspecialchars($customer['first_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person-fill"></i>
                                </span>
                                <input type="text" name="last_name" class="form-control" placeholder="Last Name" value="<?php echo htmlspecialchars($customer['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope-fill"></i>
                                </span>
                                <input type="email" name="email" class="form-control" placeholder="Email Address" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-telephone-fill"></i>
                                </span>
                                <input type="text" name="phone" class="form-control" placeholder="Phone Number" value="<?php echo htmlspecialchars($customer['phone']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" name="password" class="form-control" placeholder="New Password (leave blank to keep unchanged)">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm New Password">
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Close connection
$conn->close();

// Include footer
include 'footer.php';
?> 