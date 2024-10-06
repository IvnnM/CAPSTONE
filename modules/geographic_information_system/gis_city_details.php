<?php
session_start();
include("../../includes/cdn.php"); 
include("../../config/database.php");

$city = $_GET['city'];

// Fetch all transactions for the selected city
$query = "
    SELECT TransacTb.*, ProductTb.ProductName 
    FROM TransacTb
    LEFT JOIN OnhandTb ON TransacTb.OnhandID = OnhandTb.OnhandID
    LEFT JOIN InventoryTb ON OnhandTb.InventoryID = InventoryTb.InventoryID
    LEFT JOIN ProductTb ON InventoryTb.ProductID = ProductTb.ProductID
    LEFT JOIN LocationTb ON TransacTb.LocationID = LocationTb.LocationID
    WHERE LocationTb.City = :city
";

$stmt = $conn->prepare($query);
$stmt->bindParam(':city', $city);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for the chart and table
$transactionDates = [];
$totalAmounts = [];

foreach ($transactions as $transac) {
    $transactionDates[] = $transac['TransactionDate'];
    $totalAmounts[] = (float)$transac['TotalPrice'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>City Transactions: <?php echo htmlspecialchars($city); ?></title>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="./js/charts/city_transactions_line_graph.js"></script>
    <link rel="stylesheet" href="../../assets/css/form.css">
    <style>
        #cityTransactionsChart {
            max-width: 600px;
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>Transactions in <?php echo htmlspecialchars($city); ?></h1>
            <!-- Year Dropdown aligned to the right -->
            <div>
                <label for="year">Select Year:</label>
                <select id="year" onchange="filterByYear()">
                    <option value="">All Years</option>
                    <?php 
                    // Generate year options based on transaction dates
                    $years = array_unique(array_map(function($date) {
                        return date('Y', strtotime($date));
                    }, $transactionDates));
                    
                    sort($years);
                    
                    foreach ($years as $year): ?>
                        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./../../views/admin_view.php#Overview">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Transaction List</li>
            </ol>
        </nav>
        <table id="transactionsTable" class="display">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Customer Name</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Date</th>
                    <th>Year</th> <!-- New Year Column -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transac): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transac['TransacID']); ?></td>
                        <td><?php echo htmlspecialchars($transac['CustName']); ?></td>
                        <td><?php echo htmlspecialchars($transac['ProductName']); ?></td>
                        <td><?php echo htmlspecialchars($transac['Quantity']); ?></td>
                        <td><?php echo htmlspecialchars($transac['TotalPrice']); ?></td>
                        <td><?php echo htmlspecialchars($transac['TransactionDate']); ?></td>
                        <td><?php echo date('Y', strtotime($transac['TransactionDate'])); ?></td> <!-- Display Year -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Add a canvas for the chart -->
        <div>
            <h2>Total Transactions Over Time</h2>
            <canvas id="cityTransactionsChart" width="400" height="200"></canvas>
        </div>
    </div>
    <script>
        // Initialize DataTables with additional features
        $(document).ready(function() {
            $('#transactionsTable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "pageLength": 10, // Show 10 entries by default
                "order": [[5, "desc"]] // Sort by Transaction Date by default (assuming it's the 6th column)
            });
        });

        // Prepare the data for the chart
        const labels = <?php echo json_encode($transactionDates); ?>; // Transaction dates
        const data = <?php echo json_encode($totalAmounts); ?>; // Total amounts

        // Call the renderCityTransactionsLineGraph function
        renderCityTransactionsLineGraph(labels, data, '<?php echo htmlspecialchars($city); ?>');

        // Function to filter transactions by year
        function filterByYear() {
            const selectedYear = document.getElementById('year').value;
            const table = $('#transactionsTable').DataTable();
            
            // Filter the DataTable based on the selected year
            if (selectedYear) {
                table.column(6).search(selectedYear).draw(); // Assuming the year is in the 7th column (0-indexed)
            } else {
                table.column(6).search('').draw(); // Reset filter
            }

            // Also filter the chart data
            const filteredLabels = [];
            const filteredData = [];

            if (selectedYear) {
                // Filter data based on the selected year
                labels.forEach((label, index) => {
                    const year = new Date(label).getFullYear();
                    if (year === parseInt(selectedYear)) {
                        filteredLabels.push(label);
                        filteredData.push(data[index]);
                    }
                });
            } else {
                // No year selected, use all data
                filteredLabels.push(...labels);
                filteredData.push(...data);
            }

            // Render the chart with filtered data
            renderCityTransactionsLineGraph(filteredLabels, filteredData, '<?php echo htmlspecialchars($city); ?>');
        }
    </script>
</body>
</html>
