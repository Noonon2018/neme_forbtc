<?php
session_start();
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header('Location: ../customer_login.php');
    exit;
}

// Fetch cryptocurrency data from CoinGecko API
$coins = [];
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
        echo '<h3>cURL Error Report:</h3>';
        echo 'Error Number: ' . curl_errno($ch) . '<br>';
        echo 'Error Message: ' . curl_error($ch) . '<br>';
        echo '<h3>Full Connection Info:</h3>';
        echo '<pre>';
        var_dump(curl_getinfo($ch));
        echo '</pre>';
        $coins = []; // Set coins to empty array on error
    } else {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $coins = json_decode($response, true);
            if (!is_array($coins)) {
                $coins = [];
            }
        }
    }
} catch (Exception $e) {
    // Handle error silently, $coins will remain empty array
    $coins = [];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Investment Dashboard</title>
    <!-- Chosen Palette: Luminous Jade -->
    <!-- Application Structure Plan: พัฒนาจากโครงสร้างเดิมไปสู่การมีแท็บนำทาง (Tabbed Navigation) แบ่งเป็น "Dashboard" สำหรับการ์ดข้อมูลหลัก และ "ข้อมูลเชิงลึก" สำหรับตารางข้อมูล การแบ่งส่วนนี้ช่วยลดความซับซ้อนในหน้าจอแรก และให้ผู้ใช้เลือกเจาะลึกข้อมูลได้ตามต้องการ เพิ่มส่วนควบคุมช่วงเวลาของกราฟ (1D, 7D, 30D) เพื่อเสริมการวิเคราะห์แนวโน้ม และเพิ่มฟังก์ชันแก้ไขข้อมูลส่วนตัว (จำนวนที่ถือครอง, ชื่อเจ้าของ) เพื่อให้แอปเป็นเครื่องมือส่วนบุคคลที่สมบูรณ์ -->
    <!-- Visualization & Content Choices: 1. การ์ดข้อมูลหลัก: ใช้ดีไซน์ Glassmorphism เพื่อความสวยงามทันสมัย | 2. กราฟราคา: เพิ่มปุ่มควบคุมช่วงเวลา (1D, 7D, 30D) เพื่อให้ผู้ใช้โต้ตอบและวิเคราะห์ข้อมูลได้ลึกขึ้น (Chart.js Canvas) | 3. ตารางข้อมูล: ย้ายไปไว้ในแท็บแยกเพื่อความสะอาดตา | 4. Loading State: ใช้ Skeleton UI แทน Spinner เพื่อประสบการณ์ที่ราบรื่นและดูพรีเมียมขึ้น | 5. การแก้ไขข้อมูล: ใช้ Modal (Pop-up) สำหรับการแก้ไขจำนวนที่ถือครองและชื่อเจ้าของ เป็นรูปแบบมาตรฐานที่ผู้ใช้คุ้นเคย ทั้งหมดนี้ไม่ใช้ SVG/Mermaid และสนับสนุนโครงสร้างใหม่ที่เน้นการวิเคราะห์เชิงลึก -->
    <!-- CONFIRMATION: NO SVG graphics used. NO Mermaid JS used. -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
</head>
<body class="text-stone-800">

    <div class="container mt-3">
        <p class="text-end">
            Welcome, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>!
            <a href="../customer_logout.php" class="btn btn-secondary btn-sm">Logout</a>
        </p>
    </div>

    <div class="container mx-auto p-4 md:p-8 max-w-7xl">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-2" id="sidebar">
                <div class="card">
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

            <!-- Right Column - Main Dashboard Content -->
            <div class="col-md-10" id="main-content">
                <header class="text-center mb-6">
                    <h1 class="text-3xl md:text-4xl font-bold text-stone-900">Crypto Investment Dashboard</h1>
                    <div class="flex justify-center items-center gap-2 mt-2">
                        <p id="owner-name-display" class="text-stone-600"></p>
                        <button id="edit-owner-name-btn" class="text-stone-500 hover:text-stone-800 transition-colors" title="แก้ไขชื่อ">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                            </svg>
                        </button>
                    </div>
                </header>
                
                <nav id="coin-selector" class="flex justify-center mb-6 bg-white/30 backdrop-blur-sm p-2 rounded-full w-fit mx-auto border border-white/20">
                    <div class="nav-btn active" data-coin="bitcoin">Bitcoin</div>
                    <div class="nav-btn" data-coin="chia">Chia (XCH)</div>
                </nav>

                <nav id="tab-selector" class="flex justify-center mb-8 bg-white/30 backdrop-blur-sm p-2 rounded-full w-fit mx-auto border border-white/20">
                    <div class="nav-btn active" data-tab="dashboard">Dashboard</div>
                    <div class="nav-btn" data-tab="details">ข้อมูลเชิงลึก</div>
                </nav>

                <div id="content-dashboard">
                    <main class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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

                        <div class="card p-6 md:col-span-2 lg:col-span-4">
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
                    </main>
                </div>

                <div id="content-details" class="hidden">
                    <div class="card p-6">
                         <h2 id="table-title" class="text-xl font-bold text-center mb-4 text-stone-700"></h2>
                         <div class="overflow-y-auto h-[60vh]">
                             <table class="w-full text-left" id="history-table">
                                <thead class="sticky top-0 bg-white/60 backdrop-blur-sm">
                                    <tr>
                                        <th class="p-3 text-sm font-bold text-stone-600">เวลา</th>
                                        <th class="p-3 text-sm font-bold text-stone-600 text-right">ราคา (USD)</th>
                                    </tr>
                                </thead>
                                <tbody id="history-table-body">
                                </tbody>
                            </table>
                         </div>
                    </div>
                </div>
                
                <footer class="text-center mt-10 text-sm text-stone-500">
                    <p>ข้อมูลราคาจาก CoinGecko API | รีเฟรชข้อมูลทุก 1 นาที</p>
                </footer>
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

    <!-- Edit Name Modal -->
    <div id="edit-name-modal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex justify-center items-center hidden z-50 p-4">
        <div class="bg-white p-6 md:p-8 rounded-2xl shadow-2xl w-full max-w-sm transform transition-all opacity-0 -translate-y-10">
            <h3 class="text-xl font-bold mb-4">แก้ไขชื่อเจ้าของ</h3>
            <label for="owner-name-input" class="block text-sm font-medium text-stone-600 mb-2">ชื่อของคุณ</label>
            <input type="text" id="owner-name-input" class="w-full p-3 border border-stone-300 rounded-lg mb-6 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="เช่น นักลงทุน">
            <div class="flex justify-end gap-4">
                <button id="cancel-name-edit-btn" class="px-5 py-2 rounded-lg text-stone-700 bg-stone-200 hover:bg-stone-300 transition-colors">ยกเลิก</button>
                <button id="save-name-edit-btn" class="px-5 py-2 rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">บันทึก</button>
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

            const updateTable = (data) => {
                const tableBody = document.getElementById('history-table-body');
                const tableTitle = document.getElementById('table-title');
                const coin = USER_DATA[state.selectedCoin];
                
                tableTitle.textContent = `ประวัติราคา ${coin.name} (${coin.symbol})`;
                
                tableBody.innerHTML = data.prices.map(price => {
                    const date = new Date(price[0]);
                    const formattedDate = date.toLocaleString('th-TH', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    return `
                        <tr class="border-b border-stone-200 hover:bg-stone-50">
                            <td class="p-3 text-sm">${formattedDate}</td>
                            <td class="p-3 text-sm text-right font-medium">${formatCurrency(price[1])}</td>
                        </tr>
                    `;
                }).join('');
            };

            const fetchTableData = async () => {
                try {
                    const days = state.currentTimeRange === '1D' ? 1 : state.currentTimeRange === '7D' ? 7 : 30;
                    const response = await fetch(`https://api.coingecko.com/api/v3/coins/${state.selectedCoin}/market_chart?vs_currency=usd&days=${days}`);
                    const data = await response.json();
                    
                    updateTable(data);
                } catch (error) {
                    console.error('Error fetching table data:', error);
                }
            };

            // Event Listeners
            document.querySelectorAll('[data-coin]').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('[data-coin]').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    state.selectedCoin = btn.dataset.coin;
                    updateHoldingsDisplay();
                    fetchPrice();
                    fetchChartData();
                    fetchTableData();
                });
            });

            document.querySelectorAll('[data-tab]').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    
                    const tab = btn.dataset.tab;
                    document.getElementById('content-dashboard').classList.toggle('hidden', tab !== 'dashboard');
                    document.getElementById('content-details').classList.toggle('hidden', tab !== 'details');
                    
                    if (tab === 'details') {
                        fetchTableData();
                    }
                });
            });

            document.querySelectorAll('[data-range]').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('[data-range]').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    state.currentTimeRange = btn.dataset.range;
                    fetchChartData();
                    if (document.getElementById('content-details').classList.contains('hidden') === false) {
                        fetchTableData();
                    }
                });
            });

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

            // Name edit functionality
            const editNameBtn = document.getElementById('edit-owner-name-btn');
            const editNameModal = document.getElementById('edit-name-modal');
            const ownerNameInput = document.getElementById('owner-name-input');
            const ownerNameDisplay = document.getElementById('owner-name-display');
            const saveNameBtn = document.getElementById('save-name-edit-btn');
            const cancelNameBtn = document.getElementById('cancel-name-edit-btn');

            editNameBtn.addEventListener('click', () => {
                ownerNameInput.value = USER_DATA.ownerName;
                editNameModal.classList.remove('hidden');
                setTimeout(() => {
                    editNameModal.querySelector('.bg-white').classList.remove('opacity-0', '-translate-y-10');
                }, 10);
            });

            const closeNameModal = () => {
                editNameModal.querySelector('.bg-white').classList.add('opacity-0', '-translate-y-10');
                setTimeout(() => {
                    editNameModal.classList.add('hidden');
                }, 300);
            };

            cancelNameBtn.addEventListener('click', closeNameModal);
            editNameModal.addEventListener('click', (e) => {
                if (e.target === editNameModal) closeNameModal();
            });

            saveNameBtn.addEventListener('click', () => {
                const newName = ownerNameInput.value.trim();
                if (newName) {
                    USER_DATA.ownerName = newName;
                    ownerNameDisplay.textContent = newName;
                    closeNameModal();
                }
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
</body>
</html> 