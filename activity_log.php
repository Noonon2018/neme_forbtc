<?php
// 1. Initialize language system (which also starts the session)
include_once 'init_lang.php';

// 2. Perform security check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// 3. Set page title
$page_title = 'Login History';

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

// Get the current user's ID from the session
$user_id = $_SESSION['id'];
?>

    <div class="container mt-5">
        <h2>Your Login History</h2>
        
        <?php
        // Prepare and execute SELECT statement to get login history with username
        $stmt = $conn->prepare("SELECT login_logs.login_time, login_logs.ip_address, users.username FROM login_logs JOIN users ON login_logs.user_id = users.id ORDER BY login_logs.login_time DESC");
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            echo "<table class='table table-striped'>";
            echo "<thead><tr><th>Username</th><th>Login Date & Time</th><th>IP Address</th></tr></thead>";
            echo "<tbody>";
            
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["login_time"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["ip_address"]) . "</td>";
                echo "</tr>";
            }
            
            echo "</tbody></table>";
        } else {
            echo "<p class='text-center'>No login history found.</p>";
        }

        // Close statement
        $stmt->close();
        ?>

        <div class="text-center">
            <a href="list.php" class="btn btn-secondary mt-3">Back to Customer List</a>
        </div>
    </div>

<?php include 'footer.php'; ?>

<?php
// Close connection
$conn->close();
?> 