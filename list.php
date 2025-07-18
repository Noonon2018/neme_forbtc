<?php
// 1. Initialize language system (which also starts the session)
include_once 'init_lang.php';

// 2. Perform security check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// 3. Set page title
$page_title = 'Customer List';

// 4. Include the header
include 'header.php';

// 5. Check for success or error messages
if (isset($_GET['success']) && $_GET['success'] == 'updated') {
    echo '<div class="alert alert-success" role="alert">Customer information has been updated successfully!</div>';
}

// 6. Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_project";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<div class="container mt-5">
    
    <div class="text-center mb-4">
        <?php if (isset($_SESSION['name'])): ?>
            <p class="text-white-50">
                Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>! |
                <a href="manage_users.php">Manage Users</a> |
                <a href="activity_log.php">View Login History</a> |
                <a href="logout.php">Logout</a>
            </p>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Registered Customers</h5>
            <div class="d-flex align-items-center">
                <form action="list.php" method="GET" class="d-flex me-2" style="max-width: 300px;">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn btn-secondary btn-sm ms-2">Search</button>
                </form>
                <a href="index.php" class="btn btn-success btn-sm">+ Register New Customer</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Registration Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Build the SQL query
                        $sql = "SELECT id, first_name, last_name, email, phone, reg_date FROM customers"; // Using reg_date as per earlier fixes
                        $params = [];
                        $types = "";

                        if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
                            $search_term = trim($_GET['search']);
                            $sql_search_term = "%" . $search_term . "%";
                            $sql .= " WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?";
                            $params = [$sql_search_term, $sql_search_term, $sql_search_term, $sql_search_term];
                            $types = "ssss";
                        }
                        $sql .= " ORDER BY id DESC";

                        $stmt = $conn->prepare($sql);
                        if ($types) {
                            $stmt->bind_param($types, ...$params);
                        }
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['reg_date']) . "</td>";
                                echo "<td>";
                                echo "<a href='edit_form.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'>Edit</a> ";
                                echo "<a href='delete.php?id=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick=\"return confirm('Are you sure?');\">Delete</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>No customers found.</td></tr>";
                        }
                        $stmt->close();
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?> 