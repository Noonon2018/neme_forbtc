<?php
// 1. Initialize language system (which also starts the session)
include_once '../init_lang.php';

// 2. Perform security check for customers
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header('Location: ../customer_login.php');
    exit;
}

// 3. Set page title
$page_title = 'Live Crypto Prices'; // You can create a language key for this later

// 4. Include the header
include '../header.php';

// 5. Fetch API Data
$coins = [];
$api_error = '';
try {
    $url = 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=100&page=1';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception(curl_error($ch));
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code != 200) {
        throw new Exception("API returned HTTP status code: " . $http_code);
    }
    
    curl_close($ch);
    $coins = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding JSON response.");
    }
} catch (Exception $e) {
    $api_error = "Unable to load cryptocurrency data. Please try again later. Error: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <h2 class="text-white">Live Cryptocurrency Prices</h2>
    <a href="index.php" class="btn btn-outline-light mb-3">&laquo; Back to Dashboard</a>

    <div class="card card-body">
        <?php if (!empty($api_error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $api_error; ?>
            </div>
        <?php elseif (!empty($coins)): ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($coins as $coin): ?>
                    <a href="coin_detail.php?id=<?php echo $coin['id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo htmlspecialchars($coin['image']); ?>" alt="<?php echo htmlspecialchars($coin['name']); ?> logo" class="me-3" style="width: 32px; height: 32px;">
                            <div>
                                <strong><?php echo htmlspecialchars($coin['name']); ?></strong>
                                <small class="text-muted ms-1"><?php echo strtoupper(htmlspecialchars($coin['symbol'])); ?></small>
                            </div>
                        </div>
                        <div class="text-end">
                            <strong class="d-block">$<?php echo number_format($coin['current_price'], 2); ?></strong>
                            <?php 
                                $change = $coin['price_change_percentage_24h'];
                                $color_class = $change >= 0 ? 'text-success' : 'text-danger';
                                $arrow = $change >= 0 ? '▲' : '▼';
                            ?>
                            <small class="<?php echo $color_class; ?>"><?php echo $arrow; ?> <?php echo number_format(abs($change), 2); ?>%</small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No data available at the moment.</p>
        <?php endif; ?>
    </div>
</div>

<?php
include '../footer.php';
?> 