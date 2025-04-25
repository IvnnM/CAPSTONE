<?php
session_start();
include("../../includes/cdn.html"); 
include("../../config/database.php");

// Check if the user is logged in
// if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
//     echo "<script>alert('You must be logged in to access this page.'); 
//     window.location.href = '../../login.php';</script>";
//     exit;
// }

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get the selected province and city from the URL parameters
    $selectedProvince = $_GET['province'] ?? null;
    $selectedCity = $_GET['city'] ?? null;

    if ($selectedProvince && $selectedCity) {
        // Prepare the query to fetch all transactions for the selected city
        $query = "
            SELECT T.TransacID, T.CustName, T.CustNum, T.CustEmail, T.DeliveryFee, T.TotalPrice, T.TransactionDate, T.Status
            FROM TransacTb T
            JOIN LocationTb L ON T.LocationID = L.LocationID
            WHERE L.Province = :province AND L.City = :city
            ORDER BY T.TransactionDate DESC
        ";

        // Prepare and execute the query
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':province', $selectedProvince);
        $stmt->bindParam(':city', $selectedCity);
        $stmt->execute();

        // Fetch all transactions
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        throw new Exception("Province or city parameter is missing.");
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions for <?php echo htmlspecialchars($selectedCity); ?></title>
    <link rel="stylesheet" href="../../../../css/styles.css"> <!-- Add your CSS file here -->
    <!-- Include DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>
    
    <div class="container-fluid"><hr>
        <div class="sticky-top bg-light pb-2">
            <h3>Transactions for <?php echo htmlspecialchars($selectedCity); ?>, <?php echo htmlspecialchars($selectedProvince); ?></h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../views/personnel_view.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Transactions</li>
                </ol>
            </nav>
            <hr>
        </div>

        <!-- Button to download transactions as CSV -->
        <div class="mb-3">
            <a href="./map/download_transactions.php?province=<?php echo urlencode($selectedProvince); ?>&city=<?php echo urlencode($selectedCity); ?>" class="btn btn-primary">
                Download Transactions as CSV
            </a>
        </div>

        <?php if (!empty($transactions)): ?>
            <div class="table-responsive">
                <table id="transactionsTable" class="display table table-light table-bordered table-striped table-hover fixed-table pt-2">
                    <thead class="table-info">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Number</th>
                            <th>Email</th>
                            <th>Delivery Fee</th>
                            <th>Total Cost</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?= htmlspecialchars($transaction['TransacID']) ?></td>
                                <td><?= htmlspecialchars($transaction['CustName']) ?></td>
                                <td><?= htmlspecialchars($transaction['CustNum']) ?></td>
                                <td><?= htmlspecialchars($transaction['CustEmail']) ?></td>
                                <td><?= number_format(htmlspecialchars($transaction['DeliveryFee']), 2) ?></td>
                                <td><?= number_format(htmlspecialchars($transaction['TotalPrice']), 2) ?></td>
                                <td><?= htmlspecialchars($transaction['TransactionDate']) ?></td>
                                <td><?= htmlspecialchars($transaction['Status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="mt-4">No transactions found for this city.</p>
        <?php endif; ?>
    </div>

    <!-- Include jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#transactionsTable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "pageLength": 10, // Default number of entries per page
            });
        });
    </script>
</body>
</html>

<?php
// Close the connection
$conn = null;
?>
