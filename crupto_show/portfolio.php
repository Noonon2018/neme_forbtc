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

// --- API Fetching Block for Live Prices Sidebar ---
$coins = [];
$api_error = '';
try {
    $url = 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=25&page=1';
    
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
    
    $coins = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding JSON response.");
    }

} catch (Exception $e) {
    // This line creates the user-friendly error message.
    $api_error = "Unable to load cryptocurrency data. Please try again later.";
}
// --- End of API Fetching Block ---
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;700&display=swap');
    body {
        font-family: 'Sarabun', sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #eef2f7 100%);
        color: #1c1917; /* stone-900 */
    }
    .chart-container {
        position: relative;
        width: 100%;
        max-width: 900px;
        height: 300px;
        margin: auto;
    }
    @media (min-width: 768px) { .chart-container { height: 350px; } }
    
    .card {
        background: rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 1.25rem;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.15);
    }
    .card-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 500;
        color: #57534e; /* stone-600 */
    }
    .nav-btn {
        cursor: pointer;
        padding: 0.5rem 1.5rem;
        border-radius: 999px;
        transition: background-color 0.3s, color 0.3s;
        font-weight: 500;
    }
    .nav-btn.active {
        background-color: #047857; /* emerald-600 */
        color: white;
        box-shadow: 0 4px 14px 0 rgba(4, 120, 87, 0.25);
    }
    .time-btn {
         cursor: pointer;
         padding: 0.25rem 0.75rem;
         border-radius: 999px;
         transition: background-color 0.3s, color 0.3s;
         font-size: 0.875rem;
         font-weight: 500;
         border: 1px solid #d6d3d1; /* stone-300 */
    }
    .time-btn.active {
        background-color: #57534e; /* stone-600 */
        color: white;
        border-color: #57534e;
    }
    .skeleton {
        background-color: #e7e5e4; /* stone-200 */
        border-radius: 0.5rem;
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulse {
        50% { opacity: .5; }
    }
</style>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container mt-5">
    <div class="row">
        <!-- Left Column: Live Prices Sidebar -->
        <div class="col-md-3">
            <!-- Live Prices Section -->
            <div class="card shadow">
                <div class="card-body">
                    <h4 class="text-lg font-bold mb-4">Live Prices</h4>
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($coins)): ?>
                            <?php foreach ($coins as $coin): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($coin['image']); ?>" class="me-2" style="width: 24px;">
                                        <strong><?php echo htmlspecialchars($coin['name']); ?></strong>
                                        <small class="text-muted ms-1"><?php echo strtoupper(htmlspecialchars($coin['symbol'])); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">$<?php echo number_format($coin['current_price'], 2); ?></div>
                                        <small class="<?php echo ($coin['price_change_percentage_24h'] >= 0) ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($coin['price_change_percentage_24h'] >= 0) ? '▲' : '▼'; ?>
                                            <?php echo number_format(abs($coin['price_change_percentage_24h']), 2); ?>%
                                        </small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center text-muted">
                                Unable to load cryptocurrency data. Please try again later.
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Portfolio Content -->
        <div class="col-md-9">
            <!-- 1. FOUR SUMMARY CARDS FROM INDEX.PHP -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="card p-6 flex flex-col justify-center items-center text-center">
                    <h2 class="card-title"><span>📈</span>ราคาล่าสุด (USD)</h2>
                    <div id="live-price-container" class="my-2 text-4xl font-bold text-stone-800 h-10 flex items-center">
                        <div class="skeleton w-48 h-8"></div>
                    </div>
                    <p id="last-updated" class="text-xs text-stone-400 h-4"></p>
                </div>

                <div class="card p-6 flex flex-col justify-center items-center text-center">
                     <h2 class="card-title"><span>💼</span>มูลค่าพอร์ต</h2>
                     <div id="portfolio-value-container" class="my-2 text-4xl font-bold text-stone-800 h-10 flex items-center">
                        <div class="skeleton w-40 h-8"></div>
                    </div>
                    <p class="text-xs text-stone-400 flex items-center gap-2">
                        <span>ถือครอง <span id="holdings-display" class="font-bold"></span> <span id="holdings-symbol"></span></span>
                        <button id="edit-holdings-btn" class="text-stone-500 hover:text-stone-800 transition-colors" title="แก้ไขจำนวนที่ถือครอง">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                            </svg>
                        </button>
                    </p>
                </div>

                <div class="card p-6 flex flex-col justify-center items-center text-center">
                     <h2 class="card-title"><span>💰</span>กำไร/ขาดทุน</h2>
                     <div id="pl-container" class="my-2 text-4xl font-bold h-10 flex items-center">
                        <div class="skeleton w-36 h-8"></div>
                    </div>
                     <p id="investment-display" class="text-xs text-stone-400"></p>
                </div>
                
                <div class="card p-6 flex flex-col justify-center items-center text-center">
                     <h2 class="card-title"><span>📊</span>แนวโน้มล่าสุด</h2>
                     <div id="trend-container" class="my-2 text-4xl font-bold h-10 flex items-center">
                        <div class="skeleton w-24 h-8"></div>
                    </div>
                     <p class="text-xs text-stone-400">เทียบกับข้อมูลก่อนหน้า</p>
                </div>
            </div>

            <!-- 2. EXISTING PORTFOLIO SUMMARY TABLE -->
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

            <!-- 3. MAIN CHART FROM INDEX.PHP -->
            <div class="card p-6 md:col-span-2 lg:col-span-4 mb-6">
                <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
                    <h2 id="chart-title" class="text-xl font-bold text-stone-700"></h2>
                    <div id="time-range-selector" class="flex space-x-2">
                        <button class="time-btn active" data-range="1D">1D</button>
                        <button class="time-btn" data-range="7D">7D</button>
                        <button class="time-btn" data-range="30D">30D</button>
                    </div>
                </div>
                 <div class="chart-container">
                    <canvas id="priceChart"></canvas>
                </div>
            </div>

            <!-- 4. EXISTING TRANSACTION HISTORY TABLE -->
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
    </div>
