<?php
// 1. Initialize language system (which also starts the session)
include_once '../init_lang.php';

// 2. Perform security check for customers
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header('Location: ../customer_login.php');
    exit;
}

// 3. Set page title
$page_title = 'Coin Details';

// 4. Include the header
include '../header.php';

// 5. Fetch Coin ID from URL parameter
$coin_id = isset($_GET['id']) ? $_GET['id'] : 'bitcoin';

// 6. Fetch API Data
$coin = null;
$api_error = '';

try {
    $url = 'https://api.coingecko.com/api/v3/coins/' . urlencode($coin_id);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        throw new Exception('cURL error: ' . curl_error($ch));
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code != 200) {
        throw new Exception("API returned HTTP status code: " . $http_code);
    }
    
    curl_close($ch);
    
    $coin = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding JSON response.");
    }

} catch (Exception $e) {
    $api_error = "Unable to load cryptocurrency data. Please try again later.";
}

// 7. Fetch Chart Data
$chart_prices = [];
if (!$api_error && $coin) {
    try {
        $chart_url = 'https://api.coingecko.com/api/v3/coins/' . urlencode($coin_id) . '/market_chart?vs_currency=usd&days=30';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $chart_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
        
        $chart_response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code != 200) {
            throw new Exception("Chart API returned HTTP status code: " . $http_code);
        }
        
        curl_close($ch);
        
        $chart_data = json_decode($chart_response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error decoding chart JSON response.");
        }
        
        if (isset($chart_data['prices'])) {
            $chart_prices = $chart_data['prices'];
        }
        
    } catch (Exception $e) {
        // Chart data error doesn't affect the main coin data display
        $chart_prices = [];
    }
}
?>

<div class="container mt-5">
    <?php if ($coin && !$api_error): ?>
        <!-- Coin Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo htmlspecialchars($coin['image']['large']); ?>" 
                                 alt="<?php echo htmlspecialchars($coin['name']); ?>" 
                                 class="me-3" style="width: 64px; height: 64px;">
                            <div>
                                <h1 class="mb-1"><?php echo htmlspecialchars($coin['name']); ?></h1>
                                <h3 class="text-muted mb-0"><?php echo strtoupper(htmlspecialchars($coin['symbol'])); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Market Data Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Current Price</h5>
                        <h3 class="text-primary">$<?php echo number_format($coin['market_data']['current_price']['usd'], 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Market Cap</h5>
                        <h3 class="text-info">$<?php echo number_format($coin['market_data']['market_cap']['usd']); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">24h High</h5>
                        <h3 class="text-success">$<?php echo number_format($coin['market_data']['high_24h']['usd'], 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">24h Low</h5>
                        <h3 class="text-danger">$<?php echo number_format($coin['market_data']['low_24h']['usd'], 2); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Price Change Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header">
                        <h4 class="mb-0">24 Hour Price Change</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="text-muted">Price Change</h5>
                                <h3 class="<?php echo ($coin['market_data']['price_change_percentage_24h'] >= 0) ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo ($coin['market_data']['price_change_percentage_24h'] >= 0) ? '▲' : '▼'; ?>
                                    <?php echo number_format(abs($coin['market_data']['price_change_percentage_24h']), 2); ?>%
                                </h3>
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-muted">Price Change (USD)</h5>
                                <h3 class="<?php echo ($coin['market_data']['price_change_24h'] >= 0) ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo ($coin['market_data']['price_change_24h'] >= 0) ? '+' : ''; ?>
                                    $<?php echo number_format($coin['market_data']['price_change_24h'], 2); ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Placeholder -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header">
                        <h4 class="mb-0">Price Chart</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="priceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Error State -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Unable to Load Coin Data</h4>
                        <p class="text-muted"><?php echo $api_error; ?></p>
                        <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Pass historical price data from PHP to JavaScript
const historicalData = <?php echo json_encode($chart_prices); ?>;

// Initialize chart when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('priceChart');
    
    if (ctx && historicalData.length > 0) {
        // Process the historical data
        const labels = [];
        const prices = [];
        
        historicalData.forEach(point => {
            const date = new Date(point[0]);
            labels.push(date.toLocaleDateString());
            prices.push(point[1]);
        });
        
        // Create gradient background
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(76, 175, 80, 0.3)');
        gradient.addColorStop(1, 'rgba(76, 175, 80, 0.0)');
        
        // Create the chart
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Price (USD)',
                    data: prices,
                    borderColor: '#4CAF50',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#4CAF50',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#4CAF50',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return 'Price: $' + context.parsed.y.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#666',
                            maxTicksLimit: 8
                        }
                    },
                    y: {
                        display: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            color: '#666',
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }
});
</script>

<?php
// Include footer
include '../footer.php';
?> 