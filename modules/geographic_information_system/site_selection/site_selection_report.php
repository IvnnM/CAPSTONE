<?php
include("../../../includes/cdn.html"); 
include_once('../../../config/database.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get the province, city, and forecast data from the URL parameters
    $selectedProvinces = explode(',', trim($_GET['province'] ?? ''));
    $selectedCity = trim($_GET['city'] ?? '');
    $forecastedRevenue = json_decode($_GET['forecastedRevenue'] ?? '[]', true);
    $forecastedTransactions = json_decode($_GET['forecastedTransactions'] ?? '[]', true);
    $historicalRevenue = json_decode($_GET['historicalRevenue'] ?? '[]', true);
    $historicalTransactions = json_decode($_GET['historicalTransactions'] ?? '[]', true);

    // Validate inputs
    if (empty($selectedProvinces) || empty($selectedCity)) {
        throw new Exception("Province and city must be provided.");
    }

    // Prepare placeholders for multiple provinces in the SQL query
    $placeholders = rtrim(str_repeat('?, ', count($selectedProvinces)), ', ');


    // Fetch data for the selected city in the selected provinces
    $query = "
    SELECT L.City, L.Province, SUM(T.TotalPrice) AS TotalRevenue, COUNT(T.TransacID) AS TransactionCount
    FROM LocationTb L
    JOIN TransacTb T ON L.LocationID = T.LocationID
    WHERE L.Province IN ($placeholders) AND L.City = ? AND T.Status = 'Delivered'
    GROUP BY L.City, L.Province
    ";

    $stmt = $conn->prepare($query);

    // Merge provinces and city into one array for execution
    $params = array_merge($selectedProvinces, [$selectedCity]);
    $stmt->execute($params);
    $cityData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if city data was found
    if (!$cityData) {
    throw new Exception("No data found for the selected city and province(s).");
    }

        // Prepare data for the charts
        $allYears = array_unique(array_merge(array_keys($historicalRevenue), array_keys($forecastedRevenue)));
        sort($allYears);
        
        $combinedRevenueData = [];
        $combinedTransactionData = [];

        // Store keys in variables to avoid "Only variables should be passed by reference" notice
        $historicalRevenueKeys = array_keys($historicalRevenue);
        $historicalTransactionsKeys = array_keys($historicalTransactions);

        foreach ($allYears as $year) {
            // Use the last historical value if no forecast is provided
            $lastHistoricalRevenue = $historicalRevenue[end($historicalRevenueKeys)] ?? 0;
            $lastHistoricalTransactions = $historicalTransactions[end($historicalTransactionsKeys)] ?? 0;

            $combinedRevenueData[$year] = [
                'historical' => $historicalRevenue[$year] ?? 0,
                'forecasted' => $forecastedRevenue[$year] ?? $lastHistoricalRevenue,
            ];
            $combinedTransactionData[$year] = [
                'historical' => $historicalTransactions[$year] ?? 0,
                'forecasted' => $forecastedTransactions[$year] ?? $lastHistoricalTransactions,
            ];
        }
// Display the report
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Selection Report for <?php echo htmlspecialchars($selectedCity); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container-fluid"><hr class="bg-dark">
    <div class="sticky-top bg-light pb-2">
        <h3>Site selection report for <?php echo htmlspecialchars($cityData['Province']); ?>, <?php echo htmlspecialchars($cityData['City']); ?></h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../site_selection/site_selection.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Site selection report</li>
            </ol>
        </nav>
        <button id="printButton" class="btn btn-primary" onclick="printReport()">Print Report</button> <!-- Print Button -->
        <hr class="bg-dark">
    </div>



    <p>Total Revenue: <?php echo htmlspecialchars(number_format($cityData['TotalRevenue'], 2)); ?></p>
    <p>Total Transactions: <?php echo htmlspecialchars($cityData['TransactionCount']); ?></p>

    <div class="row">
        <div class="col-6 p-2">
            <h3>Forecasted and Historical Revenue</h3>
            <canvas id="revenueChart" width="400" height="200"></canvas>
        </div>
    </div>
    <div class="row">
        <div class="col-6 p-2">
            <h3>Forecasted and Historical Transactions</h3>
            <canvas id="transactionChart" width="400" height="200"></canvas>
        </div>
    </div><hr class="bg-dark">
    <div class="row">
    <div class="col-12">
        <h3>Trends and Insights</h3>
        <p>
        Based on the historical data, the revenue trend for the last few years shows 
        <?php
        // Calculate growth or decline
        if (count($historicalRevenue) > 1) {
            $lastYearRevenue = end($historicalRevenue);
            $previousYearRevenue = prev($historicalRevenue);
            $growth = (($lastYearRevenue - $previousYearRevenue) / $previousYearRevenue) * 100;

            echo 'a revenue of ' . number_format($lastYearRevenue, 2) . ' in the last year, ';
            echo 'compared to ' . number_format($previousYearRevenue, 2) . ' in the previous year, resulting in ';
            echo number_format($growth, 2) . '% ' . ($growth >= 0 ? 'growth' : 'decline');
        } else {
            echo 'insufficient data to calculate trends';
        }
        ?>
        in revenue. Additionally, the total number of transactions has 
        <?php
        // Calculate transaction growth or decline
        if (count($historicalTransactions) > 1) {
            $lastYearTransactions = end($historicalTransactions);
            $previousYearTransactions = prev($historicalTransactions);
            $transactionGrowth = (($lastYearTransactions - $previousYearTransactions) / $previousYearTransactions) * 100;

            echo 'a total of ' . number_format($lastYearTransactions) . ' transactions in the last year, ';
            echo 'compared to ' . number_format($previousYearTransactions) . ' transactions in the previous year, resulting in ';
            echo number_format($transactionGrowth, 2) . '% ' . ($transactionGrowth >= 0 ? 'growth' : 'decline');
        } else {
            echo 'insufficient data to calculate transaction trends';
        }
        ?>
        in the same period.
    </p>


    </div>
</div>
</div>
<script>
        function printReport() {
        window.print(); // Opens the print dialog
    }
    const years = <?php echo json_encode(array_keys($combinedRevenueData)); ?>;
    const historicalRevenue = <?php echo json_encode(array_column($combinedRevenueData, 'historical')); ?>;
    const forecastedRevenue = <?php echo json_encode(array_column($combinedRevenueData, 'forecasted')); ?>;
    const historicalTransactions = <?php echo json_encode(array_column($combinedTransactionData, 'historical')); ?>;
    const forecastedTransactions = <?php echo json_encode(array_column($combinedTransactionData, 'forecasted')); ?>;

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: years,
            datasets: [
                {
                    label: 'Historical Revenue',
                    data: historicalRevenue,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    fill: true,
                },
                {
                    label: 'Forecasted Revenue',
                    data: forecastedRevenue,
                    borderColor: 'rgba(153, 102, 255, 1)',
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderWidth: 2,
                    fill: true,
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Transactions Chart
    const transactionCtx = document.getElementById('transactionChart').getContext('2d');
    new Chart(transactionCtx, {
        type: 'line',
        data: {
            labels: years,
            datasets: [
                {
                    label: 'Historical Transactions',
                    data: historicalTransactions,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderWidth: 2,
                    fill: true,
                },
                {
                    label: 'Forecasted Transactions',
                    data: forecastedTransactions,
                    borderColor: 'rgba(255, 159, 64, 1)',
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderWidth: 2,
                    fill: true,
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
</body>
</html>
<?php
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $conn = null; // Close the database connection
}
?>
