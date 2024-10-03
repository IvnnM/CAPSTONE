<?php
session_start();
include("../../../../includes/cdn.php"); 
include("../../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
}

// Initialize the transactions array
$transactions = [];

// Fetch all delivered transactions
$sql = "SELECT t.*, o.OnhandQty, o.RetailPrice, o.PromoPrice, p.ProductName
        FROM TransacTb t
        JOIN OnhandTb o ON t.OnhandID = o.OnhandID
        JOIN InventoryTb i ON o.InventoryID = i.InventoryID
        JOIN ProductTb p ON i.ProductID = p.ProductID
        WHERE t.Status = 'Delivered'";

$stmt = $conn->prepare($sql);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivered Transactions</title>
    <link rel="stylesheet" href="path-to-bootstrap.css"> <!-- Add bootstrap link if needed -->
    <style>
        /* Add some basic styling */
        .container {
            margin-top: 30px;
        }
        .table {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h3>Delivered Transactions</h3>
        <a href="available_product.php"> Go to Available Product</a> |
        <a href="transac_read_pending.php">Go to Pending Transactions</a> |
        <a href="transac_read_approved.php">Go to Approved Transactions</a> 
        <?php if (!empty($transactions)): ?>
            <h4 class="mt-4">Delivered Transaction Records</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Customer Name</th>
                        <th>Customer Number</th>
                        <th>Customer Email</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
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
                                <a href="../transac_update.php?id=<?= htmlspecialchars($transaction['TransacID']) ?>&action=approve" class="btn btn-secondary btn-sm" onclick="return confirm('Are you sure you want to mark this transaction as approve?');">Revert to Approve(failed to deliver)</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="mt-4">No delivered transactions found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
