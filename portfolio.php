<?php
// 1. Initialize language system (which also starts the session)
include_once '../init_lang.php';

// 2. Perform security check for customers
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header('Location: ../customer_login.php');
    exit;
}

// 3. Set page title
$page_title = $lang['page_title_portfolio'];

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

// 7. Fetch transaction data using prepared statement
$stmt = $conn->prepare("SELECT * FROM transactions WHERE customer_id = ? ORDER BY transaction_date DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

// 8. Aggregate transactions to calculate holdings
$holdings = [];
$total_portfolio_value = 0;
$total_profit_loss = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $coin_id = $row['coin_id'];
        $quantity = $row['quantity'];
        $price_per_coin = $row['price_per_coin'];
        $transaction_type = $row['transaction_type'];
        
        if (!isset($holdings[$coin_id])) {
            $holdings[$coin_id] = [
                'quantity' => 0,
                'total_cost_basis' => 0,
                'total_bought' => 0,
                'total_sold' => 0
            ];
        }
        
        if ($transaction_type === 'buy') {
            $holdings[$coin_id]['quantity'] += $quantity;
            $holdings[$coin_id]['total_cost_basis'] += ($quantity * $price_per_coin);
            $holdings[$coin_id]['total_bought'] += ($quantity * $price_per_coin);
        } else { // sell
            $holdings[$coin_id]['quantity'] -= $quantity;
            $holdings[$coin_id]['total_sold'] += ($quantity * $price_per_coin);
        }
    }
    
    // Remove coins with zero or negative holdings
    $holdings = array_filter($holdings, function($holding) {
        return $holding['quantity'] > 0;
    });
    
    // 9. Fetch live prices for held coins
    if (!empty($holdings)) {
        $coin_ids = array_keys($holdings);
        $coin_ids_str = implode(',', $coin_ids);
        
        $url = "https://api.coingecko.com/api/v3/simple/price?ids={$coin_ids_str}&vs_currencies=usd";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $live_prices = json_decode($response, true);
        
        // 10. Calculate final values
        foreach ($holdings as $coin_id => &$holding) {
            if (isset($live_prices[$coin_id]['usd'])) {
                $current_price = $live_prices[$coin_id]['usd'];
                $holding['current_price'] = $current_price;
                $holding['current_market_value'] = $holding['quantity'] * $current_price;
                $holding['total_profit_loss'] = $holding['current_market_value'] - $holding['total_cost_basis'];
                $holding['avg_cost_basis'] = $holding['quantity'] > 0 ? $holding['total_cost_basis'] / $holding['quantity'] : 0;
                
                $total_portfolio_value += $holding['current_market_value'];
                $total_profit_loss += $holding['total_profit_loss'];
            }
        }
    }
}

// Reset result pointer for transaction history display
$stmt->data_seek(0);
?>

<div class="container mt-5">
    <?php if (!empty($holdings)): ?>
        <!-- Portfolio Summary Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0"><?php echo $lang['portfolio_summary_heading']; ?></h2>
                    </div>
                    <div class="card-body">
                        <!-- Overall Portfolio Stats -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title"><?php echo $lang['portfolio_total_value']; ?></h5>
                                        <h3 class="text-primary">$<?php echo number_format($total_portfolio_value, 2); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title"><?php echo $lang['portfolio_total_pl']; ?></h5>
                                        <h3 class="<?php echo $total_profit_loss >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            $<?php echo number_format($total_profit_loss, 2); ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Holdings Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th><?php echo $lang['portfolio_col_coin']; ?></th>
                                        <th><?php echo $lang['portfolio_col_holdings']; ?></th>
                                        <th><?php echo $lang['portfolio_col_avg_cost']; ?></th>
                                        <th><?php echo $lang['portfolio_col_current_price']; ?></th>
                                        <th><?php echo $lang['portfolio_col_market_value']; ?></th>
                                        <th><?php echo $lang['portfolio_col_pl']; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($holdings as $coin_id => $holding): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars(ucfirst($coin_id)); ?></span>
                                            </td>
                                            <td><?php echo number_format($holding['quantity'], 8); ?></td>
                                            <td>$<?php echo number_format($holding['avg_cost_basis'], 2); ?></td>
                                            <td>$<?php echo number_format($holding['current_price'], 2); ?></td>
                                            <td><strong>$<?php echo number_format($holding['current_market_value'], 2); ?></strong></td>
                                            <td>
                                                <span class="<?php echo $holding['total_profit_loss'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                    <strong>$<?php echo number_format($holding['total_profit_loss'], 2); ?></strong>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Transaction History Card -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h2 class="mb-0"><?php echo $lang['history_heading']; ?></h2>
                </div>
                <div class="card-body">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th><?php echo $lang['history_col_date']; ?></th>
                                        <th><?php echo $lang['portfolio_col_coin']; ?></th>
                                        <th><?php echo $lang['history_col_type']; ?></th>
                                        <th><?php echo $lang['history_col_quantity']; ?></th>
                                        <th><?php echo $lang['history_col_price_per_coin']; ?></th>
                                        <th><?php echo $lang['history_col_total_value']; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($row['transaction_date']))); ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars(ucfirst($row['coin_id'])); ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                $type_class = $row['transaction_type'] === 'buy' ? 'success' : 'danger';
                                                $type_text = $row['transaction_type'] === 'buy' ? $lang['transaction_buy'] : $lang['transaction_sell'];
                                                ?>
                                                <span class="badge bg-<?php echo $type_class; ?>"><?php echo $type_text; ?></span>
                                            </td>
                                            <td><?php echo number_format($row['quantity'], 8); ?></td>
                                            <td>$<?php echo number_format($row['price_per_coin'], 2); ?></td>
                                            <td>
                                                <strong>$<?php echo number_format($row['quantity'] * $row['price_per_coin'], 2); ?></strong>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <h4 class="text-muted mt-3"><?php echo $lang['history_no_transactions']; ?></h4>
                            <p class="text-muted"><?php echo $lang['history_no_transactions_desc']; ?></p>
                            <a href="index.php" class="btn btn-primary"><?php echo $lang['history_add_first_button']; ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Close statement and connection
$stmt->close();
$conn->close();

// Include footer
include '../footer.php';
?> 