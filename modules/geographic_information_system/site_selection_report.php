<?php
include '../../config/database.php'; // Ensure correct path to database connection

$selected_year = isset($_POST['year']) ? $_POST['year'] : date('Y'); // Default to current year

$sql = "SELECT 
            L.Province, 
            L.City, 
            COUNT(T.TransacID) AS num_transactions, 
            COALESCE(SUM(T.TotalPrice), 0) AS total_sales, 
            COALESCE(AVG(T.TotalPrice), 0) AS avg_sales
        FROM LocationTb L
        LEFT JOIN TransacTb T ON L.LocationID = T.LocationID 
        AND T.Status = 'Delivered' AND YEAR(T.TransactionDate) = :year
        GROUP BY L.Province, L.City
        ORDER BY total_sales DESC"; // Sort by total sales

$stmt = $conn->prepare($sql);
$stmt->bindParam(':year', $selected_year, PDO::PARAM_INT);
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetching aggregated data for the pie chart
$province_sales = []; // Array to hold total sales per province

foreach ($data as $row) {
    if (!isset($province_sales[$row['Province']])) {
        $province_sales[$row['Province']] = 0; // Initialize if not set
    }
    $province_sales[$row['Province']] += $row['total_sales']; // Sum total sales per province
}

// Prepare data for the pie chart
$provinces = array_keys($province_sales); // Unique province names
$total_sales = array_values($province_sales); // Corresponding total sales for each province
$total_sum = array_sum($total_sales); // Total sales for percentage calculation

// Identify the top provinces for the recommendation
$top_provinces = array_slice($data, 0, 3); // Get the top 3 provinces
$top_province_names = array_map(function($row) {
    return htmlspecialchars($row['Province']);
}, $top_provinces);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Selection Report</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        #pieChart {
            width: 350px;
            height: 350px;
        }

        @media print {
            #pieChart {
                display: block; /* Ensure chart displays in print */
                page-break-inside: avoid; /* Prevent breaking inside chart */
            }
            button {
                display: none; /* Hide buttons during printing */
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="my-4">Site Selection Report for <?php echo htmlspecialchars($selected_year); ?></h2>

    <!-- Year Selection Form -->
    <form method="post" id="yearForm" class="mb-4">
        <div class="form-group">
            <label for="year">Select Year:</label>
            <select name="year" id="year" class="form-control" required>
                <!-- Year options will be populated here -->
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <button type="submit" name="download_csv" class="btn btn-success">Download CSV</button>
        <button type="button" class="btn btn-info" onclick="window.print()">Print Report</button>
    </form>

    <div class="row">
        <!-- Data Table -->
        <div class="col-md-6">
            <table id="siteSelectionTable" class="display">
                <thead>
                    <tr>
                        <th>Province</th>
                        <th>City</th>
                        <th>Total Transactions</th>
                        <th>Total Sales (₱)</th>
                        <th>Average Sales per Transaction (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Province']); ?></td>
                            <td><?php echo htmlspecialchars($row['City']); ?></td>
                            <td><?php echo htmlspecialchars($row['num_transactions']); ?></td>
                            <td>₱<?php echo number_format($row['total_sales'], 2); ?></td>
                            <td>₱<?php echo number_format($row['avg_sales'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pie Chart Visualization -->
        <div class="col-md-6">
            <h3>Sales Distribution by Province</h3>
            <canvas id="pieChart"></canvas>
        </div>
    </div>

    <!-- Site Recommendation Paragraph -->
    <div class="recommendation mt-4">
        <h5>Site Recommendation:</h5>
        <p>Based on the sales distribution across different provinces for the year <?php echo htmlspecialchars($selected_year); ?>, we recommend focusing on the provinces with the highest total sales: <?php echo implode(', ', $top_province_names); ?>. This analysis helps identify potential areas for expanding your business operations and targeting marketing efforts effectively.</p>
    </div>
</div>

<script>
    // Initialize DataTables on page load
    $(document).ready(function() {
        $('#siteSelectionTable').DataTable();
    });

    // Populate year dropdown from get_years.php
    fetch('../../modules/geographic_information_system/php/get_years.php')
        .then(response => response.json())
        .then(data => {
            const yearSelect = document.getElementById('year');
            yearSelect.innerHTML = ''; // Clear existing options
            data.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                if (year == "<?php echo $selected_year; ?>") {
                    option.selected = true;
                }
                yearSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching years:', error));

    // Pie chart data
    const ctx = document.getElementById('pieChart').getContext('2d');
    const pieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($provinces); ?>,
            datasets: [{
                label: 'Total Sales (₱)',
                data: <?php echo json_encode($total_sales); ?>,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 159, 64, 0.6)',
                    'rgba(255, 99, 132, 0.6)',
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: false, // Set to false to keep the specified dimensions
            maintainAspectRatio: true, // Keep the aspect ratio
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Sales Distribution by Province'
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            const percentage = ((tooltipItem.raw / <?php echo $total_sum; ?>) * 100).toFixed(2);
                            return tooltipItem.label + ': ₱' + tooltipItem.raw.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
</script>
<br><br>
<hr class="my-4" style="border-top: 1px solid;">
<footer class="text-dark text-center">
  <div class="container">
    <p class="mb-0">© 2024 DKAT's Company. All rights reserved.</p>
    <a href="../../views/admin_view.php#Store">Back</a>
    <br>
  </div>
</footer>

</body>
</html>