</div>

<!-- Edit Holdings Modal -->
<div id="edit-holdings-modal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex justify-center items-center hidden z-50 p-4">
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-2xl w-full max-w-sm transform transition-all opacity-0 -translate-y-10">
        <h3 id="modal-holdings-title" class="text-xl font-bold mb-4"></h3>
        <label id="modal-holdings-label" for="amount-input" class="block text-sm font-medium text-stone-600 mb-2"></label>
        <input type="number" id="amount-input" class="w-full p-3 border border-stone-300 rounded-lg mb-6 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" step="any">
        <div class="flex justify-end gap-4">
            <button id="cancel-holdings-edit-btn" class="px-5 py-2 rounded-lg text-stone-700 bg-stone-200 hover:bg-stone-300 transition-colors">ยกเลิก</button>
            <button id="save-holdings-edit-btn" class="px-5 py-2 rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">บันทึก</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const API_BASE = 'https://api.coingecko.com/api/v3/simple/price?vs_currencies=usd&ids=';

        const USER_DATA = {
            ownerName: 'นักลงทุน',
            bitcoin: { name: 'Bitcoin', symbol: 'BTC', holdings: 0.02097989, investment: 2324.18 },
            chia: { name: 'Chia', symbol: 'XCH', holdings: 150, investment: 3000 }
        };

        let priceChart = null, currentInterval = null;
        let state = { 
            selectedCoin: 'bitcoin', 
            currentTimeRange: '1D', 
            isFirstLoad: true, 
            currentPrice: 0 
        };

        const holdingsDisplay = document.getElementById('holdings-display');
        const holdingsSymbol = document.getElementById('holdings-symbol');

        const animateValue = (element, start, end, duration) => {
            if (start === end || isNaN(start) || isNaN(end)) {
                element.textContent = formatCurrency(end || 0); return;
            }
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const value = start + (end - start) * progress;
                element.textContent = formatCurrency(value);
                if (progress < 1) window.requestAnimationFrame(step);
            };
            window.requestAnimationFrame(step);
        };

        const formatCurrency = (value) => {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        };

        const formatNumber = (value) => {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 8
            }).format(value);
        };

        const updateHoldingsDisplay = () => {
            const coin = USER_DATA[state.selectedCoin];
            holdingsDisplay.textContent = formatNumber(coin.holdings);
            holdingsSymbol.textContent = coin.symbol;
        };

        const updatePortfolioValue = (price) => {
            const coin = USER_DATA[state.selectedCoin];
            const portfolioValue = coin.holdings * price;
            const container = document.getElementById('portfolio-value-container');
            
            if (state.isFirstLoad) {
                container.innerHTML = `<div class="skeleton w-40 h-8"></div>`;
                setTimeout(() => {
                    container.innerHTML = formatCurrency(portfolioValue);
                }, 1000);
            } else {
                const currentValue = parseFloat(container.textContent.replace(/[$,]/g, '')) || 0;
                animateValue(container, currentValue, portfolioValue, 1000);
            }
        };

        const updatePL = (price) => {
            const coin = USER_DATA[state.selectedCoin];
            const currentValue = coin.holdings * price;
            const pl = currentValue - coin.investment;
            const plPercent = (pl / coin.investment) * 100;
            
            const container = document.getElementById('pl-container');
            const investmentDisplay = document.getElementById('investment-display');
            
            if (state.isFirstLoad) {
                container.innerHTML = `<div class="skeleton w-36 h-8"></div>`;
                setTimeout(() => {
                    container.innerHTML = formatCurrency(pl);
                    container.className = `my-2 text-4xl font-bold h-10 flex items-center ${pl >= 0 ? 'text-green-600' : 'text-red-600'}`;
                    investmentDisplay.textContent = `${plPercent >= 0 ? '+' : ''}${plPercent.toFixed(2)}%`;
                }, 1000);
            } else {
                const currentPL = parseFloat(container.textContent.replace(/[$,]/g, '')) || 0;
                animateValue(container, currentPL, pl, 1000);
                container.className = `my-2 text-4xl font-bold h-10 flex items-center ${pl >= 0 ? 'text-green-600' : 'text-red-600'}`;
                investmentDisplay.textContent = `${plPercent >= 0 ? '+' : ''}${plPercent.toFixed(2)}%`;
            }
        };

        const updateTrend = (price) => {
            const container = document.getElementById('trend-container');
            const change = price - state.currentPrice;
            const changePercent = state.currentPrice > 0 ? (change / state.currentPrice) * 100 : 0;
            
            if (state.isFirstLoad) {
                container.innerHTML = `<div class="skeleton w-24 h-8"></div>`;
                setTimeout(() => {
                    container.innerHTML = `${changePercent >= 0 ? '+' : ''}${changePercent.toFixed(2)}%`;
                    container.className = `my-2 text-4xl font-bold h-10 flex items-center ${changePercent >= 0 ? 'text-green-600' : 'text-red-600'}`;
                }, 1000);
            } else {
                container.innerHTML = `${changePercent >= 0 ? '+' : ''}${changePercent.toFixed(2)}%`;
                container.className = `my-2 text-4xl font-bold h-10 flex items-center ${changePercent >= 0 ? 'text-green-600' : 'text-red-600'}`;
            }
        };

        const fetchPrice = async () => {
            try {
                const response = await fetch(`${API_BASE}${state.selectedCoin}`);
                const data = await response.json();
                const price = data[state.selectedCoin].usd;
                
                const priceContainer = document.getElementById('live-price-container');
                const lastUpdated = document.getElementById('last-updated');
                
                if (state.isFirstLoad) {
                    priceContainer.innerHTML = `<div class="skeleton w-48 h-8"></div>`;
                    setTimeout(() => {
                        priceContainer.innerHTML = formatCurrency(price);
                        lastUpdated.textContent = `อัปเดตล่าสุด: ${new Date().toLocaleTimeString('th-TH')}`;
                    }, 1000);
                } else {
                    const currentPrice = parseFloat(priceContainer.textContent.replace(/[$,]/g, '')) || 0;
                    animateValue(priceContainer, currentPrice, price, 1000);
                    lastUpdated.textContent = `อัปเดตล่าสุด: ${new Date().toLocaleTimeString('th-TH')}`;
                }
                
                state.currentPrice = price;
                updatePortfolioValue(price);
                updatePL(price);
                updateTrend(price);
                
                if (state.isFirstLoad) {
                    state.isFirstLoad = false;
                }
            } catch (error) {
                console.error('Error fetching price:', error);
            }
        };

        const initializeChart = () => {
            const ctx = document.getElementById('priceChart').getContext('2d');
            priceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Price (USD)',
                        data: [],
                        borderColor: '#047857',
                        backgroundColor: 'rgba(4, 120, 87, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        };

        const updateChart = (data) => {
            if (priceChart) {
                priceChart.data.labels = data.labels;
                priceChart.data.datasets[0].data = data.prices;
                priceChart.update();
            }
        };

        const fetchChartData = async () => {
            try {
                const days = state.currentTimeRange === '1D' ? 1 : state.currentTimeRange === '7D' ? 7 : 30;
                const response = await fetch(`https://api.coingecko.com/api/v3/coins/${state.selectedCoin}/market_chart?vs_currency=usd&days=${days}`);
                const data = await response.json();
                
                const prices = data.prices.map(price => price[1]);
                const labels = data.prices.map(price => {
                    const date = new Date(price[0]);
                    return date.toLocaleDateString('th-TH', { 
                        month: 'short', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                });
                
                updateChart({ labels, prices });
            } catch (error) {
                console.error('Error fetching chart data:', error);
            }
        };

        // Modal functionality
        const editHoldingsBtn = document.getElementById('edit-holdings-btn');
        const editHoldingsModal = document.getElementById('edit-holdings-modal');
        const amountInput = document.getElementById('amount-input');
        const modalHoldingsTitle = document.getElementById('modal-holdings-title');
        const modalHoldingsLabel = document.getElementById('modal-holdings-label');
        const saveHoldingsBtn = document.getElementById('save-holdings-edit-btn');
        const cancelHoldingsBtn = document.getElementById('cancel-holdings-edit-btn');

        editHoldingsBtn.addEventListener('click', () => {
            const coin = USER_DATA[state.selectedCoin];
            modalHoldingsTitle.textContent = `แก้ไขจำนวนที่ถือครอง ${coin.name}`;
            modalHoldingsLabel.textContent = `จำนวน ${coin.symbol} ที่ถือครอง:`;
            amountInput.value = coin.holdings;
            editHoldingsModal.classList.remove('hidden');
            setTimeout(() => {
                editHoldingsModal.querySelector('.bg-white').classList.remove('opacity-0', '-translate-y-10');
            }, 10);
        });

        const closeHoldingsModal = () => {
            editHoldingsModal.querySelector('.bg-white').classList.add('opacity-0', '-translate-y-10');
            setTimeout(() => {
                editHoldingsModal.classList.add('hidden');
            }, 300);
        };

        cancelHoldingsBtn.addEventListener('click', closeHoldingsModal);
        editHoldingsModal.addEventListener('click', (e) => {
            if (e.target === editHoldingsModal) closeHoldingsModal();
        });

        saveHoldingsBtn.addEventListener('click', () => {
            const newAmount = parseFloat(amountInput.value);
            if (!isNaN(newAmount) && newAmount >= 0) {
                USER_DATA[state.selectedCoin].holdings = newAmount;
                updateHoldingsDisplay();
                updatePortfolioValue(state.currentPrice);
                updatePL(state.currentPrice);
                closeHoldingsModal();
            }
        });

        document.querySelectorAll('[data-range]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('[data-range]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                state.currentTimeRange = btn.dataset.range;
                fetchChartData();
            });
        });

        // Initialize
        initializeChart();
        updateHoldingsDisplay();
        fetchPrice();
        fetchChartData();

        // Auto-refresh every minute
        setInterval(() => {
            fetchPrice();
        }, 60000);
    });
</script>

<?php
// Close statement and connection
$stmt->close();
$conn->close();

// Include footer
include '../footer.php';
?> 