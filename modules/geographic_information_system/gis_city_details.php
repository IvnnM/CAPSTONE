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

// Prepare data for the chart
$transactionDates = [];
$totalAmounts = [];

foreach ($transactions as $transac) {
    $transactionDates[] = $transac['TransactionDate']; // Assuming 'TransactionDate' is the field
    $totalAmounts[] = (float)$transac['TotalPrice'];   // Assuming 'TotalPrice' is the field
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>City Transactions: <?php echo htmlspecialchars($city); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="chart_functions.js"></script> <!-- Include the chart functions -->
    <style>
        /* Optional: Style the chart container */
        #cityTransactionsChart {
            max-width: 600px;
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <h1>Transactions in <?php echo htmlspecialchars($city); ?></h1>

    <table>
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Customer Name</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Date</th>
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
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Add a canvas for the chart -->
    <div>
        <h2>Total Transactions Over Time</h2>
        <canvas id="cityTransactionsChart" width="400" height="200"></canvas>
    </div>
    <a href="../../views/admin_view.php" class="btn btn-secondary">Back to Dashboard</a>
    <script>
        // Prepare the data for the chart
        const labels = <?php echo json_encode($transactionDates); ?>; // Transaction dates
        const data = <?php echo json_encode($totalAmounts); ?>; // Total amounts

        // Call the renderCityTransactionsChart function from chart_functions.js
        renderCityTransactionsChart(labels, data, '<?php echo htmlspecialchars($city); ?>');
    </script>
</body>
</html>
