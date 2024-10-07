<?php
session_start();
include("../../../../includes/cdn.html"); 
include("../../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
}

// Initialize the transactions array
$transactions = [];

// Fetch all approved transactions
$sql = "SELECT t.*, o.OnhandQty, o.RetailPrice, o.PromoPrice, p.ProductName
        FROM TransacTb t
        JOIN OnhandTb o ON t.OnhandID = o.OnhandID
        JOIN InventoryTb i ON o.InventoryID = i.InventoryID
        JOIN ProductTb p ON i.ProductID = p.ProductID
        WHERE t.Status = 'Approved'";

$stmt = $conn->prepare($sql);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Transactions</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <div class="container">
        <h3>Approved Transactions</h3>
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../../../views/admin_view.php#Transaction">Home</a></li>
                <li class="breadcrumb-item"><a href="transac_read_pending.php">Pending Transactions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Approved Transactions</li>
                <li class="breadcrumb-item"><a href="transac_read_delivered.php">Delivered Transactions</a></li>
            </ol>
        </nav>

        <?php if (!empty($transactions)): ?>
            <h4 class="mt-4">Approved Transaction Records</h4>
            <div class="container">
                <div class="table-responsive">
                    <table id="transactionsTable" class="display table table-bordered table-striped table-hover fixed-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Customer Number</th>
                                <th>Customer Email</th>
                                <th>Product Name</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Transaction Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?= htmlspecialchars($transaction['TransacID']) ?></td>
                                    <td><?= htmlspecialchars($transaction['CustName']) ?></td>
                                    <td><?= htmlspecialchars($transaction['CustNum']) ?></td>
                                    <td><?= htmlspecialchars($transaction['CustEmail']) ?></td>
                                    <td><?= htmlspecialchars($transaction['ProductName']) ?></td>
                                    <td><?= htmlspecialchars($transaction['Quantity']) ?></td>
                                    <td><?= htmlspecialchars($transaction['Price']) ?></td>
                                    <td><?= htmlspecialchars($transaction['TotalPrice']) ?></td>
                                    <td><?= htmlspecialchars($transaction['Status']) ?></td>
                                    <td><?= htmlspecialchars($transaction['TransactionDate']) ?></td>
                                    <td>
                                        <a href="../transac_update.php?id=<?= htmlspecialchars($transaction['TransacID']) ?>&action=deliver" 
                                        class="btn btn-success btn-sm" 
                                        onclick="return confirm('Are you sure you want to mark this transaction as delivered?');">
                                            <i class="bi bi-truck">     Delivered</i> <!-- Deliver icon -->
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <p class="mt-4">No approved transactions found.</p>
        <?php endif; ?>
    </div>

    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('#transactionsTable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "pageLength": 10
            });
        });
    </script>
</body>
</html>
