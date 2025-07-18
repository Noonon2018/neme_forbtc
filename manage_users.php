<?php
// 1. Initialize language system (which also starts the session)
include_once 'init_lang.php';

// 2. Perform security check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// 3. Set page title
$page_title = 'Manage Users';

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
?>

    <div class="container mt-5">
        <p><a href="list.php" class="btn btn-secondary mb-4">&laquo; Back to Customer List</a></p>
        
        <?php
        // Check for success or error messages from URL
        if (isset($_GET['success']) && $_GET['success'] == 'password_reset') {
            echo '<div class="alert alert-success" role="alert">User password has been reset successfully!</div>';
        }
        if (isset($_GET['error']) && $_GET['error'] == 'cannot_delete_self') {
            echo '<div class="alert alert-danger" role="alert">Error: You cannot delete your own account!</div>';
        }
        ?>
        
        <h2>Manage Users</h2>
        
        <?php
        // Query to get all users
        $sql = "SELECT id, username, created_at FROM users";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            echo "<table class='table table-striped'>";
            echo "<thead><tr><th>ID</th><th>Username</th><th>Created At</th><th>Action</th></tr></thead>";
            echo "<tbody>";
            
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["created_at"]) . "</td>";
                echo "<td><a href='reset_password_form.php?id=" . $row["id"] . "' class='btn btn-info btn-sm'>Reset Password</a>&nbsp;<a href='delete_user.php?id=" . $row["id"] . "' onclick=\"return confirm('Are you sure you want to delete this user? This action cannot be undone.');\" class='btn btn-danger btn-sm'>Delete</a></td>";
                echo "</tr>";
            }
            
            echo "</tbody></table>";
        } else {
            echo "<p class='text-center'>No users found.</p>";
        }
        ?>

        <h2 class="mt-5">Add New User</h2>
        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <form action="add_user_process.php" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">New Username:</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password:</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100">Add User</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'footer.php'; ?>

<?php
// Close connection
$conn->close();
?> 