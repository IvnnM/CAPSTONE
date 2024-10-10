<?php
session_start();
include("../../../../includes/cdn.html"); 
include("../../../../config/database.php");

// Fetch pending transactions for the current customer
$cust_email = $_SESSION['cust_email'] ?? '';
$query = "SELECT TransacID, CustName, CustNum, CustEmail, LocationID, OnhandID, Price, Quantity, DeliveryFee, TotalPrice, TransactionDate 
          FROM TransacTb 
          WHERE CustEmail = :cust_email AND Status = 'ToShip' 
          ORDER BY TransactionDate DESC";
$stmt = $conn->prepare($query);
$stmt->execute(['cust_email' => $cust_email]);
$pending_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Transactions</title>
</head>
<body>
    <div class="container-fluid">
        <div class="sticky-top bg-light pb-2">
            <h3>Pending Transactions</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../../views/customer_view.php#Orders">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pending Transactions</li>
                </ol>
            </nav>
            <hr>
            <!-- Button for Navigation -->
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-secondary" onclick="window.history.back();">Back</button>
            </div>
        </div>
        <!-- Table to display pending transactions -->
        <div class="table-responsive">
            <table id="transactionTable" class="table table-light table-hover border-secondary pt-2">
                <thead class="table-info">
                    <tr>
                        <th>Transaction ID</th>
                        <th>Customer Name</th>
                        <th>Customer Number</th>
                        <th>Email</th>
                        <th>Location ID</th>
                        <th>Onhand ID</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Delivery Fee</th>
                        <th>Total Price</th>
                        <th>Transaction Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pending_transactions)): ?>
                        <tr>
                            <td colspan="11">No pending transactions found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pending_transactions as $transaction): ?>
                            <tr>
                                <td><?= htmlspecialchars($transaction['TransacID']) ?></td>
                                <td><?= htmlspecialchars($transaction['CustName']) ?></td>
                                <td><?= htmlspecialchars($transaction['CustNum']) ?></td>
                                <td><?= htmlspecialchars($transaction['CustEmail']) ?></td>
                                <td><?= htmlspecialchars($transaction['LocationID']) ?></td>
                                <td><?= htmlspecialchars($transaction['OnhandID']) ?></td>
                                <td><?= htmlspecialchars($transaction['Price']) ?></td>
                                <td><?= htmlspecialchars($transaction['Quantity']) ?></td>
                                <td><?= htmlspecialchars($transaction['DeliveryFee']) ?></td>
                                <td><?= htmlspecialchars($transaction['TotalPrice']) ?></td>
                                <td><?= htmlspecialchars($transaction['TransactionDate']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('#transactionTable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "pageLength": 5, // Default number of entries per page
                "lengthMenu": [5, 10, 25, 50, 100], // Options for number of entries
            });
        });
    </script>
</body>
</html>
