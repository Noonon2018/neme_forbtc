<?php
// 1. Initialize language system (which also starts the session)
include_once 'init_lang.php';

// 2. Perform security check for admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// 3. Set page title
$page_title = 'Reset User Password';

// 4. Include the header
include 'header.php';

// 5. Get user ID from URL parameter
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Invalid user ID.</div></div>';
    include 'footer.php';
    exit;
}

// 6. Database connection and fetch user data
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 7. Fetch user data using prepared statement
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="container mt-5"><div class="alert alert-danger">User not found.</div></div>';
    include 'footer.php';
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header">
                    <h2 class="mb-0">Reset Password for: <?php echo htmlspecialchars($user['username']); ?></h2>
                </div>
                <div class="card-body">
                    <form action="update_password_process.php" method="post">
                        <!-- Hidden input to pass user ID -->
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($user_id); ?>">
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                            <a href="manage_users.php" class="btn btn-secondary">&laquo; Back to Manage Users</a>
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