<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <style>
        .progress-bar-container {
            margin-bottom: 20px;
        }
        .progress-bar-container:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body class="">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <div class="flex items-center space-x-2">
                <label for="yearSelect" class="text-gray-600">Select Year:</label>
                <select id="yearSelect" class="form-select rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <!-- Years will be populated dynamically -->
                </select>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            <a href="/views/personnel_view.php#Transaction" class="bg-white p-4 rounded-lg shadow block no-underline hover:no-underline">
                <h3 class="text-dark text-sm font-normal">Total Transactions</h3>
                <p id="total-transactions" class="text-2xl font-bold text-blue-900">0</p>
            </a>
            <a href="" class="bg-white p-4 rounded-lg shadow block no-underline hover:no-underline">
                <h3 class="text-dark text-sm font-normal">Total Revenue</h3>
                <p id="total-revenue" class="text-2xl font-bold text-blue-900">₱0</p>
            </a>
            <a href="/modules/sales_management_system/transaction/personnel/transac_read_pending.php" class="bg-white p-4 rounded-lg shadow block no-underline hover:no-underline">
                <h3 class="text-dark text-sm font-normal">Pending Orders 
                    <?php if ($pending_count > 0): ?>
                        <span class="badge bg-danger ms-1 rounded-circle" style="width: 12px; height: 12px; display: inline-block;"></span>
                    <?php endif; ?>
                </h3>
                <p id="pending-orders" class="text-2xl font-bold text-blue-900">
                    <?= $pending_count ?>
                </p>
            </a>
            
            <!-- To Ship Orders Card -->
            <a href="/modules/sales_management_system/transaction/personnel/transac_read_approved.php" class="bg-white p-4 rounded-lg shadow block no-underline hover:no-underline">
                <h3 class="text-dark text-sm font-normal">To Ship
                    <?php if ($toship_count > 0): ?>
                        <span class="badge bg-danger ms-1 rounded-circle" style="width: 12px; height: 12px; display: inline-block;"></span>
                    <?php endif; ?>
                </h3>
                <p id="to-ship-orders" class="text-2xl font-bold text-blue-900">
                    <?= $toship_count ?>
                </p>
            </a>
            <a href="/modules/sales_management_system/transaction/personnel/transac_read_delivered.php" class="bg-white p-4 rounded-lg shadow block no-underline hover:no-underline">
                <h3 class="text-dark text-sm font-normal">Delivered</h3>
                <p id="delivered-orders" class="text-2xl font-bold text-blue-900">0</p>
            </a>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <div class="bg-white p-4 rounded-lg shadow lg:col-span-2">
                <h2 class="text-xl font-bold mb-4">Sales Trend by Quarter</h2>
                <canvas id="revenue-chart"></canvas>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Revenue by Province</h2>
                <canvas id="province-pie-chart"></canvas>
            </div>
        </div>
        
        <!-- Product Performance Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-white p-4 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Top Selling Products</h2>
                <canvas id="top-selling-products-chart"></canvas>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Least Selling Products</h2>
                <canvas id="least-selling-products-chart"></canvas>
            </div>
        </div>

        <!-- Progress Bars Section -->
        <div class="bg-white p-4 rounded-lg shadow mb-8">
            <h2 class="text-xl font-bold mb-4">Province Revenue Distribution</h2>
            <div id="province-progress-bars"></div>
        </div>

        <!-- Low Stock Table -->
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Low Stock Items</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2">Inventory ID</th>
                            <th class="px-4 py-2">Current Quantity</th>
                            <th class="px-4 py-2">Retail Price</th>
                            <th class="px-4 py-2">Restock Threshold</th>
                        </tr>
                    </thead>
                    <tbody id="low-stock-body">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="../modules/geographic_information_system/dashboard/dashboard.js"></script>
</body>
</html>