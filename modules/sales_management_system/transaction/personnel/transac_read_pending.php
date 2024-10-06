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

// Fetch all pending transactions
$sql = "SELECT t.*, o.OnhandQty, o.RetailPrice, o.PromoPrice, p.ProductName
        FROM TransacTb t
        JOIN OnhandTb o ON t.OnhandID = o.OnhandID
        JOIN InventoryTb i ON o.InventoryID = i.InventoryID
        JOIN ProductTb p ON i.ProductID = p.ProductID
        WHERE t.Status = 'Pending'";

$stmt = $conn->prepare($sql);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle status change to approved
if (isset($_POST['approve_transaction'])) {
    $transac_id = $_POST['transac_id'];
    
    // Update the status to 'Approved'
    $update_sql = "UPDATE TransacTb SET Status = 'Approved' WHERE TransacID = :transac_id";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bindParam(':transac_id', $transac_id, PDO::PARAM_INT);
    
    if ($update_stmt->execute()) {
        echo "<script>alert('Transaction approved successfully!');</script>";
        echo"<script>window.history.back();</script>";
    } else {
        echo "<script>alert('Error: Could not approve transaction.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Transactions</title>
    <link rel="stylesheet" href="../../../../assets/css/form.css">
    <style>

    </style>
</head>
<body>
    <div class="container">
        <h3>Pending Transactions</h3>
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../../../views/admin_view.php#Transaction">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pending Transactions</li>
                <li class="breadcrumb-item"><a href="transac_read_approved.php">Approved Transactions</a></li>
                <li class="breadcrumb-item"><a href="transac_read_delivered.php">Delivered Transactions</a></li>
            </ol>
        </nav>
        <?php if (!empty($transactions)): ?>
            <h4 class="mt-4">Pending Transaction Records</h4>
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
                                        <form method="POST" action="" class="d-flex align-items-center">
                                            <input type="hidden" name="transac_id" value="<?= htmlspecialchars($transaction['TransacID']) ?>">
                                            <button type="submit" name="approve_transaction" class="btn btn-success btn-sm me-2" onclick="return confirm('Are you sure you want to mark this transaction as approved?');">
                                                <i class="bi bi-check-lg"></i> <!-- Approve icon -->
                                            </button>
                                            <a href="../transac_delete.php?id=<?= htmlspecialchars($transaction['TransacID']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this transaction?');">
                                                <i class="bi bi-trash"></i> <!-- Delete icon -->
                                            </a>
                                        </form>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <p class="mt-4">No pending transactions found.</p>
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
